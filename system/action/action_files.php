<?php
require __DIR__ . "/../config_session.php";
if (isset($_POST["remove_uploaded_file"]) && boomAllow(1)) {
    global $mysqli, $data;
    $toremove = trim($_POST["remove_uploaded_file"]);
    // Prevent SQL injection
    $stmt = $mysqli->prepare("SELECT file_zone, file_name FROM boom_upload WHERE file_key = ? AND file_user = ? AND file_complete = 0");
    $stmt->bind_param("si", $toremove, $data["user_id"]);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($file_zone, $file_name);
        $stmt->fetch();
        $stmt->close();
        // Securely remove the file
        unlinkUpload($file_zone, $file_name);
        // Delete record securely
        $stmt = $mysqli->prepare("DELETE FROM boom_upload WHERE file_key = ? AND file_user = ? AND file_complete = 0");
        $stmt->bind_param("si", $toremove, $data["user_id"]);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt->close();
    }

    exit;
}


?>