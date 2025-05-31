// Attach an event listener to the 'Test Connection' button
$('#testConnectionButton').on('click', function() {
    // Emit a 'testConnection' event to the server
    FUSE_Admin_SOCKET.socket.emit('testConnection', { message: 'Testing connection' });

    // Optionally, display an alert that the test is in progress
    displayAlert("Testing connection...", "info", "<i class='ri-refresh-line'></i>");
})
var FUSE_Admin_SOCKET = {
    socket: FUSE_SOCKET.socket,
    start: function() {
        if (!this.socket) {
            this.socket = io(); // Initialize socket connection
        }
        // Listen for the 'connect' event
        this.socket.on('connect', () => {
            console.log('Socket connected with SID:', this.socket.id); // Log socket ID
            // Check if transport is WebSocket
            if (this.socket.io.engine.transport.name === 'websocket') {
				displayAlert("Successfully connected to the server!", "success", "<i class='ri-checkbox-circle-fill'></i>");
                console.log('Transport is WebSocket');
            } else {
                console.log('Transport is not WebSocket:', this.socket.io.engine.transport.name);
            }
        });		
        // Listen for connection error
        this.socket.on('connect_error', () => {
            console.log('Connection failed!');
            displayAlert("Disconnected from the server...", "danger", "<i class='ri-xrp-line'></i>");
        });
        // Listen for disconnect event
        this.socket.on('disconnect', () => {
            console.log('Disconnected from the server');
        });
        // Listen for the test connection response from the server
        this.socket.on('testConnectionResponse', (data) => {
            console.log('Test Connection Response:', data);
            displayAlert(data.message, "success", "<i class='ri-checkbox-circle-fill'></i>");
        });
		
    }
};
function displayAlert(message, type, icon) {
    const alertContainer = $("#websocket-box-alert");
    const alert = $("<div>", {
        class: `alert alert-${type}`,
        text: message
    }).prepend(icon); // Add the icon before the message

    alertContainer.html(alert); // Update the alert container's content with the new alert
    alertContainer.show(); // Ensure the alert container is visible
}

// Call the start method to initiate the connection and handle events
FUSE_Admin_SOCKET.start();
