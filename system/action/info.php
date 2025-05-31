<?php


require __DIR__ . "../database.php";
require __DIR__ . "../variable.php";
date_default_timezone_set("America/Montreal");
$yes = "installed";
$no = "not installed";
$gd = $yes;
$curl = $yes;
$mysq = $yes;
$zip = $yes;
$mail = $yes;
$mbs = $yes;
if (!function_exists("mysqli_connect")) {
    $mysq = $no;
}
if (!extension_loaded("gd") && !function_exists("gd_info")) {
    $gd = $no;
}
if (!function_exists("curl_init")) {
    $curl = $no;
}
if (!extension_loaded("zip")) {
    $zip = $no;
}
if (!function_exists("mail")) {
    $mail = $no;
}
if (!extension_loaded("mbstring")) {
    $mbs = $no;
}
echo "<p>Mysqli: ";
echo $mysq;
echo "</p>\r\n<p>Server host: ";
echo $_SERVER["SERVER_NAME"];
echo "</p>\r\n<p>Php version: ";
echo phpVersion();
echo "</p>\r\n<p>Curl is on : ";
echo $curl;
echo "</p>\r\n<p>Gd library : ";
echo $gd;
echo "</p>\r\n<p>Zip : ";
echo $zip;
echo "</p>\r\n<p>Mail : ";
echo $mail;
echo "</p>\r\n<p>Mbstring : ";
echo $mbs;
echo "</p>\r\n";
if ($mysq == $yes && !mysqli_connect_errno()) {
    $mysqli = @new mysqli(BOOM_DHOST, BOOM_DUSER, BOOM_DPASS, BOOM_DNAME);
    $get_data = $mysqli->query("SELECT * FROM boom_setting WHERE id = '1'");
    if (0 < $get_data->num_rows) {
        $data = $get_data->fetch_assoc();
        echo "<p>Index path : " . $data["domain"] . "</p>";
    } else {
        exit;
    }
} else {
    exit;
}

?>