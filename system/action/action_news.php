<?php

require __DIR__ . "./../config_session.php";

if (isset($_POST["add_news"]) && isset($_POST["post_file"])) {
    echo postsystemnews();
    exit;
}
if (isset($_POST["like_news"]) && isset($_POST["like_type"])) {
    echo newslike();
    exit;
}
if (isset($_POST["more_news"])) {
    echo morenews();
    exit;
}
if (isset($_POST["id"]) && isset($_POST["load_news_comment"])) {
    echo loadnewscomment();
    exit;
}
if (isset($_POST["content"]) && isset($_POST["reply_news"])) {
    echo newsreply();
    exit;
}
if (isset($_POST["current"]) && isset($_POST["id"]) && isset($_POST["load_news_reply"])) {
    echo morenewscomment();
    exit;
}
if (isset($_POST["delete_news_reply"])) {
    echo deletenewsreply();
    exit;
}
if (isset($_POST["remove_news"])) {
    echo deletenews();
    exit;
}
function newsReplyCount($id) {
    global $mysqli;
    // Ensure ID is a valid integer
    if (!is_numeric($id) || $id < 1) {
        return 0; // Invalid input, return 0
    }
    // SQL query with a placeholder
    $query = "SELECT COUNT(reply_id) FROM boom_news_reply WHERE parent_id = ?";
    // Prepare the statement
    if ($stmt = $mysqli->prepare($query)) {
        // Bind the integer parameter
        $stmt->bind_param("i", $id);
        // Execute the query
        $stmt->execute();
        // Get the result
        $stmt->bind_result($total);
        $stmt->fetch();  // Fetch the count value
        $stmt->close();  // Ensure the statement is closed
        return $total ?? 0; // Return count or 0 if null
    }
    // Log an error if preparation fails
    error_log("newsReplyCount SQL Error: " . $mysqli->error);
    return 0;
}
function moreNews(){
    global $mysqli, $data;
    // Validate and sanitize input
    if (!isset($_POST["more_news"]) || !is_numeric($_POST["more_news"]) || $_POST["more_news"] < 1) {
        return 0; // Invalid input
    }
    $news = (int) $_POST["more_news"]; // Ensure integer type
    $news_content = "";
    // Optimized query using JOINs instead of subqueries
    $query = "
        SELECT 
            boom_news.*, 
            boom_users.*, 
            COUNT(DISTINCT boom_news_reply.id) AS reply_count, 
            MAX(boom_news_like.like_type) AS liked
        FROM boom_news
        INNER JOIN boom_users ON boom_news.news_poster = boom_users.user_id
        LEFT JOIN boom_news_reply ON boom_news_reply.parent_id = boom_news.id
        LEFT JOIN boom_news_like ON boom_news_like.uid = ? AND boom_news_like.like_post = boom_news.id
        WHERE boom_news.id < ?
        GROUP BY boom_news.id
        ORDER BY boom_news.news_date DESC
        LIMIT 10
    ";
    // Prepare and execute statement
    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param("ii", $data["user_id"], $news);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($news_item = $result->fetch_assoc()) {
                $news_content .= boomTemplate("element/news", $news_item);
            }
        } else {
            $news_content .= 0; // No more news
        }
        $stmt->close(); // Always close statement
    } else {
        error_log("moreNews SQL Error: " . $mysqli->error);
        return 0; // Query failed
    }
    return $news_content;
}
function newsReply() {
    global $mysqli, $data, $cody;
    // Validate input
    if (!isset($_POST["content"], $_POST["reply_news"])) {
        return boomCode(0); // Invalid request
    }
    // Get user input
    $content = trim($_POST["content"]);
    $reply_to = (int) $_POST["reply_news"];
    // Check if user can reply
    if (!boomAllow($cody["can_reply_news"])) {
        return boomCode(0);
    }
    // Prevent empty content
    if (empty($content)) {
        return boomCode(0);
    }
    // Prevent flooding
    if (checkFlood()) {
        return boomCode(100); // Too many requests
    }
    // Check if user is muted
    if (muted() || isRoomMuted($data)) {
        return boomCode(0);
    }
    // Apply word filtering
    $filtered_content = wordFilter($content);
    // Check if the content is too long
    if (strlen($filtered_content) > 1000) {
        return boomCode(0);
    }
    // Verify that the news post exists
    $check_valid = $mysqli->prepare("SELECT id, news_poster FROM boom_news WHERE id = ?");
    $check_valid->bind_param("i", $reply_to);
    $check_valid->execute();
    $result = $check_valid->get_result();
    if ($result->num_rows < 1) {
        return boomCode(0);
    }
    $news = $result->fetch_assoc();
    $news_poster = $news["news_poster"];
    // Insert reply into database
    $insert_reply = $mysqli->prepare("
        INSERT INTO boom_news_reply (parent_id, reply_date, reply_user, reply_content, reply_uid) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $current_time = time();
    $insert_reply->bind_param("iiisi", $reply_to, $current_time, $data["user_id"], $filtered_content, $news_poster);
    $insert_reply->execute();
    if ($insert_reply->affected_rows < 1) {
        return boomCode(0);
    }
    $last_id = $mysqli->insert_id;
    // Fetch the inserted reply
    $get_back = $mysqli->prepare("
        SELECT boom_news_reply.*, boom_users.* 
        FROM boom_news_reply
        INNER JOIN boom_users ON boom_news_reply.reply_user = boom_users.user_id
        WHERE boom_news_reply.reply_id = ? 
        LIMIT 1
    ");
    $get_back->bind_param("i", $last_id);
    $get_back->execute();
    $result = $get_back->get_result();
    if ($result->num_rows < 1) {
        return boomCode(0);
    }
    $reply = $result->fetch_assoc();
    $log = boomTemplate("element/news_reply", $reply);
    // Get the total number of replies
    $total = newsReplyCount($reply_to);
    return boomCode(1, ["data" => $log, "total" => $total]);
}
function loadNewsComment() {
    global $mysqli, $data, $lang;
    // Validate and cast input
    if (!isset($_POST["id"]) || !is_numeric($_POST["id"])) {
        return boomCode(0); // Invalid input
    }
    $id = (int) $_POST["id"];
    $load_reply = "";
    $reply_count = 0;
    // Fetch total reply count & first 10 replies in a single query
    $query = "
        SELECT 
            (SELECT COUNT(reply_id) FROM boom_news_reply WHERE parent_id = ?) AS reply_count,
            r.*, 
            u.*
        FROM boom_news_reply AS r
        INNER JOIN boom_users AS u ON r.reply_user = u.user_id
        WHERE r.parent_id = ?
        ORDER BY r.reply_id DESC
        LIMIT 10
    ";
    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param("ii", $id, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($reply = $result->fetch_assoc()) {
            if (!isset($reply_count)) {
                $reply_count = $reply["reply_count"]; // Get total count from first row
            }
            $load_reply .= boomTemplate("element/news_reply", $reply);
        }
        $stmt->close();
    } else {
        error_log("Error preparing query: " . $mysqli->error);
        return boomCode(0);
    }
    // Check if there are more comments to load
    $more = ($reply_count > 10) ? "<a onclick=\"moreNewsComment(this, $id)\" class=\"theme_color text_small more_comment\">" . $lang["view_more_comment"] . "</a>" : 0;
    return boomCode(1, ["reply" => $load_reply, "more" => $more]);
}
function moreNewsComment() {
    global $mysqli, $data, $lang;
    // Validate and cast input to integers
    if (!isset($_POST["id"]) || !isset($_POST["current"]) || !is_numeric($_POST["id"]) || !is_numeric($_POST["current"])) {
        return boomCode(0); // Invalid input
    }
    $id = (int) $_POST["id"];
    $offset = (int) $_POST["current"];
    $reply_comment = "";
    // Prepare the query
    $query = "
        SELECT 
            boom_news_reply.*, 
            boom_users.* 
        FROM boom_news_reply
        INNER JOIN boom_users ON boom_news_reply.reply_user = boom_users.user_id
        WHERE boom_news_reply.parent_id = ? 
        AND boom_news_reply.reply_id < ?
        ORDER BY boom_news_reply.reply_id DESC
        LIMIT 20
    ";
    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param("ii", $id, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        // Check if we have any replies and format them
        while ($reply = $result->fetch_assoc()) {
            $reply_comment .= boomTemplate("element/news_reply", $reply);
        }
        $stmt->close();
    } else {
        error_log("Error preparing query: " . $mysqli->error);
        return boomCode(0);
    }
    // If no comments were loaded, return 0
    return (!empty($reply_comment)) ? $reply_comment : boomCode(0);
}

function deleteNewsReply() {
    global $mysqli, $data, $lang, $cody;
    // Validate and sanitize input
    if (!isset($_POST["delete_news_reply"]) || !is_numeric($_POST["delete_news_reply"])) {
        return boomCode(0); // Invalid request
    }
    $reply_id = (int) $_POST["delete_news_reply"];
    // Fetch only the parent_id and reply_user (avoid unnecessary user data)
    $query = "SELECT parent_id, reply_user FROM boom_news_reply WHERE reply_id = ?";
    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param("i", $reply_id);
        $stmt->execute();
        $stmt->bind_result($parent_id, $reply_user);
        $stmt->fetch();
        $stmt->close();
        // If no result, reply does not exist
        if (!$parent_id) {
            return boomCode(0);
        }
        // Check if the user has permission to delete
        if (!canDeleteNewsReply(["reply_user" => $reply_user])) {
            return boomCode(0); // No permission
        }
        // Delete the reply
        $delete_query = "DELETE FROM boom_news_reply WHERE reply_id = ?";
        if ($delete_stmt = $mysqli->prepare($delete_query)) {
            $delete_stmt->bind_param("i", $reply_id);
            $delete_stmt->execute();
            $delete_stmt->close();
        } else {
            error_log("Error preparing DELETE statement: " . $mysqli->error);
            return boomCode(0);
        }
        // Get the updated reply count
        $total = newsReplyCount($parent_id);

        // Return success with updated reply count
        return boomCode(1, ["news" => $parent_id, "reply" => $reply_id, "total" => $total]);
    }
    // Log error if statement preparation fails
    error_log("Error preparing SELECT statement: " . $mysqli->error);
    return boomCode(0);
}
function postSystemNews() {
    global $mysqli, $data, $lang, $cody;
    // Sanitize and trim news content
    $news = trimContent(clearBreak($_POST["add_news"] ?? ""));
    $post_file = $_POST["post_file"] ?? "";
    $news_file = "";
    $file_ok = 0;
    $current_time = time();
    // Check if the user is muted or lacks permission
    if (muted() || !canPostNews()) {
        return 0;
    }
    // Ensure there is content (either text or file)
    if (empty($news) && empty($post_file)) {
        return 0;
    }
    // Handle file attachment validation
    if (!empty($post_file)) {
        $get_file_stmt = $mysqli->prepare("
            SELECT file_name FROM boom_upload 
            WHERE file_key = ? AND file_user = ? AND file_complete = 0
        ");
        $get_file_stmt->bind_param("si", $post_file, $data["user_id"]);
        $get_file_stmt->execute();
        $get_file_stmt->store_result();

        if ($get_file_stmt->num_rows > 0) {
            $get_file_stmt->bind_result($file_name);
            $get_file_stmt->fetch();
            $news_file = "/upload/news/" . $file_name;
            $file_ok = 1;
        } else if (empty($news)) {
            // If no file found and no text content, return 0
            $get_file_stmt->close();
            return 0;
        }
        $get_file_stmt->close();
    }
    // Update the user's last news post timestamp
    $update_user_stmt = $mysqli->prepare("UPDATE boom_users SET user_news = ? WHERE user_id = ?");
    $update_user_stmt->bind_param("ii", $current_time, $data["user_id"]);
    if (!$update_user_stmt->execute()) {
        return 0; // Fail if update fails
    }
    $update_user_stmt->close();
    // Insert the news into the database
    $insert_news_stmt = $mysqli->prepare("
        INSERT INTO boom_news (news_poster, news_message, news_file, news_date) 
        VALUES (?, ?, ?, ?)
    ");
    $insert_news_stmt->bind_param("issi", $data["user_id"], $news, $news_file, $current_time);
    if (!$insert_news_stmt->execute()) {
        return 0; // Fail if insertion fails
    }
    $news_id = $mysqli->insert_id;
    $insert_news_stmt->close();
    // Update the file completion status if a file was attached
    if ($file_ok == 1) {
        $update_file_stmt = $mysqli->prepare("
            UPDATE boom_upload 
            SET file_complete = 1, relative_post = ? 
            WHERE file_key = ? AND file_user = ?
        ");
        $update_file_stmt->bind_param("isi", $news_id, $post_file, $data["user_id"]);
        $update_file_stmt->execute();
        $update_file_stmt->close();
    }
    // Notify all users
    updateAllNotify();
    // Return the news post display
    return showNews($news_id);
}

function deleteNews() {
    global $mysqli, $data, $lang, $cody;
    $news_id = (int) $_POST["remove_news"];  // Explicitly cast to integer to avoid SQL injection risk
    // Validate that the news exists and the user has permission to delete it
    $valid_stmt = $mysqli->prepare("
        SELECT boom_news.*, boom_users.* 
        FROM boom_news 
        INNER JOIN boom_users ON boom_news.news_poster = boom_users.user_id 
        WHERE boom_news.id = ?
    ");
    $valid_stmt->bind_param("i", $news_id);
    $valid_stmt->execute();
    $valid_result = $valid_stmt->get_result();
    if ($valid_result->num_rows === 0) {
        // News not found
        return "Error: News not found.";
    }
    $tnews = $valid_result->fetch_assoc();
    // Check if the user has permission to delete the news
    if (!canDeleteNews($tnews)) {
        return "Error: You do not have permission to delete this news.";
    }
    // Start transaction to delete all related data
    $mysqli->begin_transaction();
    try {
        // Delete related news
        $delete_news_stmt = $mysqli->prepare("DELETE FROM boom_news WHERE id = ?");
        $delete_news_stmt->bind_param("i", $news_id);
        if (!$delete_news_stmt->execute()) {
            throw new Exception("Failed to delete news.");
        }
        // Delete related replies
        $delete_replies_stmt = $mysqli->prepare("DELETE FROM boom_news_reply WHERE parent_id = ?");
        $delete_replies_stmt->bind_param("i", $news_id);
        if (!$delete_replies_stmt->execute()) {
            throw new Exception("Failed to delete replies.");
        }
        // Delete related likes
        $delete_likes_stmt = $mysqli->prepare("DELETE FROM boom_news_like WHERE like_post = ?");
        $delete_likes_stmt->bind_param("i", $news_id);
        if (!$delete_likes_stmt->execute()) {
            throw new Exception("Failed to delete likes.");
        }
        // Remove related files if applicable
        removeRelatedFile($news_id, "news");
        // Commit transaction if everything is successful
        $mysqli->commit();
        // Notify all users
        updateAllNotify();
        // Log the deletion if the user isn't deleting their own news
        if (!mySelf($tnews["user_id"])) {
            boomConsole("news_delete", ["hunter" => $data["user_id"], "target" => $tnews["user_id"]]);
        }
        // Return a success message with the news ID
        return "News deleted successfully: boom_news" . $news_id;
    } catch (Exception $e) {
        // Rollback the transaction if anything fails
        $mysqli->rollback();
        return "Error: " . $e->getMessage();
    }
}

function newsLike() {
    global $mysqli, $data, $lang, $cody;
    if (!boomAllow(1)) {
        return "";
    }
    // Sanitize and validate the inputs
    $id = (int) $_POST["like_news"];  // Ensure it's an integer
    $type = (int) $_POST["like_type"]; // Ensure it's an integer
    // Check if the like type is valid (assuming 1 and 2 are the valid types, adjust as needed)
    if (!in_array($type, [1, 2])) {
        return boomCode(0);  // Invalid like type
    }
    // Use a prepared statement to check the current like status
    $like_stmt = $mysqli->prepare("
        SELECT news_poster, 
               (SELECT like_type 
                FROM boom_news_like 
                WHERE like_post = ? AND uid = ?) AS type 
        FROM boom_news 
        WHERE id = ?
    ");
    $like_stmt->bind_param("iii", $id, $data["user_id"], $id);
    $like_stmt->execute();
    $like_result = $like_stmt->get_result();
    if ($like_result->num_rows > 0) {
        $like = $like_result->fetch_assoc();
        // Delete the existing like if any
        $delete_like_stmt = $mysqli->prepare("DELETE FROM boom_news_like WHERE like_post = ? AND uid = ?");
        $delete_like_stmt->bind_param("ii", $id, $data["user_id"]);
        $delete_like_stmt->execute();
        // If the user is trying to like with the same type, cancel the action
        if ($like["type"] == $type) {
            return boomCode(1, ["data" => getLikes($id, 0, "news")]);
        }
        // Insert the new like
        $insert_like_stmt = $mysqli->prepare("
            INSERT INTO boom_news_like (uid, liked_uid, like_type, like_post, like_date) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $current_time = time();
        $insert_like_stmt->bind_param("iiisi", $data["user_id"], $like["news_poster"], $type, $id, $current_time);
        $insert_like_stmt->execute();
        return boomCode(1, ["data" => getLikes($id, $type, "news")]);
    }
    // Return failure if the news doesn't exist or cannot be liked
    return boomCode(0);
}


?>