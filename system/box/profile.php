<?php
require('../config_session.php');

if(!isset($_POST['get_profile'], $_POST['cp'])){
	die();
}
$id = escape($_POST['get_profile']);
$curpage = escape($_POST['cp']);
$user = boomUserInfo($id);
if(empty($user)){
	echo 2;
	die();
}
$user['page'] = $curpage;
$pro_menu = boomTemplate('element/pro_menu', $user);
$room = roomInfo($user['user_roomid']);
//fuse -> who visit you
echo blackNotify($id);
?>
<style>
.ex_profile_overlay{
	
}
</style>
<div class="modal_wrap_top modal_top profile_background <?php echo coverClass($user); ?>" <?php echo getCover($user); ?>>
	<div class="brow">
		<div class="bcell">
			<div class="modal_top_menu">
				<div class="bcell_mid hpad15">
				</div>
				<?php if(canEditUser($user, 70)){ ?>
				<div onclick="editUser(<?php echo $user['user_id']; ?>);" class="cover_text modal_top_item lite_olay">
					<i class="ri-settings-2-line"></i>
				</div>
				<?php } ?>
				<?php if(canEditUser($user, 70, 1)){ ?>
				<div onclick="getActions(<?php echo $user['user_id']; ?>);" class="cover_text modal_top_item lite_olay">
					<i class="ri-water-flash-line"></i>
				</div>
				<?php } ?>
				<?php if(!mySelf($user['user_id']) && !empty($pro_menu)){ ?>
				<div id="promenu" onclick="loadProMenu(<?php echo $user['user_id']; ?>, 'pro_menu');" class="cover_text modal_top_item lite_olay">
					<i class="ri-bar-chart-horizontal-line"></i>
					<div id="pro_menu" class="add_shadow fmenu lite_olay">
						<?php echo $pro_menu; ?>
					</div>
				</div>
				<?php } ?>
				<div class="modal_top_menu_empty">
				</div>
				<div class="cancel_modal cover_text modal_top_item lite_olay">
					<i class="ri-close-circle-line i_btm"></i>
				</div>
			</div>
		</div>
	</div>
	<div class="brow">
		<div class="bcell_bottom profile_top">
			<div class="btable_auto">
				<div id="proav" class="profile_avatar" data="<?php echo $user['user_tumb']; ?>" >
					<div class="avatar_spin">
						<img data-fancybox class="fancybox avatar_profile" <?php echo profileAvatar($user['user_tumb']); ?>/>
					</div>
					<?php echo userActive($user, 'state_profile'); ?>
				</div>
				<div class="profile_tinfo cover_text">
					<div class="pdetails">
						<div class="pdetails_text pro_rank" >
							<?php echo proRanking($user, 'pro_ranking'); ?>
						</div>
					</div>
					<div class="pdetails">
						<div class="pdetails_text pro_name ">
							<?php echo $user['user_name']; ?>
						</div>
					</div>
					<div class="pdetails">
						<?php if(!empty($user['user_mood'])){ ?>
						<div class="pdetails_text pro_mood bellips">
							<?php echo $user['user_mood']; ?>
						</div>
						<?php } ?>
					</div>
					<div  class="pdetails">
					<?php if(showUserLike($user)){ ?>
						<?php echo getProfileLevel($user); ?>
						<div id="profile_like">
						<?php echo getProfileLikes($user); ?>
						</div>
					<?php } ?>
					<?php if(useStore() && ($user['user_prim'] > 0) && !empty($user['pro_song'])) { 
						// Define the target directory
						$targetDir = 'upload/premium/profile_music/';
						$audio_file = $targetDir . $user['pro_song'];
						?>
						<div class="lite_olay plevel_item pro_audio" onclick="audio_profile(this);">
							<i class="ri-play-circle-line"></i><span class="plevel_count">Play</span>
							<!-- Hidden audio element -->
							<audio id="audioPlayer" style="display:none;">
								<source src="<?php echo $audio_file; ?>" type="audio/mp3">
								Your browser does not support the audio element.
							</audio>							
						</div>	
					<?php } ?>						
					</div>
						
				</div>
			</div>
		</div>
	</div>
</div>
<?php if(isRegmute($user) && !isMuted($user) && !isBanned($user)){ ?>
<div class="im_muted profile_info_box theme_btn">
	<i class="ri-information-line"></i> <?php echo $lang['user_regmuted']; ?>
</div>
<?php } ?>
<?php if(isMuted($user) && !isBanned($user)){ ?>
<div class="im_muted profile_info_box warn_btn">
	<i class="ri-information-line"></i> <?php echo $lang['user_muted']; ?>
</div>
<?php } ?>
<?php if(isBanned($user)){ ?>
<div class="im_banned profile_info_box delete_btn">
	<i class="ri-information-line"></i> <?php echo $lang['user_banned']; ?>
</div>
<?php } ?>
<div class="modal_menu">
	<ul>
		<li class="modal_menu_item modal_selected" data="mprofilemenu" data-z="profile_info"><?php echo $lang['about_me']; ?></li>
		<?php if(!isGuest($user) && !isBot($user)){ ?>
		<li class="modal_menu_item" data="mprofilemenu" onclick="lazyBoom('profile_friends');" data-z="profile_friends"><?php echo $lang['friends']; ?></li>
		<?php } ?>
		<?php if(!isBot($user)){ ?> 
		<li class="modal_menu_item" data="mprofilemenu" data-z="prodetails"><?php echo $lang['main_info']; ?></li>
		<?php } ?>
		<?php if(userShareGift($user)){ ?>
		    <li class="modal_menu_item" data="mprofilemenu" data-z="progift" onclick="getUserGift(<?php echo $user['user_id']; ?>);"><?php echo $lang['gift']; ?></li>
		<?php } ?>		
	</ul>
