const path = require('path');
const fs = require('fs');

// Load .env from Laravel root (parent directory)
const envPath = path.resolve(__dirname, '..', '.env');
if (fs.existsSync(envPath)) {
    require('dotenv').config({ path: envPath });
} else {
    // Fallback: try current directory or default
    require('dotenv').config();
}

const express = require('express');
const { default: makeWASocket, DisconnectReason } = require('@whiskeysockets/baileys');
const QRCode = require('qrcode');
const mysql = require('mysql2/promise');
const cors = require('cors');
const useMySQLAuthState = require('./db_auth'); // Same folder
const pino = require('pino');

const app = express();
app.use(express.json());
app.use(cors());

const pool = mysql.createPool({
    host: process.env.DB_HOST || '127.0.0.1',
    user: process.env.DB_USER || 'root',
    password: process.env.DB_PASSWORD || '',
    database: process.env.DB_NAME || 'mikbill'
});

let isConnecting = false;

async function connectToWhatsApp() {
    if (isConnecting) return;
    isConnecting = true;

    try {
        console.log('Initializing connection...');
        const { state, saveCreds } = await useMySQLAuthState(pool, 'admin');

        sock = makeWASocket({
            auth: state,
            printQRInTerminal: true,
            logger: pino({ level: 'silent' }),
            browser: ['MikBill Gateway', 'Chrome', '1.0.0'],
            connectTimeoutMs: 60000,
            defaultQueryTimeoutMs: 60000,
            retryRequestDelayMs: 250
        });

        sock.ev.on('connection.update', async (update) => {
            const { connection, lastDisconnect, qr } = update;

            if (qr) {
                qrCodeData = qr;
                status = 'QR_READY';
                console.log('QR Code generated');
            }

            if (connection === 'close') {
                const shouldReconnect = (lastDisconnect.error)?.output?.statusCode !== DisconnectReason.loggedOut;
                console.log('Connection closed due to ', lastDisconnect.error, ', reconnecting ', shouldReconnect);
                status = 'DISCONNECTED';
                qrCodeData = null;

                if (shouldReconnect) {
                    setTimeout(() => { isConnecting = false; connectToWhatsApp(); }, 3000);
                } else {
                    console.log('Logged out. Clearing session...');
                    await pool.query("DELETE FROM whatsapp_sessions WHERE session_id = 'admin'");
                    status = 'DISCONNECTED';
                    setTimeout(() => { isConnecting = false; connectToWhatsApp(); }, 1000);
                }
            } else if (connection === 'open') {
                console.log('Opened connection');
                status = 'CONNECTED';
                qrCodeData = null;
                isConnecting = false;
            }
        });

        sock.ev.on('creds.update', saveCreds);

    } catch (error) {
        console.error('Error connecting:', error);
        isConnecting = false;
        setTimeout(() => { isConnecting = false; connectToWhatsApp(); }, 5000);
    }
}


// Start connection
connectToWhatsApp();

app.post('/logout', async (req, res) => {
    try {
        if (sock) {
            await sock.logout(); // Will trigger connection.update 'close' with loggedOut reason
        }
        // Force clear DB just in case
        await pool.query("DELETE FROM whatsapp_sessions WHERE session_id = 'admin'");
        res.json({ status: true, message: 'Logged out' });
    } catch (e) {
        // If sock.logout fails (e.g. not connected), just clear DB
        await pool.query("DELETE FROM whatsapp_sessions WHERE session_id = 'admin'");
        // Re-init to get QR
        connectToWhatsApp();
        res.json({ status: true, message: 'Forced logout. QR will be generated.' });
    }
});

app.get('/qr', async (req, res) => {
    if (status === 'CONNECTED') {
        return res.json({ status: 'CONNECTED', message: 'Already connected' });
    }
    if (qrCodeData) {
        try {
            const url = await QRCode.toDataURL(qrCodeData);
            return res.json({ status: 'QR_READY', qr_code: url });
        } catch (err) {
            return res.status(500).json({ error: 'Failed to generate QR image' });
        }
    }
    return res.json({ status: 'WAITING', message: 'Generating QR...' });
});

app.get('/status', (req, res) => {
    res.json({ status });
});

app.post('/send', async (req, res) => {
    const { number, message } = req.body;
    if (status !== 'CONNECTED') {
        return res.status(400).json({ status: false, message: 'WhatsApp not connected' });
    }

    try {
        let id = number;
        if (!id.includes('@s.whatsapp.net')) {
            // Simple formatting
            if (id.startsWith('0')) id = '62' + id.slice(1);
            id = id + '@s.whatsapp.net';
        }

        await sock.sendMessage(id, { text: message });
        res.json({ status: true, message: 'Message sent' });
    } catch (e) {
        res.status(500).json({ status: false, message: e.message });
    }
});

app.post('/restart', (req, res) => {
    process.exit(0);
});

app.post('/shutdown', (req, res) => {
    res.json({ status: true, message: 'Shutting down...' });
    console.log('Received shutdown signal via HTTP.');
    setTimeout(() => {
        process.exit(0);
    }, 500);
});

app.listen(3000, () => {
    // Write PID file to gateway folder
    const pidPath = path.join(__dirname, 'gateway.pid');
    fs.writeFileSync(pidPath, process.pid.toString());
    console.log('WhatsApp Gateway running on port 3000. PID:', process.pid);
});
