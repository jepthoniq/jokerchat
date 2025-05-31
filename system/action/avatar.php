<?php
require __DIR__ . "./../config_session.php";
// Handle avatar upload for the logged-in user
if (isset($_FILES["file"]) && isset($_POST["self"])) {
    echo processAvatar();
    exit;
}
// Handle avatar upload for a staff member
if (isset($_FILES["file"]) && isset($_POST["target"])) {
    echo processStaffAvatar();
    exit;
}
// Handle avatar deletion for the logged-in user
if (isset($_POST["delete_avatar"])) {
    $avatar_link = myAvatar(resetAvatar($data));
    echo boomCode(0, ["data" => $avatar_link]);
    exit;
}
// Handle avatar removal for a staff member
if (isset($_POST["remove_avatar"])) {
    echo staffRemoveAvatar();
}
function staffRemoveAvatar() {
    global $mysqli, $data, $cody;
    $target = filter_input(INPUT_POST, "remove_avatar", FILTER_SANITIZE_NUMBER_INT);
    // Fetch user details
    $user = userDetails($target);
    if (empty($user)) {
        return boomCode(0); // User not found
    }
    // Check permission to modify avatar
    if (!canModifyAvatar($user)) {
        return boomCode(0); // Insufficient permissions
    }
    // Reset the avatar
    $reset = myAvatar(resetAvatar($user));
    return boomCode(1, ["data" => $reset]); // Return reset avatar link
}
function processAvatar() {
    global $mysqli, $data, $cody,$lang;
	$res['msg'] = $lang['upadate_avatar'];
    ini_set("memory_limit", "128M");
    $info = pathinfo($_FILES["file"]["name"]);
    $extension = strtolower($info["extension"]);
    // Check if the avatar upload is allowed
    if (!canAvatar()) {
        return boomCode(1); // Avatar upload not allowed
    }
    // Check if there was a file error
    if (fileError(2)) {
        return boomCode(1); // File error
    }
    // Generate file names
    $file_tumb = "avatar_user" . $data["user_id"] . "_" . time() . ".jpg";
    $file_avatar = "temporary_avatar_user_" . $data["user_id"] . "." . $extension;
    // Clean up any old avatar files
    unlinkAvatar($file_avatar);
    // Check if the file is a valid image
    if (isImage($extension)) {
        $info = getimagesize($_FILES["file"]["tmp_name"]);
        if ($info !== false) {
            list($width, $height) = $info;
            $type = $info["mime"];
            // Move the file and create a thumbnail
            boomMoveFile("avatar/" . $file_avatar);
            $filepath = "avatar/" . $file_tumb;
            $filesource = "avatar/" . $file_avatar;
            $create = createTumbnail($filesource, $filepath, $type, $width, $height, 200, 200);
            // Validate the created image
            if (sourceExist($filepath) && sourceExist($filesource)) {
                if (validImageData($filepath)) {
                    // Update the database and delete old avatar files
                    unlinkAvatar($data["user_tumb"]);
                    unlinkAvatar($file_avatar);
                    $stmt = $mysqli->prepare("UPDATE boom_users SET user_tumb = ? WHERE user_id = ?");
                    $stmt->bind_param("si", $file_tumb, $data["user_id"]);
                    $stmt->execute();
                    $stmt->close();
					// send msg to room with  updated avatar
					// Introduce a delay of 2 seconds (adjust as needed)
					sleep(2);
					$res =['image_thumb' => myAvatar($file_tumb),'msg' => $res['msg']];
					$change_msg = boomTemplate("element/avatar_update", $res);
					systemPostChat($data['user_roomid'], $change_msg, ['type' => 'public__message']);
                    return boomCode(5, ["data" => myAvatar($file_tumb)]);
                }
                unlinkAvatar($file_avatar);
                return boomCode(7); // Invalid image data
            }
            unlinkAvatar($file_avatar);
            return boomCode(7); // Error processing image
        }
        return boomCode(7); // Invalid image file
    }
    return boomCode(1); // Not an image
}

function processStaffAvatar() {
    global $mysqli, $data, $cody;
    $target = filter_input(INPUT_POST, "target", FILTER_SANITIZE_NUMBER_INT);
    // Fetch user details
    $user = userDetails($target);
    if (empty($user)) {
        return boomCode(1); // User not found
    }
    // Check permission to modify avatar
    if (!canModifyAvatar($user)) {
        return boomCode(1); // Insufficient permissions
    }
    ini_set("memory_limit", "128M");
    $info = pathinfo($_FILES["file"]["name"]);
    $extension = strtolower($info["extension"]);
    // Check if the file has an error
    if (fileError(2)) {
        return boomCode(1); // File error
    }
    // Generate file names
    $file_tumb = "avatar_user" . $user["user_id"] . "_" . time() . ".jpg";
    $file_avatar = "temporary_avatar_user_" . $user["user_id"] . "." . $extension;
    // Clean up any old avatar files
    unlinkAvatar($file_avatar);
    // Check if the file is a valid image
    if (isImage($extension)) {
        $info = getimagesize($_FILES["file"]["tmp_name"]);
        if ($info !== false) {
            list($width, $height) = $info;
            $type = $info["mime"];
            // Move the file and create a thumbnail
            boomMoveFile("avatar/" . $file_avatar);
            $filepath = "avatar/" . $file_tumb;
            $filesource = "avatar/" . $file_avatar;
            $create = createTumbnail($filesource, $filepath, $type, $width, $height, 200, 200);
            // Validate the created image
            if (sourceExist($filepath) && sourceExist($filesource)) {
                if (validImageData($filepath)) {
                    // Update the database and delete old avatar files
                    unlinkAvatar($user["user_tumb"]);
                    unlinkAvatar($file_avatar);
                    $stmt = $mysqli->prepare("UPDATE boom_users SET user_tumb = ? WHERE user_id = ?");
                    $stmt->bind_param("si", $file_tumb, $user["user_id"]);
                    $stmt->execute();
                    $stmt->close();
                    boomConsole("change_avatar", ["target" => $user["user_id"]]);
                    return boomCode(5, ["data" => myAvatar($file_tumb)]);
                }
                unlinkAvatar($file_avatar);
                return boomCode(7); // Invalid image data
            }
            unlinkAvatar($file_avatar);
            return boomCode(7); // Error processing image
        }
        return boomCode(7); // Invalid image file
    }
    return boomCode(1); // Not an image
}

?>