</div>
<div id="mprofilemenu" <?php if (useStore() && ($user['user_prim'] > 0 && !empty($user['pro_background']))) { echo exProfileBg($user); } ?>>
	<div class="modal_zone pad25 tpad15" id="profile_info">
		<div class="clearbox">
		<?php if(useStore()) { ?>
			<div class="listing_half_element info_pro">
				<div class="listing_title">Membership</div>
				<div class="listing_text">
				<?php
				$upgrade = '';
				if (boomAllow(100)) {
				$upgrade = '<span class="success" onclick="upgradeToPremium(' . $user['user_id'] . ');" style="border: 1px solid;border-radius:13px;padding: 0px 3px;"><i style="height:15px;" class="ri-bubble-chart-line error"></i> Promotion</span>';
				}
				if ($user['user_prim'] > 0) {
					echo '<i style="color:#2bb8ff;" class="ri-copper-diamond-line"></i> Premium User ' . $upgrade . '';
				} elseif ($user['user_prim'] == 0) {
					echo ' Normal User ' . $upgrade . '';
				}
			?>
				</div>
			</div>
			<?php if ($user['user_prim'] > 0 && isOwner($data)) { ?>
				<div class="listing_half_element info_pro">
					<div class="listing_title">Premium Expiry Date</div>
					<div class="listing_text"><?php echo Fu_premiumEndingDate($user['prim_end']); ?></div>
				</div>
			<?php } ?>
			<div class="listing_half_element info_pro">
				<div class="listing_title"><?php echo $lang['level_info']; ?></div>
				<div class="listing_text"><?php echo $user['user_level']; ?></div>
			</div>
		<?php } ?>		
	   <?php if(userShareAge($user)){ ?>
			<?php if(boomAge($user['user_age'])){ ?>
			<div class="listing_half_element info_pro">
				<div class="listing_title"><?php echo $lang['age']; ?></div>
				<div class="listing_text"><?php echo getUserAge($user['user_age']); ?></div>
			</div>
			<?php } ?>
		<?php } ?>
		<?php if(userShareGender($user)){ ?>
			<?php if(boomSex($user['user_sex'])){ ?>
			<div class="listing_half_element info_pro">
				<div class="listing_title"><?php echo $lang['gender']; ?></div>
				<div class="listing_text"><?php echo getGender($user['user_sex']); ?></div>
			</div>
			<?php } ?>
		<?php } ?>	
			<?php if(verified($user)){ ?>
			<div class="listing_half_element info_pro">
				<div class="listing_title"><?php echo $lang['account_status']; ?></div>
				<div class="listing_text"><?php echo $lang['verified']; ?></div>
			</div>
			<?php } ?>
		<?php if(userShareLocation($user)){ ?>
			<?php if(usercountry($user['country'])){ ?>
			<div class="listing_half_element info_pro">
				<div class="listing_title"><?php echo $lang['country']; ?></div>
				<div class="listing_text"><?php echo countryName($user['country']); ?></div>
			</div>
			<?php } ?>
		<?php } ?>	
			<div class="listing_half_element info_pro">
				<div class="listing_title"><?php echo $lang['join_chat']; ?></div>
				<div class="listing_text"><?php echo longDate($user['user_join']); ?></div>
			</div>
			<?php if(canViewGold() && !isBot($user) && !isGuest($user)){ ?>
			<div class="listing_half_element info_pro">
				<div class="listing_title"><?php echo $lang['gold']; ?></div>
				<div class="listing_text"><?php echo $user['user_gold']; ?></div>
			</div>
			<?php } ?>			
			<?php if(userInRoom($user) && !empty($room)){ ?>
			<div class="listing_half_element info_pro">
				<div class="listing_title"><?php echo $lang['user_room']; ?></div>
				<div class="listing_text"><?php echo $room['room_name']; ?></div>
			</div>
			<?php } ?>
    		<?php if($user['user_about'] != ''){ ?>
    		
    			<div class="listing_half_element info_pro">
    				<div class="listing_title"><?php echo $lang['about_me']; ?></div>
    				<div class="listing_text"><?php echo boomFormat($user['user_about']); ?></div>
    			</div>
    	
		<?php } ?>			
		</div>

	</div>
	<?php if(!isBot($user)){ ?>
	<div class="hide_zone  pad25 tpad15 modal_zone" id="prodetails">
		<div class="clearbox">
			<?php if(isVisible($user) && !isBot($user)){ ?>
			<div class="listing_half_element info_pro">
				<div class="listing_title"><?php echo $lang['last_seen']; ?></div>
				<div class="listing_text"><?php echo longDateTime($user['last_action']); ?></div>
			</div>
			<?php } ?>
			<div class="listing_half_element info_pro">
				<div class="listing_title"><?php echo $lang['language']; ?></div>
				<div class="listing_text"><?php echo $user['user_language']; ?></div>
			</div>
			<div class="listing_half_element info_pro">
				<div class="listing_title"><?php echo $lang['user_theme']; ?></div>
				<div class="listing_text"><?php echo boomUserTheme($user); ?></div>
			</div>
			<?php if(canViewTimezone($user)){ ?>
			<div class="listing_half_element info_pro">
				<div class="listing_title"><?php echo $lang['user_timezone']; ?></div>
				<div class="listing_text"><?php echo userTime($user); ?></div>
			</div>
			<?php } ?>
			<?php if(canViewEmail($user)){ ?>
			<div class="listing_half_element info_pro">
				<div class="listing_title"><?php echo $lang['email']; ?></div>
				<div class="listing_text"><?php echo $user['user_email']; ?></div>
			</div>
			<?php } ?>
			<?php if(canViewIp($user)){ ?>
			<div class="listing_half_element info_pro">
				<div class="listing_title"><?php echo $lang['ip']; ?></div>
				<div class="listing_text"><?php echo $user['user_ip']; ?></div>
			</div>
			<?php } ?>
			<?php if(canViewId($user)){ ?>
			<div class="listing_half_element info_pro">
				<div class="listing_title"><?php echo $lang['user_id']; ?></div>
				<div class="listing_text"><?php echo $user['user_id']; ?></div>
			</div>
			<?php } ?>
			<?php if(canEditUser($user, 70, 1)){ ?>
			<div class="listing_half_element info_pro">
				<div class="listing_title"><?php echo $lang['other_account']; ?></div>
				<div class="listing_text"><?php echo sameAccount($user); ?></div>
			</div>
			<?php } ?>
		</div>
	</div>
	<?php } ?>
	<?php if(!isGuest($user) && !isBot($user)){ ?>
	<div class="hide_zone pad20 modal_zone" id="profile_friends">
		<?php echo findFriend($user); ?>
		<div class="clear"></div>
	</div>
	<?php } ?>
	<div class="modal_zone hide_zone pad25" id="progift" value="0">
	    	<div class="clearbox">
	        	<div class="menu_spinner_wrap"><div class="large_spinner"><i class="spinner spinner1  boom_spinner"></i></div></div>
			</div>
	</div>
	

