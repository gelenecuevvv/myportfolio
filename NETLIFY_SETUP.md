# Portfolio Netlify Deployment Guide

This guide will help you deploy your PHP portfolio to Netlify using Netlify Functions and Supabase.

## Prerequisites

- Netlify account
- Supabase account (free tier available)
- Git repository for your code

## Step 1: Set Up Supabase Database

1. Go to [Supabase](https://supabase.com) and create a new project
2. Once your project is created, go to the SQL Editor
3. Run the following SQL to create the contacts table:

```sql
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
```

4. Note down your:
   - Project URL (found in Settings > API)
   - Public anon key (found in Settings > API)

## Step 2: Deploy to Netlify

1. **Connect Your Repository**
   - Go to Netlify dashboard
   - Click "New site from Git"
   - Connect your repository

2. **Configure Build Settings**
   - Build command: `npm install`
   - Publish directory: `.`
   - Functions directory: `netlify/functions`

3. **Set Environment Variables**
   Go to Site Settings > Environment Variables and add:
   ```
   SUPABASE_URL=your-supabase-project-url
   SUPABASE_ANON_KEY=your-supabase-anon-key
   ADMIN_API_KEY=your-secure-admin-key
   ```

4. **Deploy**
   - Click "Deploy site"
   - Wait for the build to complete

## Step 3: Install Dependencies

If you're developing locally, install the dependencies:

```bash
npm install
```

## Step 4: Test Your Setup

1. **Test Contact Form**
   - Visit your deployed site
   - Fill out the contact form
   - Check if the submission works

2. **Test Admin API** (optional)
   - Use a tool like Postman or curl:
   ```bash
   # Get all contacts
   curl -X GET https://your-site.netlify.app/.netlify/functions/admin-contacts \
     -H "X-API-Key: your-admin-api-key"
   
   # Update contact status
   curl -X PUT https://your-site.netlify.app/.netlify/functions/admin-contacts/1 \
     -H "X-API-Key: your-admin-api-key" \
     -H "Content-Type: application/json" \
     -d '{"status": "read"}'
   ```

## Step 5: Local Development

To run the site locally with Netlify Functions:

```bash
# Install Netlify CLI globally
npm install -g netlify-cli

# Run local development server
netlify dev
```

This will start a local server that simulates the Netlify environment.

## API Endpoints

After deployment, your API endpoints will be:

- **Contact Form**: `/.netlify/functions/submit-contact`
- **Admin API**: `/.netlify/functions/admin-contacts`

## Email Notifications (Optional)

To enable email notifications when someone submits the contact form:

1. Choose an email service (SendGrid, Mailgun, etc.)
2. Update the `sendEmailNotification` function in `netlify/functions/submit-contact.js`
3. Add the necessary API keys to your environment variables

## Security Notes

1. **Admin API Key**: Use a strong, unique key for the admin API
2. **Database Security**: The current setup uses RLS policies that allow public access. For production, implement proper authentication
3. **Rate Limiting**: The current rate limiting is basic. Consider implementing more sophisticated rate limiting for production

## Troubleshooting

1. **Function Errors**: Check the Netlify Functions logs in your dashboard
2. **Database Connection**: Verify your Supabase environment variables
3. **CORS Issues**: The functions include CORS headers, but you may need to adjust them for your domain

## Migration from PHP

Your original PHP files are no longer needed for the Netlify deployment:
- `submit_contact.php` → `/.netlify/functions/submit-contact`
- `admin/contacts.php` → `/.netlify/functions/admin-contacts`
- `config/database.php` → `netlify/functions/lib/database.js`
- `classes/Contact.php` → `netlify/functions/lib/contact.js`

You can keep the original PHP files for backup or remove them from your repository.

## Next Steps

1. Test all functionality thoroughly
2. Implement proper authentication for admin functions
3. Set up monitoring and logging
4. Consider adding more advanced features like email templates, file uploads, etc. 