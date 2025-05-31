<?php
/**
 * FuseChat
 *
 * @package FuseChat
 * @author www.nemra-1.com
 * @copyright 2020
 * @terms Unauthorized use of this script without a valid license is prohibited.
 * All content of FuseChat is the property of BoomCoding and cannot be used in another project.
 */

require_once('../config_session.php');

// Ensure the 'id' parameter is passed
if(!isset($_POST['id'])){
    echo 0;
    die();
}

// Sanitize the 'id' input
$id = escape($_POST['id']);

// Prepare the SQL query using prepared statements
$query = "
SELECT boom_post_like.*, boom_users.*  
FROM boom_post_like 
JOIN boom_users ON boom_users.user_id = boom_post_like.uid 
WHERE boom_post_like.like_post = ?
";

// Prepare the statement
$stmt = $mysqli->prepare($query);

// Bind the 'id' parameter as a string
$stmt->bind_param('s', $id);

// Execute the query
$stmt->execute();

// Get the result
$get_like = $stmt->get_result();

// Initialize variables for storing like counts and user lists
$like = '';
$like_count = 0;
$dislike = '';
$dislike_count = 0;
$love = '';
$love_count = 0;
$funny = '';
$funny_count = 0;

// Check if there are any results
if($get_like->num_rows > 0){
    while($likes = $get_like->fetch_assoc()){
        switch($likes['like_type']){
            case 1:
                $like .= boomTemplate('element/user', $likes);
                $like_count++;
                break;
            case 2:
                $dislike .= boomTemplate('element/user_lazy', $likes);
                $dislike_count++;
                break;
            case 3:
                $love .= boomTemplate('element/user_lazy', $likes);
                $love_count++;
                break;
            case 4:
                $funny .= boomTemplate('element/user_lazy', $likes);
                $funny_count++;
                break;
        }
    }

    // If no likes, display the "no data" message
    if($like == ''){
        $like = emptyZone($lang['no_data']);
    }
    if($dislike == ''){
        $dislike = emptyZone($lang['no_data']);
    }
    if($love == ''){
        $love = emptyZone($lang['no_data']);
    }
    if($funny == ''){
        $funny = emptyZone($lang['no_data']);
    }
} else {
    // Default values if there are no results
    $like = emptyZone($lang['no_data']);
    $like_count = 0;
    $dislike = emptyZone($lang['no_data']);
    $dislike_count = 0;
    $love = emptyZone($lang['no_data']);
    $love_count = 0;
    $funny = emptyZone($lang['no_data']);
    $funny_count = 0;
}

// Close the prepared statement
$stmt->close();
?>

<!-- HTML Structure for the Modal -->
<div class="modal_top">
    <div class="modal_top_empty bold"></div>
    <div class="modal_top_element close_modal">
        <i class="ri-close-circle-line i_btm"></i>
    </div>
</div>

<div class="modal_menu">
    <ul>
        <li class="modal_menu_item modal_selected" data="wlikes" data-z="like_it">
            <img class="wlike_icon" src="<?php echo $data['domain']; ?>/default_images/reaction/like.svg"/> 
            <span class="plike_text"><?php echo $like_count; ?></span>
        </li>
        <li class="modal_menu_item" data="wlikes" onclick="lazyBoom('dislike_it');" data-z="dislike_it">
            <img class="wlike_icon" src="<?php echo $data['domain']; ?>/default_images/reaction/dislike.svg"/> 
            <span class="plike_text"><?php echo $dislike_count; ?></span>
        </li>
        <li class="modal_menu_item" data="wlikes" onclick="lazyBoom('love_it');" data-z="love_it">
            <img class="wlike_icon" src="<?php echo $data['domain']; ?>/default_images/reaction/love.svg"/> 
            <span class="plike_text"><?php echo $love_count; ?></span>
        </li>
        <li class="modal_menu_item" data="wlikes" onclick="lazyBoom('funny_it');" data-z="funny_it">
            <img class="wlike_icon" src="<?php echo $data['domain']; ?>/default_images/reaction/funny.svg"/> 
            <span class="plike_text"><?php echo $funny_count; ?></span>
        </li>
    </ul>
</div>

<div id="wlikes">
    <div class="modal_zone box_height400 pad15" id="like_it">
        <?php echo $like; ?>
    </div>
    <div class="modal_zone hide_zone box_height400 pad15" id="dislike_it">
        <?php echo $dislike; ?>
    </div>
    <div class="modal_zone hide_zone box_height400 pad15" id="love_it">
        <?php echo $love; ?>
    </div>
    <div class="modal_zone hide_zone box_height400 pad15" id="funny_it">
        <?php echo $funny; ?>
    </div>
</div>
