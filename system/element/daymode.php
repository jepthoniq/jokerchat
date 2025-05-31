<?php 
$value = '';
$message ='';
$icon = '';
// Check if the cookie is set
$hide_modal = isset($_COOKIE['hide_modDay']) && $_COOKIE['hide_modDay'] === 'true';

if (!$hide_modal) {
    if ($boom == "dark") {
        $message = $lang['night_mode'];
        $value = 'Dark';
        $icon = $data['domain'].'/default_images/icons/moon.png';
    } else if ($boom == "light") {
        $message = $lang['light_mode'];
        $value = 'Lite';
        $icon = $data['domain'].'/default_images/icons/daylight.png';
    }
?>

<div class="top_mod">
    <div class="top_mod_empty"></div>
    <div class="top_mod_option close_modal close_daylight">
        <i class="ri-close-circle-line i_btm"></i>
    </div>
</div>
<div class="bpad30 hpad30 extra_model_content">
    <div class="centered_element bpad15">
        <img class="large_icon" src="<?php echo $icon; ?>"/>
    </div>
    <div class="centered_element">
        <p class="text_large bold"><?php echo textReplace($lang['welcome_user']); ?></p>
        <p class="text_small tpad10"><?php echo $message; ?></p>
    </div>
    <div class="tpad20 centered_element">
        <button class="close_modal default_btn reg_button close_daylight"><?php echo $lang['close']; ?></button>
        <button onclick="setUserTheme(this);" class="close_modal ok_btn reg_button" value="<?php echo $value; ?>"><?php echo $lang['ok']; ?></button>
    </div>
</div>

<?php 
}
?>
