<?php
$res = array();
$hide_modal = isset($_COOKIE['hide_modDay']) && $_COOKIE['hide_modDay'] === 'true';
function suggestThemeChange($userTheme,$suggestedMode) {
    global $lang;
    $string = '';
    // Ensure you're comparing with the correct theme names
    if ($userTheme === "Lite" && $suggestedMode === "dark") {
        $string =  boomTemplate('element/daymode', $suggestedMode);
    } elseif ($userTheme === "Dark" && $suggestedMode === "light") {
        $string =  boomTemplate('element/daymode',$suggestedMode);
    }else{
        $string = false;
    }
    return $string; 
}

if ($f == 'day_mode') {
    if ($s == 'check_day' && boomLogged() === true) {
        if (!$hide_modal) {
            $res['suggestedMode'] =  escape($_POST['time']);
            $res['hide'] = false;
            if ($cody['enable_daymode'] == 1) {
                $res['status'] = true;
                $res['result'] = suggestThemeChange($data['user_theme'],$res['suggestedMode']);
            }
       }else{
           $res['hide'] = true;
       }
        header("Content-type: application/json");
        echo json_encode($res);
        exit();
    }     
}
?>
