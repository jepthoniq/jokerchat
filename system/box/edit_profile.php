<?php
require('../config_session.php');
?>
<div id="my_profile_top" class="modal_wrap_top modal_top profile_background <?php echo coverClass($data); ?>" <?php echo getCover($data); ?>>
	<div class="brow">
		<div class="bcell">
			<div class="modal_top_menu">
				<div class="bcell_mid">
				</div>
				<?php if(canCover()){ ?>
				<div class="cover_menu">
					<div class="cover_item_wrap lite_olay">
						<div class="cover_item delete_cover" onclick="deleteCover();">
							<i id="cover_button" class="ri-close-circle-line"></i>
						</div>
						<div class="cover_item add_cover">
								<i id="cover_icon" data="ri-camera-lens-fill" class="ri-image-circle-fill"></i>
								<input id="cover_file" class="up_input" onchange="uploadCover();" type="file"/>
						</div>
					</div>
				</div>
				<div class="modal_top_menu_empty">
				</div>
				<?php } ?>
				<div class="cancel_modal modal_top_item cover_text">
					<i class="ri-close-circle-line i_btm lite_olay "></i>
				</div>
			</div>
		</div>
	</div>
	<div class="brow">
		<div class="bcell_bottom profile_top">
			<div class="btable_auto">
				<div id="proav" class="profile_avatar" data="<?php echo $data['user_tumb']; ?>" >
					<div class="avatar_spin">
						<img data-fancybox class="avatar_profile" <?php echo profileAvatar($data['user_tumb']); ?>/>
					</div>
					<?php if(canAvatar()){ ?>
					<div class="avatar_control olay">
						<div class="avatar_button" onclick="deleteAvatar();" id="delete_avatar">
							<i class="ri-close-circle-line i_btm"></i>
						</div>
						<div id="avatarupload" class="avatar_button">
							<i id="avat_icon" data="ri-camera-lens-fill" class="ri-image-circle-fill"></i>
							<input id="avatar_image" class="up_input" onchange="uploadAvatar();" type="file">
						</div>
					</div>
					<?php } ?>
				</div>
				<div class="profile_tinfo">
					<div class="pdetails">
						<div id="pro_name" class="pdetails_text pro_name cover_text">
							<?php echo $data['user_name']; ?>
						</div>
					</div>
					<?php if(canMood()){ ?>
					<div class="pdetails">
						<div id="pro_mood" class="pdetails_text pro_mood cover_text bellips">
							<?php echo getMood($data); ?>
						</div>
					</div>
					<?php } ?>
					<?php if(useLike() || useLevel()){ ?>
					<div id="profile_like" class="pdetails tpad5">
						<?php echo getProfileLevel($data); ?>
						<?php echo getProfileLikes($data); ?>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php if(!isSecure($data) && isMember($data)){ ?>
<div id="secure_account_warn" onclick="openSecure();" class="profile_info_box ok_btn">
	<i class="ri-information-line"></i> <?php echo $lang['secure_account']; ?>
</div>
<?php } ?>
<?php if(guestCanRegister()){ ?>
<div id="secure_account_warn" onclick="openGuestRegister();" class="profile_info_box ok_btn">
	<i class="ri-information-line"></i> <?php echo $lang['register_guest']; ?>
</div>
<?php } ?>
<?php if(userDelete($data)){ ?>
<div id="delete_warn" class="pad15 warn_btn">
	<p class="text_xsmall">
	<span><?php echo str_replace('%date%', longDate($data['user_delete']), $lang['close_warning']); ?></span> 
	<span onclick="cancelDelete();" class="link_like"><?php echo $lang['cancel_request']; ?></span>
	</p>
</div>
<?php } ?>
<div class="modal_menu">
	<ul>
	<li class="modal_menu_item modal_selected" data="meditprofile" data-z="personal_more"><i class="ri-account-circle-line"></i><?php echo $lang['account']; ?></li>
	<?php if(isMember($data)){ ?>
	    <li class="modal_menu_item" data="meditprofile"  data-z="my_security"><i class="ri-rotate-lock-line"></i><?php  echo $lang['security']; ?></li>
		<li class="modal_menu_item" data="meditprofile" onclick="getGift();" data-z="proselfgift"><i class="ri-gift-line"></i><?php echo $lang['gift']; ?></li>
	<?php } ?>	
	<?php if(useLike()){ ?>
		<li class="modal_menu_item" data="meditprofile" onclick="getMylikes('<?php echo ($data['user_id']); ?>');" data-z="my_likes_content"><i class="ri-heart-add-2-fill"></i><?php echo $lang['view_likes']; ?></li>
		<?php } ?>
	</ul>
</div>
<div id="meditprofile">
	<div class="modal_zone pad10" id="personal_more">
		<div class="clearbox">
			<?php if(canInfo()){ ?>
			<div onclick="changeInfo();" class="listing_half_element">
				<i class="ri-information-line listing_icon"></i><?php echo $lang['edit_info']; ?>
			</div>
			<?php } ?>
			<?php if(canAbout()){ ?>
			<div onclick="changeAbout();" class="listing_half_element">
				<i class="ri-question-line listing_icon"></i><?php echo $lang['edit_about']; ?>
			</div>
			<?php } ?>
			<?php if(canName()){ ?>
			<div onclick="changeUsername();" class="listing_half_element">
				<i class="ri-settings-2-line listing_icon"></i><?php echo $lang['edit_username']; ?>
			</div>
			<?php } ?>
			<?php if(canNameColor()){ ?>
			<div onclick="changeColor();" class="listing_half_element">
				<i class="ri-brush-line listing_icon"></i><?php echo $lang['edit_color']; ?>
			</div>
			<?php } ?>
			<?php if(canMood()){ ?>
			<div onclick="changeMood();" class="listing_half_element">
				<i class="ri-edit-2-line listing_icon"></i><?php echo $lang['edit_mood']; ?>
			</div>
			<?php } ?>
			<?php if($data['verified'] == 0 && canVerify()){ ?>
			<div onclick="getVerify();" class="listing_half_element">
				<i class="ri-chat-check-line listing_icon"></i><?php echo $lang['verify_account']; ?>
			</div>
			<?php } ?>
			<?php if(isMember($data)){ ?>
			<div onclick="getFriends();" class="listing_half_element">
				<i class="ri-user-add-line listing_icon"></i><?php echo $lang['manage_friends']; ?>
			</div>
			<?php } ?>
			<div onclick="getIgnore();" class="listing_half_element">
				<i class="ri-forbid-2-line listing_icon"></i><?php echo $lang['manage_ignores']; ?>
			</div>
			<div onclick="getSoundSetting();" class="listing_half_element">
				<i class="fa ri-volume-up-line listing_icon"></i><?php echo $lang['sound_settings']; ?>
			</div>
			<?php if(canTheme()){ ?>
			<div onclick="getDisplaySetting();" class="listing_half_element">
				<i class="ri-computer-line listing_icon"></i><?php echo $lang['theme_settings']; ?>
			</div>
			<?php } ?>

			
			<div onclick="getLocation();" class="listing_half_element">
				<i class="ri-earth-line listing_icon"></i><?php echo $lang['lang_location']; ?>
			</div>


		</div>
	</div>
	<div class="modal_zone hide_zone pad10 tpad10" id="proselfgift" value="0">
	   <div class="clearbox">
	        	<div class="menu_spinner_wrap"><div class="large_spinner"><i class="spinner spinner1  boom_spinner"></i></div></div>
		</div>	
	</div>	
	<div class="modal_zone pad10 hide_zone" id="my_likes_content">
	     <div class="clearbox">
	    	<div class="menu_spinner_wrap"><div class="large_spinner"><i class="spinner spinner1  boom_spinner"></i></div></div>
	    	</div>
	</div>
	<div class="modal_zone pad10 hide_zone" id="my_security">
	    <div class="clearbox">
        
	    <?php if(isMember($data) && isSecure($data)){ ?>
			<div onclick="getPrivateSettings();" class="listing_half_element">
				<i class="ri-eye-line listing_icon"></i><?php echo $lang['private_settings']; ?>
			</div>	    
    		<div onclick="changeShared();" class="listing_half_element">
    			<div class="bcell_mid"><i class="ri-spy-line listing_icon"></i><?php echo $lang['edit_privacy']; ?></div>
    		</div>		    
			<div onclick="getEmail();" class="listing_half_element">
				<i class="ri-mail-send-fill listing_icon"></i><?php echo $lang['edit_email']; ?>
			</div>
			<div onclick="getPassword();" class="listing_half_element">
				<i class="ri-lock-password-fill listing_icon"></i><?php echo $lang['change_password']; ?>
			</div>
				<?php if(useCall()){ ?>
			<div onclick="getCallSettings();" class="listing_half_element">
				<i class="ri-phone-line listing_icon"></i><?php echo $lang['call_psettings']; ?>
			</div>
				
				<?php } ?>
			
			<?php } ?>
			<?php if(!boomAllow(100) && !userDelete($data) && !isBot($data) && isSecure($data)){ ?>
			<div id="del_account_btn" onclick="getDeleteAccount();" class="listing_half_element">
				<i class="ri-delete-bin-2-fill listing_icon"></i><?php echo $lang['close_account']; ?>
			</div>
			<?php } ?>
		
	    </div>	
	 </div>	
</div>