<?php
require('../config_session.php');

if(!userDj($data)){
	return 0;
}
?>
<div class="modal_top">
	<div class="modal_top_empty">
		<div class="btable">
			<div class="avatar_top_mod">
				<img src="<?php echo myAvatar($data['user_tumb']); ?>"/>
			</div>
			<div class="avatar_top_name">
				<?php echo $data['user_name']; ?>
			</div>
		</div>
	</div>
	<div class="modal_top_element cancel_modal">
		<i class="ri-close-circle-line i_btm"></i>
	</div>
</div>
<div class="pad_box">
	<div class="setting_element ">
		<p class="label"><?php echo $lang['onair_status']; ?></p>
		<select id="set_user_onair" onchange="userOnair(this);">
			<?php echo onOff($data['user_onair']); ?>
		</select>
	</div>
	<div class="tpad10 centered_element">
		<button class="cancel_modal reg_button default_btn"><?php echo $lang['cancel']; ?></button>
	</div>
</div>
