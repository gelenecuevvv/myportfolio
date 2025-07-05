const { createClient } = require('@supabase/supabase-js');

// Initialize Supabase client
const supabase = createClient(
    process.env.SUPABASE_URL,
    process.env.SUPABASE_ANON_KEY
);

class Database {
    constructor() {
        this.supabase = supabase;
    }

    async testConnection() {
        try {
            const { data, error } = await this.supabase.from('contacts').select('count', { count: 'exact' });
            if (error) throw error;
            return { success: true, message: 'Database connected successfully' };
        } catch (error) {
            return { success: false, error: error.message };
        }
    }

    async createContact(contactData) {
        try {
            const { data, error } = await this.supabase
                .from('contacts')
                .insert([contactData])
                .select();
            
            if (error) throw error;
            return { success: true, data: data[0] };
        } catch (error) {
            return { success: false, error: error.message };
        }
    }

    async getAllContacts() {
        try {
            const { data, error } = await this.supabase
                .from('contacts')
                .select('*')
                .order('submitted_at', { ascending: false });
            
            if (error) throw error;
            return { success: true, data };
        } catch (error) {
            return { success: false, error: error.message };
        }
    }

    async getContactById(id) {
        try {
            const { data, error } = await this.supabase
                .from('contacts')
                .select('*')
                .eq('id', id)
                .single();
            
            if (error) throw error;
            return { success: true, data };
        } catch (error) {
            return { success: false, error: error.message };
        }
    }

    async updateContactStatus(id, status) {
        try {
            const { data, error } = await this.supabase
                .from('contacts')
                .update({ status })
                .eq('id', id)
                .select();
            
            if (error) throw error;
            return { success: true, data: data[0] };
        } catch (error) {
            return { success: false, error: error.message };
        }
    }

    async deleteContact(id) {
        try {
            const { error } = await this.supabase
                .from('contacts')
                .delete()
                .eq('id', id);
            
            if (error) throw error;
            return { success: true };
        } catch (error) {
            return { success: false, error: error.message };
        }
    }

    async checkRateLimit(ipAddress, minutes = 5, maxAttempts = 2) {
        try {
            const timeAgo = new Date();
            timeAgo.setMinutes(timeAgo.getMinutes() - minutes);
            
            const { data, error } = await this.supabase
                .from('contacts')
                .select('id')
                .eq('ip_address', ipAddress)
                .gte('submitted_at', timeAgo.toISOString());
            
            if (error) throw error;
            return { success: true, count: data.length, isBlocked: data.length > maxAttempts };
        } catch (error) {
            return { success: false, error: error.message };
        }
    }
}

module.exports = Database; 