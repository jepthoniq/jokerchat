<?php
require('../config_session.php');
if(!useCall()){
	die();
}
?>
<div class="modal_content">
	<div class="pad_box">
		<div class="boom_form">
			<p class="label"><?php echo $lang['call']; ?></p>
			<select id="set_user_call">
				<option <?php echo selCurrent($data['user_call'], 1); ?> value="1"><?php echo $lang['on']; ?></option>
				<?php if(boomAllow(1)){ ?>
				<option <?php echo selCurrent($data['user_call'], 3); ?> value="3"><?php echo $lang['members_only']; ?></option>
				<option <?php echo selCurrent($data['user_call'], 2); ?> value="2"><?php echo $lang['friend_only']; ?></option>
				<?php } ?>
				<option <?php echo selCurrent($data['user_call'], 0); ?> value="0"><?php echo $lang['off']; ?></option>
			</select>
		</div>
		<button onclick="saveCallSettings();" class="reg_button theme_btn"><i class="ri-upload-cloud-2-fill"></i> <?php echo $lang['save']; ?></button>
		<button class="cancel_over reg_button default_btn"><?php echo $lang['cancel']; ?></button>
	</div>
</div>
