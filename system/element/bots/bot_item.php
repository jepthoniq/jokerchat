<div class="sub_list_item" id="bot_line_<?php echo $boom['id']; ?>">
	<div class="sub_list_avatar">
		<img class="admin_user28" src="<?php echo myAvatar($boom['user_tumb']); ?>" />
		<img class="sub_list_active" src="default_images/icons/active.svg" />
	</div>
	<div class="sub_list_name">
		<p class="username <?php echo $boom['user_color']; ?>"><?php echo $boom['bot_name']; ?></p>
	</div>
	<div class="sub_list_name">
		<p class="username user"><?php echo $boom['reply']; ?></p>
	</div>
	<div onclick="getBot_info(<?php echo $boom['id']; ?>,<?php echo $boom['group_id']; ?>);" class="sub_list_option">
		<i class="ri-settings-2-line edit_btn"></i>
	</div>
	<div onclick="del_bot(<?php echo $boom['id']; ?>);" class="sub_list_option">
		<i class="ri-close-circle-line edit_btn"></i>
	</div>
</div>