</div>
<script>
function audio_profile(element) {
const audioPlayer = element.querySelector('#audioPlayer'); // Find the hidden audio element inside the clicked div
const playText = element.querySelector('.plevel_count'); // Get the play/pause text span
const playIcon = element.querySelector('i'); // Get the play/pause icon
if (audioPlayer.paused) {
		// If audio is paused, play it and update text/icon to 'Pause'
		audioPlayer.play();
		playText.textContent = 'Pause';
		playIcon.classList.remove('ri-play-circle-line');
		playIcon.classList.add('ri-pause-circle-line');
	} else {
		// If audio is playing, pause it and update text/icon to 'Play'
		audioPlayer.pause();
		playText.textContent = 'Play';
		playIcon.classList.remove('ri-pause-circle-line');
		playIcon.classList.add('ri-play-circle-line');
	}
}
upgradeUserPremium = function(target) {
	$.post(FU_Ajax_Requests_File(), {
		f:'store',
		s:'admin_upgrade_premium',
		premium_plan: $('#upgrade_premium_user').val(),
		premium_target: target,
		token: utk
	}, function(response) {
		if (response.code == 1) {
		    callSaved(system.saved, 1);
		    } else {
		     callSaved(system.error, 3);
		}
	});
}
// upgrade user to premium
upgradeToPremium = function(u) {
	$.post('system/box/admin_upgrade_premium.php', {
		target: u,
		token: utk,
	}, function(response) {
		if (response == 0) {
		   callSaved(system.error, 3);
		} else {
		   overModal(response);
		}
	});
}
<?php if (!empty($user['pro_background'])) { ?>
<?php } ?>

$(document).ready(function() {
	$('.pro_audio').trigger('click');
<?php if (!empty($user['pro_text_main'])) { ?>
var mainTextCol = '<?php echo $user['pro_text_main']; ?>';
$('.listing_title').css('color', mainTextCol);
$('#ex_menu').css('color', mainTextCol);
<?php } ?>
<?php if (!empty($user['pro_text_sub'])) { ?>
var subTextCol = '<?php echo $user['pro_text_sub']; ?>';
$('.listing_text').css('color', subTextCol);
<?php } ?>	
});

</script>
