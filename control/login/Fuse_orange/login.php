<div id="login_panel" class="login_panel fleft back_panel rborder">
		<div class="login_panel_inner">
			<div class="reg_menu_container bpad10 bborder">
				<div class="reg_menu">
					<ul>
						<li class="reg_menu_item rselected" data="lpanel_menu" data-z="active_rooms"><?php echo $lang['active_room']; ?></li>
					</ul>
				</div>
			</div>
			<div id="lpanel_menu">
				<div id="active_rooms" class="reg_zone vpad5">
				<?php echo guest_room_list('list'); ?>
				</div>
			</div>
		</div>
		<div id="login_panel_close" class="lpanel_close">
			<div class="lpanel_close_btn btable_height brad100 bclick">
				<div class="bcell_mid_center">
					<i class="ri-close-circle-line lpanel_cicon"></i>
				</div>
			</div>
		</div>
	</div>
<div id="login_body" class=" out_page_container simple_back login_body">
	<div class="out_page_content">
	    <div class="overlay"></div>
		<div class="out_page_box">
			<div class="out_page_data pad_box login_box">
				<div class="embed_logo_wrap">
					<img id="login_logo" class="embed_logo" src="<?php echo getLogo(); ?>"/>
				</div>
				<p class="bpad10"><?php echo $lang['left_welcome']; ?></p>
                <div class="centered_element bpad20 bborder">
                    <?php if(bridgeMode(0)){ ?>
                	<button onclick="getLogin();" class="intro_login_btn mod_button rounded_button theme_btn btnshadow"><i class="ri-send-plane-line"></i> <?php echo $lang['login']; ?></button>
                	<?php } ?>
                	<?php if(bridgeMode(1)){ ?>
                		<button class="intro_login_btn mod_button rounded_button theme_btn btnshadow" onclick="bridgeLogin('<?php echo getChatPath(); ?>');"><i class="fa fa-user"></i> <?php echo $lang['enter_now']; ?></button>
                	<?php } ?>
                	<?php if(allowGuest()){ ?>
                	<button onclick="getGuestLogin();" class="intro_login_btn mod_button rounded_button theme_btn btnshadow"><i class="ri-send-plane-2-line"></i> <?php echo $lang['guest_login']; ?></button>
                	<?php } ?>
                </div>
				<?php if(boomUseSocial() && !embedMode()){ ?>
				<div class="intro_social_container">
					<div class="intro_social_content">
						<?php if(boomSocial('facebook_login')){ ?>
						<img onclick="window.location.href='<?php echo getSocialLoginCallbackUrl('facebook'); ?>'" class="intro_social_btn bclick" src="default_images/social/facebook.svg"/>
						<?php } ?>
						<?php if(boomSocial('google_login')){ ?>
						<img onclick="window.location.href='<?php echo getSocialLoginCallbackUrl('google'); ?>'" class="intro_social_btn bclick" src="default_images/social/google.svg"/>
						<?php } ?>
						<?php if(boomSocial('twitter_login')){ ?>
						<img onclick="window.location.href='<?php echo getSocialLoginCallbackUrl('twitter'); ?>'" class="intro_social_btn bclick" src="default_images/social/twitter.svg"/>
						<?php } ?>
					</div>
				</div>
				<?php } ?>
				<?php if(registration()){ ?>
				<div id="not_yet_member" class="login_not_member bclick">
					<p onclick="getRegistration();" class="inblock login_register_text pad10"><?php echo $lang['not_member']; ?></p>
				</div>
				<?php } ?>
				<div id="last_embed">
					<?php echo embedActive(5); ?>
				</div>
				<div class="embed_lang bclick" onclick="getLanguage();" id="intro_lang" title="<?php echo $lang['language']; ?>">
					<img class="intro_lang" src="system/language/<?php echo $cur_lang; ?>/flag.png"/>
				</div>	
				<div id="login_panel_toggle" class="lpanel_toggle"> <div class="lpanel_toggle_btn btable_height brad100 bclick theme_btn btnshadow"> <div class="bcell_mid_center"> <i class="ri-menu-unfold-line lpanel_ticon"></i> </div> </div> </div>
			</div>
		</div>
		<?php if(bridgeMode(1)){ ?>
		<div onclick="getLogin();" class="adm_login bclick">
			<i class="ri-settings-line"></i> <?php echo $lang['login']; ?>
		</div>
		<?php } ?>
	</div>
	
</div>
<div class="foot vpad25 hpad15 centered_element" id="main_footer">
    <div id="menu_main_footer">
        <?php boomFooterMenu(); ?>
    </div>
</div>		

<?php if(boomCookieLaw()){ ?>
<div class="cookie_wrap">
	<div class="cookie_text">
		<p><?php echo str_replace('%data%', '<span onclick="openSamePage(\'privacy.php\');" class="bclick link_like">' . $lang['privacy'] . '</span>', $lang['cookie_law']); ?></p>
	</div>
	<div class="cookie_button">
		<button onclick="hideCookieBar();" class="ok_btn reg_button"><?php echo $lang['ok']; ?></button>
	</div>
</div>
<?php } ?>
<script data-cfasync="false" src="js/function_login.js<?php echo $bbfv; ?>"></script>
<script data-cfasync="false">
	var pvisible = 1; // 0 - Hide panel 1 - Show panel.
	showLoginPanel = function(){
		$('#login_panel').show();
		$('#login_body').addClass('login_pbody');
	}
	hideLoginPanel = function(){
		$('#login_panel').hide();
		$('#login_body').removeClass('login_pbody');
	}
	checkLoginPanel = function(){
		var wwidth = $(window).width();
		if(wwidth <= 930){
			hideLoginPanel();
		}
	}
	visibleLoginPanel = function(){
		var wwidth = $(window).width();
		if(pvisible == 1 && wwidth > 930){
			showLoginPanel();
		}
	}
	toggleLoginPanel = function(){
		if(!$('#login_panel:visible').length){
			showLoginPanel();
		}
		else{
			hideLoginPanel();
		}
	}
	$(document).ready(function(){
		checkLoginPanel();
		visibleLoginPanel();
		$(document).on('click', '#login_panel_toggle, #login_panel_close', toggleLoginPanel);
		$(window).on('resize', checkLoginPanel);
	});
</script>