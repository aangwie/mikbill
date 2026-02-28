const {
    default: makeWASocket,
    useMultiFileAuthState,
    DisconnectReason,
    fetchLatestBaileysVersion,
    makeCacheableSignalKeyStore,
} = require("@whiskeysockets/baileys");
const express = require("express");
const qrcode = require("qrcode");
const pino = require("pino");
const cors = require("cors");
const bodyParser = require("body-parser");
const fs = require("fs");
const path = require("path");

const app = express();
const port = process.env.PORT || 3000;

// Simple file logging for shared hosting debugging
const logFile = path.join(__dirname, "gateway.log");
const logStream = fs.createWriteStream(logFile, { flags: "a" });

function logToFile(msg) {
    const timestamp = new Date().toISOString();
    logStream.write(`[${timestamp}] ${msg}\n`);
    console.log(msg);
}

app.use(cors());
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));

// ============================================================
// MULTI-SESSION MANAGEMENT
// ============================================================
const MAX_LOGS = 100;
const sessions = new Map(); // Map<sessionId, SessionObject>

function getSession(sessionId) {
    return sessions.get(sessionId) || null;
}

function createSession(sessionId) {
    const session = {
        sock: null,
        qrCode: null,
        connectionStatus: "disconnected",
        connectedNumber: null,
        isLoggingOut: false,
        connectionId: 0,
        logBuffer: [],
    };
    sessions.set(sessionId, session);
    return session;
}

function sessionLog(sessionId, msg) {
    const fullMsg = `[${sessionId}] ${msg}`;
    logToFile(fullMsg);

    const session = getSession(sessionId);
    if (session) {
        const time = new Date().toISOString().split('T')[1].split('.')[0];
        session.logBuffer.push(`[${time}] ${msg}`);
        if (session.logBuffer.length > MAX_LOGS) {
            session.logBuffer = session.logBuffer.slice(-MAX_LOGS);
        }
    }
}

async function connectSession(sessionId) {
    let session = getSession(sessionId);
    if (!session) {
        session = createSession(sessionId);
    }

    // Increment connection ID to track this specific connection instance
    const myConnectionId = ++session.connectionId;
    sessionLog(sessionId, `Starting connection #${myConnectionId} to WhatsApp...`);

    try {
        // Clean up old socket if it exists
        if (session.sock) {
            try {
                session.sock.ev.removeAllListeners();
                session.sock.ws.close();
            } catch (e) {
                // Ignore cleanup errors
            }
            session.sock = null;
        }

        session.connectionStatus = "connecting";
        session.qrCode = null;

        const authPath = path.join(__dirname, "auth_info_baileys", sessionId);
        if (!fs.existsSync(authPath)) {
            fs.mkdirSync(authPath, { recursive: true });
        }

        const { state, saveCreds } = await useMultiFileAuthState(authPath);
        const { version } = await fetchLatestBaileysVersion();

        const newSock = makeWASocket({
            version,
            printQRInTerminal: false,
            auth: {
                creds: state.creds,
                keys: makeCacheableSignalKeyStore(state.keys, pino({ level: "silent" })),
            },
            logger: pino({ level: "silent" }),
        });

        // Only assign if this is still the active connection for this session
        if (myConnectionId !== session.connectionId) {
            sessionLog(sessionId, `Connection #${myConnectionId} superseded, cleaning up.`);
            try { newSock.ev.removeAllListeners(); newSock.ws.close(); } catch (e) { }
            return;
        }

        session.sock = newSock;

        session.sock.ev.on("connection.update", async (update) => {
            // Ignore events from stale connections
            if (myConnectionId !== session.connectionId) return;

            const { connection, lastDisconnect, qr } = update;

            if (qr) {
                sessionLog(sessionId, `QR code received for connection #${myConnectionId}`);
                session.qrCode = await qrcode.toDataURL(qr);
                session.connectionStatus = "qr";
            }

            if (connection === "connecting") {
                session.connectionStatus = session.qrCode ? "qr" : "connecting";
                sessionLog(sessionId, "Connecting to WhatsApp...");
            } else if (connection === "close") {
                const statusCode = lastDisconnect?.error?.output?.statusCode;
                const shouldReconnect = statusCode !== DisconnectReason.loggedOut;

                sessionLog(sessionId, `Connection #${myConnectionId} closed. Reason: ${statusCode}, shouldReconnect: ${shouldReconnect}, isLoggingOut: ${session.isLoggingOut}`);

                session.connectionStatus = "disconnected";
                session.connectedNumber = null;

                // Handle stale auth: if loggedOut (401) but NOT user-initiated,
                // clear auth data and reconnect to get a fresh QR code
                if (!session.isLoggingOut && !shouldReconnect && myConnectionId === session.connectionId) {
                    session.qrCode = null;
                    const authPath = path.join(__dirname, "auth_info_baileys", sessionId);
                    if (fs.existsSync(authPath)) {
                        fs.rmSync(authPath, { recursive: true, force: true });
                        sessionLog(sessionId, "Stale auth detected (401). Auth data cleared, reconnecting for fresh QR...");
                    }
                    setTimeout(() => {
                        if (myConnectionId === session.connectionId) {
                            connectSession(sessionId);
                        }
                    }, 3000);
                }

                // Auto-reconnect on other disconnect reasons (network errors, etc.)
                if (!session.isLoggingOut && shouldReconnect && myConnectionId === session.connectionId) {
                    session.qrCode = null;
                    sessionLog(sessionId, "Auto-reconnecting...");
                    setTimeout(() => {
                        if (myConnectionId === session.connectionId) {
                            connectSession(sessionId);
                        }
                    }, 3000);
                }
            } else if (connection === "open") {
                session.connectionStatus = "connected";
                session.qrCode = null;
                session.connectedNumber = session.sock?.user?.id;
                sessionLog(sessionId, "WhatsApp connected as: " + session.connectedNumber);
            }
        });

        session.sock.ev.on("creds.update", saveCreds);
    } catch (err) {
        sessionLog(sessionId, "Fatal Connection Error: " + err.message);
        session.connectionStatus = "disconnected";

        // Auto-retry on fatal errors (e.g., network issues on shared hosting)
        if (!session.isLoggingOut && myConnectionId === session.connectionId) {
            sessionLog(sessionId, "Retrying in 10 seconds...");
            setTimeout(() => {
                if (myConnectionId === session.connectionId) {
                    connectSession(sessionId);
                }
            }, 10000);
        }
    }
}

