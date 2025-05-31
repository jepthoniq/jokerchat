<div class="sub_list_item" id="found<?php echo $boom['user_id']; ?>">
	<div class="sub_list_avatar">
		<img class="admin_user<?php echo $boom['user_id']; ?>" src="<?php echo myAvatar($boom['user_tumb']); ?>"/>
		<?php echo userActive($boom, 'sub_list_active'); ?>
	</div>
	<div class="sub_list_name">
		<p class="username <?php echo myColor($boom); ?>"><?php echo $boom['user_name']; ?></p>
	</div>
	<div onclick="getProfile(<?php echo $boom['user_id']; ?>);" class="sub_list_option">
		<i class="ri-settings-2-line edit_btn"></i>
	</div>
	<?php if(canEditUser($boom, 100, 1) && !isOwner($boom)){ ?>
	<div title="Login as <?php echo $boom['user_name']; ?>" onclick="switch_account('<?php echo $boom['user_id']; ?>');" class="sub_list_option">
		<i class="ri-toggle-line"></i>
	</div>
	<?php } ?>
	<?php if(canEditUser($boom, 90, 1) && !isOwner($boom)){ ?>
	<div onclick="eraseAccount(<?php echo $boom['user_id']; ?>);" class="sub_list_option">
		<i class="ri-close-circle-line edit_btn"></i>
	</div>
	<?php } ?>
</div>