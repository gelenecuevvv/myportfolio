const { Pool } = require('pg');

class Database {
    constructor() {
        this.pool = new Pool({
            connectionString: process.env.NETLIFY_DATABASE_URL,
            ssl: { rejectUnauthorized: false }
        });
    }

    async testConnection() {
        try {
            const client = await this.pool.connect();
            await client.query('SELECT 1');
            client.release();
            return { success: true, message: 'Database connected successfully' };
        } catch (error) {
            return { success: false, error: error.message };
        }
    }

    async createContact(contactData) {
        try {
            const client = await this.pool.connect();
            const query = `
                INSERT INTO contacts (name, email, message, ip_address, status, submitted_at)
                VALUES ($1, $2, $3, $4, $5, $6)
                RETURNING *
            `;
            const values = [
                contactData.name,
                contactData.email,
                contactData.message,
                contactData.ip_address,
                contactData.status || 'new',
                contactData.submitted_at
            ];
            
            const result = await client.query(query, values);
            client.release();
            return { success: true, data: result.rows[0] };
        } catch (error) {
            return { success: false, error: error.message };
        }
    }

    async getAllContacts() {
        try {
            const client = await this.pool.connect();
            const query = 'SELECT * FROM contacts ORDER BY submitted_at DESC';
            const result = await client.query(query);
            client.release();
            return { success: true, data: result.rows };
        } catch (error) {
            return { success: false, error: error.message };
        }
    }

    async getContactById(id) {
        try {
            const client = await this.pool.connect();
            const query = 'SELECT * FROM contacts WHERE id = $1';
            const result = await client.query(query, [id]);
            client.release();
            
            if (result.rows.length === 0) {
                return { success: false, error: 'Contact not found' };
            }
            
            return { success: true, data: result.rows[0] };
        } catch (error) {
            return { success: false, error: error.message };
        }
    }

    async updateContactStatus(id, status) {
        try {
            const client = await this.pool.connect();
            const query = 'UPDATE contacts SET status = $1 WHERE id = $2 RETURNING *';
            const result = await client.query(query, [status, id]);
            client.release();
            
            if (result.rows.length === 0) {
                return { success: false, error: 'Contact not found' };
            }
            
            return { success: true, data: result.rows[0] };
        } catch (error) {
            return { success: false, error: error.message };
        }
    }

    async deleteContact(id) {
        try {
            const client = await this.pool.connect();
            const query = 'DELETE FROM contacts WHERE id = $1';
            const result = await client.query(query, [id]);
            client.release();
            return { success: true };
        } catch (error) {
            return { success: false, error: error.message };
        }
    }

    async checkRateLimit(ipAddress, minutes = 5, maxAttempts = 2) {
        try {
            const client = await this.pool.connect();
            const timeAgo = new Date();
            timeAgo.setMinutes(timeAgo.getMinutes() - minutes);
            
            const query = `
                SELECT COUNT(*) as count 
                FROM contacts 
                WHERE ip_address = $1 AND submitted_at > $2
            `;
            const result = await client.query(query, [ipAddress, timeAgo.toISOString()]);
            client.release();
            
            const count = parseInt(result.rows[0].count);
            return { success: true, count, isBlocked: count > maxAttempts };
        } catch (error) {
            return { success: false, error: error.message };
        }
    }
}

module.exports = Database; 