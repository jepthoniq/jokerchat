const sanitizeHtml = require('sanitize-html');
const htmlMinifier = require('html-minifier');

class EventManager {
    constructor(io, userManager) {
        this.io = io;
		this.ip ='127.0.0.1';
        this.userManager = userManager;
        this.privateChatSessions = {};
        this.TYPING_DELAY = 500; // Delay in milliseconds
        this.lastTypingEmitTime = 0; // Track last emit time
        this.botName = 'FuseChat Bot';
        this.registerEvents();
    }

    registerEvents() {
        this.io.on('connection', (socket) => {
            console.log('A user connected:', socket.id);

            // Handle login
            socket.on('login', ({ user_id, username, room_id, avatar, role }) => {
                user_id = this.normalizeId(user_id);
                room_id = this.normalizeId(room_id);
                role = this.normalizeId(role);

                const user = this.userManager.userJoin(socket, user_id, username, room_id, avatar, role);
                console.log(`User ${user_id} joined with socket ID: ${socket.id}`);
                console.log('Updated userToSocketMap:', this.userManager.userToSocketMap);

                socket.join(user.room_id); // Join the public room

                // Notify the owner
                this.sendToRoleOnce(socket, 'monitor', () => {
                    const numericRole = this.userManager.resolveRoleByName('owner'); // Convert "owner" to 100
                    if (numericRole === null) {
                        console.warn(`Invalid role detected: owner`);
                        return;
                    }
                    this.userManager.sendToRole(
                        numericRole,
                        'monitor',
                        this.formatMessage(this.botName, `${user.username} has joined the room`, socket, 'join_room',this.ip)
                    );
                });

                // Send users and room info
                this.io.to(user.room_id).emit('room_Users', {
                    room_id: user.room_id,
                    users: this.userManager.getUsers(user.room_id)
                });
            });

            // Handle room switch
            socket.on('switch_room', ({ user_id, username, room_id, avatar, role }) => {
                const user = this.userManager.userJoin(socket, user_id, username, room_id, avatar, role);
                if (!user) {
                    console.error(`Failed to join user ${user_id} to room ${room_id}`);
                    return;
                }
                socket.join(user.room_id);
                const previousRoom = this.userManager.getCurrentUser(user_id)?.room_id;
                if (previousRoom && previousRoom !== user.room_id) {
                    socket.leave(previousRoom);
                    // Notify users in the previous room about the departure
                    this.io.to(previousRoom).emit('room_Users', {
                        room_id: previousRoom,
                        users: this.userManager.getUsers(previousRoom)
                    });
                }
                // Notify the owner about the room switch
                this.sendToRoleOnce(socket, 'monitor', () => {
                    const numericRole = this.userManager.resolveRoleByName('owner'); // Convert "owner" to 100
                    if (numericRole === null) {
                        console.warn(`Invalid role detected: owner`);
                        return;
                    }
                    this.userManager.sendToRole(
                        numericRole,
                        'monitor',
                        this.formatMessage(this.botName, `${user.username} has switched to room ${user.room_id}`, socket, 'switch_room',this.ip)
                    );
                });

                // Notify users in the new room about the arrival
                this.io.to(user.room_id).emit('room_Users', {
                    room_id: user.room_id,
                    users: this.userManager.getUsers(user.room_id)
                });
            });

            // Handle private chat requests
            socket.on('startPrivateChat', ({ user_id, target_id }) => {
                target_id = this.normalizeId(target_id);
                console.log('Starting private chat between:', user_id, 'and', target_id);
                if (!this.userManager.userToSocketMap.has(target_id)) {
                    console.warn(`Invalid or inactive target user ID: ${target_id}`);
                    return;
                }
                const targetSocketId = this.userManager.userToSocketMap.get(target_id);
                if (!targetSocketId) {
                    console.warn(`Target user ${target_id} is not connected.`);
                    return;
                }
                this.privateChatSessions[user_id] = target_id;
                this.privateChatSessions[target_id] = user_id;
                this.io.to(socket.id).emit('privateChatStarted', { target_id });
                this.io.to(targetSocketId).emit('privateChatStarted', { target_id: user_id });
                console.log(`Private chat started between ${user_id} and ${target_id}`);
            });
            // Handle "user is typing" for private chats
            socket.on('privateTyping', ({ user_id, target_id, stopped }) => {
                target_id = this.normalizeId(target_id);
                const userInfo = this.userManager.getUserInfo(user_id);
                console.log(`User ${user_id} is typing in private chat with ${target_id}`);
                if (!this.userManager.userToSocketMap.has(target_id)) {
                    console.warn(`Invalid or inactive target user ID: ${target_id}`);
                    return;
                }
                const targetSocketId = this.userManager.userToSocketMap.get(target_id);
                if (!targetSocketId) {
                    console.warn(`Target user ${target_id} is not connected.`);
                    return;
                }
                this.io.to(targetSocketId).emit('privateUserTyping', {
                    user_id: user_id,
                    username: userInfo['username'] || 'Unknown User',
                    avatar: userInfo['avatar'] || 'Unknown avatar',
                    role: userInfo['role'] || 'Unknown avatar',
                    stopped: !!stopped // Convert to boolean
                });
            });
            // Handle message deletion requests
            socket.on('deleteMessage', ({ msg_id, target_id }, callback) => {
                console.log('Attempting to delete message:', { msg_id, target_id });
                const numericMsgId = Number(msg_id);
                const numericTargetId = Number(target_id);
                if (!numericMsgId || !numericTargetId) {
                    console.warn('Invalid message ID or target ID:', { msg_id, target_id });
                    callback({ success: false, error: 'Invalid message or target ID.' });
                    return;
                }
                if (!this.userManager.userToSocketMap.has(numericTargetId)) {
                    console.warn(`Invalid or inactive target user ID: ${numericTargetId}`);
                    callback({ success: false, error: 'Target user is not connected.' });
                    return;
                }
                const targetSocketId = this.userManager.userToSocketMap.get(numericTargetId);
                if (!targetSocketId) {
                    console.warn(`Target user ${numericTargetId} is not connected.`);
                    callback({ success: false, error: 'Target user is not connected.' });
                    return;
                }
                this.io.to(targetSocketId).emit('messageDeleted', { msg_id: numericMsgId });
                console.log(`Message deletion event sent to user ${numericTargetId}`);
                callback({ success: true });
            });
            // Handle typing notifications
            socket.on('typing', (user_id) => {
                const currentTime = Date.now();
                if (!this.lastTypingEmitTime || currentTime - this.lastTypingEmitTime > this.TYPING_DELAY) {
                    this.lastTypingEmitTime = currentTime;
                    const user = this.userManager.getCurrentUser(user_id);
                    if (!user) {
                        console.warn(`User with ID ${user_id} not found.`);
                        return;
                    }
                    console.log(`User ${user.username} is typing in room ${user.room_id}`);
                    socket.broadcast.to(user.room_id).emit('userTyping', {
                        user_id: user.user_id,
                        username: user.username,
                        avatar: user.avatar
                    });
                }
            });
			// Handle incoming chat messages
			socket.on('chatMessage', ({ user_id, msg, room_id }) => {
				// Validate input: Ensure user_id and msg are provided
				if (!user_id || !msg) {
					console.warn('Invalid or missing user_id or message:', { user_id, msg });
					return;
				}
				// Retrieve the current user based on user_id
				const user = userManager.getCurrentUser(user_id);
				if (!user) {
					console.warn(`User with ID ${user_id} not found.`);
					return;
				}
				// Use the user's current room_id from the UserManager
				const currentRoomId = user.room_id;
				// Sanitize the message to allow specific HTML tags and attributes
				// Minify the message to remove unnecessary whitespace
				const minifiedMsg = htmlMinifier.minify(msg.trim(), {
					collapseWhitespace: true,
					removeComments: true,
					minifyCSS: true,
					minifyJS: true
				});

				// Broadcast the sanitized message to all users in the same room (excluding the sender)
				socket.broadcast.to(currentRoomId).emit('message', {
					user_id: user.user_id,
					username: user.username,
					avatar: user.avatar,
					msg: minifiedMsg
				});
				// Optionally, log the message details for debugging
				console.log(`Broadcasted message to room ${currentRoomId}:`, {
					user_id: user.user_id,
					username: user.username,
					msg: minifiedMsg
				});
			});

			// Listen for Public announcement events
            // Handle incoming "newMessage" events
            socket.on('newMessage', (data) => {
                console.log("Incoming message data:", data);
                console.log("Current users array:", this.userManager.users);
                // Normalize user_id to a string
                data.user_id = String(data.user_id);
                const userInfo = this.userManager.getUserInfo(data.user_id);
                if (!userInfo) {
                    console.warn(`User with ID ${data.user_id} not found. Cannot broadcast message.`);
                    return;
                }
                const enrichedData = {
                    ...data,
                    senderInfo: {
                        username: userInfo.username,
                        avatar: userInfo.avatar,
                        role: this.userManager.UsRrank(userInfo.role),
                        user_type: userInfo.user_type,
                        user_id: userInfo.user_id
                    }
                };
                console.log("Received message:", enrichedData);
                if (data.room_id) {
                    this.broadcastToroom_id(data.room_id, "newMessage", enrichedData);
                } else {
                    this.broadcastToAll("newMessage", enrichedData);
                }
            });
            // Handle disconnection
            socket.on('disconnect', () => {
                const user = this.userManager.getCurrentUserBySocketId(socket.id);
                if (user) {
                    delete this.privateChatSessions[user.user_id];
                    this.io.to(user.room_id).emit('room_Users', {
                        room_id: user.room_id,
                        users: this.userManager.getUsers(user.room_id)
                    });
                    this.sendToRoleOnce(socket, 'monitor', () => {
                        this.userManager.sendToRole(
                            'owner',
                            'monitor',
                            this.formatMessage(this.botName, `${user.username} has left the chat!`, socket, 'left_server',this.ip)
                        );
                    });
                    this.userManager.userLeave(socket.id);
                } else {
                    console.log(`User with socket ID ${socket.id} not found on disconnect.`);
                }
            });
        });
    }
	  /**
	 * Broadcasts a message to all clients in a specific room_id.
	 *
	 * @param {string} room_id The room_id to broadcast to.
	 * @param {string} event The event name (e.g., "newMessage").
	 * @param {object} data The message payload.
	 */
    broadcastToroom_id(room_id, event, data) {
        this.io.to(room_id).emit(event, data);
        console.log(`Broadcasted "${event}" to room "${room_id}":`, data);
    }
    broadcastToAll(event, data) {
        this.io.emit(event, data);
        console.log(`Broadcasted to all "${event}" to all clients:`, data);
    }	
    normalizeId(id) {
        const normalizedId = Number(id);
        if (!Number.isInteger(normalizedId)) {
            throw new Error(`Invalid ID: ${id}`);
        }
        return normalizedId;
    }
    sendToRoleOnce(socket, event, callback) {
        if (!socket.listeners(event).length) {
            callback();
        }
    }
    formatMessage(botName, text, socket, type, ip) {
        return {
            botName,
            text,
            socketId: socket.id,
            type,
			ip
        };
    }
}

module.exports = EventManager;