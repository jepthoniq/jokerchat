<?php
/**
* FuseChat
*
* @package FuseChat
* @author www.nemra-1.com
* @copyright 2025
* @terms any use of this script without a legal license is prohibited
* all the content of FuseChat is the propriety of FuseChat and Cannot be 
* used for another project.
*/
require_once('../config_chat.php');

$chat_history = 20;
$chat_substory = 20;
$private_history = 18;
$status_delay = $data['last_action'] + 21;
$out_delay = time() - 1800;
//update user last active if user outside of chat 
$last_active = updateLastActive($data['user_id']);
if(isset($_POST['last'], $_POST['snum'], $_POST['caction'], $_POST['fload'], $_POST['preload'], $_POST['priv'], $_POST['lastp'], $_POST['pcount'], $_POST['room'], $_POST['notify'])){
    // Escape and validate POST data
    $last = isset($_POST['last']) ? (int) $_POST['last'] : 0;
    $fload = isset($_POST['fload']) ? (int) $_POST['fload'] : 0;
    $snum = isset($_POST['snum']) ? (int) $_POST['snum'] : 0;
    $caction = isset($_POST['caction']) ? htmlspecialchars($_POST['caction'], ENT_QUOTES, 'UTF-8') : '';
    $preload = isset($_POST['preload']) ? (int) $_POST['preload'] : 0;
    $priv = isset($_POST['priv']) ? (int) $_POST['priv'] : 0;
    $lastp = isset($_POST['lastp']) ? (int) $_POST['lastp'] : 0;
    $pcount = isset($_POST['pcount']) ? (int) $_POST['pcount'] : 0;
    $room = isset($_POST['room']) ? (int) $_POST['room'] : 0;
    $notify = isset($_POST['notify']) ? (int) $_POST['notify'] : 0;
	$check_dj = checkAndUpdateBroadcaster($data['user_roomid'],$data['user_id']);
	if($room != $data['user_roomid']){
		echo json_encode( array("check" => 199));
		die();
	}
	// main chat part
	$d['mlogs'] = '';
	$d['plogs'] = '';
	$d['mlast'] = $last;
	$d['plast'] = $lastp;
	$d['rewards'] = updateUserGold();
	$d['rooms_updates'] = get_rooms_notifications();
	$gnotif   = gift_notification();
	$main = 1;
	$private = 1;
	$ssnum = 0;
	//$d['bot'] = get_bots($data['user_roomid']);
	// join room message part
	if( time() > $status_delay || $fload == 0 ){
		$ip = getIp();
		if($fload == 0 && $data['join_msg'] == 0 || $data['last_action'] < $out_delay){
			joinRoom();
		}
		$mysqli->query("UPDATE boom_users SET join_msg = '1', last_action = '" . time() . "', user_ip = '$ip' WHERE user_id = '{$data['user_id']}'");
		
	}
	if(useGold()){
		$d['gold'] = (int) $data['user_gold'];
	}
	// notification check
	if($notify < $data['naction']){
		$get_notify = $mysqli->query("SELECT
		(SELECT count(*) FROM boom_friends WHERE target = '{$data['user_id']}' AND fstatus = '2' AND viewed = '0') as friend_count,
		(SELECT count(*) FROM boom_notification WHERE notified = '{$data['user_id']}' AND notify_view = '0') as notify_count,
		(SELECT count(*) FROM boom_report) as report_count,
		(SELECT count(*) FROM boom_news WHERE news_date > '{$data['user_news']}') as news_count
		");
		if($get_notify->num_rows == 1){
			$fetch = $get_notify->fetch_assoc();
			$d['use'] = 1;
			$d['friends'] = $fetch['friend_count'];
			$d['notify'] = $fetch['notify_count'];
			$d['news'] = $fetch['news_count'];
			$d['nnotif'] = $data['naction'];
			if(boomAllow(70)){
				$d['report'] = $fetch['report_count'];
			}
		}
	}
	$d['r_info'] = array(
	    "room_name" => $data['room_name'],
	    "room_icon" => myRoomIcon($data['room_icon']),
	    "max_user" => $data['max_user'],
	    );
		// main chat logs part
		if ($fload == 0) {
			$add = (!isGhosted($data) && !canViewGhost()) ? 'AND pghost = 0' : '';
			// Prepare the query
			$query = "
				SELECT log.*, 
					u.user_name, u.user_color, u.user_font, u.user_rank, u.bccolor, u.user_sex, u.user_age, 
					u.user_tumb, u.user_cover, u.country, u.user_bot, u.user_ghost, u.user_pmute, 
					u.user_mmute, u.room_mute, u.warn_msg, u.photo_frame, u.user_level, u.user_exp, u.user_badge, u.name_wing1, u.name_wing2
				FROM (
					SELECT * FROM boom_chat 
					WHERE post_roomid = {$data['user_roomid']} AND post_id > '$last' $add
					ORDER BY post_id DESC LIMIT $chat_history
				) AS log
				LEFT JOIN boom_users u ON log.user_id = u.user_id
				ORDER BY log.post_id ASC
			";
			// Execute the query
			$log = $mysqli->query($query);
			$ssnum = 1;
		} else {
			if ($caction != $data['rcaction']) {
				$add = (!isGhosted($data) && !canViewGhost()) ? 'AND pghost = 0' : '';
				// Prepare the query
				$query = "
					SELECT log.*,
						u.user_name, u.user_color, u.user_font, u.user_rank, u.bccolor, u.user_sex, u.user_age, 
						u.user_tumb, u.user_cover, u.country, u.user_bot, u.user_ghost, u.user_pmute, u.user_mmute, u.room_mute, u.warn_msg, u.photo_frame, u.user_level, u.user_exp, u.user_badge, u.name_wing1, u.name_wing2
					FROM (
						SELECT * FROM boom_chat 
						WHERE post_roomid = {$data['user_roomid']} AND post_id > '$last' $add
						ORDER BY post_id DESC LIMIT $chat_substory
					) AS log
					LEFT JOIN boom_users u ON log.user_id = u.user_id
					ORDER BY log.post_id ASC
				";
				// Execute the query
				$log = $mysqli->query($query);
			} else {
				$main = 0;
			}
		}
	if($main == 1){
		if($log->num_rows > 0){
			while ($chat = $log->fetch_assoc()){
				$d['mlast'] = $chat['post_id'];
				if($chat['snum'] != $snum || $ssnum == 1){
					$d['mlogs'] .= createLog($data, $chat, $ignore);
				}
			}
		}
	}	
	if(!delExpired($data['rltime'])){
		$d['del'] = array();
		$todelete = explode(",", $data['rldelete']);
		foreach($todelete as $delpost) {
			$delpost = trim($delpost);
			array_push($d['del'], $delpost);
		}
	}	
	// private logs part
    if ($preload == 1) {
        $privlog = $mysqli->query("
            SELECT 
                log.*, u.user_id, u.user_name, u.user_color, u.user_tumb, u.user_bot, 
                u.user_ghost, u.user_pmute, u.user_mmute, u.room_mute
            FROM (
                SELECT * FROM boom_private 
                WHERE (hunter = '{$data['user_id']}' AND target = '$priv') 
                   OR (hunter = '$priv' AND target = '{$data['user_id']}') 
                ORDER BY id DESC 
                LIMIT $private_history
            ) AS log
            LEFT JOIN boom_users u ON log.hunter = u.user_id
            ORDER BY log.time ASC
        ");
    }
	else {
		if ($pcount != $data['pcount'] && $priv != 0) {
            $privlog = $mysqli->query("
                SELECT 
                    log.*, u.user_id, u.user_name, u.user_color, u.user_tumb, u.user_bot, 
                    u.user_ghost, u.user_pmute, u.user_mmute, u.room_mute
                FROM (
                    SELECT * FROM boom_private 
                    WHERE 
                        (hunter = '$priv' AND target = '{$data['user_id']}' AND id > '$lastp') 
                        OR 
                        (hunter = '{$data['user_id']}' AND target = '$priv' AND id > '$lastp' AND file = 1)
                    ORDER BY id DESC 
                    LIMIT $private_history
                ) AS log
                LEFT JOIN boom_users u ON log.hunter = u.user_id
                ORDER BY log.time ASC
            ");
        }
		else {
			$private = 0;
		}
	}
	if ($private == 1) {
		if ($privlog->num_rows > 0) {
			// Use prepared statements to avoid SQL injection
			$stmt = $mysqli->prepare("UPDATE `boom_private` SET `status` = 1, `view` = 1 WHERE `hunter` = ? AND `target` = ?");
			$stmt->bind_param("ii", $priv, $data['user_id']);  // Bind parameters (assumes both are integers)
			$stmt->execute();  // Execute the update query
			// Fetch the private logs securely
			while ($private = $privlog->fetch_assoc()) {
				// Assuming privateLog() is already secure and returns safe output
				$d['plogs'] .= privateLog($private, $data['user_id']);
				$d['plast'] = $private['id'];
			}
			// Close the prepared statement to free up resources
			$stmt->close();
		}
	}	
	// topic part
	if($fload == 0){
		if($data['topic'] != ''){
			$d['top'] = getTopic($data['topic']);
		}
	}	
	// room access part
	if(canEditRoom()){
		$d['rset'] = 1;
	}	
	// room ranking
	if(haveRole($data['user_role'])){
		$d['role'] = $data['user_role'];
	}	
	// mute check
	$d['rm'] = checkMute($data);
    if($gnotif){
        $d['gnotif'] =$gnotif;
    }
    	// warning
	if(isWarned($data)){
		$d['warn'] = $data['warn_msg'];
	}
	if($check_dj['status']==200){
	   $d['dj'] = $check_dj;
	}elseif($check_dj['status']==404){
	    $d['dj'] = $check_dj; 
	}

	mysqli_close($mysqli);
	// sending results
	$d['pcount'] = $data['pcount'];
	$d['cact'] = $data['rcaction'];
	$d['act'] = $data['user_action'];
	$d['ses'] = $data['session_id'];
	$d['curp'] = $priv;
	$d['spd'] = (int)$data['speed'];
	$d['acd'] = $data['act_delay'];
	$d['pico'] = $data['private_count'];
	//$d['userRelationDetails'] = roomDetails($room);

	echo fu_json_results($d, JSON_UNESCAPED_UNICODE);
}
?>
