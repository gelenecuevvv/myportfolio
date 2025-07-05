const Contact = require('./lib/contact');

// Helper function to parse form data
function parseFormData(body) {
    const params = new URLSearchParams(body);
    return {
        name: params.get('name'),
        email: params.get('email'),
        message: params.get('message')
    };
}

// Helper function to parse JSON data
function parseJSON(body) {
    try {
        return JSON.parse(body);
    } catch (error) {
        return null;
    }
}

// Get client IP address
function getClientIP(headers) {
    return headers['x-forwarded-for'] || 
           headers['x-real-ip'] || 
           headers['x-client-ip'] || 
           'unknown';
}

// Send email notification (optional)
async function sendEmailNotification(contactData) {
    // You can implement email sending here using services like:
    // - SendGrid
    // - Mailgun
    // - AWS SES
    // - Nodemailer with SMTP
    
    // For now, we'll just log it
    console.log('New contact submission:', {
        name: contactData.name,
        email: contactData.email,
        message: contactData.message,
        timestamp: new Date().toISOString()
    });
}

exports.handler = async (event, context) => {
    // Set CORS headers
    const headers = {
        'Access-Control-Allow-Origin': '*',
        'Access-Control-Allow-Headers': 'Content-Type',
        'Access-Control-Allow-Methods': 'POST, OPTIONS',
        'Content-Type': 'application/json'
    };

    // Handle preflight requests
    if (event.httpMethod === 'OPTIONS') {
        return {
            statusCode: 200,
            headers,
            body: ''
        };
    }

    // Only allow POST requests
    if (event.httpMethod !== 'POST') {
        return {
            statusCode: 405,
            headers,
            body: JSON.stringify({
                success: false,
                message: 'Method not allowed'
            })
        };
    }

    try {
        // Parse request body
        let contactData;
        const contentType = event.headers['content-type'] || '';
        
        if (contentType.includes('application/json')) {
            // Handle JSON requests
            contactData = parseJSON(event.body);
        } else if (contentType.includes('application/x-www-form-urlencoded')) {
            // Handle form data requests
            contactData = parseFormData(event.body);
        } else {
            // Try to parse as JSON first, then as form data
            contactData = parseJSON(event.body) || parseFormData(event.body);
        }

        if (!contactData) {
            return {
                statusCode: 400,
                headers,
                body: JSON.stringify({
                    success: false,
                    message: 'Invalid request format'
                })
            };
        }

        // Get client IP
        const clientIP = getClientIP(event.headers);
        contactData.ip_address = clientIP;

        // Create contact instance and process submission
        const contact = new Contact();
        const result = await contact.create(contactData);

        if (result.success) {
            // Send email notification (optional)
            try {
                await sendEmailNotification(contactData);
            } catch (emailError) {
                console.error('Failed to send email notification:', emailError);
                // Don't fail the request if email fails
            }

            return {
                statusCode: 200,
                headers,
                body: JSON.stringify({
                    success: true,
                    message: result.message
                })
            };
        } else {
            return {
                statusCode: 400,
                headers,
                body: JSON.stringify({
                    success: false,
                    message: result.error
                })
            };
        }

    } catch (error) {
        console.error('Error in submit-contact function:', error);
        
        return {
            statusCode: 500,
            headers,
            body: JSON.stringify({
                success: false,
                message: 'Internal server error. Please try again later.'
            })
        };
    }
}; 