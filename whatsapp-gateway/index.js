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

app.use(cors());
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));

let sock;
let qrCode = null;
let connectionStatus = "disconnected";
let connectedNumber = null;

async function connectToWhatsApp() {
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
            console.log("Connecting to WhatsApp...");
        } else if (connection === "close") {
            const statusCode = lastDisconnect?.error?.output?.statusCode;
            const shouldReconnect = statusCode !== DisconnectReason.loggedOut;

            console.log(`Connection closed. Reason: ${statusCode}, Reconnecting: ${shouldReconnect}`);

            connectionStatus = "disconnected";
            qrCode = null;
            connectedNumber = null;

            if (shouldReconnect) {
                connectToWhatsApp();
            }
        } else if (connection === "open") {
            connectionStatus = "connected";
            qrCode = null;
            connectedNumber = sock.user?.id;
            console.log("WhatsApp connected as: " + connectedNumber);
        }
    });

    sock.ev.on("creds.update", saveCreds);
}

// API Endpoints
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
        await sock.logout();
        fs.rmSync(path.join(__dirname, "auth_info_baileys"), { recursive: true, force: true });
        res.json({ status: true, message: "Logged out" });
        connectToWhatsApp();
    } catch (error) {
        res.status(500).json({ status: false, message: error.message });
    }
});

app.listen(port, () => {
    console.log(`WhatsApp Gateway listening at http://localhost:${port}`);
    connectToWhatsApp();
});
