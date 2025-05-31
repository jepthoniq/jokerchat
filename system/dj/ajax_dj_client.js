var currentMedia = {
    url: null,
    type: null
};
let isBroadcaster = false; // Default role
// Main function to handle broadcast updates
function Fuse_broadcast_client(data) {
    const dj_data = data.dj_data;
    const bviewer_vElement = $('.broadcast_chat_container');
    const riseHandcount = $('.riseHandcount');
    // Function to initialize the media container if not already present
    function initializeMediaContainer() {
        // Check if the media container already exists
        if (!bviewer_vElement.find('.media-container').length) {
            let otherElm1 = '';
            let otherElm2 = '';
            // Determine elements based on the user's role
            if (user_id !== dj_data.dj_id) {
                otherElm1 = `
                    <div data="${dj_data.dj_id}" onclick="loadGiftPanelSuccessfully(this);" 
                         id="broadcaster_gifts" class="broad_icon">
                         <i class="ri-gift-line"></i>
                    </div>`;
                otherElm2 = `
                    <div onclick="riseHand_dj('${dj_data.id}')" id="broadcaster_risehand" class="broad_icon">
                        <i class="ri-hand"></i>
                    </div>`;
            }else{
                isBroadcaster = true;
            }

            // Construct media HTML template
            const mediaHtml = `
                <div id="broadcaster_header" class="broadcaster_header">
                    <div id="broadcaster_block"></div>
                    ${otherElm1}
                    ${otherElm2}
                </div>
                <div class="media-container">
                    <iframe id="media-iframe" style="display:none;" 
                            allow="autoplay; encrypted-media" 
                            frameborder="0" allowfullscreen></iframe>
                    <video id="media-video" width="600" controls style="display:none;"></video>
                    <audio id="audio-player" controls style="display:none;"></audio>
                    <div id="liveIframe" style="display:none;"></div>
                </div>
            `;

            // Append the media HTML to the viewer element
            bviewer_vElement.html(mediaHtml);
        }

        // Ensure the media container is visible
        bviewer_vElement.show();
    }
// Function to output the raised hands data
function outputRaisedHands(users) {
    var container = $('#raised-hands-container');
    container.empty(); // Clear existing content
    if (users.length === 0) {
        container.append('<p>No users have raised their hands.</p>');
        riseHandcount.html('').hide();
    } else {
        $.each(users, function(index, user) {
            var userItem = $('<div>', {
                class: 'ulist_item'
            });
            var userAvatar = $('<div>', {
                    class: 'ulist_avatar'
                })
                .append($('<img>', {
                    src: user.avatar,
                    alt: user.username + "'s avatar"
                }));
            var userName = $('<div>', {
                    class: 'ulist_name'
                })
                .append($('<p>', {
                    class: 'username bgrad19',
                    text: user.username
                }));
            var userOption = $('<div>', {
                    class: 'ulist_option',
                    onclick: 'removeHandrise(this, ' + user.user_id + ');'
                })
                .append($('<i>', {
                    class: 'ri-hand i_btm'
                }));

            userItem.append(userAvatar).append(userName).append(userOption);
            container.append(userItem);
        });
        riseHandcount.html(users.length).show();
    }
}
    // Function to handle media broadcast
    function handleMediaBroadcast({
        mediaUrl,
        mediaType
    }) {
        if (mediaUrl !== currentMedia.url || mediaType !== currentMedia.type) {
            console.log(`Media broadcast received: ${mediaType} - ${mediaUrl}`);
            initializeMediaContainer();
            const $iframeElement = $('#media-iframe');
            const $videoElement = $('#media-video');
            const $audioElement = $('#audio-player');
             const $liveIframe = $('#liveIframe');
            // Reset the display of all media elements
            resetMediaElements($iframeElement, $videoElement, $audioElement,$liveIframe);
            // Handle new media type
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
                case 'live':
                    handleLiveMedia(mediaUrl, $liveIframe);
                    break;                    
                default:
                    console.warn('Unknown media type:', mediaType);
            }
            // Update current media state
            currentMedia.url = mediaUrl;
            currentMedia.type = mediaType;
        } else {
            console.log('Media URL and type have not changed.');
        }
    }

    // Function to handle broadcaster admin info
    function broadcaster_admin(broadData) {
        const {
            avatar,
            username
        } = broadData;
        const string = `
            <div class="user_item"  onclick="getProfile(${dj_data.dj_id});"> 
                <div class="user_item_avatar">
                    <img class="avav acav avsex nosex" src="${avatar}">
                </div> 
                <div class="user_item_data">${username}</div>
            </div>`;
        $('#broadcaster_block').html(string);
    }

    // Function to handle YouTube media
    function handleYouTubeMedia(mediaUrl, $iframeElement) {
        const videoId = extractYouTubeID(mediaUrl);
        if (videoId) {
            $iframeElement.attr('src', `https://www.youtube.com/embed/${videoId}?autoplay=1&mute=0&modestbranding=1&rel=0`).show();
        } else {
            console.error('Invalid YouTube URL:', mediaUrl);
        }
    }

    // Function to handle SoundCloud media
    function handleSoundCloudMedia(mediaUrl, $iframeElement) {
        $iframeElement.attr('src', `https://w.soundcloud.com/player/?url=${mediaUrl}&auto_play=true`)
            .show()
            .css({
                width: '100%',
                height: '178px' // SoundCloud player height
            });
    }

    // Function to handle MP3 audio
    function handleAudioMedia(mediaUrl, $audioElement) {
        $audioElement.attr('src', mediaUrl).show();
    }

    // Function to handle MP4 video
    function handleVideoMedia(mediaUrl, $videoElement) {
        $videoElement.attr('src', mediaUrl).show();
    }
    // Function to handle live media
    function handleLiveMedia(mediaUrl, $liveIframe) {
            // Append the Jitsi API script dynamically
            $.getScript('https://jitsi.riot.im/external_api.js')
                .done(function(script, textStatus) {
                    console.log('Jitsi API script loaded successfully.');

                    // Check if Jitsi iframe already exists
                    if ($('#liveIframe iframe').length === 0) {
                        // Initialize Jitsi only if the iframe doesn't exist
                  const domain = 'jitsi.riot.im';
                   const options = {
                            roomName: mediaUrl,
                            userInfo: {
                               displayName: isBroadcaster ? 'Broadcaster' : 'Viewer',
                            },
                            configOverwrite: {
                                startWithAudioMuted: !isBroadcaster,
                                startWithVideoMuted: true,  // Mute viewers' video on join
                                disableDeepLinking: true,   // Prevent mobile app prompts
                                disableAudio: !isBroadcaster, // Disable audio for viewers
                                disableVideo: !isBroadcaster,  // Enable video for broadcaster only
                                disableInviteFunctions: true,  // Disable invite functions for viewers
                                prejoinPageEnabled: false,
                            },
                            interfaceConfigOverwrite: {
                                DISABLE_JOIN_LEAVE_NOTIFICATIONS: true,  // Disable notifications
                                TOOLBAR_BUTTONS: isBroadcaster? ['microphone', 'camera', 'fullscreen', 'hangup','settings']: ['fullscreen', 'hangup'],
                                SHOW_JITSI_WATERMARK: false,  // No watermark
                                SHOW_BRAND_WATERMARK: false,  // No brand watermark
                                MOBILE_APP_PROMO: false,  // No mobile app prompts
                                prejoinPageEnabled: false,
                            },
                            parentNode: document.querySelector('#liveIframe') // The DOM element for Jitsi iframe
                        };
                        const api = new JitsiMeetExternalAPI(domain, options);
                          if (isBroadcaster) {
                            api.addEventListener('videoConferenceJoined', () => {
                                api.executeCommand('toggleVideo'); // Enable broadcaster's video
                                api.executeCommand('toggleAudio'); // Enable broadcaster's audio
                                api.executeCommand('subject', user_id);
                                api.executeCommand('displayName', user_name);
                                api.executeCommand('avatarUrl', avatar);
                            });
                        }
                        // Optionally handle viewer permissions
                        api.addEventListener('videoConferenceJoined', () => {
                            if (!isBroadcaster) {
                                console.log('Viewer joined, muting video/audio.');
                                api.executeCommand('muteVideo'); // Mute viewer's video
                                //api.executeCommand('muteAudio'); // Mute viewer's audio

                                api.executeCommand('setToolbarVisible', false); // Example: Hide the toolbar for viewers
                                api.executeCommand('subject', user_id);
                                api.executeCommand('displayName', user_name);
                                api.executeCommand('avatarUrl', avatar);
                              
                            }
                        }); 
                     api.on('readyToClose', () => {
                        $('#liveIframe').html('').hide();
                        bviewer_vElement.hide();
                    });                    
                       
                    } else {
                        console.log('Jitsi iframe already exists.');
                    }
                })
                .fail(function(jqxhr, settings, exception) {
                    console.error('Failed to load Jitsi API script.');
                });
                $liveIframe.show();

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

    // Function to reset all media elements to default hidden state
    function resetMediaElements($iframeElement, $videoElement, $audioElement,$liveIframe) {
        $iframeElement.hide().attr('src', '');
        $videoElement.hide().attr('src', '');
        $audioElement.hide().attr('src', '');
        $liveIframe.hide().html('');
    }

    // Function to clear all media and hide the media container
    function clearMedia() {
        const $iframeElement = $('#media-iframe');
        const $videoElement = $('#media-video');
        const $audioElement = $('#audio-player');
        const $liveIframe = $('#liveIframe');
        riseHandcount.html('').hide();
        resetMediaElements($iframeElement, $videoElement, $audioElement,$liveIframe);
        bviewer_vElement.hide(); // Hide the media container
         $liveIframe.hide().html('');
        // Reset current media state
        currentMedia = {
            url: null,
            type: null
        };
    }

    // Check DJ data and status
    if (dj_data && dj_data.status === "active") {
        handleMediaBroadcast(dj_data);
        broadcaster_admin(dj_data);
        // Call the function with the example data
        outputRaisedHands(dj_data.raised_hands);
    } else if (data.status === 404 || (dj_data && dj_data.status !== "active")) {
        clearMedia(); // Clear media if DJ is not active or if status is 404
    }
}
riseHand_dj = function(bId) {
    $.post(FU_Ajax_Requests_File(), {
        f: 'action_member',
        s: 'risehand_dj',
        token: utk,
        b_id: bId,
    }, function(res) {
        if (res.status == 200) {
            callSaved(res.msg, 1);
        } else {
            callSaved(res.msg, 3);
        }

    });
    console.clear();
}
// Initialize the module once the DOM is fully loaded
$(document).ready(() => {
    setTimeout(() => {
        // Example data for initialization
        Fuse_broadcast_client({
            dj_data: {
                mediaUrl: null,
                mediaType: null,
                status: 'ended' // Example status
            },
            status: 404 // Example status
        });
    }, 1000);
});