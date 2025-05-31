<?php

require __DIR__ . "./../config_session.php";

if (isset($_POST["more_chat"])) {
    echo chatMoreChat();
    exit;
}
if (isset($_POST["more_private"]) && isset($_POST["target"])) {
    echo privateMorePrivate();
    exit;
}

function chatMoreChat()
{
    global $mysqli;
    global $data;
    
    $last = trim($_POST["more_chat"]);
    $clogs = "";
    $count = 0;

    if (!canHistory()) {
        return boomCode(0, ["total" => 0, "clogs" => 0]);
    }

    // Prepare the SQL statement
    $stmt = $mysqli->prepare("
        SELECT log.*, boom_users.*
        FROM (
            SELECT * 
            FROM boom_chat 
            WHERE post_roomid = ? AND post_id < ? 
            ORDER BY post_id DESC 
            LIMIT 60
        ) AS log
        LEFT JOIN boom_users ON log.user_id = boom_users.user_id
        ORDER BY post_id ASC
    ");
    $stmt->bind_param('ii', $data["user_roomid"], $last);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        for ($ignore = getIgnore(); $chat = $result->fetch_assoc(); $count++) {
            $clogs .= createLog($data, $chat, $ignore);
        }
    } else {
        $clogs = 0;
    }

    $stmt->close();
    return boomCode(0, ["total" => $count, "clogs" => $clogs]);
}

function privateMorePrivate()
{
    global $mysqli;
    global $data;
    
    $last = trim($_POST["more_private"]);
    $priv = trim($_POST["target"]);
    $plogs = "";
    $count = 0;

    // Prepare the SQL statement
    $stmt = $mysqli->prepare("
        SELECT log.*, boom_users.*
        FROM (
            SELECT * 
            FROM boom_private 
            WHERE (hunter = ? AND target = ? AND id < ?) 
               OR (hunter = ? AND target = ? AND id < ?) 
            ORDER BY id DESC 
            LIMIT 30
        ) AS log
        LEFT JOIN boom_users ON log.hunter = boom_users.user_id
        ORDER BY time ASC
    ");
    $stmt->bind_param('iiiiii', $data["user_id"], $priv, $last, $priv, $data["user_id"], $last);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($private = $result->fetch_assoc()) {
            $plogs .= privateLog($private, $data["user_id"]);
            $count++;
        }
    } else {
        $plogs = 0;
    }

    $stmt->close();
    return boomCode(0, ["total" => $count, "clogs" => $plogs]);
}
?>
