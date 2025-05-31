<?php
/**
 * FuseChat - chat_process.php
 *
 * @package FuseChat
 * @author www.nemra-1.com
 * @copyright 2020
 * @terms Unauthorized use of this script without a valid license is prohibited.
 * All content of FuseChat is the property of BoomCoding and cannot be used in another project.
 */
require_once("./../config_session.php");

 if (!isset($_POST['content'], $_POST['snum'])){
	die();
}
if(isTooLong($_POST['content'], $data['max_main'])){
	die();
}
if (muted() || isRoomMuted($data)) {
	die();
}
if(checkFlood()){
	echo 100;
	die();
}

$snum = escape($_POST['snum']);
$content = escape($_POST['content']);
$content = wordFilter($content, 1);
$content = textFilter($content);

if(empty($content) && $content !== '0' || !inRoom()){
	die();
}
//this part need to handle but is working for now just keep it off untill we finsh it
$data['chat_socket_mode'] = false;
if($data['websocket_mode']==1 && $data['chat_socket_mode']){
	// Example usage: Notify redies with  a new message
	$event = 'newMessage';
	$chat_data = [
		'user_id' => $data['user_id'],
		'msg' => userPostChat($content, array('snum'=> $snum)),
		'room_id' => $data['user_roomid']
	];
	echo notifyRedis($event, $chat_data);
}else{
	echo userPostChat($content, array('snum'=> $snum));	
}
?>
