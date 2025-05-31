<?php


require __DIR__ . "./../config_session.php";

if (isset($_POST["send_verification"]) && isset($_POST["verify"]) && boomAllow(1)) {
    if (!okVerify()) {
        echo 3;
        exit;
    }
    echo sendActivation($data);
    exit;
}
if (isset($_POST["valid_code"]) && isset($_POST["verify_code"])) {
    echo checkCode();
}

function checkCode()
{
    global $data;
    global $mysqli;
    $code = escape($_POST["valid_code"]);
    if ($code == $data["valid_key"] && $data["valid_key"] != "") {
        $mysqli->query("UPDATE boom_users SET verified = '1', user_verify = '0', valid_key = '' WHERE user_id = '" . $data["user_id"] . "'");
        return 1;
    }
    return 0;
}

?>