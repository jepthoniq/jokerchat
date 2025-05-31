<?php 
require('../config_session.php');

if(!isset($_POST['target'])){
	echo 0;
	die();
}
$target = escape($_POST['target']);
$user = userDetails($target);

if(!boomAllow(100)){
	die();
}
?>
<div class="pad20">
	<p class="label">Promotion to membership</p>
	<select id="upgrade_premium_user" onchange="upgradeUserPremium(<?php echo $user['user_id']; ?>);">
		<option <?php echo selCurrent($user['user_prim'], 0); ?> value="0">No premium</option>
		<option <?php echo selCurrent($user['user_prim'], 7); ?> value="7">premium 7 days</option>
		<option <?php echo selCurrent($user['user_prim'], 15); ?> value="15">premium 15 days</option>
		<option <?php echo selCurrent($user['user_prim'], 30); ?> value="30">Primum 1 month</option>
		<option <?php echo selCurrent($user['user_prim'], 180); ?> value="180">Premium 6 months</option>
		<option <?php echo selCurrent($user['user_prim'], 365); ?> value="365">Premium 1 year</option>
	</select>
</div>