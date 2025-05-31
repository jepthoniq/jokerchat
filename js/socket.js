const FUSE_SOCKET = {
    socket: null,
    isConnected: false,
    currentRoom: logged ? user_room : 'index',
    private_id: private_id,
    listenersAttached: false,
    curPage: curPage,
    room_name: $('.mroom_name').text().trim(),
    typing: false, // Flag to track typing status
    typingTimeout: 600,
    typingTimer: null,
	user_id: user_id ?? 0, // Set user_id to 0 if undefined
    /**
     * Debounce function to limit the frequency of function calls.
     */
    debounce: function (func, delay) {
        let timer;
        return function (...args) {
            clearTimeout(timer);
            timer = setTimeout(() => {
                func.apply(this, args);
            }, delay);
        };
    },

    /**
     * Generate user update data for login and status updates.
     */
    sendUserUpdate: function () {
        return {
            user_id: user_id,
            username: user_name,
            avatar: avatar,
            role: user_rank,
            room_id: this.currentRoom,
            is_logged: logged === 1,
            is_joined: curPage === 'chat',
            user_type: logged === 1 ? 'member' : 'guest',
			room_name: this.room_name,
        };
    },

    /**
     * Initialize the WebSocket connection.
     */
    start: function () {
        if (!this.socket || !this.isConnected) {
            console.log('Initializing new socket connection...');
            this.socket = io(s_protocol + s_server + ':' + s_port, {
                reconnectionDelay: 3000,
                reconnectionAttempts: 5, // Allow up to 5 reconnection attempts
                reconnection: true,
				 path: '/socket.io',
                transports: ['websocket', 'polling'],
                secure: true,
                withCredentials: true,
				 rejectUnauthorized: false
            });

            // Attach event listeners only once
            if (!this.listenersAttached) {
                this.attachEventListeners();
                this.listenersAttached = true; // Mark that listeners have been attached
            }
			rname =  this.currentRoom;
        } else {
            console.log('Socket connection already exists or is being initialized');
        }
    },
    /**
     * Attach all necessary event listeners to the socket.
     */
    attachEventListeners: function () {
        const self = this;
		this.privateTyping();
		this.startPublicChat();
        // Handle socket connection
        this.socket.off('connect').on('connect', () => {
            console.log('Socket connected:', this.socket.id);
            this.isConnected = true;
            if (this.curPage === 'chat') {
                this.socket.emit('login', this.sendUserUpdate());
            }
        });
        // Handle reconnection logic
        this.socket.off('reconnect').on('reconnect', () => {
            console.log('Reconnected to the server');
            this.socket.emit('login', this.sendUserUpdate());
        });
        // Handle disconnection
        this.socket.off('disconnect').on('disconnect', () => {
            console.log('Socket disconnected');
            this.isConnected = false;
            this.socket.removeAllListeners(); // Clean up listeners
        });
        // Handle connection errors
        this.socket.off('connect_error').on('connect_error', (err) => {
            console.error('Connection failed: ', err.message);
            console.log('Failed to connect to the server. Please check your internet connection and try again.');
        });
        // Handle room switching
        $(document).off('click', '.switch_room').on('click', '.switch_room', (event) => {
			if(logged >0){
            let newRoom = $(event.currentTarget).data('room');
				newRoom = $(event.currentTarget).data('roomid');
				if (newRoom && newRoom !== this.currentRoom) {
					this.switchRoom(newRoom);
				}				
			}
        });
		// Handle message deletion notifications
		this.socket.off('messageDeleted').on('messageDeleted', (data) => {
			const { msg_id } = data;
			removeMessageFromDOM(msg_id);
			// Log the received data for debugging
			console.log('Received messageDeleted event with data:', data);
			// Normalize msg_id to a string
			const normalizedMsgId = String(msg_id);
			// Validate the message ID
			if (!normalizedMsgId || typeof normalizedMsgId !== 'string') {
				console.error('Invalid message ID received:', msg_id);
				return;
			}
			console.log('Message deleted for me:', normalizedMsgId);
		});
		// Handle private chat notifications
		this.socket.off('privateChatStarted').on('privateChatStarted', (data) => {
			const { target_id } = data;	
		});
		//handle public announcement msg
		/**
		* Listens for new messages and displays them.
		*/
		this.socket.off('newMessage').on('newMessage', (data) => {
			var modal_title = $('.modal_top_empty').text('Public announcement');
			var senderInfo = data.senderInfo;
			var cancel_btn = `<div class="pad5 centered_element"><button onclick="" class="reg_button theme_btn close_over">ok</button></div>`;
			var template = `
				<div class="public_message_modal">
					<div class="public_message_content">
						<div class="update_card">
							<a class="update_avatar">
								<img src="${senderInfo.avatar}" alt="New Avatar" class="avatar_image" />
							</a>
							<div class="update_content get_info" value="Lucifer" data="1">
								<div class="avatar_username username bgif17"><img src="default_images/rank/${senderInfo.role}.gif" class="list_rank" title="${senderInfo.role}" />${senderInfo.username}</div>
								<span class="avatar_action public_message_text message-box">${data.message}</span>
							</div>
						</div>
					</div>

				</div>
				${cancel_btn}
			`;
			overModal(template,400);
		});
		// Disconnect socket on page unload
        window.addEventListener('beforeunload', () => {
            if (this.socket) {
                this.socket.disconnect();
            }
        });
    },

    /**
     * Notify the server that the user is typing.
     */
    notifyTyping: function (user_id) {
        const notify = this.debounce(() => {
            if (!this.typing) {
                this.typing = true; // Set typing status
                this.socket.emit('typing', user_id); // Emit typing event
            }
        }, this.typingTimeout);
        notify();
        // Reset typing status after timeout
        clearTimeout(this.typingTimer);
        this.typingTimer = setTimeout(() => {
            this.typing = false;
        }, this.typingTimeout);
    },

    /**
     * privateTyping room.
     */
	privateTyping: function (newRoom) {
			// Typing-related variables
			if(privateTyping >0){
				let PR_typingTimer; // Timer to track when the user stops typing
				const PR_TYPING_DELAY = 1000; // Delay in milliseconds (adjust as needed)
				let PR_isTyping = false; // Track whether the user is currently typing
				// Listen for input events on the message input field
				$('#message_content').on('input', () => {
					const currentUserID = user_id; // Replace with the actual current user ID
					const targetUserID = this.private_id; // Replace with the actual target user ID
					// Clear the existing timer if it exists
					clearTimeout(PR_typingTimer);
					// If the user was not typing before, emit the "start typing" event
					if (!PR_isTyping) {
						console.log('User started typing...');
						PR_isTyping = true;
						// Emit the "typing" event to the server
						this.socket.emit('privateTyping', { user_id: currentUserID, target_id: targetUserID });
					}
					// Set a new timer to detect when the user stops typing
					PR_typingTimer = setTimeout(() => {
						console.log('User stopped typing...');
						PR_isTyping = false;
						// Emit the "stop typing" event to the server
						this.socket.emit('privateTyping', { user_id: currentUserID, target_id: targetUserID, stopped: true });
					}, PR_TYPING_DELAY);
				});
				// Listen for "privateUserTyping" events
			const typingIndicatorTemplate = ({ username, avatar }) => `
					<div class="typing-avatar">
						<img id="typing-avatar" src="${avatar || 'default-avatar.png'}" alt="User Avatar" />
					</div>
					<div class="typing-info">
						<span id="typing-username">${username || 'Someone'}</span> is typing...
						<div class="typing-animation"><span class="dot"></span><span class="dot"></span><span class="dot"></span></div>
					</div>`;
			// Listen for "privateUserTyping" events
			this.socket.on('privateUserTyping', ({ user_id, username, avatar, stopped }) => {
				console.log( user_id, username, avatar, stopped);
				let $typingIndicator = $('#private_typing_indicator');
				if ($typingIndicator.length === 1) {
					$typingIndicator.html(typingIndicatorTemplate({ username, avatar })).show();
					if (stopped) {
						// Hide the typing indicator if the user has stopped typing
						$typingIndicator.hide();
						const $typingAvatar = $('#typing-avatar');
						const $typingUsername = $('#typing-username');
						$typingAvatar.attr('src', avatar || 'default_images/avatar/default_avatar.svg'); // Use a default avatar if none is provided
						$typingUsername.text(username || 'Someone');
					}
				}
			});			
	}	
	},	
	switchRoom: function (newRoom) {
        if (this.currentRoom) {
            this.socket.emit('leaveRoom', { room: this.currentRoom });
        }
        this.socket.emit('switch_room', {
            user_id: user_id,
            room_id: newRoom,
            role: user_rank,
            avatar: avatar,
            is_logged: logged === 1,
            is_joined: curPage === 'chat',
            user_type: logged === 1 ? 'member' : 'guest',
            username: user_name
        });
        this.currentRoom = newRoom;
    },  
	/**
     * Switch to start Private Chat.
     */
    startPublicChat: function (currentRoom) {
		this.socket.on('message', (data) => {
			const { user_id, username, avatar, msg } = data;
			$('#show_chat ul').append(msg);
			 scrollIt(fload);
		});		
        // Handle typing indicator
        if (allow_typing >0) {
			$('#content').on('input', () => {
				 this.notifyTyping(user_id);
			}); 
		this.socket.off('userTyping').on('userTyping', (data) => {
			console.log(`${data.username} is typing...`);
			// Create a unique ID for the typing indicator based on user_id
			const typingIndicatorId = `typing-${data.user_id}`;
			// Check if the typing indicator already exists
			if ($(`#${typingIndicatorId}`).length === 0) {
				// Add a new typing indicator
				const typing_temp = `
					<div id="${typingIndicatorId}" onclick="getProfile(${data.user_id});" class="cp_typing_indicator" title="${data.username} is typing...">
						<span class="cp_ball cp_ball1"></span>
						<span class="cp_ball cp_ball2"></span>
						<div class="user_item_avatar">
							<img class="avav acav avsex nosex" src="${data.avatar}">
						</div>
						<span class="cp_ball cp_ball3"></span>
						<span class="cp_ball cp_ball4"></span>
					</div>
				`;
				$('#typing-indicator').append(typing_temp);
			}
			// Remove the typing indicator after a delay
			setTimeout(() => {
				$(`#${typingIndicatorId}`).remove();
			}, 5000); // Adjust the delay as needed (e.g., 5 seconds)
		});
       } 		
	},
    startPrivateChat: function (targetId) {
		const userId = this.user_id; // Replace with your logic to get the current user's ID
		this.private_id = targetId;
		if (!targetId || typeof targetId !== 'string') {
			console.error('Invalid target ID:', targetId);
			alert('An error occurred while starting the private chat.');
			return;
		}
		$('#private_wrap_content').data('target-id', targetId);
		// Emit the startPrivateChat event to the server
		this.socket.emit('startPrivateChat', { user_id: userId, target_id: targetId });
		console.log(`Private chat request sent to user ${targetId}`);
    },
		/**
		 * Delete a private message.
		 */
	delete_private_msg: function(button,messageId, targetId) {
			 messageId = button.dataset.messageId; // Extract the message ID from the button
			 targetId = $('#get_private').attr('value'); // Extract the target user's ID
			if(messageId> 0 && targetId > 0){
				if(!confirm('Are you sure you want to delete this message?')) return;
				$(button).prop('disabled', true).html('<i class="ri-loader-2-line spin"></i>');
					// Emit the deleteMessage event to the server
					this.socket.emit('deleteMessage', { msg_id: messageId, target_id: targetId }, (response) => {
						if (response.success) {
							console.log('Message deletion request sent successfully.');
							// Remove the message from the DOM
							removeMessageFromDOM(messageId);
							
						} else {
							alert(response.error || 'Failed to send deletion request.');
						}
						$(button).prop('disabled', false).html('<i class="ri-chat-delete-line"></i>');
					});				
			}
		},
    /**
     * Log socket events for debugging purposes.
     */
    logSocket: function () {
        if (!this.socket) {
            this.start();
        }
		if(user_rank ==100){
			this.socket.on('monitor', (data) => {
				displayLogsInContainer();
				saveMonitorDataToLocalStorage(data);
			});			
		}

    }
};
// Function to clear monitor data from localStorage
function clearMonitorDataFromLocalStorage() {
    const STORAGE_KEY = 'monitorData';
    desk.removeItem(STORAGE_KEY);
	 $('#SocketMonitor_wrap_stream').html('');
    console.log("Monitor data cleared from localStorage.");
}
// Function to display logs in #SocketMonitor_wrap_stream
function displayLogsInContainer() {
    // Retrieve monitor data from localStorage
    const monitorData = getMonitorDataFromLocalStorage();
    // Clear the container before appending new logs
    $('#SocketMonitor_wrap_stream').empty();
    // Iterate over the monitor data and generate HTML for each log entry
    monitorData.forEach(log => {
        const logEntry = generateLogEntry(log.data); // Use the generateLogEntry function
        $('#SocketMonitor_wrap_stream').append(logEntry); // Append to the container
    });

    // Optional: Scroll to the bottom of the container
    $('#SocketMonitor_wrap_stream').scrollTop($('#SocketMonitor_wrap_stream')[0].scrollHeight);
}
// Function to retrieve monitor data from localStorage
function getMonitorDataFromLocalStorage() {
    const STORAGE_KEY = 'monitorData';
    const monitorData = JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];
    console.log("Retrieved monitor data from localStorage:", monitorData);
    return monitorData;
}
function saveMonitorDataToLocalStorage(newData) {
    const STORAGE_KEY = 'monitorData'; // Key for storing monitor data in localStorage
    // Retrieve existing monitor data from localStorage
    let monitorData = JSON.parse(desk.getItem(STORAGE_KEY)) || [];
    // Add the new data to the array
    monitorData.push({
        timestamp: new Date().toISOString(), // Add a timestamp for when the data was received
        data: newData
    });
    // Limit the number of entries to prevent excessive storage usage
    const MAX_ENTRIES = 50; // Maximum number of monitor data entries to store
    if (monitorData.length > MAX_ENTRIES) {
        monitorData = monitorData.slice(-MAX_ENTRIES); // Keep only the latest entries
    }
    // Save the updated monitor data back to localStorage
    desk.setItem(STORAGE_KEY, JSON.stringify(monitorData));

    console.log("Monitor data saved to localStorage:", monitorData);
}
function generateLogEntry(data) {
    // Determine the log type, color, and icon
    const log_type = data.type;
    let color = '';
    let icon = '';
    switch (log_type) {
        case 'connect_to_server':
            color = 'error';
            icon = `<i class="ri-plug-line lmargin3 ${color}"></i>`;
            break;
        case 'join_room':
            color = 'success';
            icon = `<i class="ri-chat-1-line lmargin3 ${color}"></i>`;
            break;
        case 'switch_room':
            color = 'warn';
            icon = `<i class="ri-expand-width-line lmargin3 ${color}"></i>`;
            break;
        case 'left_room':
            color = 'black';
            icon = `<i class="ri-plug-fill lmargin3 ${color}"></i>`;
            break;
        case 'logged_in':
            color = 'purple';
            icon = `<i class="ri-gradienter-line lmargin3 ${color}"></i>`;
            break;
        case 'left_server':
            color = 'dark_gray';
            icon = `<i class="ri-reset-left-line lmargin3 ${color}"></i>`;
            break;
        case 'room_list':
            color = 'dark_gray';
            icon = `<i class="ri-chat-voice-fill lmargin3 ${color}"></i>`;
            break;
        default:
            color = 'default';
            icon = `<i class="ri-question-line lmargin3 ${color}"></i>`;
            break;
    }

    // Generate the timestamp
    const timestamp = new Date().toLocaleTimeString();

    // Generate the log entry HTML
    const logList = `
        <div class="sub_list_item console_data_logs ${color}" value="1">
            <div class="text_small console_log">${icon}
                <span class="bold console_user ${color}">${data.ip}:${data.text}</span>
            </div>
            <div class="console_date sub_text centered_element">${timestamp}</div>
        </div>`;

    return logList;
}
// Start the socket connection on DOMContentLoaded
document.addEventListener('DOMContentLoaded', () => {
    FUSE_SOCKET.start();
});
