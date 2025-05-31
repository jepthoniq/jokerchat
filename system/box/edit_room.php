<?php
require('../config_session.php');

if(!isset($_POST['edit_room']) || !canManageRoom()){
	die();
}
$target = escape($_POST['edit_room'], true);
$room = roomDetails($target);
if(empty($room)){
	echo 0;
	die();
}
?>
<style>
.ricon_current_wrap {
    width: 120px;
    display: table-cell;
    vertical-align: bottom;
    position: relative;
}
.ricon_current {
    width: 120px;
    height: 120px;
    border-radius: 10px;
    display: block;
}
.ricon_control {
    position: absolute;
    display: table;
    table-layout: fixed;
    width: 100%;
    z-index: 40;
    width: 80px;
    left: 20px;
    border-radius: 50px;
    bottom: 10px;
}
.ricon_button {
    display: table-cell;
    vertical-align: middle;
    text-align: center;
    width: 40px;
    padding: 8px 0;
    position: relative;
}
</style>
<div class="pad_box">
	<div class="setting_element">
		<div class="btable">
			<div id="set_room_icon" class="ricon_current_wrap">
				<img class="ricon_current" src="<?php echo myRoomIcon($room['room_icon']); ?>"/>
				<div class="ricon_control olay">
					<div class="ricon_button" onclick="staffRemoveRoomIcon(<?php echo $room['room_id']; ?>);">
						<i class="ri-close-circle-line"></i>
					</div>
					<div class="ricon_button">
						<i class="ri-image-circle-line" id="ricon_icon" data="ri-camera-lens-fill"></i>
						<input id="ricon_image" class="up_input" onchange="adminRoomIcon(<?php echo $room['room_id']; ?>);" type="file">
					</div>
				</div>
			</div>
			<div class="bcell">
			</div>
		</div>
	</div>    
	<div class="boom_form">
		<?php if(usePlayer()){ ?>
		<div class="setting_element ">
			<p class="label"><?php echo $lang['default_player']; ?></p>
			<select id="set_room_player">
				<?php echo adminPlayer($room['room_player_id'], 1); ?>
			</select>
		</div>
		<?php } ?>
		<div class="setting_element">
			<p class="label"><?php echo $lang['room_name']; ?></p>
			<input id="set_room_name" maxlength="30" class="full_input" value="<?php echo $room['room_name']; ?>" type="text"/>
		</div>
		<div class="setting_element ">
			<p class="label"><?php echo $lang['room_type']; ?></p>
			<select id="set_room_access">
				<?php echo roomRanking($room['access']); ?>
			</select>
		</div>
		<div class="setting_element">
			<p class="label"><?php echo $lang['password']; ?></p>
			<input id="set_room_password" maxlength="20" class="full_input" value="<?php echo $room['password']; ?>" type="text"/>
		</div>
		<div class="setting_element">
			<p class="label"><?php echo $lang['room_description']; ?></p>
			<textarea id="set_room_description" class="full_textarea medium_textarea" type="text" maxlength="<?php echo $cody['max_description']; ?>"><?php echo $room['description']; ?></textarea>
		</div>
		<div class="setting_element">
			<p class="label"><?php echo $lang['room_keywords']; ?></p>
			<textarea id="set_room_keywords" class="full_textarea medium_textarea" type="text" maxlength="1000"><?php echo $room['room_keywords']; ?></textarea>
		</div>		
	</div>
	<button data="<?php echo $room['room_id']; ?>" type="button" id="admin_save_room" class="reg_button theme_btn"><i class="ri-save-line"></i> <?php echo $lang['save']; ?></button>
	<button class="cancel_modal reg_button default_btn"><?php echo $lang['cancel']; ?></button>
</div>