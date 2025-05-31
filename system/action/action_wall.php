<?php

require __DIR__ . "./../config_session.php";

if (!useWall()) {
    exit;
}
if (isset($_POST["offset"]) && isset($_POST["load_more"]) && isset($_POST["load_more_wall"])) {
    echo usermorewall();
    exit;
}
if (isset($_POST["post_to_wall"]) && isset($_POST["post_file"])) {
    echo userpostwall();
    exit;
}
if (isset($_POST["like"]) && isset($_POST["like_type"])) {
    echo userpostlike();
    exit;
}
if (isset($_POST["delete_reply"])) {
    echo deletereply();
    exit;
}
if (isset($_POST["content"]) && isset($_POST["reply_to_wall"])) {
    echo userwallreply();
    exit;
}
if (isset($_POST["id"]) && isset($_POST["load_comment"])) {
    echo userloadcomment();
    exit;
}
if (isset($_POST["current"]) && isset($_POST["id"]) && isset($_POST["load_reply"])) {
    echo userloadreply();
    exit;
}
if (isset($_POST["delete_wall_post"])) {
    echo userdeletewall();
    exit;
}
if (isset($_POST["view_likes"])) {
    echo viewWallLikes();
    exit;
}
function wallReplyCount($id) {
    global $mysqli,$data,$cody;

    $query = "SELECT COUNT(reply_id) AS total FROM boom_post_reply WHERE parent_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id); // "i" denotes an integer
    $stmt->execute();
    $result = $stmt->get_result();
    $t = $result->fetch_assoc();
    
    return $t["total"] ?? 0; // Return 0 if no result is found
}

function userMoreWall() {
    global $mysqli, $data;
    $of = isset($_POST["offset"]) ? intval($_POST["offset"]) : 0; // Ensure offset is an integer
    // Get friend list securely
    $friend_array = [$data["user_id"]];
    $stmt = $mysqli->prepare("SELECT target FROM boom_friends WHERE hunter = ? AND fstatus = 3");
    $stmt->bind_param("i", $data["user_id"]);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $friend_array[] = $row["target"];
    }
    // Avoid direct injection in the IN clause
    $placeholders = implode(",", array_fill(0, count($friend_array), "?"));
    $types = str_repeat("i", count($friend_array)); // All are integers
    $query = "
        SELECT boom_post.*, boom_users.*,
        (SELECT COUNT(parent_id) FROM boom_post_reply WHERE parent_id = boom_post.post_id) as reply_count,
        (SELECT like_type FROM boom_post_like WHERE uid = ? AND like_post = boom_post.post_id) as liked
        FROM boom_post
        INNER JOIN boom_users ON boom_post.post_user = boom_users.user_id
        WHERE boom_post.post_user IN ($placeholders)
        ORDER BY boom_post.post_actual DESC
        LIMIT 10 OFFSET ?
    ";
    // Prepare the statement
    $stmt = $mysqli->prepare($query);
    // Bind parameters
    $params = array_merge([$types . "i"], $friend_array, [$of]); // Add offset as last param
    $stmt->bind_param(...$params);
    // Execute and fetch
    $stmt->execute();
    $result = $stmt->get_result();
    $wall_content = "";
    while ($wall = $result->fetch_assoc()) {
        $wall_content .= boomTemplate("element/wall_post", $wall);
    }

    return !empty($wall_content) ? $wall_content : "0";
}

