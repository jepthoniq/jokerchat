<?php
require('../config_session.php');

if(!isWarned($data)){
	echo 0;
	die();
}
?>
<div class="pad15">
	<div class="modal_content">
		<div class="centered_element tpad25">
			<div class="bpad3">
				<img class="med_icon" src="default_images/icons/warning.svg"/>
			</div>
			<div class="bpad15 tpad10">
				<p class="text_med bold"><?php echo $lang['warning']; ?></p>
				<p class="tpad5"><?php echo $data['warn_msg']; ?></p>
			</div>
		</div>
	</div>
	<div class="modal_control centered_element">
		<button onclick="acceptWarn();" class="reg_button ok_btn"><i class="ri-alarm-warning-fill"></i><?php echo $lang['ok']; ?></button>
	</div>
</div>
