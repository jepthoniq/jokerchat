<?php
require_once('../config_session.php');
require(BOOM_PATH . '/system/language/' . $data['user_language'] . '/notification.php');

$find_notify = $mysqli->query("
SELECT boom_notification.*, boom_users.user_name, boom_users.user_id, boom_users.user_tumb, boom_users.user_color
FROM boom_notification
LEFT JOIN boom_users
ON boom_notification.notifier = boom_users.user_id
WHERE boom_notification.notified = '{$data['user_id']}'
ORDER BY boom_notification.notify_date DESC LIMIT 40
");
function renderNotification($notify){
	global $data, $nlang, $lang;
	// Safe fallback if key does not exist
	$ntext = $nlang[$notify['notify_type']] ?? '[Notification not defined]';
	$ntext = str_replace('%custom%', $notify['notify_custom'] ?? '', $ntext);
	$ntext = str_replace('%rank%', rankTitle($notify['notify_rank']), $ntext);
	$ntext = str_replace('%roomrank%', roomRankTitle($notify['notify_rank']), $ntext);
	$ntext = str_replace('%delay%', boomRenderMinutes($notify['notify_delay']), $ntext);
	$ntext = str_replace('%data%', $notify['notify_custom'] ?? '', $ntext);
	$ntext = str_replace('%data2%', $notify['notify_custom2'] ?? '', $ntext);
	
	return $ntext;
}

function notifyLikeBase($type){
	switch($type){
		case 1: 	return 'like.svg';
		case 2: 	return 'dislike.svg';
		case 3: 	return 'love.svg';
		case 4: 	return 'funny.svg';
		default:	return 'default.svg';
	}
}
function notifyIconBase($n){
	switch($n['notify_icon']){
		case 'plike':		return 'proliked.svg';
		case 'like':		return 'like.svg';
		case 'dislike':		return 'dislike.svg';
		case 'love':		return 'love.svg';
		case 'fun':			return 'funny.svg';
		case 'gold':		return 'gold.svg';
		case 'action':		return 'action.svg';
		case 'raction':		return 'raction.svg';
		case 'reply':		return 'reply.svg';
		case 'post':		return 'post.svg';
		case 'friend':		return 'friend.svg';
		case 'account':		return 'account.svg';
		case 'setting':		return 'setting.svg';
		case 'gift':		return 'gift.svg';
		case 'bookmark':	return 'bookmark.svg';
		case 'star':		return 'star.svg';
		case 'announce':	return 'announce.svg';
		case 'flag':		return 'flag.svg';
		case 'mail':		return 'mail.svg';
		case 'vip':			return 'vip.svg';
		case 'preact':		return notifyLikeBase($n['notify_custom']);
		default:			return 'default.svg';
	}
}
function notifyIcon($n){
	return '<img class="notify_icon" src="default_images/notification/' . notifyIconBase($n) .'"/>';
}
$notify_list = '';
if($find_notify->num_rows > 0){
	while($notify = $find_notify->fetch_assoc()){
		$view = '';
		$add_click = '';
		$add_to_date = '';
		$notify_message = '';
		if($notify['notify_view'] == 0){
			$view = '<i class="ri-circle-fill theme_color"></i>';
		}
		$notify_message = renderNotification($notify);
		if($notify['notify_source'] == 'post' && $notify['notify_id'] > 0){
			$add_click = 'onclick="showPost(this, \'' . $notify['notify_id'] . '\');"';
		}
		if($notify['notify_type'] == 'like' && !empty($notify['notify_custom'])){
			$add_to_date = likeType($notify['notify_custom'], 'notify_reaction') . ' ';
		}
		$notify_list .= '<div ' . $add_click . ' class="list_element notify_item">
							<div class="notify_avatar">
								<img src="' . myAvatar($notify['user_tumb']) . '"/>
								' . notifyIcon($notify) . '
							</div>
							<div class="notify_details">
								<p class="hnotify username ' . myColor($notify) . '">' .systemNameFilter($notify) . '</p>
								<p class="text_small sub_text notify_text" >' . $notify_message . '</p>
								<p class="text_micro date date_notify">' . $add_to_date . displayDate($notify['notify_date']) . '</p>
							</div>
							<div class="notify_status">
								' . $view . '
							</div>
						</div>';
	}
	$mysqli->query("UPDATE boom_notification SET notify_view = 1 WHERE notified = '{$data['user_id']}'");
}
else {
	$notify_list .= '<div class="pad_box">' . emptyZone($lang['no_notify']) . '</div>';
}
?>
<div id="notify_list">
	<div id="notify_content">
		<?php echo $notify_list; ?>
	</div>
</div>