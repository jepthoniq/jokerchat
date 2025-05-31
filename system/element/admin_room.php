<div class="sub_list_item box_room">
	<div class="sub_list_img">
	<img id="ricon<?php echo $boom['room_id']; ?>" class="lazy room_listing" data-src="<?php echo myRoomIcon($boom['room_icon']); ?>" src="<?php echo imgLoader(); ?>"/>
		<?php echo roomActive($boom); ?>
	</div>
	<div class="sub_list_name">
		<?php echo $boom['room_name']; ?>
		<p class="text_small bellips sub_text"><?php echo $boom['description']; ?></p>
	</div>
	<div class="sub_list_img">
		<?php echo roomIcon($boom, 'roomlisting'); ?>
	</div>	
	<div onclick="editRoom(<?php echo $boom['room_id']; ?>);" class="sub_list_option">
		<i class="ri-settings-2-line edit_btn"></i>
	</div>
	<?php if($boom['room_id'] == 1){ ?>
		<div class="sub_list_option">
			<i class="ri-wechat-line"></i>
		</div>
	<?php } else { ?>
	<div onclick="deleteRoom(this, <?php echo $boom['room_id']; ?>);" class="sub_list_option">
		<i class="ri-close-circle-line i_btm"></i>
	</div>
	<?php } ?>
</div>