function userPostWall() {
    global $mysqli, $data, $lang;
    if (!boomAllow(1)) { return ""; }
    if (checkFlood()) { return ""; }
    $content = sanitizeChatInput($_POST["post_to_wall"]);
    $post_file = escape($_POST["post_file"]);
    $file_content = "";
    $file_ok = 0;
    if (muted()) { return 0; }
    if (empty($content) && empty($post_file)) { return 0; }
    if ($post_file != "") {
        $stmt = $mysqli->prepare("SELECT * FROM boom_upload WHERE file_key = ? AND file_user = ? AND file_complete = '0'");
        $stmt->bind_param('si', $post_file, $data["user_id"]);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $file = $result->fetch_assoc();
            $file_content = "/upload/wall/" . $file["file_name"];
            $file_ok = 1;
        } else {
            if ($content == "") { return 0; }
        }
    }
    if (strlen($content) < 2000) {
        $stmt = $mysqli->prepare("INSERT INTO boom_post (post_date, post_user, post_content, post_file, post_actual) VALUES (?, ?, ?, ?, ?)");
        $post_time = time();
        $stmt->bind_param('iisss', $post_time, $data["user_id"], $content, $file_content, $post_time);
        $stmt->execute();
        $postid = $stmt->insert_id;
        if ($file_ok == 1) {
            $stmt = $mysqli->prepare("UPDATE boom_upload SET file_complete = '1', relative_post = ? WHERE file_key = ? AND file_user = ?");
            $stmt->bind_param('isi', $postid, $post_file, $data["user_id"]);
            $stmt->execute();
        }
        $list = getFriendList($data["user_id"]);
        boomListNotify($list, "add_post", ["hunter" => $data["user_id"], "source" => "post", "sourceid" => $postid]);
        if (useLevel()) {
            $stmt = $mysqli->prepare("UPDATE boom_users SET user_exp = user_exp + 1 WHERE user_id = ?");
            $stmt->bind_param('i', $data['user_id']);
            $stmt->execute();
            userExpLevel("exp_post");
        }
        return showPost($postid);
    }
    return 2;
}

function userPostLike() {
    global $mysqli, $data;
    // Validate and sanitize input
    if (!isset($_POST["like"], $_POST["like_type"])) {
        return boomCode(0);
    }
    $id = intval($_POST["like"]); // Ensure it's an integer
    $type = intval($_POST["like_type"]); // Ensure it's an integer

    if (!boomAllow(1) || !canPostAction($id)) {
        return boomCode(0);
    }
    // Securely fetch like data
    $stmt = $mysqli->prepare("SELECT post_user, (SELECT like_type FROM boom_post_like WHERE like_post = ? AND uid = ?) AS type FROM boom_post WHERE post_id = ?");
    $stmt->bind_param("iii", $id, $data["user_id"], $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $like = $result->fetch_assoc();
        $stmt->close();
        // Delete existing like
        $stmt = $mysqli->prepare("DELETE FROM boom_post_like WHERE like_post = ? AND uid = ?");
        $stmt->bind_param("ii", $id, $data["user_id"]);
        $stmt->execute();
        $stmt->close();
        // Delete related notifications
        $stmt = $mysqli->prepare("DELETE FROM boom_notification WHERE notifier = ? AND notify_id = ? AND notify_type = 'like'");
        $stmt->bind_param("ii", $data["user_id"], $id);
        $stmt->execute();
        $stmt->close();
        // If user had the same like type before, just remove it
        if ($like["type"] == $type) {
            updateNotify($like["post_user"]);
            return boomCode(1, ["data" => getLikes($id, 0, "wall")]);
        }
        // Insert new like
        $stmt = $mysqli->prepare("INSERT INTO boom_post_like (uid, liked_uid, like_type, like_post, like_date) VALUES (?, ?, ?, ?, ?)");
        $time_now = time();
        $stmt->bind_param("iiiii", $data["user_id"], $like["post_user"], $type, $id, $time_now);
        $stmt->execute();
        $stmt->close();
        // Notify user if the like is for someone else
        if (!mySelf($like["post_user"])) {
            boomNotify("like", ["hunter" => $data["user_id"], "target" => $like["post_user"], "source" => "post", "sourceid" => $id, "custom" => $type]);
        }
        return boomCode(1, ["data" => getLikes($id, $type, "wall")]);
    }

    return boomCode(0);
}
function userWallReply(){
    global $mysqli,$data,$lang;
    $content=sanitizeChatInput($_POST["content"]);
    $reply_to=intval($_POST["reply_to_wall"]);
    if(checkFlood()){return "";}
    if(!boomAllow(1)){return "";}
    if(muted()){return boomCode(0);}
    $content=wordFilter($content);
    if(strlen($content)>1000){return boomCode(0);}
    if(!canPostAction($reply_to)){return boomCode(0);}
    $stmt=$mysqli->prepare("SELECT post_id,post_user FROM boom_post WHERE post_id=?");
    $stmt->bind_param("i",$reply_to);
    $stmt->execute();
    $result=$stmt->get_result();
    if($result->num_rows<1){return boomCode(0);}
    $get_id=$result->fetch_assoc();
    $id=$get_id["post_id"];
    $who=$get_id["post_user"];
    $stmt->close();
    $current_time = time(); // Assign time to variable
    $stmt=$mysqli->prepare("INSERT INTO boom_post_reply (parent_id,reply_uid,reply_date,reply_user,reply_content) VALUES (?,?,?,?,?)");
    $stmt->bind_param("iiiss",$id,$who,$current_time,$data["user_id"],$content);
    $stmt->execute();
    $last_id=$stmt->insert_id;
    $stmt->close();
    $stmt=$mysqli->prepare("UPDATE boom_post SET post_actual=? WHERE post_id=?");
    $stmt->bind_param("ii",$current_time,$id);
    $stmt->execute();
    $stmt->close();
    if(!mySelf($who)){
        boomNotify("reply",["hunter"=>$data["user_id"],"target"=>$who,"source"=>"post","sourceid"=>$reply_to,"custom"=>$last_id]);
    }
    $stmt=$mysqli->prepare("SELECT boom_post_reply.*,boom_users.* FROM boom_post_reply JOIN boom_users ON boom_post_reply.reply_user=boom_users.user_id WHERE boom_post_reply.parent_id=? AND boom_post_reply.reply_user=? ORDER BY reply_id DESC LIMIT 1");
    $stmt->bind_param("ii",$reply_to,$data["user_id"]);
    $stmt->execute();
    $result=$stmt->get_result();
    if($result->num_rows<1){return boomCode(0);}
    $reply=$result->fetch_assoc();
    $stmt->close();
    $log=boomTemplate("element/reply",$reply);
    $total=wallReplyCount($reply_to);
    return boomCode(1,["data"=>$log,"total"=>$total]);
}


