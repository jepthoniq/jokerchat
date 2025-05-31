<?php


require __DIR__ . "./../config.php";
if (isset($_POST["password"]) && isset($_POST["username"])) {
    $password = encrypt(escape($_POST["password"]));
    $username = escape($_POST["username"]);
    echo chatlogin($username, $password);
}
if (isset($_POST["guest_name"]) && isset($_POST["guest_gender"]) && isset($_POST["guest_age"])) {
    echo guestnamelogin();
    exit;
}

function guestNameLogin(){
    global $mysqli,$data,$cody;
    $guest_lang = getLanguage();
    $guest_ip = getIp();
    $create = 0;
            if (!allowGuest()) {
                return 0;
            }
            if (!boomCheckRecaptcha()) {
                return 6;
            }
            if (!okGuest($guest_ip)) {
                return 16;
            }
            $guest_name = escape($_POST["guest_name"]);
            $guest_gender = escape($_POST["guest_gender"]);
            $guest_age = escape($_POST["guest_age"]);
            if (!validName($guest_name)) {
                return 4;
            }
            if (!boomUsername($guest_name)) {
                return 5;
            }
            if (guestForm()) {
                if (!validAge($guest_age)) {
                    return 13;
                }
                if (!validGender($guest_gender)) {
                    return 14;
                }
            }
            $guest_user = ["name" => $guest_name, "password" => randomPass(), "language" => $guest_lang, "ip" => $guest_ip, "rank" => 0, "avatar" => "default_guest.png", "email" => ""];
            if (guestForm()) {
                $guest_user["age"] = $guest_age;
                $guest_user["gender"] = $guest_gender;
            }
            $user = boomInsertUser($guest_user);
            if (empty($user)) {
                return 2;
            }
            return 1;
        }

function chatLogin($username, $password){
    global $mysqli,$data;
    $user_ip = getIp();
    if (empty($password) || empty($username) || $password == "0") {
        return 1;
    }
    if (isEmail($username)) {
        $validate = $mysqli->query("SELECT * FROM boom_users WHERE user_password = '" . $password . "' AND user_email = '" . $username . "'");
    } else {
        $validate = $mysqli->query("SELECT * FROM boom_users WHERE user_password = '" . $password . "' AND user_name = '" . $username . "' || temp_pass = '" . $password . "' AND user_name = '" . $username . "' AND temp_pass != '0'");
    }
    if (0 < $validate->num_rows) {
        $valid = $validate->fetch_assoc();
        $post_time = date("H:i", time());
        $ssesid = $valid["session_id"] + 1;
        $id = $valid["user_id"];
        if ($valid["temp_pass"] == $password) {
            $mysqli->query("UPDATE boom_users SET temp_pass = '0', user_password = '" . $password . "', user_ip = '" . $user_ip . "', join_msg = '0', user_roomid = '0', `session_id` = '" . $ssesid . "' WHERE `user_id` = '" . $id . "'");
        } else {
            $mysqli->query("UPDATE boom_users SET user_ip = '" . $user_ip . "', session_id = '" . $ssesid . "', join_msg = '0', user_roomid = '0' WHERE user_id = '" . $id . "'");
        }
        setBoomCookie($id, $password);
        return 3;
    }
    return 2;
}

?>