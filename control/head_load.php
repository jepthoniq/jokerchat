<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';
if($chat_install != 1){
	header('location: ./');
	die();
}
$ip = getIp();
$page = getPageData($page_info);
$bbfv = boomFileVersion();
$brtl = 0;
// Initialize the message variable
$logged_as = '';

if(isRtl($cur_lang) && $page['page_rtl'] == 1){
	$brtl = 1;
}
// Retrieve the chat room identifier from the URL parameter "chatroom"
$chat_room_identifier = isset($_GET['chatroom']) ? trim($_GET['chatroom']) : null;
$canonical_url = $data['domain'];
if($page['page'] == 'chat'){
	$room = roomDetails($data['user_roomid']);
	$page['page_title'] = $room['room_name'];
	$radio = getPlayer($room['room_player_id']);
	 // Construct the canonical URL using the chat room identifier
    if ($chat_room_identifier) {
        $canonical_url .= '/' . urlencode($chat_room_identifier) . '.html';
    }	
}
if(boomLogged() && !boomAllow($page['page_rank'])){
	header('location: ' . $data['domain']);
	die();
}
// Check if the referral code (user ID) exists in the URL
$show_referral_input = false;  // Flag to show/hide the referral input field
if (isset($_GET['ref'])) {
    $referrer_id = intval($_GET['ref']);  // Convert referral code to an integer
    // Get user details for the given referral ID
    $ref_info = userDetails($referrer_id);
    if ($ref_info) {
        // If the referral ID exists in the database, store the referrer ID in session
        $_SESSION['referrer_id'] = $referrer_id;
         $show_referral_input = true;  // Set flag to true to show hidden input
    } else {
        // If the referral ID does not exist, do not store it in the session
        unset($_SESSION['referrer_id']);  // Optionally remove referrer ID from session
    }
}
// Check if the owner has switched to a different user
if (!empty($_SESSION['switched_user_name']) && !empty($_SESSION['original_owner_name'])) {
    // Safely create the message using htmlspecialchars
    $logged_as = '<div class="notification-message extra_model_content"><div class="alert-danger pad10">You are logged in as ' . htmlspecialchars($_SESSION['switched_user_name']) . '</div><a href="requests.php?f=login_as&s=restore_owner" class="return-owner-button dark_selected">Return to your original account (' . htmlspecialchars($_SESSION['original_owner_name']) . ')</a></div>';
    $logged_as .= '';
}



