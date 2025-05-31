// MediaSocket Module with jQuery
const Admin_MediaSocket = (() => {
    const roomId = cur_room+'_broadcast';
    const broadcast_vElement = $('.video_chat_container');
    // Function to detect media type from the URL
    function detectMediaType(url) {
        if (url.includes('youtube.com') || url.includes('youtu.be')) {
            return 'youtube';
        } else if (url.includes('soundcloud.com')) {
            return 'soundcloud';
        } else if (url.endsWith('.mp3')) {
            return 'mp3';
        } else if (url.endsWith('.mp4')) {
            return 'mp4';
        } else {
            return null; // Unknown media type
        }
    }

    // Function to handle media URL input and auto-detect media type
    function handleMediaUrlInput() {
        const mediaUrl = $('#mediaUrl').val();
        const mediaTypeSelect = $('#mediaType');

        if (!mediaUrl) {
            // If input is empty, highlight with red and disable the mediaType select
            $('#mediaUrl').css('background-color', '#ffe0e0'); // Red background for empty input
            mediaTypeSelect.prop('disabled', true);
            return;
        } else {
            $('#mediaUrl').css('background-color', '#fff'); // Reset to white background if input is valid
        }

        const mediaType = detectMediaType(mediaUrl);
        if (mediaType) {
            mediaTypeSelect.val(mediaType); // Update the select dropdown value
            mediaTypeSelect.prop('disabled', false); // Enable the select dropdown
            mediaTypeSelect.css('background-color', '#e0ffe0'); // Green background for valid URL
        } else {
            mediaTypeSelect.val(''); // Clear the select value
            mediaTypeSelect.prop('disabled', true); // Disable the select dropdown
            mediaTypeSelect.css('background-color', '#ffe0e0'); // Red background for invalid URL
        }
    }

    // Function to join the room and emit media data
    function joinRoomAndBroadcast() {
        if (!FUSE_SOCKET || !FUSE_SOCKET.socket) {
            console.error('FUSE_SOCKET is not initialized or does not have a socket instance.');
            return;
        }
        const socket = FUSE_SOCKET.socket; // Use the socket from FUSE_SOCKET
        const mediaUrl = $('#mediaUrl').val();
        const mediaType = $('#mediaType').val();
        // Validate media URL and media type before broadcasting
        if (!mediaUrl || !mediaType) {
            // If media URL is empty or invalid media type, show alert and prevent broadcast
            $('#mediaUrl').css('background-color', '#ffe0e0'); // Highlight input in red
            alert('Invalid or empty media URL. Please provide a valid media URL and type.');
            return; // Prevent further execution
        }
        // Proceed with broadcasting if validation passes
        socket.emit('join_broadcast', {
            room: roomId,
            role: user_rank,
            username: user_name,
            avatar: avatar,
            user_id: user_id,
            type:'broadcaster',
        });

        console.log('Joined room with ID:', socket.id);
        // Broadcast the media data
        socket.emit('broadcastMedia', { room: roomId, mediaUrl, mediaType, role: user_rank, user_id: user_id,senderId:socket.id ,status:'ready'});
    }

    // Initialize event listeners
    function initialize() {
        if (!FUSE_SOCKET || !FUSE_SOCKET.socket) {
            console.error('FUSE_SOCKET is not initialized or does not have a socket instance.');
            return;
        }

        const socket = FUSE_SOCKET.socket; // Use the socket from FUSE_SOCKET

        socket.on('broadcast_viewers', ({ users }) => {
            const viewerListElement = $('#viewer-list');
            if (viewerListElement.length) {
                viewerListElement.empty(); // Clear existing list
                users.forEach(user => {
                    const viewerItem = $(`
                        <div class="viewer-item">
                            <img src="${user.avatar}" alt="${user.username}'s avatar" class="viewer-avatar" />
                            <span class="viewer-username">${user.username}</span>
                        </div>
                    `);
                    viewerListElement.append(viewerItem);
                });
            }
            // $('#viewerCount').text(`Total Viewers: ${users.length}`);
        });

        // Attach event listeners with jQuery
        $('#broadcastBtn').on('click', joinRoomAndBroadcast);
        $('#mediaUrl').on('input', handleMediaUrlInput);
         socket.on('broadcastMedia', (data) => {
            console.log(data);
            if(data.status=="ready"){
                broadcast_vElement.show();
            }
            
        });       
    }
    $('#end_broadcast').on('click', function() {
        if (FUSE_SOCKET && FUSE_SOCKET.socket) {
            const socket = FUSE_SOCKET.socket;
            
            // Emit 'endBroadcast' event to server
            socket.emit('endBroadcast', {
                room: roomId,
                user_id: user_id
            });
    
        }
    });

    // Public API
    return {
        initialize
    };
})();

// Initialize the module once the DOM is fully loaded
$(document).ready(() => {
    Admin_MediaSocket.initialize();
});
