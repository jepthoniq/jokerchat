<?php
// Fetch user data for the hunter and target
$hunter = fuse_user_data($call['call_hunter']);
$target = fuse_user_data($call['call_target']);

// Define the current user's ID
$current_user_id = $data['user_id'];

// Determine which avatar belongs to the current user and the other user
if ($current_user_id == $call['call_hunter']) {
    $my_avatar = myAvatar($hunter['user_tumb']); // Current user is the hunter
    $other_avatar = myAvatar($target['user_tumb']); // Other user is the target
} elseif ($current_user_id == $call['call_target']) {
    $my_avatar = myAvatar($target['user_tumb']); // Current user is the target
    $other_avatar = myAvatar($hunter['user_tumb']); // Other user is the hunter
} else {
    // Fallback in case the current user is neither the hunter nor the target
    $my_avatar = ''; // Default or placeholder avatar
    $other_avatar = ''; // Default or placeholder avatar
}

?>
<style>
.call_background {
    background-image: url("<?php echo $other_avatar;?>");
    background-position: center;
    background-repeat: no-repeat;
    background-size: cover;
}
#vcall_self{
	display:none
}
.call_body { backdrop-filter: blur(5px); background-color: #00000057; border-radius: 0; padding: 1px; color: white; }
</style>
<div class="btable call_background" style="width:100%; height:100%;">
	<div class="bcell_mid centered_element call_body" style="width:100%; height:100%;">
		<div id="vcall_streams">
		</div>
		<div id="vcall_self" class="vcallhide">
		</div>
		<div id="vcall_control_wrap">
			<div id="vcall_control">
				<div class="bcell">
				</div>
				<div id="vcall_mic" class="bcell_mid vcall_btn">
					<img class="vcall_icon" src="default_images/call/microphone.svg" />
				</div>
				<div class="bcell vcall_spacer">
				</div>
				<div id="vcall_leave" class="bcell_mid vcall_btn">
					<img class="vcall_icon" src="default_images/call/leave.svg" />
				</div> 
				<div class="bcell">
				</div>
			</div>
		</div>
	</div>
</div>
<script>
var appid = '<?php echo $data['call_appid']; ?>';
var approom = '<?php echo $call['call_room']; ?>';
var apptoken = '<?php echo $call['call_token']; ?>';
var appuser = <?php echo (int) $data['user_id']; ?>;
var domain = '<?php echo $data['domain']; ?>';	
var appcall = '<?php echo $call['call_id']; ?>';
</script>
<script src="system/webrtc/voice_call/agora/AgoraRTC_N-4.23.1.js<?php echo $bbfv; ?>"></script>
<script src='system/webrtc/voice_call/agora/main_audio.js<?php echo $bbfv; ?>'></script>