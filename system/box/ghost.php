<?php
require('../config_session.php');
if(!isset($_POST['ghost'])){
	die();
}
if(!canGhost()){
	die();
}
$target = escape($_POST['ghost'], true);
$user = userDetails($target);

if(!canGhostUser($user)){
	return 0;
}
?>
<div class="modal_top">
	<div class="modal_top_empty">
		<div class="btable">
			<div class="avatar_top_mod">
				<img src="<?php echo myAvatar($user['user_tumb']); ?>"/>
			</div>
			<div class="avatar_top_name">
				<?php echo $user['user_name']; ?>
			</div>
		</div>
	</div>
	<div class="modal_top_element close_over">
		<i class="ri-close-circle-line i_btm"></i>
	</div>
</div>
<div class="pad_box">
	<div class="setting_element">
		<p class="label"><?php echo $lang['duration']; ?></p>
		<select id="ghost_delay">
			<?php echo optionMinutes(5, ghostValues()); ?>
		</select>
	</div>
	<div class="setting_element">
		<p class="label"><?php echo $lang['reason']; ?> <span class="sub_text text_xsmall"><?php echo $lang['optional']; ?></span></p>
		<textarea id="ghost_reason" maxlength="300" class="full_textarea small_textarea" type="text"/></textarea>
	</div>
	<div class="tpad10">
		<button onclick="ghostUser(<?php echo $user['user_id']; ?>);" class="reg_button delete_btn">Ghost</button>
		<button class="close_over reg_button default_btn"><?php echo $lang['cancel']; ?></button>
	</div>
</div>
