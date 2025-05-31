<div class="wrapper">
	<svg class="black-svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="749.408" height="205.786" viewBox="0 0 749.408 205.786">
		<defs>
			<linearGradient id="linear-gradient" x1="0.5" x2="0.5" y2="1" gradientUnits="objectBoundingBox">
				<stop offset="0" stop-color="#343535"></stop>
				<stop offset="1" stop-color="#181717"></stop>
			</linearGradient>
		</defs>
		<path
			id="Path_6944"
			data-name="Path 6944"
			d="M-5446.921,2382.22s56.339,35.212,97.03,139.591,142.954,58.659,183.2,40.26,58.17-10.726,116.144-16.1,98.37-35.6,112.644-71.44,41.936-71.2,87.934-74.325,54.709,7.772,82.308,11.222,70.146-26.449,70.146-26.449Z"
			transform="translate(5446.921 -2382.22)"
			fill="url(#linear-gradient)"
		></path>
	</svg>
	<img class="red-svg" src="control/login/<?php echo getLoginPage(); ?>/images/red-wave.svg" alt="wave" />

	<div class="new_login">
		<div class="login_header">
			<div>
				<div class="header_logo icon_border">
					<a href="javascript:void(0)" class="logo"><img src="<?php echo getLogo(); ?>" alt="Logo" /> </a>
				</div>
				<div class="header_text">
					<h1><?php echo $data['title']; ?></h1>
				</div>
			</div>
			<div class="form-tabs">
				<button id="openLogin"><?php echo $lang['login']; ?></button>
				<?php if(registration()){ ?>
				<button id="openRegister"><?php echo $lang['register']; ?></button>
				<?php } ?>
					<?php if(allowGuest()){ ?>
					<button onclick="getGuestLogin();" ><?php echo $lang['guest_login']; ?></button>
					<?php } ?>				
				<a href="javascript:void(0)" onclick="getLanguage();" id="open_login_menu"><button><img alt="flag" class="intro_lang" src="<?php echo $data['domain']; ?>/system/language/<?php echo $cur_lang; ?>/flag.png"/></button></a>
			</div>
			<?php if(registration()){ ?>
			<div class="dontHaveAnAccount">
				<p><?php echo $lang['not_member']; ?><button><?php echo $lang['register']; ?></button></p>
			</div>
			<?php } ?>
		</div>
		<div class="login_div" id="login_div">
			<div class="login_left_content">
				<div class="login_left_content_text">
					<h2><?php echo $lang['left_title']; ?></h2>
					<p><?php echo $lang['left_welcome']; ?></p>
				</div>
			</div>
			<div class="formDiv">
				<form id="login_form_box" method="post">
					<p class="title"><?php echo $lang['login']; ?></p>
					<?php if(bridgeMode(0)){ ?>
					<div class="wow_form_fields">
						<label for="user_username"><?php echo $lang['name_email']; ?></label>
						<input id="user_username" name="username" type="text" autocomplete="off" autofocus="" />
					</div>
					<div class="wow_form_fields">
						<div>
							<label for="user_password"><?php echo $lang['password']; ?></label>
							<a  onclick="getRecovery();" class="main"><?php echo $lang['forgot']; ?></a>
						</div>
						<input id="user_password"  name="password" type="password" autocomplete="off" />
					</div>
					<?php if(boomRecaptcha()){ ?>
					<div class="wow_form_fields recapcha_div">
						<!-- Login Form Recaptcha -->
						<div id="recaptcha_login"></div>
						<a onclick="resetRecaptcha('login');" class="main">Reset Recaptcha</a>
						</br>
					</div>
					<?php } ?>
					<?php } ?>
					<div class="forgot_password">
						<div class="terms forgotPass-terms">
							<input type="checkbox" name="remember_device" id="remember_device" checked="" />
							<label for="remember_device"> Remember this device </label>
							<div class="clear"></div>
						</div>

					</div>
					<?php if(bridgeMode(0)){ ?>
					<div class="login_signup_combo">
						<div class="login__">
							<button type="button" onclick="sendLogin();" style="background-color: #c32e3a!important;" class="btn btn-main btn-mat add_wow_loader"> <?php echo $lang['login']; ?></button>
						</div>
						<div class="signup__">
						</div>
					</div>
					<?php } ?>
					<?php if(bridgeMode(1)){ ?>
					<div class="login_signup_combo">
						<div class="login__">
							<button type="button" onclick="bridgeLogin('<?php echo getChatPath(); ?>');" style="background-color: #c32e3a!important;" class="btn btn-main btn-mat add_wow_loader"><?php echo $lang['enter_now']; ?></button>
						</div>
					</div>
					<?php } ?>					

					
				</form>
				<?php if(boomUseSocial() && !embedMode()){ ?>
				<div class="social_btns">
				<p><span></span><?php echo $lang['connect_with']; ?><span></span></p>
					<div class="social_btns_div">
					<?php if(boomSocial('facebook_login')){ ?>
					<a onclick="window.location.href='<?php echo getSocialLoginCallbackUrl('facebook'); ?>'" class="btn no_padd">
						<svg id="Capa_1" enable-background="new 0 0 512 512" height="512" viewBox="0 0 512 512" width="512" xmlns="http://www.w3.org/2000/svg"> <g> <path d="m512 256c0 127.78-93.62 233.69-216 252.89v-178.89h59.65l11.35-74h-71v-48.02c0-20.25 9.92-39.98 41.72-39.98h32.28v-63s-29.3-5-57.31-5c-58.47 0-96.69 35.44-96.69 99.6v56.4h-65v74h65v178.89c-122.38-19.2-216-125.11-216-252.89 0-141.38 114.62-256 256-256s256 114.62 256 256z" fill="#1877f2" ></path> <path d="m355.65 330 11.35-74h-71v-48.021c0-20.245 9.918-39.979 41.719-39.979h32.281v-63s-29.296-5-57.305-5c-58.476 0-96.695 35.44-96.695 99.6v56.4h-65v74h65v178.889c13.034 2.045 26.392 3.111 40 3.111s26.966-1.066 40-3.111v-178.889z" fill="#fff" ></path> </g> </svg>
					</a>
					<?php } ?>
					<?php if(boomSocial('twitter_login')){ ?>
					<a onclick="window.location.href='<?php echo getSocialLoginCallbackUrl('twitter'); ?>'" class="btn no_padd"><svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve"> <path style="fill:#03A9F4;" d="M512,97.248c-19.04,8.352-39.328,13.888-60.48,16.576c21.76-12.992,38.368-33.408,46.176-58.016 c-20.288,12.096-42.688,20.64-66.56,25.408C411.872,60.704,384.416,48,354.464,48c-58.112,0-104.896,47.168-104.896,104.992 c0,8.32,0.704,16.32,2.432,23.936c-87.264-4.256-164.48-46.08-216.352-109.792c-9.056,15.712-14.368,33.696-14.368,53.056 c0,36.352,18.72,68.576,46.624,87.232c-16.864-0.32-33.408-5.216-47.424-12.928c0,0.32,0,0.736,0,1.152 c0,51.008,36.384,93.376,84.096,103.136c-8.544,2.336-17.856,3.456-27.52,3.456c-6.72,0-13.504-0.384-19.872-1.792 c13.6,41.568,52.192,72.128,98.08,73.12c-35.712,27.936-81.056,44.768-130.144,44.768c-8.608,0-16.864-0.384-25.12-1.44 C46.496,446.88,101.6,464,161.024,464c193.152,0,298.752-160,298.752-298.688c0-4.64-0.16-9.12-0.384-13.568 C480.224,136.96,497.728,118.496,512,97.248z"></path> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> </svg> </a>
					<?php } ?>
					<?php if(boomSocial('google_login')){ ?>
					<a onclick="window.location.href='<?php echo getSocialLoginCallbackUrl('google'); ?>'" class="btn no_padd">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="rgba(255,87,0,1)"><path d="M3.06364 7.50914C4.70909 4.24092 8.09084 2 12 2C14.6954 2 16.959 2.99095 18.6909 4.60455L15.8227 7.47274C14.7864 6.48185 13.4681 5.97727 12 5.97727C9.39542 5.97727 7.19084 7.73637 6.40455 10.1C6.2045 10.7 6.09086 11.3409 6.09086 12C6.09086 12.6591 6.2045 13.3 6.40455 13.9C7.19084 16.2636 9.39542 18.0227 12 18.0227C13.3454 18.0227 14.4909 17.6682 15.3864 17.0682C16.4454 16.3591 17.15 15.3 17.3818 14.05H12V10.1818H21.4181C21.5364 10.8363 21.6 11.5182 21.6 12.2273C21.6 15.2727 20.5091 17.8363 18.6181 19.5773C16.9636 21.1046 14.7 22 12 22C8.09084 22 4.70909 19.7591 3.06364 16.4909C2.38638 15.1409 2 13.6136 2 12C2 10.3864 2.38638 8.85911 3.06364 7.50914Z"></path></svg>
					</a>
					<?php } ?>
					</div>
				</div>
				<?php } ?>
			</div>
		</div>
		<div class="login_div" id="register_div">
			<div class="login_left_content">
				<div class="login_left_content_text">
					<h2>Connect with friends!</h2>
					<p>Share Posts, videos, and other with your friends.</p>
				</div>
			</div>
			<div class="formDiv">
				<form id="registration_form_box" method="post">
					<p class="title main"><?php echo $lang['register']; ?></p>
					<div class="wow_form_fields">
						<label for="reg_username"><?php echo $lang['username']; ?></label>
						<input spellcheck="false" id="reg_username" name="reg_username" type="text"  maxlength="<?php echo $data['max_username']; ?>" autocomplete="off" />
						<input type="text" style="display:none">
					</div>
					<div class="wow_form_fields">
						<label for="reg_email"><?php echo $lang['email']; ?></label>
						<input id="reg_email" name="reg_email" type="email" maxlength="80" type="text" autocomplete="off"/>
					</div>
					<div class="wow_form_fields">
						<label for="password"><?php echo $lang['password']; ?></label>
						<input spellcheck="false" id="reg_password"  name="reg_password"  maxlength="30" type="password" autocomplete="off" />
						<input type="password" style="display:none">
					</div>
					<div class="wow_form_fields hidden">
						<label for="confirm_password">Confirm Password</label>
						<input id="confirm_password" name="confirm_password" type="password" />
					</div>
					<div class="wow_form_fields">
						<label for="login_select_gender"><?php echo $lang['gender']; ?></label>
						<select name="login_select_gender" id="login_select_gender">
							<?php echo listGender(1); ?>
						</select>
					</div>
					<div class="wow_form_fields">
						<label for="login_select_age"><?php echo $lang['age']; ?></label>
						<select  size="1"name="login_select_age" id="login_select_age">
							<?php
								echo listAge('', 1);
								?>
						</select>
					</div>
					<?php if(boomRecaptcha()){ ?>
						<!-- Registration Form Recaptcha -->
						<div id="recaptcha_register"></div>
						<a onclick="resetRecaptcha('register');" class="main" >Reset Recaptcha</a>
						</br>
					<?php } ?>
					<div class="terms">
						<input type="checkbox" name="accept_terms" id="accept_terms" onchange="activateButton(this)" />
						<label for="accept_terms">
							<?php echo $lang['i_agree']; ?> - <a href="javascript:void(0)" class="main"><span class="rules_click" onclick="showRules();"><?php echo $lang['rules']; ?></span></a>
						</label>
						<div class="clear"></div>
					</div>
					<div class="login_signup_combo">
						<div class="login__">
							<button  onclick="sendRegistration();" type="button" style="background: #c32e3a !important;" class="btn btn-main btn-mat " id="register_button" disabled=""><?php echo $lang['register']; ?></button>
						</div>
                        <?php if ($show_referral_input): ?>
                            <input type="hidden" name="referrer_id" value="<?php echo $_SESSION['referrer_id']; ?>">
                        <?php endif; ?>
						
						<div class="signup__">
							<p>Already have an account? <a class="dec main another_login" href="javascript:void(0)">Login</a></p>
						</div>
					</div>
				</form>
			</div>
		</div>
		

		<div class="login_randomUsers">
			<div class="random_users">
			<?php echo introActive(8); ?>
			</div>
		</div>
		<div class="footer">
			<div class="container">
				<?php boomFooterMenu(); ?>
			</div>
			<div class="clearfix"></div>
		</div>
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
<script>
$('#openLogin ,.another_login').click(() => {
   $('#register_div').hide();
   $('#login_div').css('display', 'flex');
})
$('#openRegister').click(() => {
   $('#login_div').hide();
   $('#register_div').css('display', 'flex');
})
$('.dontHaveAnAccount p button').click(() => {
   $('#openRegister').click();
})
function activateButton(element) {
	if(element.checked) {
		document.getElementById("register_button").disabled = false;
	}
	else  {
		document.getElementById("register_button").disabled = true;
	}
};

</script>
<script data-cfasync="false" src="js/function_login.js<?php echo $bbfv; ?>"></script>
<script data-cfasync="false" src="js/function_active.js<?php echo $bbfv; ?>"></script>