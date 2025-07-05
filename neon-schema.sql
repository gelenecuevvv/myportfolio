-- Create contacts table for Neon PostgreSQL
CREATE TABLE contacts (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    status VARCHAR(20) DEFAULT 'new' CHECK (status IN ('new', 'read', 'replied'))
);

-- Create indexes for better performance
CREATE INDEX idx_contacts_email ON contacts(email);
CREATE INDEX idx_contacts_submitted_at ON contacts(submitted_at);
CREATE INDEX idx_contacts_status ON contacts(status);
CREATE INDEX idx_contacts_ip_address ON contacts(ip_address); 