// ============================================================
// API ENDPOINTS
// ============================================================

app.get("/", (req, res) => {
    res.setHeader('Content-Type', 'text/html');
    const sessionCount = sessions.size;
    res.send(`WhatsApp Gateway is running. Active sessions: ${sessionCount}`);
});

app.get("/status", (req, res) => {
    const sessionId = req.query.session;
    if (!sessionId) {
        return res.status(400).json({ status: "disconnected", message: "Missing session parameter" });
    }

    let session = getSession(sessionId);

    // Auto-create and connect session on first status check
    if (!session) {
        createSession(sessionId);
        connectSession(sessionId);
        session = getSession(sessionId);
    }

    res.json({
        status: session.connectionStatus,
        qr: session.qrCode,
        number: session.connectedNumber ? session.connectedNumber.split(':')[0] : null,
    });
});

app.get("/logs", (req, res) => {
    const sessionId = req.query.session;
    if (!sessionId) {
        return res.json({ logs: [] });
    }

    const session = getSession(sessionId);
    res.json({
        logs: session ? session.logBuffer : [],
    });
});

app.post("/send", async (req, res) => {
    const { number, message, session: sessionId } = req.body;

    if (!sessionId) {
        return res.status(400).json({ status: false, message: "Missing session parameter" });
    }

    const session = getSession(sessionId);
    if (!session || session.connectionStatus !== "connected") {
        return res.status(500).json({ status: false, message: "WhatsApp not connected for this session" });
    }

    try {
        const jid = number.includes("@s.whatsapp.net") ? number : `${number}@s.whatsapp.net`;
        await session.sock.sendMessage(jid, { text: message });
        sessionLog(sessionId, `Message sent to ${number}`);
        res.json({ status: true, message: "Message sent" });
    } catch (error) {
        sessionLog(sessionId, `Send error: ${error.message}`);
        res.status(500).json({ status: false, message: error.message });
    }
});

app.post("/logout", async (req, res) => {
    const sessionId = req.body.session;
    if (!sessionId) {
        return res.status(400).json({ status: false, message: "Missing session parameter" });
    }

    const session = getSession(sessionId);
    if (!session) {
        return res.status(404).json({ status: false, message: "Session not found" });
    }

    try {
        sessionLog(sessionId, "Logout requested...");
        session.isLoggingOut = true;

        // Reset state immediately
        session.connectionStatus = "disconnected";
        session.qrCode = null;
        session.connectedNumber = null;

        // Try to logout gracefully
        if (session.sock) {
            try {
                session.sock.ev.removeAllListeners();
                await session.sock.logout();
            } catch (logoutErr) {
                sessionLog(sessionId, "Logout error (non-fatal): " + logoutErr.message);
            }
            try { session.sock.ws.close(); } catch (e) { }
            session.sock = null;
        }

        // Remove auth data to force fresh QR on reconnect
        const authPath = path.join(__dirname, "auth_info_baileys", sessionId);
        if (fs.existsSync(authPath)) {
            fs.rmSync(authPath, { recursive: true, force: true });
            sessionLog(sessionId, "Auth data cleared.");
        }

        res.json({ status: true, message: "Logged out" });

        // Wait for everything to settle, then start fresh connection
        setTimeout(() => {
            session.isLoggingOut = false;
            sessionLog(sessionId, "Starting fresh connection after logout...");
            connectSession(sessionId);
        }, 3000);
    } catch (error) {
        session.isLoggingOut = false;
        sessionLog(sessionId, "Logout fatal error: " + error.message);
        res.status(500).json({ status: false, message: error.message });
    }
});

app.listen(port, () => {
    console.log(`WhatsApp Gateway listening at http://localhost:${port}`);
    // No auto-connect at startup — sessions connect lazily on first /status call
});