function userLoadComment(){
    global $mysqli,$data,$lang;
    $id = intval($_POST["id"]);
    if (!boomAllow(1)) {return "";}
    if (!canPostAction($id)) {return boomCode(0, ["reply" => 0, "more" => ""]);} 
    $load_reply = ""; 
    $reply_count = 0;
    $stmt = $mysqli->prepare("SELECT boom_post_reply.*, boom_users.*, (SELECT count(reply_id) FROM boom_post_reply WHERE parent_id = ?) as reply_count FROM boom_post_reply JOIN boom_users ON boom_post_reply.reply_user = boom_users.user_id WHERE boom_post_reply.parent_id = ? ORDER BY boom_post_reply.reply_id DESC LIMIT 10");
    $stmt->bind_param("ii", $id, $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($reply = $result->fetch_assoc()) {
            $load_reply .= boomTemplate("element/reply", $reply);
            $reply_count = $reply["reply_count"];
        }
    }
    $stmt->close();
    if ($reply_count > 10) {
        $more = "<a onclick=\"moreComment(this, {$id})\" class=\"theme_color text_small more_comment\">" . $lang["view_more_comment"] . "</a>";
    } else {
        $more = 0;
    }
    return boomCode(1, ["reply" => $load_reply, "more" => $more]);
}


function userLoadReply(){
    global $mysqli,$data,$lang;
    $id = escape($_POST["id"]);
    $offset = escape($_POST["current"]);
    if (!boomAllow(1)) {return "";}
    if (!canPostAction($id)) {return 99;}
    $reply_comment = "";
    $stmt = $mysqli->prepare("SELECT boom_post_reply.*, boom_users.* FROM boom_post_reply JOIN boom_users ON boom_post_reply.reply_user = boom_users.user_id WHERE boom_post_reply.parent_id = ? AND boom_post_reply.reply_id < ? ORDER BY boom_post_reply.reply_id DESC LIMIT 20");
    $stmt->bind_param("ii", $id, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($reply = $result->fetch_assoc()) {
            $reply_comment .= boomTemplate("element/reply", $reply);
        }
    } else {
        $reply_comment = 0;
    }
    $stmt->close();
    return $reply_comment;
}


