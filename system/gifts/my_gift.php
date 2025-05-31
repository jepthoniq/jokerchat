<?php
$gifts = array();
function userGiftList($id){
    global $mysqli, $data, $lang;
    // Secure input - escape and sanitize
    $id = escape($id);
    $id = cleanString($id);
    $gift = array();
    // Use prepared statements to prevent SQL injection
    $query = $mysqli->prepare("SELECT * FROM `boom_users_gift` WHERE boom_users_gift.target = ? ORDER BY boom_users_gift.gift_count DESC");
    $query->bind_param('s', $id); // Bind user id parameter
    $query->execute();
    $result = $query->get_result();
    // Check if any gifts were found
    if ($result->num_rows) {
        while ($row = $result->fetch_assoc()) {
            $gift_id = $row['gift'];
            $to = userDetails($row['target']);
            $from = userDetails($row['hunter']);
            $fu_gifts['gift_data'] = gift_list_byId($gift_id);
            $fu_gifts['gift_xtimes'] = $row['gift_count'];
            $fu_gifts['to'] = htmlspecialchars($to['user_name'], ENT_QUOTES, 'UTF-8'); // Prevent XSS
            $fu_gifts['from'] = htmlspecialchars($from['user_name'], ENT_QUOTES, 'UTF-8'); // Prevent XSS
            $gift[] = $fu_gifts; 
        }
    }   
    return $gift;
}
if (isset($_POST['get_gift'], $_POST['user_id'])) {
    // Secure input - sanitize and escape
    $user_id = escape($_POST['user_id']);
    $user_id = cleanString($user_id);
    $gifts['list'] = userGiftList($user_id);
}
?>
<ul class="gift_list_container">
<?php
if (!empty($gifts['list'])) {
    foreach ($gifts['list'] as $key) {
        $gift_data = $key['gift_data'];
?>
<li class="view_gift fborder bhover pgcard" onclick="play_gift(this)" 
    data-src="<?php echo htmlspecialchars($gift_data['gif_file'], ENT_QUOTES, 'UTF-8'); ?>" 
    data-to="<?php echo htmlspecialchars($key['to'], ENT_QUOTES, 'UTF-8'); ?>"
    data-from="<?php echo htmlspecialchars($key['from'], ENT_QUOTES, 'UTF-8'); ?>"
    data-price="<?php echo htmlspecialchars($gift_data['gift_cost'], ENT_QUOTES, 'UTF-8'); ?>"
    data-gname="<?php echo htmlspecialchars($gift_data['gift_title'], ENT_QUOTES, 'UTF-8'); ?>"
    data-icon="<?php echo htmlspecialchars($gift_data['gift_url'], ENT_QUOTES, 'UTF-8'); ?>"
>
    <img class="pgcard_img" data-src="gift/clown.svg" src="<?php echo htmlspecialchars($gift_data['gift_url'], ENT_QUOTES, 'UTF-8'); ?>">
    <div class="btable_auto gtag pgcard_count">
        <div class="bcell_mid text_small">
            <div class="btable_auto">
                <div class="bcell_mid hpad3 bold"><?php echo htmlspecialchars($key['gift_xtimes'], ENT_QUOTES, 'UTF-8'); ?></div>
            </div>
        </div>
    </div>
</li>
<?php
    }
} else {
    echo emptyZone($lang['empty']);
}
?>
</ul>
