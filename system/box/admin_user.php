<?php 
require('../config_session.php');
if(!isset($_POST['edit_user'])){
	die();
}
if(!boomAllow(70)){
	die();
}
$result = '';
$target = escape($_POST['edit_user']);
$user = userDetails($target);
if(!canEditUser($user, 70)){
	echo 99;
	die();
}
?>
<div class="modal_wrap_top modal_top  profile_background <?php echo coverClass($user); ?>" <?php echo getCover($user); ?>>
	<div class="brow">
		<div class="bcell">
			<div class="modal_top_menu">
				<div onclick="getProfile(<?php echo $user['user_id']; ?>);" class="modal_top_item cover_text">
					<i class="ri-skip-left-line"></i>
				</div>
				<div class="bcell_mid">
				</div>
				<?php if(canModifyCover($user)){ ?>
					<div class="cover_menu">
						<div class="cover_item_wrap lite_olay">
							<div class="cover_item delete_cover" onclick="adminRemoveCover(<?php echo $user['user_id']; ?>);">
								<i id="cover_button" class="ri-close-circle-line"></i>
							</div>
							<div class="cover_item add_cover">
								<i id="admin_cover_icon" data="fa-ri-camera-lens-fill" class="ri-image-circle-fill"></i>
								<input id="admin_cover_file" class="up_input" onchange="adminUploadCover(<?php echo $user['user_id']; ?>);" type="file"/>
							</div>
						</div>
					</div>
				<?php } ?>
				<div class="modal_top_menu_empty">
				</div>
				<div class="cancel_modal modal_top_item cover_text lite_olay">
					<i class="ri-close-circle-line i_btm"></i>
				</div>
			</div>
		</div>
	</div>
	<div class="brow">
		<div class="bcell_bottom profile_top">
			<div class="btable_auto">	
				<div class="profile_avatar" data="<?php echo $user['user_tumb']; ?>" >
					<div class="avatar_spin">
						<img data-fancybox class=" avatar_profile" <?php echo profileAvatar($user['user_tumb']); ?>/>
					</div>
					<?php 
					if(canModifyAvatar($user)){ ?>
					<div class="avatar_control olay">
						<div class="avatar_button" onclick="adminRemoveAvatar(<?php echo $user['user_id']; ?>);">
							<i class="ri-close-circle-line i_btm"></i>
						</div>
						<div id="avatarupload" class="avatar_button">
							<i id="avat_admin" data="ri-camera-lens-fill" class="ri-image-circle-fill"></i>
							<input id="admin_avatar_image" class="up_input" onchange="adminUploadAvatar(<?php echo $user['user_id']; ?>);" type="file" >
						</div>
					</div>
					<?php } ?>
				</div>
				<div class="profile_tinfo cover_text">
					<div class="pdetails">
						<div id="pro_admin_name" class="pdetails_text pro_name">
							<?php echo $user['user_name']; ?>
						</div>
					</div>
					<div class="pdetails">
						<div id="pro_admin_mood" class="pdetails_text pro_mood bellips">
							<?php echo getMood($user); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="modal_menu">
	<ul>
		<li class="modal_menu_item modal_selected" data="madminuser" data-z="admin_pro_details"><?php echo $lang['options']; ?></li>
		<?php if(canUserHistory($user)){ ?>
		<li class="modal_menu_item" data="madminuser" data-z="admin_history_box" onclick="actionHistory(<?php echo $user['user_id']; ?>);"><?php echo $lang['history']; ?></li>
		<?php } ?>
	</ul>
</div>
<div id="madminuser">
	<div class="modal_zone pad25" id="admin_pro_details">
		<div class="clearbox">
			<?php if(!isGuest($user) && canEditUser($user, 9)){ ?>
			<div onclick="adminGetRank(<?php echo $user['user_id']; ?>);" class="listing_half_element">
				<i class="ri-gemini-line listing_icon"></i><?php echo $lang['change_rank']; ?>
			</div>
			<?php } ?>
			<?php if(canModifyName($user)){ ?>
			<div onclick="adminChangeName(<?php echo $user['user_id']; ?>);" class="listing_half_element">
				<i class="ri-settings-2-line listing_icon"></i><?php echo $lang['edit_username']; ?>
			</div>
			<?php } ?>
			<?php if(canModifyColor($user)){ ?>
			<div onclick="adminUserColor(<?php echo $user['user_id']; ?>);" class="listing_half_element">
				<i class="ri-brush-line listing_icon"></i><?php echo $lang['edit_color']; ?>
			</div>
			<?php } ?>
			<?php if(canModifyMood($user)){ ?>
			<div onclick="adminChangeMood(<?php echo $user['user_id']; ?>);" class="listing_half_element">
				<i class="ri-edit-2-line listing_icon"></i><?php echo $lang['edit_mood']; ?>
			</div>
			<?php } ?>
			<?php if(canModifyEmail($user)){ ?>
			<div onclick="adminGetEmail(<?php echo $user['user_id']; ?>);" class="listing_half_element">
				<i class="ri-mail-send-fill listing_icon"></i><?php echo $lang['edit_email']; ?>
			</div>
			<?php } ?>
			<?php if(canModifyAbout($user)){ ?>
			<div onclick="adminUserAbout(<?php echo $user['user_id']; ?>);" class="listing_half_element">
				<i class="ri-user-line listing_icon"></i><?php echo $lang['edit_about']; ?>
			</div>
			<?php } ?>
			<?php if(canModifyPassword($user)){ ?>
			<div onclick="adminUserPassword(<?php echo $user['user_id']; ?>);" class="listing_half_element">
				<i class="ri-lock-password-fill listing_icon"></i><?php echo $lang['change_password']; ?>
			</div>
			<?php } ?>
    		<?php if(!verified($user) && canEditUser($user, $data['can_verify'], 1)){ ?>
			<div onclick="adminUserVerify(<?php echo $user['user_id']; ?>);" class="listing_half_element">
				<i class="ri-verified-badge-line listing_icon"></i><?php echo $lang['edit_verify']; ?>
			</div>
    		
    		<?php } ?>
    		<?php if(canWhitelist($user) && userCanVpn($user)){ ?>
			<div onclick="adminUserWhitelist(<?php echo $user['user_id']; ?>);" class="listing_half_element">
				<i class="ri-chat-private-fill listing_icon"></i><?php echo $lang['vpn_option']; ?>
			</div>    		
    		<?php } ?>
			
			<?php if(canDeleteUser($user)){ ?>
			<div onclick="eraseAccount(<?php echo $user['user_id']; ?>);" class="listing_half_element">
				<i class="ri-delete-bin-line listing_icon"></i><?php echo $lang['delete_account']; ?>
			</div>
			<?php } ?>
		</div>
	</div>
	<?php if(canUserHistory($user)){ ?>
	<div class="hide_zone modal_zone" id="admin_history_box">
		<div id="history_list" class="box_height400 clearbox pad15">
			<?php echo emptyZone($lang['fetching']); ?>
		</div>
	</div>
	<?php } ?>
</div>