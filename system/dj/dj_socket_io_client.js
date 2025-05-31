// Fuse_broadcast_client Module with jQuery
const Fuse_broadcast_client = (() => {
    const roomId = cur_room + '_broadcast';
    var bviewer_vElement = $('.video_chat_container');
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
        // Attach onchange event to the media type select element

   

    // Function to handle media broadcast
    function handleMediaBroadcast({ mediaUrl, mediaType }) {
        console.log(`Media broadcast received: ${mediaType} - ${mediaUrl}`);
        const $iframeElement = $('#media-iframe');
        const $videoElement = $('#media-video');
        const $audioElement = $('#audio-player');

        if ($iframeElement.length === 0 || $videoElement.length === 0 || $audioElement.length === 0) {
            console.error('Media elements not found.');
            return;
        }

        // Reset the display of all media elements
        $iframeElement.hide();
        $videoElement.hide();
        $audioElement.hide();

        switch (mediaType) {
            case 'youtube':
                handleYouTubeMedia(mediaUrl, $iframeElement);
                break;
            case 'soundcloud':
                handleSoundCloudMedia(mediaUrl, $iframeElement);
                break;
            case 'mp3':
                handleAudioMedia(mediaUrl, $audioElement);
                break;
            case 'mp4':
                handleVideoMedia(mediaUrl, $videoElement);
                break;
            default:
                console.warn('Unknown media type:', mediaType);
        }
    }

    // Function to handle YouTube media
    function handleYouTubeMedia(mediaUrl, $iframeElement) {
        const videoId = extractYouTubeID(mediaUrl);
        console.log('YouTube video ID:', videoId);
        if (videoId) {
            $iframeElement.attr('src', `https://www.youtube.com/embed/${videoId}?autoplay=1&mute=0&modestbranding=1&rel=0`).show();
        } else {
            console.error('Invalid YouTube URL:', mediaUrl);
        }
    }

    // Function to handle SoundCloud media
    function handleSoundCloudMedia(mediaUrl, $iframeElement) {
        $iframeElement.attr('src', `https://w.soundcloud.com/player/?url=${mediaUrl}&auto_play=true`) // Enable autoplay
                      .show()
                      .css({ width: '100%', height: '225px' }); // SoundCloud player height
    }

    // Function to handle MP3 audio
    function handleAudioMedia(mediaUrl, $audioElement) {
        $audioElement.attr('src', mediaUrl).show();
    }

    // Function to handle MP4 video
    function handleVideoMedia(mediaUrl, $videoElement) {
        $videoElement.attr('src', mediaUrl).show();
    }

    // Function to extract YouTube video ID from URL
    function extractYouTubeID(url) {
        if (typeof url !== 'string') {
            console.error('Invalid URL:', url);
            return null;
        }
        const regex = /(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|(?:watch\?v=)|(?:playlist\?list=)|(?:shorts\/))|youtu\.be\/)([a-zA-Z0-9_-]{11})(?:[?&]|$)/;
        const match = url.match(regex);
        return match ? match[1] : null;
    }

    // Initialize media container
    function initializeMediaContainer() {
        const mediaHtml = `
            <div class="media-container">
                <iframe id="media-iframe" style="display:none;"  allow="autoplay; encrypted-media" frameborder="0" allowfullscreen></iframe>
                <video id="media-video" width="600" controls style="display:none;"></video>
                <audio id="audio-player" controls style="display:none;"></audio>
            </div>
        `;
       return  $('.video_chat_container').html(mediaHtml).show();
    }

    // Function to clear all media
    function clearMedia() {
        const $iframeElement = $('#media-iframe');
        const $videoElement = $('#media-video');
        const $audioElement = $('#audio-player');
        // Hide all media elements and clear their sources
        $iframeElement.hide().attr('src', '');
        $videoElement.hide().attr('src', '');
        $audioElement.hide().attr('src', '');
        $('.video_chat_container').hide();
        console.log('Media cleared.');
    }

    // Join the room as a viewer
    function joinRoom() {
        if (!FUSE_SOCKET || !FUSE_SOCKET.socket) {
            console.error('FUSE_SOCKET is not initialized or does not have a socket instance.');
            return;
        }
         // Use the socket from FUSE_SOCKET
        const socket = FUSE_SOCKET.socket;

        socket.emit('join_broadcast', {
            room: roomId,
            role: user_rank,
            username: user_name,
            avatar: avatar,
            user_id: user_id,
            type: 'viewer',
        });
        console.log('Joined room with ID:', user_id);
    }

    // Event listeners
    function initialize() {
        if (!FUSE_SOCKET || !FUSE_SOCKET.socket) {
            console.error('FUSE_SOCKET is not initialized or does not have a socket instance.');
            return;
        }

        const socket = FUSE_SOCKET.socket; // Use the socket from FUSE_SOCKET

        socket.on('broadcastMedia', (data) => {
            console.log(data);
            if(data.status=="ready"){
                initializeMediaContainer();
                handleMediaBroadcast(data);
            }
            
        });

        socket.on('viewerListUpdate', viewers => {
            const $viewerListElement = $('#viewer-list');
            if ($viewerListElement.length) {
                $viewerListElement.empty(); // Clear existing list
                viewers.forEach(viewer => {
                    const viewerItem = $(`
                        <div class="viewer-item">
                            <img src="${viewer.avatar}" alt="${viewer.username}'s avatar" class="viewer-avatar" />
                            <span class="viewer-username">${viewer.username}</span>
                        </div>
                    `);
                    $viewerListElement.append(viewerItem);
                });
            }
        });

        socket.on('viewerCountUpdate', count => {
            const $viewerCountElement = $('#viewerCount');
            if ($viewerCountElement.length) {
                $viewerCountElement.text(`Total viewers: ${count}`);
            }
        });

        // Handle broadcast end
        socket.on('broadcastEnd', (data) => {
            console.log(data);
            clearMedia();
            console.log('Broadcast has ended. Media cleared.');
            console.clear();
        });

        // Call joinRoom to join the room when socket is connected
        socket.on('connect', joinRoom);
    }

    // Public API
    return {
        initialize
    };
})();

// Initialize the module once the DOM is fully loaded
setTimeout(() => {
    Fuse_broadcast_client.initialize();
}, 1000);
