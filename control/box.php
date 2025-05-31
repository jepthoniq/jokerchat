<div id="side_content" class="chat_side_panel">
	<div id="side_top" class="side_bar">
		<div class="side_bar_item close_side">
			<i class="ri-close-circle-line i_btm"></i>
		</div>
		<div class="bcell_mid">
		</div>
	</div>
	<div id="side_inside">
	</div>
</div>
<div id="small_modal" class="small_modal_out modal_back">
	<div id="small_modal_in" class="small_modal_in modal_in">
		<div class="modal_top">
			<div class="modal_top_empty">
			</div>
			<div class="modal_top_element close_modal">
				<i class="ri-close-circle-line i_btm"></i>
			</div>
		</div>
		<div id="small_modal_content" class="modal_content small_modal_content">
		</div>
	</div>
</div>
<div id="large_modal" class="large_modal_out modal_back">
	<div id="large_modal_in" class="large_modal_in modal_in">
		<div id="large_modal_content" class="modal_content large_modal_content">
		</div>
	</div>
</div>
<div id="over_modal" class="over_modal_out modal_back">
	<div id="over_modal_in" class="over_modal_in modal_in">
		<div class="modal_top">
			<div class="modal_top_empty">
			</div>
			<div class="modal_top_element close_over">
				<i class="ri-close-circle-line i_btm"></i>
			</div>
		</div>
		<div id="over_modal_content" class="modal_content over_modal_content">
		</div>
	</div>
</div>
<div id="over_emodal" class="over_emodal_out modal_back">
	<div id="over_emodal_in" class="over_emodal_in modal_in">
		<div id="over_emodal_content" class="modal_content over_emodal_content">
		</div>
	</div>
</div>
<?php if (function_exists('isAVCallPurchased') && isAVCallPurchased()) { ?>
<div id="container_stream_audio" class="streamers audstream background_stream ui-draggable">
	<div class="btable stream_header">
		<div id="move_audio" class="bcell_mid ui-draggable-handle">
		</div>
		<div onclick="toggleStreamAudio(1);" class="bcell_mid vidminus vidopt">
			<i class="fa fa-minus"></i>
		</div>
		<div onclick="closeAudio();" class="bcell_mid vidopt">
			<i class="fa fa-times"></i>
		</div>
	</div>
	<div id="wrap_stream_audio">
	</div>
</div>
<div id="container_call" class="callsize streamers vcallstream background_stream call_back ui-draggable">
	<div class="btable stream_header">
		<div id="move_cam" class="bcell_mid ui-draggable-handle"></div>
		<div onclick="toggleCall(1);" class="bcell_mid vidopt">
			<i class="ri-subtract-line"></i>
		</div>
		<div class="hide_call bcell_mid vidopt">
			<i class="ri-close-circle-line"></i>
		</div>
	</div>
	<div id="wrap_call" class="callsize"></div>
</div>
<div id="call_request" data="" class="btable back_modal bshadow fborder hpad10 vpad10 fhide">
	<div class="bcell_mid call_avatar">
		<img id="call_request_avatar" src="">
	</div>
	<div class="bcell_mid call_name bellips hpad10">
		<p id="call_request_type" class="text_xsmall"></p>
		<p id="call_request_name" class="bold"></p>
	</div>
	<div onclick="acceptCall();" class="bcell_mid call_btn">
		<img src="default_images/call/accept.svg">
	</div>
	<div class="bcell_mid call_spacer">
	</div>
	<div onclick="declineCall();" class="bcell_mid call_btn">
		<img src="default_images/call/decline.svg">
	</div>
</div>
<?php } ?>
<div class="saved_data">
	<span class="saved_span"></span>
</div>
<?php if(boomLogged()){ ?>
<audio class="hidden" id="private_sound" src="<?php echo $data['domain']; ?>/sounds/private.mp3<?php echo $bbfv; ?>"></audio>
<audio class="hidden" id="message_sound" src="<?php echo $data['domain']; ?>/sounds/new_messages.mp3<?php echo $bbfv; ?>"></audio>
<audio class="hidden" id="username_sound" src="<?php echo $data['domain']; ?>/sounds/username.mp3<?php echo $bbfv; ?>"></audio>
<audio class="hidden" id="whistle_sound" src="<?php echo $data['domain']; ?>/sounds/whistle.mp3<?php echo $bbfv; ?>"></audio>
<audio class="hidden" id="notify_sound" src="<?php echo $data['domain']; ?>/sounds/notify.mp3<?php echo $bbfv; ?>"></audio>
<audio class="hidden" id="news_sound" src="<?php echo $data['domain']; ?>/sounds/new_news.mp3<?php echo $bbfv; ?>"></audio>
<audio class="hidden" id="clear_sound" src="<?php echo $data['domain']; ?>/sounds/clear.mp3<?php echo $bbfv; ?>"></audio>
<audio class="hidden" id="join_sound" src="<?php echo $data['domain']; ?>/sounds/join.mp3<?php echo $bbfv; ?>"></audio>
<audio class="hidden" id="leave_sound" src="<?php echo $data['domain']; ?>/sounds/leave.mp3<?php echo $bbfv; ?>"></audio>
<audio class="hidden" id="action_sound" src="<?php echo $data['domain']; ?>/sounds/action.mp3<?php echo $bbfv; ?>"></audio>
<audio class="hidden" id="okay_sound" src="<?php echo $data['domain']; ?>/sounds/success.mp3<?php echo $bbfv; ?>"></audio>
<audio class="hidden" id="levelup_sound" src="sounds/levelup.mp3<?php echo $bbfv; ?>"></audio>
<audio class="hidden" id="badge_sound" src="sounds/badge.mp3<?php echo $bbfv; ?>"></audio>
<audio class="hidden" id="quote_sound" src="sounds/quote.mp3<?php echo $bbfv; ?>"></audio>
<audio class="hidden" id="call_in" src="sounds/call_in.mp3<?php echo $bbfv; ?>"></audio>
<audio class="hidden" id="call_out" src="sounds/call_out.mp3<?php echo $bbfv; ?>"></audio>
<audio class="hidden" id="callend_sound" src="sounds/call_end.mp3<?php echo $bbfv; ?>"></audio>
<?php } ?>