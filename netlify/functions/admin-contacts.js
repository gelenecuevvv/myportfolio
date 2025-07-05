const Contact = require('./lib/contact');

// Simple authentication check (you should implement proper authentication)
function isAuthenticated(headers) {
    // For now, we'll use a simple API key check
    // In production, use proper authentication like JWT
    const apiKey = headers['x-api-key'] || headers['authorization'];
    return apiKey === process.env.ADMIN_API_KEY;
}

exports.handler = async (event, context) => {
    // Set CORS headers
    const headers = {
        'Access-Control-Allow-Origin': '*',
        'Access-Control-Allow-Headers': 'Content-Type, X-API-Key, Authorization',
        'Access-Control-Allow-Methods': 'GET, POST, PUT, DELETE, OPTIONS',
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

    // Check authentication
    if (!isAuthenticated(event.headers)) {
        return {
            statusCode: 401,
            headers,
            body: JSON.stringify({
                success: false,
                message: 'Unauthorized'
            })
        };
    }

    try {
        const contact = new Contact();
        const { httpMethod, queryStringParameters, path } = event;

        // Parse contact ID from path if present
        const pathParts = path.split('/');
        const contactId = pathParts[pathParts.length - 1];

        switch (httpMethod) {
            case 'GET':
                if (contactId && contactId !== 'admin-contacts') {
                    // Get specific contact
                    const result = await contact.getById(contactId);
                    return {
                        statusCode: result.success ? 200 : 404,
                        headers,
                        body: JSON.stringify(result)
                    };
                } else {
                    // Get all contacts
                    const result = await contact.getAll();
                    return {
                        statusCode: result.success ? 200 : 500,
                        headers,
                        body: JSON.stringify(result)
                    };
                }

            case 'PUT':
                if (!contactId || contactId === 'admin-contacts') {
                    return {
                        statusCode: 400,
                        headers,
                        body: JSON.stringify({
                            success: false,
                            message: 'Contact ID is required'
                        })
                    };
                }

                const updateData = JSON.parse(event.body);
                if (updateData.status) {
                    // Update contact status
                    const result = await contact.updateStatus(contactId, updateData.status);
                    return {
                        statusCode: result.success ? 200 : 400,
                        headers,
                        body: JSON.stringify(result)
                    };
                } else {
                    return {
                        statusCode: 400,
                        headers,
                        body: JSON.stringify({
                            success: false,
                            message: 'Status is required'
                        })
                    };
                }

            case 'DELETE':
                if (!contactId || contactId === 'admin-contacts') {
                    return {
                        statusCode: 400,
                        headers,
                        body: JSON.stringify({
                            success: false,
                            message: 'Contact ID is required'
                        })
                    };
                }

                const result = await contact.delete(contactId);
                return {
                    statusCode: result.success ? 200 : 400,
                    headers,
                    body: JSON.stringify(result)
                };

            default:
                return {
                    statusCode: 405,
                    headers,
                    body: JSON.stringify({
                        success: false,
                        message: 'Method not allowed'
                    })
                };
        }

    } catch (error) {
        console.error('Error in admin-contacts function:', error);
        
        return {
            statusCode: 500,
            headers,
            body: JSON.stringify({
                success: false,
                message: 'Internal server error'
            })
        };
    }
}; 