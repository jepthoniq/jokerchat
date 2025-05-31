<?php


require __DIR__ . "./../config_session.php";
session_write_close();
if (isset($_POST["clean_data"])) {
    echo cleansystemdata();
    exit;
}

function cleanSystemData(){
 global $mysqli,$data,$cody;
    if (!boomAllow(1)) {
        return boomCode(0);
    }

    $clean = calMinutes($cody["clean_delay"]);
    if ($data["last_clean"] <= $clean) {
        $mysqli->query("UPDATE boom_setting SET last_clean = '" . time() . "' WHERE id = 1");
        if (0 < $data["chat_delete"]) {
            $chatdelay = calMinutes($data["chat_delete"]);
            $mysqli->query("DELETE FROM boom_chat WHERE post_date < '" . $chatdelay . "'");
            $cfiles = $mysqli->query("SELECT file_name FROM boom_upload WHERE date_sent < '" . $chatdelay . "' AND file_zone = 'chat'");
            if (0 < $cfiles->num_rows) {
                while ($cfile = $cfiles->fetch_assoc()) {
                    unlinkUpload("chat", $cfile["file_name"]);
                }
                $mysqli->query("DELETE FROM boom_upload WHERE date_sent < '" . $chatdelay . "' AND file_zone = 'chat'");
            }
        }
        if (0 < $data["private_delete"]) {
            $privatedelay = calMinutes($data["private_delete"]);
            $mysqli->query("DELETE FROM boom_private WHERE time < '" . $privatedelay . "'");
            $pfiles = $mysqli->query("SELECT file_name FROM boom_upload WHERE date_sent < '" . $privatedelay . "' AND file_zone = 'private'");
            if (0 < $pfiles->num_rows) {
                while ($pfile = $pfiles->fetch_assoc()) {
                    unlinkUpload("private", $pfile["file_name"]);
                }
                $mysqli->query("DELETE FROM boom_upload WHERE date_sent < '" . $privatedelay . "' AND file_zone = 'private'");
            }
        }
        if (0 < $data["wall_delete"]) {
            $walldelay = calMinutes($data["wall_delete"]);
            $mysqli->query("DELETE FROM boom_post WHERE post_date < '" . $walldelay . "'");
            $mysqli->query("DELETE FROM boom_post_reply WHERE reply_date < '" . $walldelay . "'");
            $mysqli->query("DELETE FROM boom_post_like WHERE like_date < '" . $walldelay . "'");
            $mysqli->query("DELETE FROM boom_notification WHERE notify_date < '" . $walldelay . "' AND notify_source = 'post'");
            $wfiles = $mysqli->query("SELECT file_name FROM boom_upload WHERE date_sent < '" . $walldelay . "' AND file_zone = 'wall'");
            if (0 < $wfiles->num_rows) {
                while ($wfile = $wfiles->fetch_assoc()) {
                    unlinkUpload("wall", $wfile["file_name"]);
                }
                $mysqli->query("DELETE FROM boom_upload WHERE date_sent < '" . $walldelay . "' AND file_zone = 'wall'");
            }
        }
        $imagedelay = calHour(1);
        $wall_image = $mysqli->query("SELECT * FROM boom_upload WHERE file_complete = 0 AND date_sent <= '" . $imagedelay . "' AND file_zone = 'wall'");
        if (0 < $wall_image->num_rows) {
            while ($wimage = $wall_image->fetch_assoc()) {
                unlinkUpload("wall", $wimage["file_name"]);
            }
            $mysqli->query("DELETE FROM boom_upload WHERE file_complete = 0 AND date_sent <= '" . $imagedelay . "' AND file_zone = 'wall'");
        }
        if (0 < $data["room_delete"]) {
            $room_delay = calMinutes($data["room_delete"]);
            $get_rooms = $mysqli->query("SELECT * FROM boom_rooms WHERE room_action < '" . $room_delay . "' AND room_id > 1 AND room_system = 0");
            if (0 < $get_rooms->num_rows) {
                $prelist = [];
                while ($room = $get_rooms->fetch_assoc()) {
                    array_push($prelist, $room["room_id"]);
                }
                if (!empty($prelist)) {
                    $list = listThisArray($prelist);
                    cleanRoomList($list);
                }
            }
        }
        $notify_delay = calDay(1);
        $long_delay = calMonth(1);
        $mysqli->query("DELETE FROM boom_notification WHERE notify_date < '" . $notify_delay . "' AND notify_view = 1");
        $mysqli->query("DELETE FROM boom_notification WHERE notify_date < '" . $long_delay . "' AND notify_view = 0");
        if (0 < $cody["ignore_clean"]) {
            $ignore_delay = calDay($cody["ignore_clean"]);
            $mysqli->query("DELETE FROM boom_ignore WHERE ignore_date < '" . $ignore_delay . "'");
        }
        $tempDelay = calDay(1);
        $mysqli->query("UPDATE boom_users SET temp_pass = '0', temp_date = '0' WHERE temp_date <= '" . $tempDelay . "'");
        $del_account = cleanList("account_delete");
        if (0 < $data["chat_delete"]) {
            $clean_guest = cleanList("innactive_guest");
        }
        if (0 < $data["member_delete"]) {
            $member_clean = cleanList("innactive_member");
        }
        return boomCode(2);
    }
    return boomCode(1);
}

?>