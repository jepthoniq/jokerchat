<?php


require __DIR__ . "./../config_session.php";

if (isset($_POST["delete_player"])) {
    echo deleteplayer();
    exit;
}
if (isset($_POST["player_url"]) && isset($_POST["player_alias"])) {
    echo staffaddplayer();
    exit;
}
if (isset($_POST["new_stream_url"]) && isset($_POST["new_stream_alias"]) && isset($_POST["player_id"])) {
    echo staffeditstream();
    exit;
}

function deletePlayer() {
    global $mysqli, $data;
    // Escape and validate the player ID
    $delplay = (int) $_POST["delete_player"]; // Ensure it's an integer
    if (!boomAllow(90)) {
        return 0; // No permission
    }
    // Prepare the queries to update and delete player data
    $mysqli->begin_transaction(); // Begin transaction to ensure consistency
    try {
        // Update the room to remove the player
        $update_room_stmt = $mysqli->prepare("UPDATE boom_rooms SET room_player_id = 0 WHERE room_player_id = ?");
        $update_room_stmt->bind_param("i", $delplay);
        $update_room_stmt->execute();
        // Delete the player from the radio stream table
        $delete_radio_stmt = $mysqli->prepare("DELETE FROM boom_radio_stream WHERE id = ?");
        $delete_radio_stmt->bind_param("i", $delplay);
        $delete_radio_stmt->execute();
        // Check if the deleted player is the current player
        if ($delplay == $data["player_id"]) {
            $update_setting_stmt = $mysqli->prepare("UPDATE boom_setting SET player_id = 0 WHERE id = 1");
            $update_setting_stmt->execute();
            $mysqli->commit(); // Commit the transaction if successful
            return 2; // Indicate that the current player was deleted
        }
        $mysqli->commit(); // Commit the transaction
        return 1; // Indicate successful deletion
    } catch (Exception $e) {
        $mysqli->rollback(); // Rollback in case of error
        return 0; // Return failure
    }
}


function staffAddPlayer() {
     global $mysqli, $data;
    // Escape the input to prevent SQL injection
    $player_url = escape($_POST["player_url"]);
    $player_alias = escape($_POST["player_alias"]);
    // Check if the user has permission
    if (!boomAllow(90)) {
        return 0; // No permission
    }
    // Check if both player URL and alias are provided
    if ($player_url != "" && $player_alias != "") {
        // Prepare the query to count the number of players
        $count_player_stmt = $mysqli->prepare("SELECT COUNT(id) AS playcount FROM boom_radio_stream WHERE id > 0");
        $count_player_stmt->execute();
        $count_player_result = $count_player_stmt->get_result();
        $playcount = $count_player_result->fetch_assoc()["playcount"];
        // Insert the new player
        $insert_stmt = $mysqli->prepare("INSERT INTO boom_radio_stream (stream_url, stream_alias) VALUES (?, ?)");
        $insert_stmt->bind_param("ss", $player_url, $player_alias);
        $insert_stmt->execute();
        // If it's the first player, update the player ID in settings
        if ($playcount < 1) {
            $last_id = $mysqli->insert_id;
            $update_setting_stmt = $mysqli->prepare("UPDATE boom_setting SET player_id = ? WHERE id = 1");
            $update_setting_stmt->bind_param("i", $last_id);
            $update_setting_stmt->execute();
        }
        return 1; // Player added successfully
    }
    return 2; // Missing player URL or alias
}


function staffEditStream() {
    global $mysqli, $data;
    // Escape the input to prevent SQL injection
    $id = escape($_POST["player_id"]);
    $alias = escape($_POST["new_stream_alias"]);
    $url = escape($_POST["new_stream_url"]);
    // Check if the user has permission
    if (!boomAllow(90)) {
        return 0; // No permission
    }
    // Check if both alias and URL are provided
    if (!empty($alias) && !empty($url)) {
        // Prepare the update statement
        $update_stmt = $mysqli->prepare("UPDATE boom_radio_stream SET stream_url = ?, stream_alias = ? WHERE id = ?");
        $update_stmt->bind_param("ssi", $url, $alias, $id);
        // Execute the update query and check for success
        if ($update_stmt->execute()) {
            return 1; // Stream updated successfully
        } else {
            return 0; // Query failed
        }
    }
    return 0; // Missing alias or URL
}


?>