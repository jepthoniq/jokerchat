<?php


require __DIR__ . "./../config_session.php";
if (isset($_FILES["file"]) && isset($_POST["self"])) {
    echo processcover();
    exit;
}
if (isset($_FILES["file"]) && isset($_POST["target"])) {
    echo staffaddcover();
    exit;
}
if (isset($_POST["delete_cover"])) {
    $reset = resetCover($data);
    exit;
}
if (isset($_POST["remove_cover"])) {
    echo staffremovecover();
}
echo " ";

function processCover(){
	global $mysqli,$data,$cody;
	$res = array();
	$res['msg'] = $lang['upadate_cover'];
    if (!canCover()) {
        return boomCode(1);
    }

    ini_set("memory_limit", "128M");
    $info = pathinfo($_FILES["file"]["name"]);
    $extension = $info["extension"];
    if (fileError(3)) {
        return boomCode(7);
    }
    if (isCoverImage($extension)) {
        $imginfo = getimagesize($_FILES["file"]["tmp_name"]);
        if ($imginfo !== false) {
            list($width, $height) = $imginfo;
            $type = $imginfo["mime"];
            $fname = encodeFileTumb($extension, $data);
            $file_name = $fname["full"];
            $file_tumb = $fname["tumb"];
            boomMoveFile("cover/" . $file_name);
            $source = "cover/" . $file_name;
            $tumb = "cover/" . $file_tumb;
            if (canGifCover()) {
                $create = imageTumb($source, $tumb, $type, 500);
            } else {
                $create = imageTumbGif($source, $tumb, $type, 500);
            }
            if (sourceExist($source) && sourceExist($tumb)) {
                unlinkCover($file_name);
                unlinkCover($data["user_cover"]);
                $mysqli->query("UPDATE boom_users SET user_cover = '" . $file_tumb . "' WHERE user_id = '" . $data["user_id"] . "'");
                return boomCode(5, ["data" => myCover($file_tumb)]);
            }
            if (sourceExist($source)) {
                if (!canGifCover()) {
                    unlinkCover($file_name);
                    return boomCode(7);
                }
                unlinkCover($data["user_cover"]);
                $mysqli->query("UPDATE boom_users SET user_cover = '" . $file_name . "' WHERE user_id = '" . $data["user_id"] . "'");
					// send msg to room with  updated avatar
					// Introduce a delay of 2 seconds (adjust as needed)
                return sendCoverUpdateMessage($file_name,$res['msg']);
            }
            return boomCode(7);
        }
        return boomCode(1);
    }
    return boomCode(1);
}
// Helper function to send a chat message about the updated cover
function sendCoverUpdateMessage($coverFile,$msg) {
    global $data;
    $res = [
        'image_thumb' => myCover($coverFile),
        'msg'         => $msg,
    ];
    $change_msg = boomTemplate("element/avatar_update", $res);
    $execute = systemPostChat($data['user_roomid'], $change_msg, ['type' => 'public__message']);
    header("Content-type: application/json");
    return boomCode(5, ["data" => myCover($coverFile), "content" => $execute]);
}
function staffAddCover(){
    global $mysqli,$data,$cody;
    $target = escape($_POST["target"]);
    $user = userDetails($target);
    if (!canModifyCover($user)) {
        return boomCode(1);
    }

    ini_set("memory_limit", "128M");
    $info = pathinfo($_FILES["file"]["name"]);
    $extension = $info["extension"];
    if (fileError(3)) {
        return boomCode(1);
    }
    if (isCoverImage($extension)) {
        $imginfo = getimagesize($_FILES["file"]["tmp_name"]);
        if ($imginfo !== false) {
            list($width, $height) = $imginfo;
            $type = $imginfo["mime"];
            $fname = encodeFileTumb($extension, $user);
            $file_name = $fname["full"];
            $file_tumb = $fname["tumb"];
            boomMoveFile("cover/" . $file_name);
            $source = "cover/" . $file_name;
            $tumb = "cover/" . $file_tumb;
            if (canGifCover()) {
                $create = imageTumb($source, $tumb, $type, 500);
            } else {
                $create = imageTumbGif($source, $tumb, $type, 500);
            }
            if (sourceExist($source) && sourceExist($tumb)) {
                unlinkCover($file_name);
                unlinkCover($user["user_cover"]);
                $mysqli->query("UPDATE boom_users SET user_cover = '" . $file_tumb . "' WHERE user_id = '" . $user["user_id"] . "'");
                return boomCode(5, ["data" => myCover($file_tumb)]);
            }
            if (sourceExist($source)) {
                if (!canGifCover()) {
                    unlinkCover($file_name);
                    return boomCode(7);
                }
                unlinkCover($user["user_cover"]);
                $mysqli->query("UPDATE boom_users SET user_cover = '" . $file_name . "' WHERE user_id = '" . $user["user_id"] . "'");
                return boomCode(5, ["data" => myCover($file_name)]);
            }
            return boomCode(7);
        }
        return boomCode(1);
    }
    return boomCode(1);
}

function staffRemoveCover(){
    global $mysqli,$data,$cody;
    $target = escape($_POST["remove_cover"]);
    $user = userDetails($target);
    if (!canModifyCover($user)) {
        return 0;
    }
    resetCover($user);
    boomConsole("remove_cover", ["target" => $user["user_id"]]);
    return 1;
}

?>