const Database = require('./database');

class Contact {
    constructor() {
        this.db = new Database();
        this.name = '';
        this.email = '';
        this.message = '';
        this.ip_address = '';
        this.status = 'new';
    }

    // Validate email format
    validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // Sanitize input - remove HTML tags and trim whitespace
    sanitizeInput(input) {
        if (typeof input !== 'string') return '';
        return input.replace(/<[^>]*>/g, '').trim();
    }

    // Check for spam using basic keyword detection
    isSpam() {
        const spamWords = ['viagra', 'casino', 'lottery', 'winner', 'congratulations', 'prize', 'million', 'investment', 'urgent', 'click here'];
        const text = (this.name + ' ' + this.email + ' ' + this.message).toLowerCase();
        
        return spamWords.some(word => text.includes(word));
    }

    // Validate all contact data
    validateContactData() {
        const errors = [];

        // Validate name
        if (!this.name || this.name.length < 2) {
            errors.push('Name must be at least 2 characters long');
        }
        if (this.name.length > 100) {
            errors.push('Name must be less than 100 characters');
        }

        // Validate email
        if (!this.email) {
            errors.push('Email is required');
        } else if (!this.validateEmail(this.email)) {
            errors.push('Please enter a valid email address');
        }

        // Validate message
        if (!this.message || this.message.length < 10) {
            errors.push('Message must be at least 10 characters long');
        }
        if (this.message.length > 1000) {
            errors.push('Message must be less than 1000 characters');
        }

        return errors;
    }

    // Create new contact
    async create(contactData) {
        try {
            // Set and sanitize properties
            this.name = this.sanitizeInput(contactData.name);
            this.email = this.sanitizeInput(contactData.email);
            this.message = this.sanitizeInput(contactData.message);
            this.ip_address = contactData.ip_address || 'unknown';

            // Validate data
            const validationErrors = this.validateContactData();
            if (validationErrors.length > 0) {
                return {
                    success: false,
                    error: validationErrors.join(', ')
                };
            }

            // Check for spam
            if (this.isSpam()) {
                return {
                    success: false,
                    error: 'Your message appears to be spam. Please try again.'
                };
            }

            // Check rate limiting
            const rateLimitCheck = await this.db.checkRateLimit(this.ip_address);
            if (!rateLimitCheck.success) {
                return {
                    success: false,
                    error: 'Database error during rate limit check'
                };
            }

            if (rateLimitCheck.isBlocked) {
                return {
                    success: false,
                    error: 'Too many submissions. Please wait 5 minutes before submitting again.'
                };
            }

            // Create contact in database
            const contactRecord = {
                name: this.name,
                email: this.email,
                message: this.message,
                ip_address: this.ip_address,
                status: this.status,
                submitted_at: new Date().toISOString()
            };

            const result = await this.db.createContact(contactRecord);
            
            if (result.success) {
                return {
                    success: true,
                    message: 'Thank you for your message! I will get back to you soon.',
                    data: result.data
                };
            } else {
                return {
                    success: false,
                    error: 'Failed to save contact. Please try again.'
                };
            }

        } catch (error) {
            console.error('Error creating contact:', error);
            return {
                success: false,
                error: 'An unexpected error occurred. Please try again.'
            };
        }
    }

    // Get all contacts
    async getAll() {
        try {
            const result = await this.db.getAllContacts();
            return result;
        } catch (error) {
            console.error('Error fetching contacts:', error);
            return {
                success: false,
                error: 'Failed to fetch contacts'
            };
        }
    }

    // Get contact by ID
    async getById(id) {
        try {
            const result = await this.db.getContactById(id);
            return result;
        } catch (error) {
            console.error('Error fetching contact:', error);
            return {
                success: false,
                error: 'Failed to fetch contact'
            };
        }
    }

    // Update contact status
    async updateStatus(id, status) {
        try {
            const validStatuses = ['new', 'read', 'replied'];
            if (!validStatuses.includes(status)) {
                return {
                    success: false,
                    error: 'Invalid status value'
                };
            }

            const result = await this.db.updateContactStatus(id, status);
            return result;
        } catch (error) {
            console.error('Error updating contact status:', error);
            return {
                success: false,
                error: 'Failed to update contact status'
            };
        }
    }

    // Delete contact
    async delete(id) {
        try {
            const result = await this.db.deleteContact(id);
            return result;
        } catch (error) {
            console.error('Error deleting contact:', error);
            return {
                success: false,
                error: 'Failed to delete contact'
            };
        }
    }
}

module.exports = Contact; 