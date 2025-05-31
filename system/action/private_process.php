<?php
/**
* FuseChat
*
* @package FuseChat
* @author www.nemra-1.com
* @copyright 2020
* @terms any use of this script without a legal license is prohibited
* all the content of FuseChat is the propriety of FuseChat and Cannot be 
* used for another project.
*/
require_once("./../config_session.php");
if (isset($_POST['target']) && isset($_POST['content'])){
	if(checkFlood()){
		echo systemPrivateMute($data,5);
		die(fu_json_results(['error' => 'Your account has been muted 5 minutes for flooding','code' => 100]));
	}
	if(privateBlocked()){
		die(fu_json_results(['error' => 'Private is blocked','code' => 150]));
	}	
	$target = escape($_POST['target']);
	$content = escape($_POST['content']);
	$content = wordFilter($content, 1);
	$content = textFilter($content);

	if(!canSendPrivate($target)){
		die(fu_json_results(['code' => 20]));
	}
	else {
		echo postPrivate($data['user_id'], $target, $content, 1);
	}
}
else {
	echo 4;
}
?>