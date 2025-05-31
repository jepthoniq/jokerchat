// chat.js
const UserManager = require('./users'); // Import the UserManager class
const formatMessage = require('./messages');
const TYPING_DELAY = 500; // Delay in milliseconds
let lastTypingEmitTime = 0; // Track last emit time
 const handleChatMessages = (socket, io) => {
    const userManager = new UserManager(io); // Create an instance of UserManager
    // Listen for new socket connections
    io.on('connection', (socket) => {
        // Handle incoming chat messages
        socket.on('chatMessage', (msg) => {
            // Retrieve the current user associated with the socket ID
            const user = userManager.getCurrentUser(socket.id);
            // Check if the user exists
            if (user) {
                // Emit the formatted message to all users in the same room
                io.to(user.room).emit('message', formatMessage(user.username, msg));
            } else {
                // Log an error if the user is not found
                console.log(`User with ID ${socket.id} not found.`);
            }
        });
        // Handle typing notifications


    });
};

module.exports = { handleChatMessages };