<?php
if(!defined('BOOM')){
	die();
}
?>
<div class="btable" style="width:100%; height:100%;">
	<div class="bcell_mid centered_element" style="width:100%; height:100%;">
		<div id="vcall_streams">
		</div>
		<div id="vcall_self" class="vcallhide">
		</div>
		<div id="vcall_control_wrap">
			<div id="vcall_control">
				<div class="bcell">
				</div>
				<div id="vcall_cam" class="bcell_mid vcall_btn">
					<img class="vcall_icon" src="default_images/call/video.svg" />
				</div>
				<div class="bcell vcall_spacer">
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
<script src='system/webrtc/voice_call/agora/main_video.js<?php echo $bbfv; ?>'></script>