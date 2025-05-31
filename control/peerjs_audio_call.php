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
    $isHunter = true;
    $otherUserId = $target['user_id'];
    $myUsername = $hunter['user_name'];      // Hunter username
    $otherUsername = $target['user_name'];    // Target username
} elseif ($current_user_id == $call['call_target']) {
    $my_avatar = myAvatar($target['user_tumb']); // Current user is the target
    $other_avatar = myAvatar($hunter['user_tumb']); // Other user is the hunter
    $isHunter = false;
    $otherUserId = $hunter['user_id'];
    $myUsername = $target['user_name'];       // Target username
    $otherUsername = $hunter['user_name'];     // Hunter username
} else {
    $my_avatar = '';
    $other_avatar = '';
    $isHunter = false;
    $otherUserId = null;
    $myUsername = 'Anonymous';
    $otherUsername = 'Anonymous';
}
?>
<style>
.call_background { background-image: url("<?php echo $other_avatar;?>"); background-position: center; background-repeat: no-repeat; background-size: cover; }
#vcall_self{display:none}
.call_body { backdrop-filter: blur(5px); background-color: #00000057; border-radius: 0; padding: 1px; color: white; }
.mic-state-icon{font-size: 17px;}
.remote-user-list{position: absolute;top: 0;right: 0;left: 0;margin: 0 auto;}
</style>
<div class="btable call_background" style="width:100%; height:100%;">
	<div class="bcell_mid centered_element call_body" style="width:100%; height:100%;">
		<div style="position:absolute; bottom: 60px;right: 0;left: 0;margin: 0 auto;"> Mic Status: <span id="mic-state-icon" class="mic-state-icon"> <i class="ri-mic-line" style="color: green;"></i> </span> </div>
		<!-- Remote User List / Mic Status Container -->
		<div id="remote-user-list" class="remote-user-list" style="position: absolute;top: 0;right: 0;left: 0;margin: 0 auto;"></div>
		<div id="vcall_streams"></div>
		<div id="vcall_self" class="vcallhide"></div>
		<div id="vcall_control_wrap">
			<div id="vcall_control">
				<div class="bcell"></div>
				<div id="vcall_mic" class="bcell_mid vcall_btn"><img class="vcall_icon" src="default_images/call/microphone.svg" /></div>
				<div class="bcell vcall_spacer"></div>
				<div id="vcall_leave" class="bcell_mid vcall_btn"><img class="vcall_icon" src="default_images/call/leave.svg" /></div> 
				<div class="bcell"></div>
			</div>
		</div>
	</div>
</div>
<script src="https://unpkg.com/peerjs@1.4.7/dist/peerjs.min.js<?php echo $bbfv; ?>"></script>
<script src="system/webrtc/voice_call/peerjs/main_peerjs.js<?php echo $bbfv; ?>"></script>

<script>
const isHunter = <?= json_encode($isHunter); ?>;
const otherUserId = '<?= $isHunter ? $target['user_id'] : $hunter['user_id'] ?? 'default_room' ?>';
const myUsername = <?= json_encode($myUsername); ?>;
const otherUsername = <?= json_encode($otherUsername); ?>;
const callManager = new PeerCallManager({
        userId: '<?= $data['user_id'] ?>',
        roomId: 'p2p_<?= $call['call_id'] ?? 'default_room' ?>',
		username: myUsername,
        callType: 'audio',
        domain: '<?= $data['domain'] ?>',
        utk: '<?= setToken() ?>',
        curPage: 'call'
});
// Make globally available for debugging
window.callManager = callManager;	
// If hunter, start call automatically
if (isHunter) {
    setTimeout(() => {
        console.log("Hunter calling:", otherUserId);
        callManager.call(otherUserId);
    }, 2000); // Wait for peer to be ready
}	
</script>