?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<title><?php echo $page['page_title']; ?></title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="canonical" href="<?php echo $data['domain']; ?>" />
<meta name="description" content="<?php echo $page['page_description']; ?>">
<meta name="keywords" content="<?php echo $page['page_keyword']; ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
<meta name='robots' content='index,follow'>
<link id="siteicon" rel="shortcut icon" type="image/png" href="default_images/icon.png<?php echo $bbfv; ?>"/>
<link rel="stylesheet" type="text/css" href="js/fancybox/jquery.fancybox.css<?php echo $bbfv; ?>" media="screen" />
<link rel="stylesheet" type="text/css" href="css/fonts/remixicon.css<?php echo $bbfv; ?>" />
<link rel="stylesheet" type="text/css" href="css/selectboxit.css<?php echo $bbfv; ?>" />
<link rel="stylesheet" type="text/css" href="js/jqueryui/jquery-ui.min.css<?php echo $bbfv; ?>" />
<link rel="stylesheet" type="text/css" href="css/main.css<?php echo $bbfv; ?>" />
<!-- Facebook Open Graph Meta Tags -->
<meta property="og:title" content="<?php echo $page['page_title']; ?>">
<meta property="og:description" content="<?php echo $page['page_description']; ?>">
<meta property="og:image" content="<?php echo $data['domain']; ?>/default_images/og-image.jpg<?php echo $bbfv; ?>">
<meta property="og:url" content="<?php echo $data['domain']; ?>">
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?php echo $page['page_title']; ?>">
<!-- Twitter Card Meta Tags -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?php echo $page['page_title']; ?>">
<meta name="twitter:description" content="<?php echo $page['page_description']; ?>">
<meta name="twitter:image" content="<?php echo $data['domain']; ?>/default_images/og-image.jpg<?php echo $bbfv; ?>">
<meta name="twitter:site" content="@AhmedEl98817219">
<?php if(!boomLogged()){ ?>
<link rel="stylesheet" type="text/css" href="control/login/<?php echo getLoginPage(); ?>/login.css<?php echo $bbfv; ?>" />
<?php } ?>
<?php if(boomLogged() && $page['page'] == 'home'){ ?>
<link rel="stylesheet" type="text/css" href="css/lobby.css<?php echo $bbfv; ?>" />
<?php } ?>
<link id="gradient_sheet" rel="stylesheet" type="text/css" href="css/colors.css<?php echo $bbfv; ?>" />
<link id="actual_theme" rel="stylesheet" type="text/css" href="css/themes/<?php echo getTheme(); ?><?php echo $bbfv; ?>" />
<link rel="stylesheet" type="text/css" href="css/responsive.css<?php echo $bbfv; ?>" />
<script data-cfasync="false" src="js/jquery-3.7.1.min.js<?php echo $bbfv; ?>"></script>
<script data-cfasync="false" src="system/language/<?php echo $cur_lang; ?>/language.js<?php echo $bbfv; ?>"></script>
<script data-cfasync="false" src="js/fancybox/jquery.fancybox.js<?php echo $bbfv; ?>"></script>
<script data-cfasync="false" src="js/jqueryui/jquery-ui.min.js<?php echo $bbfv; ?>"></script>
<script data-cfasync="false" src="js/global.min.js<?php echo $bbfv; ?>"></script>
<script data-cfasync="false" src="js/function_split.js<?php echo $bbfv; ?>"></script>
<?php if(boomLogged() && $data['allow_onesignal']==1){ ?>
<script src="https://cdn.onesignal.com/sdks/OneSignalSDK.js" async=""></script>
<?php } ?>
<?php if(boomRecaptcha() && !boomLogged()){ ?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<?php } ?>
<?php if(boomLogged()){ ?>
<link rel="stylesheet" type="text/css" href="system/gifts/files/gifts.css<?php echo $bbfv; ?>" />
<script data-cfasync="false" src="js/function_logged.js<?php echo $bbfv; ?>"></script>
<?php } ?>
<?php if($brtl == 1){ ?>
<link rel="stylesheet" type="text/css" href="css/rtl.css<?php echo $bbfv; ?>" />
<?php } ?>
<link rel="stylesheet" type="text/css" href="css/custom.css<?php echo $bbfv; ?>" />
	<script data-cfasync="false">
    	var _body =  $(document);
    	const desk = localStorage;
		let defaultBackground;
	    var domain ='<?php echo $data['domain']; ?>';
		var pageEmbed = <?php echo embedCode(); ?>;
		var pageRoom = <?php echo $page['page_room']; ?>;
		var curPage = '<?php echo $page['page']; ?>';
		var loadPage = '<?php echo $page['page_load']; ?>';
		var bbfv = '<?php echo $bbfv; ?>';
		var rtlMode = '<?php echo $brtl; ?>';
		const csrf_token = '<?php echo CSRF_TOKEN; ?>';
		const allow_OneSignal =  '<?php echo $data['allow_onesignal']; ?>';
		const allow_typing =  '<?php echo $data['istyping_mode']; ?>';
		let current_vmode = null;
		const allow_websocket =  <?php echo $data['websocket_mode']; ?>;
		let user_id = '<?php echo $data["user_id"] ?? 0; ?>';
		let user_name = '<?php echo $data["user_name"] ?? 'Guest'; ?>';
		 user_room = '<?php echo $room['room_id'] ?? 1; ?>';
		let private_id = 0;
		let privateTyping = <?php echo $data["privateTyping"] ?? 0; ?>;
	<?php 
	if($data['websocket_mode']==1){ ?>
        const s_protocol = '<?php echo $data['websocket_protocol']; ?>';
        const s_server = '<?php echo $data['websocket_path']; ?>';
        const s_port = '<?php echo $data['websocket_port']; ?>';		    
	<?php
	}?>

		
	</script>
