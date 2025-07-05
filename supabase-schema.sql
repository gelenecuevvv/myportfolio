-- Create contacts table
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

-- Enable Row Level Security (RLS)
ALTER TABLE contacts ENABLE ROW LEVEL SECURITY;

-- Create policy for public insert (contact form submissions)
CREATE POLICY "Enable insert for contact form" ON contacts
    FOR INSERT WITH CHECK (true);

-- Create policy for admin read (you'll need to implement proper auth)
CREATE POLICY "Enable read for admin" ON contacts
    FOR SELECT USING (true);

-- Create policy for admin update
CREATE POLICY "Enable update for admin" ON contacts
    FOR UPDATE USING (true);

-- Create policy for admin delete
CREATE POLICY "Enable delete for admin" ON contacts
    FOR DELETE USING (true); 