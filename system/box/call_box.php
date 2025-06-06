<?php
require('../config_session.php');
if(!isset($_POST['target'])){
	die();
}
$target = escape($_POST['target'], true);
$user = userRelationDetails($target);

$no_call = 0;

if(!useCall()){
	$no_call++;
}

if(!canCallUser($user)){
	$no_call++;
}
?>
<?php if($no_call > 0){ ?>
<div class="modal_content">
	<div class="centered_element hpad25">
		<div class="centered_element">
			<img class="med_avatar brad50" src="<?php echo myAvatar($user['user_tumb']); ?>"/>
		</div>
		<div class="text_large bold">
			<?php echo $user['user_name']; ?>
		</div>
		<p class="vpad5"><?php echo $lang['cannot_call']; ?></p>
	</div>
</div>
<div class="modal_control centered_element">
	<button class="close_over delete_btn reg_button"><?php echo $lang['close']; ?></button>
</div>
<?php } ?>
<?php if($no_call == 0){ ?>
<div class="modal_content">
	<div class="centered_element hpad25">
		<div class="centered_element">
			<img class="med_avatar brad50" src="<?php echo myAvatar($user['user_tumb']); ?>"/>
		</div>
		<div class="text_large bold">
			<?php echo $user['user_name']; ?>
		</div>
		<p class="vpad5"><?php echo $lang['call_select']; ?></p>
	</div>
</div>
<div class="modal_control centered_element">
	<?php if(canVideoCall()){ ?>
	<div>
		<button data-user="<?php echo $user['user_id']; ?>" data-type="1" class="startcall delete_btn large_button"><i class="ri-video-on-line"></i>  <?php echo $lang['video_call']; ?></button>
	</div>
	<?php } ?>
	<?php if(canAudioCall()){ ?>
	<div class="tpad10">
		<button data-user="<?php echo $user['user_id']; ?>" data-type="2" class="startcall default_btn large_button"><i class="ri-mic-ai-line"></i><?php echo $lang['audio_call']; ?></button>
	</div>
	<?php } ?>
	
	<?php if(useCallBalance()){ ?>
	<div class="tpad15">
		<div class="bpad3">
			<div class="cost_tag_wrapper">
				<div class="btable_auto fborder cost_tag">
					<div class="bcell_mid cost_tag_icon">
						<?php echo costTag($data['call_method'], $data['call_cost']); ?>
					</div>
					<div class="bcell_mid hpad3">
						<?php echo $lang['call_cost']; ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php } ?>
</div>
<?php } ?>