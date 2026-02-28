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

// In-memory log buffer for the /logs endpoint (capped at 100 entries)
const MAX_LOGS = 100;
let logBuffer = [];

function logToFile(msg) {
    const timestamp = new Date().toISOString();
    logStream.write(`[${timestamp}] ${msg}\n`);
    console.log(msg);

    // Store in buffer for /logs endpoint
    logBuffer.push(`[${timestamp.split('T')[1].split('.')[0]}] ${msg}`);
    if (logBuffer.length > MAX_LOGS) {
        logBuffer = logBuffer.slice(-MAX_LOGS);
    }
}

app.use(cors());
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));

let sock = null;
let qrCode = null;
let connectionStatus = "disconnected";
let connectedNumber = null;
let isLoggingOut = false;
let connectionId = 0; // Track which connection instance is active

async function connectToWhatsApp() {
    // Increment connection ID to track this specific connection instance
    const myConnectionId = ++connectionId;
    logToFile(`Starting connection #${myConnectionId} to WhatsApp...`);

    try {
        // Clean up old socket if it exists
        if (sock) {
            try {
                sock.ev.removeAllListeners();
                sock.ws.close();
            } catch (e) {
                // Ignore cleanup errors
            }
            sock = null;
        }

        connectionStatus = "connecting";
        qrCode = null;

        const authPath = path.join(__dirname, "auth_info_baileys");
        const { state, saveCreds } = await useMultiFileAuthState(authPath);
        const { version } = await fetchLatestBaileysVersion();

        const newSock = makeWASocket({
            version,
            printQRInTerminal: true,
            auth: {
                creds: state.creds,
                keys: makeCacheableSignalKeyStore(state.keys, pino({ level: "silent" })),
            },
            logger: pino({ level: "silent" }),
        });

        // Only assign if this is still the active connection
        if (myConnectionId !== connectionId) {
            logToFile(`Connection #${myConnectionId} superseded, cleaning up.`);
            try { newSock.ev.removeAllListeners(); newSock.ws.close(); } catch (e) { }
            return;
        }

        sock = newSock;

        sock.ev.on("connection.update", async (update) => {
            // Ignore events from stale connections
            if (myConnectionId !== connectionId) return;

            const { connection, lastDisconnect, qr } = update;

            if (qr) {
                logToFile(`QR code received for connection #${myConnectionId}`);
                qrCode = await qrcode.toDataURL(qr);
                connectionStatus = "qr";
            }

            if (connection === "connecting") {
                connectionStatus = qrCode ? "qr" : "connecting";
                logToFile("Connecting to WhatsApp...");
            } else if (connection === "close") {
                const statusCode = lastDisconnect?.error?.output?.statusCode;
                const shouldReconnect = statusCode !== DisconnectReason.loggedOut;

                logToFile(`Connection #${myConnectionId} closed. Reason: ${statusCode}, shouldReconnect: ${shouldReconnect}, isLoggingOut: ${isLoggingOut}`);

                connectionStatus = "disconnected";
                connectedNumber = null;

                // Handle stale auth: if loggedOut (401) but NOT user-initiated,
                // clear auth data and reconnect to get a fresh QR code
                if (!isLoggingOut && !shouldReconnect && myConnectionId === connectionId) {
                    qrCode = null;
                    const authPath = path.join(__dirname, "auth_info_baileys");
                    if (fs.existsSync(authPath)) {
                        fs.rmSync(authPath, { recursive: true, force: true });
                        logToFile("Stale auth detected (401). Auth data cleared, reconnecting for fresh QR...");
                    }
                    setTimeout(() => {
                        if (myConnectionId === connectionId) {
                            connectToWhatsApp();
                        }
                    }, 3000);
                }

                // Auto-reconnect on other disconnect reasons (network errors, etc.)
                if (!isLoggingOut && shouldReconnect && myConnectionId === connectionId) {
                    qrCode = null;
                    logToFile("Auto-reconnecting...");
                    setTimeout(() => {
                        if (myConnectionId === connectionId) {
                            connectToWhatsApp();
                        }
                    }, 3000);
                }
            } else if (connection === "open") {
                connectionStatus = "connected";
                qrCode = null;
                connectedNumber = sock?.user?.id;
                logToFile("WhatsApp connected as: " + connectedNumber);
            }
        });

        sock.ev.on("creds.update", saveCreds);
    } catch (err) {
        logToFile("Fatal Connection Error: " + err.message);
        connectionStatus = "disconnected";

        // Auto-retry on fatal errors (e.g., network issues on shared hosting)
        if (!isLoggingOut && myConnectionId === connectionId) {
            logToFile("Retrying in 10 seconds...");
            setTimeout(() => {
                if (myConnectionId === connectionId) {
                    connectToWhatsApp();
                }
            }, 10000);
        }
    }
}

// API Endpoints
app.get("/", (req, res) => {
    res.setHeader('Content-Type', 'text/html');
    res.send("WhatsApp Gateway is running.");
});

app.get("/status", (req, res) => {
    res.json({
        status: connectionStatus,
        qr: qrCode,
        number: connectedNumber ? connectedNumber.split(':')[0] : null
    });
});

app.get("/logs", (req, res) => {
    res.json({
        logs: logBuffer
    });
});

app.post("/send", async (req, res) => {
    const { number, message } = req.body;

    if (connectionStatus !== "connected") {
        return res.status(500).json({ status: false, message: "WhatsApp not connected" });
    }

    try {
        const jid = number.includes("@s.whatsapp.net") ? number : `${number}@s.whatsapp.net`;
        await sock.sendMessage(jid, { text: message });
        logToFile(`Message sent to ${number}`);
        res.json({ status: true, message: "Message sent" });
    } catch (error) {
        logToFile(`Send error: ${error.message}`);
        res.status(500).json({ status: false, message: error.message });
    }
});

app.post("/logout", async (req, res) => {
    try {
        logToFile("Logout requested...");
        isLoggingOut = true;

        // Reset state immediately
        connectionStatus = "disconnected";
        qrCode = null;
        connectedNumber = null;

        // Try to logout gracefully
        if (sock) {
            try {
                sock.ev.removeAllListeners();
                await sock.logout();
            } catch (logoutErr) {
                logToFile("Logout error (non-fatal): " + logoutErr.message);
            }
            try { sock.ws.close(); } catch (e) { }
            sock = null;
        }

        // Remove auth data to force fresh QR on reconnect
        const authPath = path.join(__dirname, "auth_info_baileys");
        if (fs.existsSync(authPath)) {
            fs.rmSync(authPath, { recursive: true, force: true });
            logToFile("Auth data cleared.");
        }

        res.json({ status: true, message: "Logged out" });

        // Wait for everything to settle, then start fresh connection
        setTimeout(() => {
            isLoggingOut = false;
            logToFile("Starting fresh connection after logout...");
            connectToWhatsApp();
        }, 3000);
    } catch (error) {
        isLoggingOut = false;
        logToFile("Logout fatal error: " + error.message);
        res.status(500).json({ status: false, message: error.message });
    }
});

app.listen(port, () => {
    console.log(`WhatsApp Gateway listening at http://localhost:${port}`);
    connectToWhatsApp();
});
