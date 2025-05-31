<?php
$ask = 0;
$current = '';
$owner = '';
if($boom['password'] != ''){
	$ask = 1;
}
if($boom['room_id'] == $data['user_roomid']){
	$current = 'noview';
}
if($boom['description'] == ''){
	$description = $lang['room_no_description'];
}
else {
	$description = $boom['description'];
}
if($data['user_id'] == $boom['room_creator']){
	$owner = 'owner ';
}
?>
<div data-room="usa-communications"  class="room-one-block col-md-4" onclick="switchRoom(<?php echo $boom['room_id']; ?>,<?php echo $ask; ?>,<?php echo $boom['access']; ?>);">
	<div class="room-container-outline">
		<div class="head-bg"></div>
		<div class="room-content d-flex">
			<div class="room-logo">
				<img class="room_cicon lazy" data-src="<?php echo myRoomIcon($boom['room_icon']); ?>" src="<?php echo imgLoader(); ?>"/>
			</div>
			<div class="room-interior">
				<div class="room-info text-hidden">
					<h3 class="mb-0 text-hidden"><?php echo $boom['room_name']; ?></h3>
					<p class="room-des text_color_room_desc mt-1"><?php echo $description; ?></p>
				</div>
				<div class="property">
                <div class="room-conut-users mb-1 text-hidden text_color_user_count">
                    <span class="user-number"><?php echo $boom['room_count']; ?></span>
                    /<span class="max-users"><i class="ri-infinity-line"></i></span>
                </div>
					<div class="d-flex property-ico">
					    <?php if(roomPass($boom)){ ?>
						<?php echo roomLock($boom, 'room_ctag'); ?>
						<?php } ?>	
	                     <div class="roomcopt bcell_mid">
				             <?php echo roomIcon($boom, 'room_ctag'); ?>
			             </div>	
            			<?php if(pinnedRoom($boom)){ ?>
            			<div class="roomcopt bcell_mid">
            				<?php echo roomPinned($boom, 'room_ctag'); ?>
            			</div>
            			<?php } ?>	
			             
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