function deleteReply(){
    global $mysqli,$data,$lang;
    $reply_id = escape($_POST["delete_reply"]);
    // Use prepared statement for better security
    $stmt = $mysqli->prepare("SELECT boom_post_reply.*, boom_users.* FROM boom_post_reply JOIN boom_users ON boom_post_reply.reply_user = boom_users.user_id WHERE boom_post_reply.reply_id = ?");
    $stmt->bind_param("i", $reply_id);  // bind the reply_id as integer
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 1) {
        $reply = $result->fetch_assoc();
        if (canDeleteWallReply($reply)) {
            // Perform delete queries
            $stmt = $mysqli->prepare("DELETE FROM boom_post_reply WHERE reply_id = ?");
            $stmt->bind_param("i", $reply_id);
            $stmt->execute();
            $stmt = $mysqli->prepare("DELETE FROM boom_notification WHERE notifier = ? AND notify_id = ? AND notify_custom = ?");
            $stmt->bind_param("iii", $reply["reply_user"], $reply["parent_id"], $reply_id);
            $stmt->execute();
            updateNotify($reply["reply_uid"]);
            if (!mySelf($reply["user_id"])) {
                boomConsole("cwall_delete", ["hunter" => $data["user_id"], "target" => $reply["user_id"]]);
            }
            $total = wallreplycount($reply["parent_id"]);
            return boomCode(1, ["wall" => $reply["parent_id"], "reply" => $reply_id, "total" => $total]);
        }
        return boomCode(0);
    }
    return boomCode(0);
}




function userDeleteWall(){
    global $mysqli,$data,$lang;
    $post = escape($_POST["delete_wall_post"]);
    // Prepared statement to fetch post details
    $stmt = $mysqli->prepare("SELECT boom_post.*, boom_users.* FROM boom_post JOIN boom_users ON boom_users.user_id = boom_post.post_user WHERE boom_post.post_id = ?");
    $stmt->bind_param("i", $post);  // Bind the post ID as integer
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $wall = $result->fetch_assoc();
        if (!canDeleteWall($wall)) {
            return 1;
        }
        // Delete associated data using prepared statements
        $stmt = $mysqli->prepare("DELETE FROM boom_post WHERE post_id = ?");
        $stmt->bind_param("i", $post);
        $stmt->execute();
        $stmt = $mysqli->prepare("DELETE FROM boom_post_reply WHERE parent_id = ?");
        $stmt->bind_param("i", $post);
        $stmt->execute();
        $stmt = $mysqli->prepare("DELETE FROM boom_notification WHERE notify_id = ? AND notify_source = 'post'");
        $stmt->bind_param("i", $post);
        $stmt->execute();
        $stmt = $mysqli->prepare("DELETE FROM boom_post_like WHERE like_post = ?");
        $stmt->bind_param("i", $post);
        $stmt->execute();
        $stmt = $mysqli->prepare("DELETE FROM boom_report WHERE report_post = ? AND report_type = 2");
        $stmt->bind_param("i", $post);
        $stmt->execute();
        // Check if any rows were affected (i.e., if the post was deleted)
        if ($stmt->affected_rows > 0) {
            updateStaffNotify();
        }
        removeRelatedFile($post, "wall");
        // Get friend list and notify
        $list = getFriendList($wall["user_id"], 1);
        updateListNotify($list);
        if (!mySelf($wall["user_id"])) {
            boomConsole("wall_delete", ["hunter" => $data["user_id"], "target" => $wall["user_id"]]);
        }
        return "boom_post" . $post;
    }
    
    return 1;
}


?>