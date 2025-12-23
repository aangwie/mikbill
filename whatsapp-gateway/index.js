require('dotenv').config();
const express = require('express');
const { default: makeWASocket, DisconnectReason } = require('@whiskeysockets/baileys');
const QRCode = require('qrcode');
const mysql = require('mysql2/promise');
const cors = require('cors');
const useMySQLAuthState = require('./db_auth');
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

let sock;
let qrCodeData = null;
let status = 'DISCONNECTED';

async function connectToWhatsApp() {
    const { state, saveCreds } = await useMySQLAuthState(pool, 'admin');

    sock = makeWASocket({
        auth: state,
        printQRInTerminal: true,
        logger: pino({ level: 'silent' }),
        browser: ['MikBill Gateway', 'Chrome', '1.0.0']
    });

    sock.ev.on('connection.update', (update) => {
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
                setTimeout(connectToWhatsApp, 3000); // Retry logic
            }
        } else if (connection === 'open') {
            console.log('Opened connection');
            status = 'CONNECTED';
            qrCodeData = null;
        }
    });

    sock.ev.on('creds.update', saveCreds);
}

// Start connection
connectToWhatsApp();

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
    process.exit(0); // Simple restart mechanism if using PM2, otherwise just stops. 
    // In dev environment this stops the script.
});

app.listen(3000, () => {
    console.log('WhatsApp Gateway running on port 3000');
});
