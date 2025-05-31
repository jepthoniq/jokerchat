class UserManager {
    constructor(io) {
        this.users = [];
        this.userToSocketMap = new Map();
        this.roleToSocketMap = new Map();
        this.io = io;
    }
    // Join user to chat
	userJoin(socket, user_id, username, room_id, avatar, role) {
		const existingUser = this.getCurrentUser(user_id);

		// Map user_id to socket.id
		this.userToSocketMap.set(user_id, socket.id);
			console.log(`Mapped user_id ${user_id} to socket.id ${socket.id}`);
			console.log('Updated userToSocketMap:', this.userToSocketMap);
		if (existingUser) {
			existingUser.username = username;
			existingUser.room_id = room_id;
			existingUser.avatar = avatar;
			existingUser.role = role;
			console.log(`User ${user_id} updated in room_id ${room_id}`);
			return existingUser;
		}

		const user = { user_id, username, room_id, avatar, role };
		console.log(`User rank for ${username}: ${role}`);
		this.users.push(user);
		console.log(`User ${user_id} added to room_id ${room_id}`);

		// Update roleToSocketMap
		if (!this.roleToSocketMap.has(role)) {
			this.roleToSocketMap.set(role, new Set());
		}
		this.roleToSocketMap.get(role).add(socket.id);

		return user;
	}

    // Get current user by user ID
    getCurrentUser(user_id) {
        return this.users.find(user => user.user_id === user_id);
    }
    // Get current user by socket ID
    getCurrentUserBySocketId(socketId) {
        const user_id = Array.from(this.userToSocketMap.entries()).find(([key, value]) => value === socketId)?.[0];
        if (!user_id) return null;
        return this.users.find(user => user.user_id === user_id) || null;
    }
    // User leaves chat
	 userLeave(socketId) {
		let user_id;
		for (const [id, socket] of this.userToSocketMap.entries()) {
			if (socket === socketId) {
				user_id = id;
				break;
			}
		}
		if (!user_id) {
			console.log(`Socket ID ${socketId} not found in userToSocketMap.`);
			return null;
		}
		// Remove user from users array
		const index = this.users.findIndex(user => user.user_id === user_id);
		if (index !== -1) {
			const user = this.users.splice(index, 1)[0];
			console.log(`User ${user.username} removed from room_id ${user.room_id}`);
			this.userToSocketMap.delete(user_id);
			console.log(`User ${user_id} removed from userToSocketMap.`);
			// Update roleToSocketMap
			if (this.roleToSocketMap.has(user.role)) {
				const roleSet = this.roleToSocketMap.get(user.role);
				roleSet.delete(socketId);
				if (roleSet.size === 0) {
					this.roleToSocketMap.delete(user.role);
				}
			}
			return user;
		}
		return null;
	}
    // Update user details
    updateUser(user_id, updatedData) {
        const userIndex = this.users.findIndex(user => user.user_id === user_id);
        if (userIndex !== -1) {
            const user = this.users[userIndex];
            const oldRole = user.role;
            // Update user properties
            user.username = updatedData.username || user.username;
            user.room_id = updatedData.room_id || user.room_id;
            user.avatar = updatedData.avatar || user.avatar;
            user.role = updatedData.role || user.role;
            user.is_logged = updatedData.is_logged !== undefined ? updatedData.is_logged : user.is_logged;
            user.is_joined = updatedData.is_joined !== undefined ? updatedData.is_joined : user.is_joined;
            user.user_type = updatedData.user_type || user.user_type;
            // Replace the old user object with the updated one
            this.users[userIndex] = user;
            // Update roleToSocketMap if the role has changed
            const newRole = user.role;
            if (oldRole !== newRole) {
                const socketId = this.userToSocketMap.get(user_id);
                // Remove the user from the old role set
                if (this.roleToSocketMap.has(oldRole)) {
                    const oldRoleSet = this.roleToSocketMap.get(oldRole);
                    oldRoleSet.delete(socketId);
                    if (oldRoleSet.size === 0) {
                        this.roleToSocketMap.delete(oldRole);
                    }
                }
                // Add the user to the new role set
                if (!this.roleToSocketMap.has(newRole)) {
                    this.roleToSocketMap.set(newRole, new Set());
                }
                this.roleToSocketMap.get(newRole).add(socketId);
            }

            return user;
        } else {
            console.log(`User with ID ${user_id} not found.`);
            return null;
        }
    }


    // Get users in a room_id
    getUsers(room_id) {
        return this.users.filter(user => user.room_id === room_id);
    }

    // Determine user rank icon
    UsRrank(rank) {
        let icon;
        switch (rank) {
            case 0: icon = 'guest'; break;
            case 1: icon = 'user'; break;
            case 50: icon = 'vip_elite'; break;
            case 51: icon = 'vip_prime'; break;
            case 52: icon = 'vip_supreme'; break;
            case 60: icon = 'premium_elite'; break;
            case 61: icon = 'premium_prime'; break;
            case 62: icon = 'premium_supreme'; break;
            case 69: icon = 'bot'; break;
            case 70: icon = 'mod'; break;
            case 80: icon = 'admin'; break;
            case 90: icon = 'super'; break;
            case 100: icon = 'owner'; break;
            default: icon = 'user';
        }
        return icon;
    }

/**
 * Helper function to update the roleToSocketMap.
 * Ensures that the user's role is properly added or updated.
 */
	
	updateRoleToSocketMap(user, role, socketId) {
		// Validate the role
		if (![0, 1, 50, 51, 52, 60, 61, 62, 69, 70, 80, 90, 100].includes(role)) {
			console.warn(`Invalid role detected: ${role} for user ${user.user_id}`);
			return;
		}
		// Remove the user from their old role set (if applicable)
		if (this.roleToSocketMap.has(user.role)) {
			const oldRoleSet = this.roleToSocketMap.get(user.role);
			oldRoleSet.delete(socketId);
			if (oldRoleSet.size === 0) {
				this.roleToSocketMap.delete(user.role);
			}
		}
		// Add the user to the new role set
		if (!this.roleToSocketMap.has(role)) {
			this.roleToSocketMap.set(role, new Set());
		}
		this.roleToSocketMap.get(role).add(socketId);
		console.log(`Updated roleToSocketMap for user ${user.user_id}: Role=${role}, SocketId=${socketId}`);
	}
    sendToRoleOnce(socket, event, callback) {
        console.log(`Checking listeners for event: ${event}`);
        if (!socket || !socket.listeners(event).length) {
            console.log(`No existing listeners for event: ${event}. Executing callback.`);
            callback();
        } else {
            console.log(`Listeners already exist for event: ${event}. Skipping callback.`);
        }
    }
    sendToRole(role, event, message) {
        // Convert role name to numeric value if necessary
        if (typeof role === 'string') {
            const numericRole = this.resolveRoleByName(role);
            if (numericRole === null) {
                console.warn(`Invalid role detected: ${role}`);
                return;
            }
            role = numericRole; // Use the numeric role
        }
        // Validate the role
        if (![0, 1, 50, 51, 52, 60, 61, 62, 69, 70, 80, 90, 100].includes(role)) {
            console.warn(`Invalid role detected: ${role}`);
            return;
        }
        // Check if the role exists in the roleToSocketMap
        if (!this.roleToSocketMap.has(role)) {
            console.warn(`No users found with role: ${this.UsRrank(role)} (${role})`);
            return;
        }
        // Emit the message to all sockets associated with the role
        const roleSockets = this.roleToSocketMap.get(role);
        roleSockets.forEach(socketId => {
            this.io.to(socketId).emit(event, message);
        });
        console.log(`Message sent to role: ${role}, Event: ${event}, Message:`, message);
    }
/**
 * Helper function to resolve a role name to its numeric value.
 */
resolveRoleByName(roleName) {
    const roleMap = {
        guest: 0,
        user: 1,
        vip_elite: 50,
        vip_prime: 51,
        vip_supreme: 52,
        premium_elite: 60,
        premium_prime: 61,
        premium_supreme: 62,
        bot: 69,
        mod: 70,
        admin: 80,
        super: 90,
        owner: 100
    };
    return roleMap[roleName] || null; // Return null for invalid role names
}
    // NEW METHOD: Get user information by user ID
		getUserInfo(user_id) {
			// Normalize user_id to a string for comparison
			const normalizedUserId = String(user_id);
			const user = this.users.find(user => String(user.user_id) === normalizedUserId);
			if (!user) {
				console.warn(`User with ID ${normalizedUserId} not found.`);
				return null;
			}
			return {
				user_id: user.user_id,
				username: user.username,
				room_id: user.room_id,
				avatar: user.avatar,
				role: user.role,
				is_logged: user.is_logged,
				is_joined: user.is_joined,
				user_type: user.user_type
			};
		}	
}

module.exports = UserManager;