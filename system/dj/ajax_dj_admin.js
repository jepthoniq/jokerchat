let riseHandcount = 0; // Default role
openOnair = function() {
    $.post('system/box/onair.php', {}, function(response) {
        showEmptyModal(response, 360);
    });
}
userOnair = function() {
    $.post(FU_Ajax_Requests_File(), {
        f: 'action_member',
        s: 'user_onair',
        token: utk,
        user_onair: $('#set_user_onair').val(),
    }, function(res) {
        if (res.status == 200) {
            $("#broadcast_windows").dialog("close");
            initializeDialog('onair');
            callSaved(res.msg, 1);
        } else if (res.status == 100) {
            $("#broadcast_windows").dialog("close");
            callSaved(res.msg, 3);
        } else {
            $("#broadcast_windows").dialog("close");
            callSaved(res.msg, 3);
        }

    });

}
start_dj = function(media_type, media_url) {
    $.post(FU_Ajax_Requests_File(), {
        f: 'action_member',
        s: 'start_dj',
        token: utk,
        media_type: media_type,
        media_url: media_url,
    }, function(res) {
        if (res.status == 200) {
           callSaved(res.msg, 1);
        } else if (res.status == 100) {
            //callSaved(res.msg, 3);
        } else {
            //callSaved(res.msg, 3);
        }

    });

}
function end_dj(end, rise_id) {
    // If rise_id is empty or not provided, default to 0
    rise_id = rise_id || 0;
    // Check if rise_id is valid (non-zero), or handle end broadcast normally if no rise_id
    if (rise_id !== 0) {
        console.log("Ending DJ session for hand raise with user ID: " + rise_id);
    } else {
        console.log("Ending DJ session normally without a hand raise.");
    }
    var end_dj_with_rise_id = rise_id;
    // Send the end DJ request (with or without rise_id)
    $.post(FU_Ajax_Requests_File(), {
        f: 'action_member',
        s: 'end_dj',
        token: utk,
        end: end,
        with_rise_id: end_dj_with_rise_id,
    }, function(res) {
        if (res.status == 200) {
            $('#mediaUrl').val('');
            callSaved(res.msg, 1);
        } else {
            callSaved(res.msg, 3);
        }
    });
    console.clear();
}


broadcast_box = function(elm) {
    $.post('system/dj/admin_broadcast.php', {
        user_onair: $('#set_user_onair').val(),
    }, function(res) {
        $(elm).html(res)
    });
}
// Function to initialize the dialog
function initializeDialog(ref) {
    $("#broadcast_windows").dialog({
        draggable: true,
        resizable: true,
        modal: false, // Non-modal dialog
        autoOpen: false, // Prevent auto-open on page load
        width: $(window).width() <= 600 ? '100%' : 420,
        open: function(event, ui) {
            broadcast_box(this); // Call the onOpen function when the dialog opens
            $(this).dialog("option", "title", "DJ Control Panel"); // Update the dialog title
        },
        buttons: {
            "Close": function() {
                $(this).dialog("close");
            }
        }
    });

    $("#open-boradcast_panel").click(function() {
        $("#broadcast_windows").dialog("open");
    });
    if(ref=='onair'){
         $("#broadcast_windows").dialog("open");
    }
}

function validateUrl(url, type) {
    var regex;
    switch (type) {
        case 'youtube':
            // Regex for YouTube URLs
            regex = /^(https?:\/\/(www\.)?(youtube\.com\/(?:watch\?v=|embed\/|v\/|shorts\/)|youtu\.be\/))[\w-]+(?:\?.*)?$/;
            break;
        case 'soundcloud':
            // Regex for SoundCloud URLs
            regex = /^https?:\/\/(www\.)?soundcloud\.com\/[\w-]+\/[\w-]+$/;
            break;
        case 'mp4':
            // Regex for MP4 URLs
            regex = /^https?:\/\/.+\.mp4$/;
            break;
        case 'mp3':
            // Regex for MP3 URLs
            regex = /^https?:\/\/.+\.mp3$/;
            break;
        default:
            return false;
    }

    return regex.test(url);
}

function detectMediaType(url) {
    var type;
    // Define regex patterns for different media types
    var youtubeRegex = /^(https?:\/\/(www\.)?(youtube\.com\/(?:watch\?v=|embed\/|v\/|shorts\/)|youtu\.be\/))[\w-]+(?:\?.*)?$/;
    var soundcloudRegex = /^https?:\/\/(www\.)?soundcloud\.com\/[\w-]+\/[\w-]+$/;
    var mp4Regex = /^https?:\/\/.+\.mp4$/;
    var mp3Regex = /^https?:\/\/.+\.mp3$/;
    // Determine the media type based on the URL pattern
    if (youtubeRegex.test(url)) {
        type = 'youtube';
    } else if (soundcloudRegex.test(url)) {
        type = 'soundcloud';
    } else if (mp4Regex.test(url)) {
        type = 'mp4';
    } else if (mp3Regex.test(url)) {
        type = 'mp3';
    } else {
        type = ''; // No valid type detected
    }

    return type;
}

