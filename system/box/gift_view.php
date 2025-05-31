<?php
require('../config_session.php');

function userGiftList($user) {
    global $mysqli, $lang;
    // Prepare the SQL query with a placeholder for the user_id
    $query = "
        SELECT boom_gift.*, boom_users_gift.gift_count
        FROM boom_users_gift 
        LEFT JOIN boom_gift ON boom_gift.id = boom_users_gift.gift
        WHERE boom_users_gift.target = ? 
        ORDER BY boom_users_gift.gift_count DESC
    ";
    // Log the query for debugging (query template without user data)
    error_log($query);
    // Prepare the statement
    if ($stmt = $mysqli->prepare($query)) {
        // Bind the parameter (user ID)
        $stmt->bind_param('i', $user['user_id']);  // 'i' is for integer
        // Execute the statement
        $stmt->execute();
        // Get the result
        $get_gift = $stmt->get_result();
        // Check if we have results
        if ($get_gift->num_rows > 0) {
            // Return paginated results
            return createPag($get_gift, 20, array('template'=> 'gifts/my_gift', 'style'=> 'arrow'));
        } else {
            return []; // No results found
        }
        // Close the prepared statement
        $stmt->close();
    } else {
        // Log an error if the statement could not be prepared
        error_log("Prepared Statement Error: " . $mysqli->error);
        echo "SQL Error: Could not prepare the query.";
        return [];
    }
}


if(!useGift()){
	echo 0;
	die();
}

if(!isset($_POST['target'])){
	echo 0;
	die();
}
$target = escape($_POST['target'], true);
if(mySelf($target)){
	$user = $data;
}
else {
	$user = userDetails($target);
}
if(empty($user)){
	echo 0;
	die();
}
if(!userShareGift($user)){
	echo 0;
	die();
}
?>
<div id="view_gift_box">
    <?php echo userGiftList($user); ?>
</div>
<div id="view_gift_template" class="hidden">
    <div class="modal_content">
        <div class="centered_element tpad25">
            <div class="bpad3">
                <img id="view_gift_img" class="gift_received" src=""/>
            </div>
            <div class="vpad15">
                <div id="view_gift_title" class="text_med bold">
                </div>
            </div>
        </div>
    </div>
    <div class="modal_control centered_element">
        <button class="reg_button ok_btn close_over"><?php echo $lang['close']; ?></button>
    </div>
</div>
