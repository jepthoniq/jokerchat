<?php
/**
 * FuseChat Backend Script (Enhanced)
 *
 * @package FuseChat
 * @author www.nemra-1.com
 * @copyright 2025
 * @terms Unauthorized use prohibited
 */
require_once('../config_chat.php');

// Initialize configurable limits
$chat_history = $data['max_public_history'] ?? 10; // Maximum public chat messages
$chat_substory = $data['max_chat_substory'] ?? 20; // Maximum subsequent chat messages
$private_history = $data['max_private_history'] ?? 18; // Maximum private messages

// Initialize response data
$d = [
    'mlogs' => '',
    'plogs' => '',
    'mlast' => 0,
    'plast' => 0,
    'rewards' => [],
    'rooms_updates' => [],
    'del' => [],
    'pcount' => 0,
    'cact' => 0,
    'act' => 0,
    'ses' => 0,
    'curp' => 0,
    'spd' => 0,
    'acd' => 0,
    'pico' => 0,
];

try {
    // Validate POST data
    $postData = filter_input_array(INPUT_POST, [
        'last' => FILTER_VALIDATE_INT,
        'snum' => FILTER_VALIDATE_INT,
        'caction' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'fload' => FILTER_VALIDATE_INT,
        'preload' => FILTER_VALIDATE_INT,
        'priv' => FILTER_VALIDATE_INT,
        'lastp' => FILTER_VALIDATE_INT,
        'pcount' => FILTER_VALIDATE_INT,
        'room' => FILTER_VALIDATE_INT,
        'notify' => FILTER_VALIDATE_INT,
    ]);

    foreach ($postData as $key => $value) {
        if ($value === false || $value === null) {
            throw new Exception("Invalid input for $key");
        }
    }

    extract($postData);

    // Validate room access
    if ($room != $data['user_roomid']) {
        echo json_encode(["check" => 199]);
        exit;
    }

    // Update user last active status
    updateLastActive($data['user_id']);

    // Main chat logic
    $status_delay = $data['last_action'] + 21;
    $out_delay = time() - 1800;

    if (time() > $status_delay || $fload == 0) {
        $ip = getIp();
        if (($fload == 0 && $data['join_msg'] == 0) || $data['last_action'] < $out_delay) {
            joinRoom();
        }
        $mysqli->query("UPDATE boom_users SET join_msg = '1', last_action = '" . time() . "', user_ip = '$ip' WHERE user_id = '{$data['user_id']}'");
    }

    // Initialize main chat part
    $d['mlogs'] = '';
    $d['plogs'] = '';
    $d['mlast'] = $last;
    $d['plast'] = $lastp;

    // Rewards, room updates, and gift notifications
    $d['rewards'] = updateUserGold();
    $d['rooms_updates'] = get_rooms_notifications();
    $gnotif = gift_notification();

    // Gold check
    if (useGold()) {
        $d['gold'] = (int)$data['user_gold'];
    }

    // Notifications
    if ($notify < $data['naction']) {
        $query = "
            SELECT
                (SELECT COUNT(*) FROM boom_friends WHERE target = ? AND fstatus = 2 AND viewed = 0) AS friend_count,
                (SELECT COUNT(*) FROM boom_notification WHERE notified = ? AND notify_view = 0) AS notify_count,
                (SELECT COUNT(*) FROM boom_report) AS report_count,
                (SELECT COUNT(*) FROM boom_news WHERE news_date > ?) AS news_count
        ";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("iis", $data['user_id'], $data['user_id'], $data['user_news']);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        $d['friends'] = $result['friend_count'];
        $d['notify'] = $result['notify_count'];
        $d['news'] = $result['news_count'];
        $d['nnotif'] = $data['naction'];
        if (boomAllow(70)) {
            $d['report'] = $result['report_count'];
        }
    }

    // Room details
    $d['r_info'] = [
        "room_name" => $data['room_name'],
        "room_icon" => myRoomIcon($data['room_icon']),
        "max_user" => $data['max_user'],
    ];

    // Main chat logs part
    $main = 1;
    $ssnum = 0;
    if ($fload == 0) {
        $add = (!isGhosted($data) && !canViewGhost()) ? 'AND pghost = 0' : '';
        $query = "
            SELECT log.*, 
                u.user_name, u.user_color, u.user_font, u.user_rank, u.bccolor, u.user_sex, u.user_age, 
                u.user_tumb, u.user_cover, u.country, u.user_bot, u.user_ghost, u.user_pmute, 
                u.user_mmute, u.room_mute, u.warn_msg, u.photo_frame, u.user_level, u.user_exp, u.user_badge, u.name_wing1, u.name_wing2
            FROM (
                SELECT * FROM boom_chat 
                WHERE post_roomid = ? AND post_id > ? $add
                ORDER BY post_id DESC LIMIT ?
            ) AS log
            LEFT JOIN boom_users u ON log.user_id = u.user_id
            ORDER BY log.post_id ASC
        ";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("iii", $data['user_roomid'], $last, $chat_history);
        $stmt->execute();
        $log = $stmt->get_result();
        $ssnum = 1;
    } else {
        if ($caction != $data['rcaction']) {
            $add = (!isGhosted($data) && !canViewGhost()) ? 'AND pghost = 0' : '';
            $query = "
                SELECT log.*,
                    u.user_name, u.user_color, u.user_font, u.user_rank, u.bccolor, u.user_sex, u.user_age, 
                    u.user_tumb, u.user_cover, u.country, u.user_bot, u.user_ghost, u.user_pmute, u.user_mmute, u.room_mute, u.warn_msg, u.photo_frame, u.user_level, u.user_exp, u.user_badge, u.name_wing1, u.name_wing2
                FROM (
                    SELECT * FROM boom_chat 
                    WHERE post_roomid = ? AND post_id > ? $add
                    ORDER BY post_id DESC LIMIT ?
                ) AS log
                LEFT JOIN boom_users u ON log.user_id = u.user_id
                ORDER BY log.post_id ASC
            ";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("iii", $data['user_roomid'], $last, $chat_substory);
            $stmt->execute();
            $log = $stmt->get_result();
        } else {
            $main = 0;
        }
    }

    if ($main == 1) {
        if ($log->num_rows > 0) {
            while ($chat = $log->fetch_assoc()) {
                $d['mlast'] = $chat['post_id'];
                if ($chat['snum'] != $snum || $ssnum == 1) {
                    $d['mlogs'] .= createLog($data, $chat, $ignore);
                }
            }
        }
    }

    // Handle message deletions
    if (!delExpired($data['rltime'])) {
        $d['del'] = [];
        $todelete = explode(",", $data['rldelete']);
        foreach ($todelete as $delpost) {
            $delpost = trim($delpost);
            if (!empty($delpost) && is_numeric($delpost)) {
                $d['del'][] = (int)$delpost;
            }
        }
    }

    // Private logs part
    $private = 1;
    if ($preload == 1) {
        $query = "
            SELECT 
                log.*, u.user_id, u.user_name, u.user_color, u.user_tumb, u.user_bot, 
                u.user_ghost, u.user_pmute, u.user_mmute, u.room_mute
            FROM (
                SELECT * FROM boom_private 
                WHERE (hunter = ? AND target = ?) OR (hunter = ? AND target = ?)
                ORDER BY id DESC LIMIT ?
            ) AS log
            LEFT JOIN boom_users u ON log.hunter = u.user_id
            ORDER BY log.time ASC
        ";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("iiiii", $data['user_id'], $priv, $priv, $data['user_id'], $private_history);
        $stmt->execute();
        $privlog = $stmt->get_result();
    } else {
        if ($pcount != $data['pcount'] && $priv != 0) {
            $query = "
                SELECT 
                    log.*, u.user_id, u.user_name, u.user_color, u.user_tumb, u.user_bot, 
                    u.user_ghost, u.user_pmute, u.user_mmute, u.room_mute
                FROM (
                    SELECT * FROM boom_private 
                    WHERE ((hunter = ? AND target = ? AND id > ?) OR (hunter = ? AND target = ? AND id > ? AND file = 1))
                    ORDER BY id DESC LIMIT ?
                ) AS log
                LEFT JOIN boom_users u ON log.hunter = u.user_id
                ORDER BY log.time ASC
            ";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("iiiiiii", $priv, $data['user_id'], $lastp, $data['user_id'], $priv, $lastp, $private_history);
            $stmt->execute();
            $privlog = $stmt->get_result();
        } else {
            $private = 0;
        }
    }

    if ($private == 1) {
        if ($privlog->num_rows > 0) {
            $stmt = $mysqli->prepare("UPDATE boom_private SET status = 1, view = 1 WHERE hunter = ? AND target = ?");
            $stmt->bind_param("ii", $priv, $data['user_id']);
            $stmt->execute();

            while ($private = $privlog->fetch_assoc()) {
                $d['plogs'] .= privateLog($private, $data['user_id']);
                $d['plast'] = $private['id'];
            }

            $stmt->close();
        }
    }

    // Topic part
    if ($fload == 0 && $data['topic'] != '') {
        $d['top'] = getTopic($data['topic']);
    }

    // Room access part
    if (canEditRoom()) {
        $d['rset'] = 1;
    }

    // Room ranking
    if (haveRole($data['user_role'])) {
        $d['role'] = $data['user_role'];
    }

    // Mute check
    $d['rm'] = checkMute($data);

    // Gift notifications
    if ($gnotif) {
        $d['gnotif'] = $gnotif;
    }

    // Warning check
    if (isWarned($data)) {
        $d['warn'] = $data['warn_msg'];
    }

    // Broadcaster check
    $check_dj = checkAndUpdateBroadcaster($data['user_roomid'], $data['user_id']);
    if ($check_dj['status'] == 200) {
        $d['dj'] = $check_dj;
    } elseif ($check_dj['status'] == 404) {
        $d['dj'] = $check_dj;
    }

    // Finalize response
    $d['pcount'] = $data['pcount'];
    $d['cact'] = $data['rcaction'];
    $d['act'] = $data['user_action'];
    $d['ses'] = $data['session_id'];
    $d['curp'] = $priv;
    $d['spd'] = (int)$data['speed'];
    $d['acd'] = $data['act_delay'];
    $d['pico'] = $data['private_count'];
	$d['call'] = (int)$data['ucall'];
	$d['pmin'] = (int)$data['allow_private'];
    // Compress JSON response
    ob_start('ob_gzhandler');
    echo fu_json_results($d, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    // Handle errors gracefully
    echo fu_json_results(["error" => $e->getMessage()]);
}

mysqli_close($mysqli);
?>