function validateMediaUrl(is_live) {
    var mediaType = $('#mediaType').val(); // Get selected media type
    var mediaUrl = $('#mediaUrl').val().trim(); // Get media URL
    var media_alert = $('#media_alert');
    if (!mediaUrl && is_live =='0') {
        media_alert.html('Please enter a media URL.');
        return false;
    }
    
    if (!validateUrl(mediaUrl, mediaType) && is_live =='0') {
            media_alert.html('The entered URL does not match the selected media type.');
            return false;
    }
    console.log('Broadcasting media:', mediaType, mediaUrl,is_live);
    // Add functionality to start broadcasting
    return true;
}
// Function to output the raised hands data
function outputRaisedHands(users) {
    var container = $('#raised-hands-container');
    var riseHandcount = $('.riseHandcount'); // Make sure this exists
    container.empty();

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
            }).append($('<img>', {
                src: user.avatar,
                alt: $('<div>').text(user.username).html() + "'s avatar"
            }));
            var userName = $('<div>', {
                class: 'ulist_name'
            }).append($('<p>', {
                class: 'username bgrad19',
                text: user.username
            }));
            var userOption = $('<div>', {
                class: 'ulist_option'
            });
            // Accept Hand Icon
            var acceptBtn = $('<i>', {
                class: 'ri-hand i_btm accept-btn',
                title: 'Accept Hand Raise'
            }).on('click', function () {
                dj_acceptHandRise(this, user.user_id);
            });
            // Remove Hand Icon
            var removeBtn = $('<i>', {
                class: 'ri-close-circle-line i_btm remove-btn',
                title: 'Remove Hand Raise'
            }).on('click', function () {
                dj_removeHandRise(this, user.user_id);
            });
            userOption.append(acceptBtn, removeBtn);
            userItem.append(userAvatar, userName, userOption);
            container.append(userItem);
        });
        riseHandcount.html(users.length).show();
    }
}
function dj_acceptHandRise(button, userId) {
    // Set rise_id to 0 if it is empty or does not exist
    userId = userId || 0;
    // Disable the button to prevent multiple clicks
    $(button).prop('disabled', true).css('opacity', '0.5');
    // Check if rise_id (userId) is valid (non-zero)
    if (!userId) {
        callSaved("Error: Invalid User ID.", 3);
        $(button).prop('disabled', false).css('opacity', '1');
        return; // Exit the function if the ID is invalid
    }
    // Show confirmation dialog
    $("#dj_admin_confirmation_modal").dialog({
        resizable: false,
        height: "auto",
        width: 400,
        modal: true,
        buttons: {
            "Confirm": function() {
                // On Confirm, proceed with the action
                $(this).dialog("close"); // Close the modal
                // Call your function to accept the hand raise (e.g., sending a request)
                handleAcceptHandRise("yes", userId); // Pass "yes" to handleAcceptHandRise
            },
            "Cancel": function() {
                // On Cancel, just close the modal
                $(this).dialog("close");
                // Re-enable the button in case user canceled the action
                $(button).prop('disabled', false).css('opacity', '1');
            }
        }
    });
}

function handleAcceptHandRise(end_dj_withid, userId) {
    // Set rise_id to 0 if it is empty or does not exist
    userId = userId || 0;
    // Check if rise_id (userId) is valid (non-zero)
    if (!userId) {
        callSaved("Error: Invalid User ID.", 3);
        return; // Exit the function if the ID is invalid
    }
    if (end_dj_withid === "yes") {
        // End the DJ session for the hand raise
        end_dj("end", userId); // Pass rise_id to end the broadcast with user ID
    } else {
        // End the DJ session normally without a rise_id (without user hand raise)
        end_dj("end", 0); // Pass 0 to end the broadcast normally
    }
    // Your logic for handling the accepted hand raise (e.g., making the user DJ)
    callSaved("User ID " + userId + " accepted hand raise.",1);
}

function broadcaster_admin_side(data){
	console.log(data);
}
$(document).ready(function() {
    var is_live = $('#is_livestream').val();
    // Listen for changes in the media URL input field
    $(document).on('input', '#mediaUrl', function() {
        var url = $(this).val().trim();
        if(is_live=='0'){
            is_live =0;
            var detectedType = detectMediaType(url);
            if (detectedType) {
                $('#mediaType').selectBoxIt('selectOption', detectedType);
            } else {
                $('#mediaType').selectBoxIt('selectOption', ''); // Optionally, clear the selection if no valid type detected
        }
        }  
    });

    $(document).on('change', '#mediaType', function() {
        if (is_live === '0') {
            validateMediaUrl(is_live);
        }
    });
    // Validate URL when the broadcast button is clicked
        $(document).on('click', '#broadcastBtn', function() {
            var is_live = $('#is_livestream').val(); // Make sure this returns the correct value
            // Validate media URL and type only if not livestream
            if (is_live === '0') {
                if(validateMediaUrl(is_live)) {
                    // Retrieve media type and URL from the form
                    var mediaType = $('#mediaType').val();
                    var mediaUrl = $('#mediaUrl').val().trim();
                    // Call the start_dj function with the validated data
                    start_dj(mediaType, mediaUrl);
                }
            }else if (is_live === '1') {
                callSaved("is live.", 3);
                start_dj("live", "null");
            }
        });


    $(document).on('click', '#end_broadcast', function() {
          end_dj("end", 0); // Pass 0 to end the broadcast normally
    });

    initializeDialog();
});