const { proto, initAuthCreds, BufferJSON } = require('@whiskeysockets/baileys');

module.exports = async function useMySQLAuthState(pool, sessionId = 'primary') {
    // Helper to query
    const query = async (sql, params) => {
        const [rows] = await pool.query(sql, params);
        return rows;
    };

    const writeData = async (data, key) => {
        try {
            const value = JSON.stringify(data, BufferJSON.replacer);
            await query(
                `INSERT INTO whatsapp_sessions (session_id, key_id, value, created_at, updated_at) 
                 VALUES (?, ?, ?, NOW(), NOW()) 
                 ON DUPLICATE KEY UPDATE value = ?, updated_at = NOW()`,
                [sessionId, key, value, value]
            );
        } catch(e) {
            console.error('Error writing auth data', e);
        }
    };

    const readData = async (key) => {
        try {
            const rows = await query(
                `SELECT value FROM whatsapp_sessions WHERE session_id = ? AND key_id = ?`,
                [sessionId, key]
            );
            if (rows.length > 0) {
                return JSON.parse(rows[0].value, BufferJSON.reviver);
            }
        } catch(e) {
            console.error('Error reading auth data', e);
        }
        return null;
    };

    const removeData = async (key) => {
        try {
            await query(
                `DELETE FROM whatsapp_sessions WHERE session_id = ? AND key_id = ?`,
                [sessionId, key]
            );
        } catch(e) {
            console.error('Error removing auth data', e);
        }
    };

    const creds = (await readData('creds')) || initAuthCreds();

    return {
        state: {
            creds,
            keys: {
                get: async (type, ids) => {
                    const data = {};
                    await Promise.all(
                        ids.map(async (id) => {
                            let value = await readData(`${type}-${id}`);
                            if (type === 'app-state-sync-key' && value) {
                                value = proto.Message.AppStateSyncKeyData.fromObject(value);
                            }
                            if (value) {
                                data[id] = value;
                            }
                        })
                    );
                    return data;
                },
                set: async (data) => {
                    for (const type in data) {
                        for (const id in data[type]) {
                            const value = data[type][id];
                            const key = `${type}-${id}`;
                            if (value) {
                                await writeData(value, key);
                            } else {
                                await removeData(key);
                            }
                        }
                    }
                },
            },
        },
        saveCreds: () => {
            return writeData(creds, 'creds');
        },
    };
};
