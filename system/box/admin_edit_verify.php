<?php
require_once('../config_session.php');
if(!isset($_POST['target']) || !canVerify()){
	die();
}
$target = escape($_POST['target'], true);
$user = userDetails($target);
if(verified($user) || !canEditUser($user, $data['can_verify'], 0)){
	echo 0;
	die();
}
?>
<div class="pad20">
	<p class="label"><?php echo $lang['account_status']; ?></p>
	<select id="profile_change_verify" onchange="changeUserVerify(this, <?php echo $user['user_id']; ?>);">
		<option value="0" <?php echo selCurrent($user['verified'], 0); ?>><?php echo $lang['unverified']; ?></option>
		<option value="1" <?php echo selCurrent($user['verified'], 1); ?>><?php echo $lang['verified']; ?></option>
	</select>
</div>