<?php if(!boomLogged()){ ?>
	<script data-cfasync="false">
		var logged = 0;
		var utk = 0;
		var recapt = <?php echo $data['use_recapt']; ?>;
		var recaptKey = '<?php echo $data['recapt_key']; ?>';
		var avatar = 'default_images/avatar/default_avatar.svg';
		var user_rank = 0;		
	</script>
<?php } ?>
<?php if(boomLogged()){ ?>
	<script data-cfasync="false">
	     user_name = '<?php echo $data["user_name"] ?? 'Guest'; ?>';
	    var cur_room = 'room_<?php echo $data['user_roomid']; ?>';
		var user_rank = <?php echo $data["user_rank"]; ?>;
		 user_id = <?php echo $data["user_id"]; ?>;
		var utk = '<?php echo setToken(); ?>';
		var avw = <?php echo $data['max_avatar']; ?>;
		var cvw = <?php echo $data['max_cover']; ?>;
		var fmw = <?php echo $data['file_weight']; ?>;
		var uSound = '<?php echo $data['user_sound']; ?>';
		var logged = 1;
		var onesignal_web_push_id =  '<?php echo $data['onesignal_web_push_id']; ?>';
		var allow_gift = '<?php echo $data['use_gift']; ?>';
    	var uQuote = <?php echo $data['allow_quote']; ?>;
    	var upQuote = <?php echo $data['allow_pquote']; ?>;
		var priMin = <?php echo $data['allow_private']; ?>;
		var avatar = '<?php echo myAvatar($data['user_tumb']); ?>';
		var useLevel = <?php echo $data['use_level']; ?>;
		var canCall = <?php if (function_exists('isVoiceCallPurchased') && isVoiceCallPurchased()) { echo minCall(); } else { echo 0; } ?>;
		var useCall = <?php echo $data['use_call']; ?>;
		var useLevel = <?php echo $data['use_level']; ?>;
		var systemLoaded = 0;
	</script>
<?php } ?>
<?php if(boomLogged() && $page['page'] == 'chat'){ ?>
	<script data-cfasync="false">
		var user_room = <?php echo $data['user_roomid']; ?>;
		var sesid = '<?php echo $data['session_id']; ?>';
		var userAction = '<?php echo $data['user_action']; ?>';
		var pCount = "<?php echo $data['pcount']; ?>";
		var source = "<?php echo $radio['player_url']; ?>";
		var speed = <?php echo $data['speed']; ?>;
		var useLevel = <?php echo $data['use_level']; ?>;
		var useBadge = <?php echo $data['use_level']; ?>;		
		var inOut = <?php echo $data['act_delay']; ?>;
		var snum = "<?php echo genSnum(); ?>";
		var balStart = <?php echo $cody['act_time']; ?>;
		var rightHide = <?php echo $cody['rbreak']; ?>;
		var rightHide2 = <?php echo $cody['rbreak'] + 1; ?>;
		var leftHide = <?php echo $cody['lbreak']; ?>;
		var leftHide2 = <?php echo $cody['lbreak']; + 1 ?>;
		var defRightWidth = <?php echo $cody['right_size']; ?>;
		var defLeftWidth = <?php echo $cody['left_size']; ?>;
		var cardCover = <?php echo $cody['card_cover']; ?>;
		var userAge = "<?php echo $lang['years_old']; ?>";
		var userTheme = "<?php echo $data['user_theme']; ?>";
		defaultBackground = '<?php echo $room['background_img'];?>';
        var rname = '<?php echo $room['room_name']; ?>';
	</script>
<?php } ?>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $data['google_analytics']; ?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '<?php echo $data['google_analytics']; ?>');
 document.addEventListener("DOMContentLoaded", function() {
	yall({
		observeChanges: true
	});
}); 
</script>
</head>
<body>
<?php


if(checkBan($ip)){
	include('banned.php');
	include('body_end.php');
	die();
}
if(checkKick()){
    $page['page'] = 'kicked';
	include('kicked.php');
	include('body_end.php');
	die();
}
if(boomLogged() && mustVerify()){
	include('verification.php');
	include('body_end.php');
	die();
}
if(maintMode()){
	include('maintenance.php');
	include('body_end.php');
	die();
}
if(!boomLogged() && $page['page_out'] == 0){
	include('control/login/' . getLoginPage() . '/login.php');
	include('body_end.php');
	die();
}
if($page['page'] == 'chat'){
	createIgnore();
}
?>