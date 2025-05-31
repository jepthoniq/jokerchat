// configModule.js

const fs = require('fs');
const path = require('path');
const express = require('express');

/**
 * Initializes the configuration module.
 * @param {Object} options - Configuration options.
 * @param {Object} options.app - The Express app instance.
 * @param {String} options.filePath - Path to save the config_setting.js file.
 * @param {Function} [options.authMiddleware] - Optional middleware for authentication.
 */
function initConfigModule({ app, filePath = 'config_setting.js', authMiddleware = null }) {
    const router = express.Router();

    // Middleware to parse JSON bodies
    router.use(express.json());

    // Apply authentication middleware if provided
    if (authMiddleware) {
        router.use(authMiddleware);
    }

    /**
     * POST /update-config
     * Receives configuration data and saves it as config_setting.js
     */
    router.post('/update-config', (req, res) => {
        const data = req.body;

        // Basic validation (you can enhance this as needed)
        if (!data || typeof data !== 'object') {
            return res.status(400).send('Invalid data format');
        }

        //console.log('Received config data:', data);

        // Define the path to the config_setting.js file
        const resolvedFilePath = path.resolve(__dirname, filePath);

        // Prepare the content to be written to config_setting.js
        const fileContent = `module.exports = ${JSON.stringify(data, null, 2)};`;

        // Write the data to config_setting.js
        fs.writeFile(resolvedFilePath, fileContent, (err) => {
            if (err) {
                console.error('Error writing config file:', err);
                return res.status(500).send('Error saving config');
            }
            console.log('config_setting.js file updated successfully');
            res.status(200).send('Config saved successfully');
        });
    });

    // Mount the router on the main app
    app.use('/', router);
}

module.exports = initConfigModule;
