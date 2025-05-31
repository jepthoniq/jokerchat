const { PeerServer } = require('peer');
const https = require('https');
const fs = require('fs');
class CallManager {
    constructor(server, credentials, userManager) {
        this.server = server; // Main HTTPS server
        this.credentials = credentials; // SSL/TLS credentials
        this.userManager = userManager; // UserManager instance
        this.peerPort = 9000; // Port for the PeerJS server
        this.initPeerServer();
        this.registerEvents();
    }
    /**
     * Initialize the PeerJS server on a separate port.
     */
    initPeerServer() {
        const peerApp = require('express')();
        this.peerServer = https.createServer(this.credentials, peerApp);
        // Initialize PeerJS server
        this.peerJsServer = PeerServer({
            port: this.peerPort,
            path: '/peerjs',
            ssl: this.credentials, // Pass SSL credentials for HTTPS
            debug: true,
        });
        console.log(`PeerJS server running on https://localhost:${this.peerPort}/peerjs`);
    }
    /**
     * Register Socket.IO event handlers for P2P calls.
     */
    registerEvents() {
        this.server.on('connection', (socket) => {
            console.log('Socket connected:', socket.id);
            // Handle call initiation
            socket.on('initiateCall', ({ caller_id, target_id, isVideoCall }) => {
                console.log(`Initiating ${isVideoCall ? 'video' : 'audio'} call from ${caller_id} to ${target_id}`);
                if (!this.userManager.userToSocketMap.has(target_id)) {
                    console.warn(`Target user ${target_id} is not connected.`);
                    return;
                }
                const targetSocketId = this.userManager.userToSocketMap.get(target_id);
                this.server.to(targetSocketId).emit('incomingCall', { caller_id, isVideoCall });
            });
            // Handle call acceptance
            socket.on('acceptCall', ({ caller_id, target_id, isVideoCall }) => {
                console.log(`Call accepted from ${caller_id} by ${target_id}`);
                if (!this.userManager.userToSocketMap.has(caller_id)) {
                    console.warn(`Caller ${caller_id} is not connected.`);
                    return;
                }
                const callerSocketId = this.userManager.userToSocketMap.get(caller_id);
                this.server.to(callerSocketId).emit('callAccepted', { target_id, isVideoCall });
            });
            // Handle call rejection
            socket.on('rejectCall', ({ caller_id, target_id }) => {
                console.log(`Call rejected by ${target_id}`);
                if (!this.userManager.userToSocketMap.has(caller_id)) {
                    console.warn(`Caller ${caller_id} is not connected.`);
                    return;
                }
                const callerSocketId = this.userManager.userToSocketMap.get(caller_id);
                this.server.to(callerSocketId).emit('callRejected', { target_id });
            });
            // Handle call termination
            socket.on('endCall', ({ caller_id, target_id }) => {
                console.log(`Call ended between ${caller_id} and ${target_id}`);
                if (this.userManager.userToSocketMap.has(target_id)) {
                    const targetSocketId = this.userManager.userToSocketMap.get(target_id);
                    this.server.to(targetSocketId).emit('callEnded', { caller_id });
                }
            });
            // Handle disconnection
            socket.on('disconnect', () => {
                const user = this.userManager.getCurrentUserBySocketId(socket.id);
                if (user) {
                    console.log(`User ${user.user_id} disconnected during a call.`);
                }
            });
        });
    }
}
module.exports = CallManager;