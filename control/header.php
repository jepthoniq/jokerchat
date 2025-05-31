<div id="header2" class="background_header topcmm-123flashchat-common-header">
	<div id="fuse_wrap_main_header" class="fuse_login_header">
		<div id="main_header" class="out_head headers">
			<?php if($page['page_menu'] == 1){ ?>
				<div id="open_sub_mobile"><i class="ri-bar-chart-horizontal-line"></i></div>
			<?php } ?>
			<?php if(!embedMode()){ ?>
			<div class="head_logo">
				<img id="main_logo" alt="logo" src="<?php echo getLogo(); ?>"/>
			</div>
			<?php } ?>
			<div id="empty_top_mob" class="bcell_mid hpad10">
			</div>
			
			<?php if($page['page_nohome'] == 0){ ?>
			<div onclick="openSamePage('<?php echo $data['domain']; ?>');" class="head_option">
				<i class="ri-kakao-talk-line  i_btm"></i>
			</div>
			<?php } ?>
			<?php if(boomLogged()){?>
			<div onclick="showMenu('mobile_main_menu');" id="main_mob_menu" class="menutrig bclick">
				<img class="menutrig glob_av avatar_menu" src="<?php echo myAvatar($data['user_tumb']); ?>"/>
				<div id="mobile_main_menu" class="hideall sysmenu fmenu">
					<div class="fmenu_item" onclick="editProfile();">
						<div class="fmenu_icon">
							<i class="ri-user-star-line menuo"></i>
						</div>
						<div class="fmenu_text">
							<?php echo $lang['my_profile']; ?>
						</div>
					</div>
					<?php if(useLobby()){ ?>
					<div id="back_home" class="fmenu_item">
						<div class="fmenu_icon">
							<i class="ri-kakao-talk-line menuo"></i>
						</div>
						<div class="fmenu_text">
							<?php echo $lang['lobby']; ?>
						</div>
					</div>
					<?php } ?>					
					<?php if($page['page'] != 'admin' && boomAllow(70)){ ?>
					<div class="fmenu_item" onclick="openLinkPage('admin.php');">
						<div class="fmenu_icon">
							<i class="ri-dashboard-3-fill menuo"></i>
						</div>
						<div class="fmenu_text">
							<?php echo $lang['admin_panel']; ?>
						</div>
					</div>
					<?php } ?>
					<div class="fmenu_item" id="open_logout" onclick="openLogout();">
						<div class="fmenu_icon">
							<i class="ri-login-circle-line menuo"></i>
						</div>
						<div class="fmenu_text">
							<?php echo $lang['logout']; ?>
						</div>
					</div>
				</div>
			</div>
			<?php } ?>
			
		</div>
	</div>
</div>
<div class="empty_subhead hidden"></div>
