<?php
require('../config_session.php');

if(!useGold()){
	die();
}
if(!isset($_POST['target'])){
	die();
}
$target = escape($_POST['target'], true);
$user = userRelationDetails($target);
if(empty($user)){
	echo 0;
	die();
}
if(!canShareGold($user)){
	echo 0;
	die();
}
?>
<div class="modal_content pad10">
	<div class="bpad10">
		<div class="text_med bold bpad10">
			<p><?php echo $lang['gold_balance']; ?></p>
		</div>
		<div class="btable">
			<div class="bcell_mid gold_icon2">
				<img src="<?php echo goldIcon(); ?>"/> 
			</div>
			<div class="bcell_mid gold_text2 hpad5">
				<?php echo $data['user_gold']; ?>
			</div>
		</div>
	</div>
	<div class="setting_element">
		<p class="label"><?php echo $lang['amount_share']; ?></p>
		<select id="gold_shared">
			<?php echo optionCount(1000, 1000, maxGoldShare(), 1000); ?>
		</select>
	</div>
</div>
<div class="modal_control pad10">
	<button class="reg_button theme_btn" onclick="shareGold(<?php echo $user['user_id']; ?>);"><?php echo $lang['send']; ?></button>
	<button class="reg_button default_btn cancel_over"><?php echo $lang['cancel']; ?></button>
</div>