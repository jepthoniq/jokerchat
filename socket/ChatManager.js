const sanitizeHtml = require('sanitize-html');
const htmlMinifier = require('html-minifier');
class ChatManager {
    constructor(io, userManager, redisManager) {
        this.io = io;
        this.userManager = userManager;
        this.redisManager = redisManager;
        // Subscribe to Redis events
        this.redisManager.subscribe('chat', this.handleRedisEvent.bind(this));
    }
    handleRedisEvent({ event, data }) {
        switch (event) {
            case 'newMessage':
                this.handleNewMessage(data);
                break;
            case 'deleteMessage':
                this.handleDeleteMessage(data);
                break;
            default:
                console.warn(`Unknown Redis event received: ${event}`);
        }
    }
    handleNewMessage({ user_id, msg, room_id }) {
        const user = this.userManager.getCurrentUser(user_id);
        if (!user) {
            console.warn(`User with ID ${user_id} not found.`);
            return;
        }
        // Sanitize the message
		// Minify the message to remove unnecessary whitespace
		const minifiedMsg = htmlMinifier.minify(msg.trim(), {
					collapseWhitespace: true,
					removeComments: true,
					minifyCSS: true,
					minifyJS: true
				});
        // Broadcast the message to the room
        this.io.to(room_id).emit('message', {
            user_id: user.user_id,
            username: user.username,
            avatar: user.avatar,
            msg: minifiedMsg
        });
        console.log(`Broadcasted message to room ${room_id}:`, minifiedMsg);
    }
    handleDeleteMessage({ msg_id, target_id }) {
        const targetSocketId = this.userManager.getUserSocket(target_id);
        if (!targetSocketId) {
            console.warn(`Target user ${target_id} is not connected.`);
            return;
        }
        // Emit the deletion event to the target user
        this.io.to(targetSocketId).emit('messageDeleted', { msg_id });
        console.log(`Message deletion event sent to user ${target_id}`);
    }
}

module.exports = ChatManager;