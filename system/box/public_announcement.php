<?php
require('../config_session.php');
?>

<div class="modal_top">
    <div class="modal_top_empty">
        <div class="btable">
            <div class="avatar_top_mod">
                <img src="<?php echo myAvatar($data['user_tumb']); ?>" alt="User Avatar" />
            </div>
            <div class="avatar_top_name">
                <?php echo htmlspecialchars($data['user_name']); ?>
            </div>
        </div>
    </div>
    <div class="modal_top_element close_over">
        <i class="ri-close-circle-line i_btm"></i>
    </div>
</div>
<div class="pad_box">
    <div class="setting_element">
        <p class="label">Sending Type</p>
        <select id="sendingType">
            <option value="room">Current Room</option>
            <option value="public">All Rooms</option>
        </select>
    </div>
    <div class="setting_element">
        <p class="label">Say something nice, :<span class="sub_text text_xsmall"</span></p>
        <textarea id="content_message" maxlength="300" class="full_textarea small_textarea <?php echo get_fontStyle(); ?>" placeholder="Start typing..."></textarea>
    </div>
    <div class="tpad10">
        <button id="send_message" class="reg_button delete_btn"><?php echo htmlspecialchars($lang['send']); ?></button>
        <button id="cancel_message" class="close_over reg_button default_btn"><?php echo htmlspecialchars($lang['cancel']); ?></button>
    </div>
</div>

<script data-cfasync="false">
$(document).ready(function() {
    // Initialize the Socket.IO client
    const sendMessageButton = document.getElementById("send_message");
    const cancelMessageButton = document.getElementById("cancel_message");
    const contentMessageTextarea = document.getElementById("content_message");
    const sendingTypeSelect = document.getElementById("sendingType");
	const font_style_cls = '<?php echo get_fontStyle(); ?>';
    // Handle sending the message
    sendMessageButton.addEventListener("click", () => {
        const messageContent = contentMessageTextarea.value.trim();
        const sendingType = sendingTypeSelect.value;

        if (!messageContent || messageContent === "Start typing...") {
            alert("Please enter a valid message.");
            return;
        }

        // Prepare the payload
        const payload = {
            message: '<div class="'+font_style_cls+'" >'+messageContent+'</div>',
            sender: "<?php echo htmlspecialchars($data['user_name']); ?>",
			room:cur_room,
			user_id:'<?php echo htmlspecialchars($data['user_id']); ?>',
            sendingType: sendingType, // Replace "current_room_id" dynamically
        };

        try {
            // Emit the message directly to the Socket.IO server
            FUSE_SOCKET.socket.emit("newMessage", payload);

            alert("Message sent successfully!");
            contentMessageTextarea.value = ""; // Clear the textarea
        } catch (error) {
            console.error("Error sending message:", error);
            alert("An error occurred while sending the message.");
        }
    });

    // Handle canceling the message
    cancelMessageButton.addEventListener("click", () => {
        contentMessageTextarea.value = ""; // Clear the textarea
        // Optionally close the modal here
    });
});
</script>