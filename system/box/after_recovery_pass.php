<?php
require_once('../config_session.php');
?>
<div class="pad_box">
	<div class="setting_element">
		<p class="label"><?php echo $lang['password']; ?></p>
		<input type="text" id="new_user_password"  class="full_input"/>
	</div>
	<div class="tpad5">
		<button onclick="after_recovery_pass(<?php echo $data['user_id']; ?>);" class="reg_button theme_btn"><i class="ri-save-fill"></i><?php echo $lang['save']; ?></button>
		<button class="reg_button cancel_over default_btn"><?php echo $lang['cancel']; ?></button>
	</div>
</div>