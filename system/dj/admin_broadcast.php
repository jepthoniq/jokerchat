<?php
require('../config_session.php');
$cacheBuster = time() . mt_rand(1000, 9999); // Time-based random number
$db->where("dj_id", $data['user_id']);
$db->where("room_id", $data['user_roomid']);
$existing_dj = $db->getOne("dj");
if (empty($existing_dj)) {
    $media_url ='';
    $media_type = '';
}else{
$media_url =$existing_dj['mediaurl'];
$media_type = $existing_dj['mediatype'];
   
}
if(!isOnAir($data)){
    $msg = '<p onclick="openOnair();" class="pad20 centered_element bgcolor1"> <i class="ri-headphone-fill menuo"></i>click Here to Go onAir</p>';
    echo $msg;
    exit();      
}
?>
<div id="" class="modal_content">
	<div class="modal_menu">
		<ul>
			<li class="modal_menu_item modal_selected" data="mroom_setting" data-z="broadcast_setting"><i class="ri-wireless-charging-line"></i>Options</li>
			<li class="modal_menu_item" data="mroom_setting" data-z="waiting_list" style=" display: inline-flex; "><div id="rise_hand" class="rise_hand"><i class="ri-hand"></i>Rise Hand</div><div class="riseHandcount"></div></li>
		</ul>
	</div>
	<div id="mroom_setting">
		<div class="modal_zone pad10" id="broadcast_setting">
			<div class="boom_form">
			    <div id="media_alert"></div>
				<div class="setting_element">
					<p class="label"><label for="mediaType">Media Type:</label></p>
					<select id="mediaType">
					    <option value="live">LiveStream</option>
                        <option value="youtube">YouTube</option>
                        <option value="soundcloud">SoundCloud</option>
                        <option value="mp4">MP4 Video</option>
                        <option value="mp3">MP3 Audio</option>
					</select>
				</div>
				<div class="setting_element" id="control_input">
					<p class="label"><label for="mediaUrl">Media URL:</label></p>
					<input id="mediaUrl" class="full_input" value="<?php echo $media_url; ?>" type="text" placeholder="Enter media URL here"/>
					<input id="is_livestream" class="hidden" value="0" type="hidden" />
				</div>
                <div class="pad10 centered_element">
                	<button id="broadcastBtn" class="reg_button ok_btn"><i class="ri-broadcast-fill"></i>Broadcast Media</button>
                	<button id="end_broadcast" class="reg_button delete_btn"><i class="ri-shut-down-line"></i>End Broadcast</button>
                </div>
			</div>


		</div>
		<div class="modal_zone hide_zone" id="waiting_list">
			<div class="ulist_container">
				<p class="label_line">Waiting List </p>
				<div class="vpad15" id="raised-hands-container"></div>
			</div>
		</div>

	</div>
</div>
<div id="dj_admin_confirmation_modal" title="Confirm Action" style="display:none;">
    <p>If you accept the hand raise for this user, your DJ will be off.</p>
</div>

<script>
selectIt();
$('#mediaType').selectBoxIt(); 
// Initialize selectBoxIt on #mediaType
$('#mediaType').on('change', function() {
    var selectedType = $(this).val();
    var is_live = $('#is_livestream');
    if (selectedType === 'live') {
        $('#control_input').hide();  // Hide the media URL field when "livestream" is selected
        is_live.val('1');
    } else {
        $('#control_input').show();  // Show the media URL field for other media types
         is_live.val('0');
    }
});
$(document).ready(function() {
    $('#mediaType').trigger('change');
});

</script>