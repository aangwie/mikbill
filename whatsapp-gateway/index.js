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

let sock;
let qrCode = null;
let connectionStatus = "disconnected";
let connectedNumber = null;
let isLoggingOut = false;

async function connectToWhatsApp() {
    logToFile("Starting connection to WhatsApp...");
    try {
        connectionStatus = "connecting";
        const { state, saveCreds } = await useMultiFileAuthState(path.join(__dirname, "auth_info_baileys"));
        const { version, isLatest } = await fetchLatestBaileysVersion();

        sock = makeWASocket({
            version,
            printQRInTerminal: true,
            auth: {
                creds: state.creds,
                keys: makeCacheableSignalKeyStore(state.keys, pino({ level: "silent" })),
            },
            logger: pino({ level: "silent" }),
        });

        sock.ev.on("connection.update", async (update) => {
            const { connection, lastDisconnect, qr } = update;

            if (qr) {
                qrCode = await qrcode.toDataURL(qr);
                connectionStatus = "connecting";
            }

            if (connection === "connecting") {
                connectionStatus = "connecting";
                logToFile("Connecting to WhatsApp...");
            } else if (connection === "close") {
                const statusCode = lastDisconnect?.error?.output?.statusCode;
                const shouldReconnect = statusCode !== DisconnectReason.loggedOut;

                logToFile(`Connection closed. Reason: ${statusCode}, Reconnecting: ${shouldReconnect}, isLoggingOut: ${isLoggingOut}`);

                connectionStatus = "disconnected";
                connectedNumber = null;

                // Only auto-reconnect if not manually logging out
                // (logout handler manages its own reconnect with delay)
                if (!isLoggingOut && shouldReconnect) {
                    qrCode = null;
                    connectToWhatsApp();
                }
            } else if (connection === "open") {
                connectionStatus = "connected";
                qrCode = null;
                connectedNumber = sock.user?.id;
                logToFile("WhatsApp connected as: " + connectedNumber);
            }
        });

        sock.ev.on("creds.update", saveCreds);
    } catch (err) {
        logToFile("Fatal Connection Error: " + err.message);
    }
}

// API Endpoints
app.get("/", (req, res) => {
    // CloudLinux check expects exact content-type matching often
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

app.post("/send", async (req, res) => {
    const { number, message } = req.body;

    if (connectionStatus !== "connected") {
        return res.status(500).json({ status: false, message: "WhatsApp not connected" });
    }

    try {
        const jid = number.includes("@s.whatsapp.net") ? number : `${number}@s.whatsapp.net`;
        await sock.sendMessage(jid, { text: message });
        res.json({ status: true, message: "Message sent" });
    } catch (error) {
        res.status(500).json({ status: false, message: error.message });
    }
});

app.post("/logout", async (req, res) => {
    try {
        // Set flag to prevent close handler from auto-reconnecting
        isLoggingOut = true;

        // Reset state immediately
        connectionStatus = "disconnected";
        qrCode = null;
        connectedNumber = null;

        try {
            if (sock) {
                await sock.logout();
            }
        } catch (logoutErr) {
            logToFile("Logout error (non-fatal): " + logoutErr.message);
        }

        // Remove auth data to force fresh QR on reconnect
        const authPath = path.join(__dirname, "auth_info_baileys");
        if (fs.existsSync(authPath)) {
            fs.rmSync(authPath, { recursive: true, force: true });
        }

        res.json({ status: true, message: "Logged out" });

        // Wait for old socket close events to finish before reconnecting
        setTimeout(() => {
            isLoggingOut = false;
            logToFile("Reconnecting after logout...");
            connectToWhatsApp();
        }, 2000);
    } catch (error) {
        res.status(500).json({ status: false, message: error.message });
    }
});

app.listen(port, () => {
    console.log(`WhatsApp Gateway listening at http://localhost:${port}`);
    connectToWhatsApp();
});
