<div class="sub_list_item btauto">
	<div class="sub_list_avatar">
		<img src="<?php echo $data['domain']; ?>/addons/<?php echo $boom; ?>/files/icon.png"/>
	</div>
	<div class="sub_list_name addons_name">
		<?php echo boomUnderClear($boom); ?>
	</div>
	<div class="sub_list_cell bcauto">
		<button class="default_btn button work_button"><i class="ri-time-line"></i> Installing...</button>
		<button data="<?php echo $boom; ?>" type="button" class="activate_addons button theme_btn"><i class="ri-upload-cloud-line edit_btn"></i> <span class="hide_mobile"><?php echo $lang['install']; ?></span></button>
	</div>
</div>