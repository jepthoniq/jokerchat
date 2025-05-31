const fs = require('fs');
const path = require('path');
const express = require('express');
const https = require('https');
const socketio = require('socket.io');
const { PeerServer } = require('peer'); // Import PeerJS
const compression = require('compression');
const UserManager = require('./users');
const RedisManager = require('./RedisManager');
const ChatManager = require('./ChatManager');
const EventManager = require('./EventManager');
const initConfigModule = require('./configModule'); // Import the config module
const config = require('./config_setting'); // Import configuration from the config_setting.js file
const CallManager = require('./CallManager'); // Import the CallManager class

// Read SSL/TLS certificate and key
//enbale this in production mode and change the ssl paths for working site
//const privateKey = fs.readFileSync('/www/server/panel/vhost/letsencrypt/albchat24.com/privkey.pem', 'utf8');
//const certificate = fs.readFileSync('/www/server/panel/vhost/letsencrypt/albchat24.com/fullchain.pem', 'utf8');

const privateKey = fs.readFileSync('privkey.pem', 'utf8');
const certificate = fs.readFileSync('fullchain.pem', 'utf8');
const credentials = { key: privateKey, cert: certificate };

// Create an HTTPS server
const app = express();
const PORT = process.env.PORT || 8543;
const server = https.createServer(credentials, app);
const io = socketio(server, {
	path: '/socket.io', // Explicitly set the path for Socket.IO
    cors: {
        origin: ["https://127.0.0.1", "https://localhost", "http://127.0.0.1", "http://localhost"],
        methods: ["GET", "POST"],
        credentials: true
    },
});

// Make 'io' globally available (if needed)
global.io = io;
app.use(compression());
app.use(express.static(path.join(__dirname, 'public')));
// Initialize the configuration module with authentication
initConfigModule({
    app,
    filePath: 'config_setting.js', // You can specify a different path if needed
    authMiddleware, // Pass the authentication middleware
})
// Initialize modules
const userManager = new UserManager(io);
const redisManager = new RedisManager();
const chatManager = new ChatManager(io, userManager, redisManager);
const eventManager = new EventManager(io, userManager, chatManager);
//const callManager = new CallManager(server, credentials, userManager); // Pass the `server` instance here
// Optional: Define your authentication middleware
// Authentication Middleware
function authMiddleware(req, res, next) {
    const token = req.headers['x-auth-token'];
    console.log(`Request to ${req.path} with token: ${token}`);
    if (token && token === 'lucifer666') {
        next();
    } else {
        console.warn(`Forbidden access to ${req.path}`);
        res.status(403).send('Forbidden');
    }
}
// Apply middleware
app.use((req, res, next) => {
    authMiddleware(req, res, next);
});

// Start the server
server.listen(PORT, () => {
    console.log(`Server running on https://localhost:${PORT}`);
});
