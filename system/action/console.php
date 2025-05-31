<?php


//require __DIR__ . "./../config_session.php";
if ($f == 'console') {
	if (isset($_POST["run_console"])) {
		$console = escape($_POST["run_console"]);
		echo boomRunConsole($console);
	} else {
		fu_json_results(0);
		exit;
	}
		
}	


function boomRunConsole($console){
global $mysqli, $data, $cody, $lang;
    $command = explode(" ", trim($console));
    // /removetheme command
    if ($command[0] == "/removetheme" && boomAllow(100)) {
        $theme = trimCommand($console, "/removetheme");
        if ($theme == $data["default_theme"]) {
            return 3;
        }
        // Prepared statement for SQL injection protection
        $stmt = $mysqli->prepare("UPDATE boom_users SET user_theme = 'system' WHERE user_theme = ?");
        $stmt->bind_param("s", $theme);
        $stmt->execute();
        $stmt->close();
        return 1;
    }

    // /removelanguage command
    if ($command[0] == "/removelanguage" && boomAllow(100)) {
        $language = trimCommand($console, "/removelanguage");
        if ($language == $data["language"]) {
            return 3;
        }
        // Prepared statement for SQL injection protection
        $stmt = $mysqli->prepare("UPDATE boom_users SET user_language = ? WHERE user_language = ?");
        $stmt->bind_param("ss", $data["language"], $language);
        $stmt->execute();
        $stmt->close();
        return 1;
    }

    // /clearwall command
    if ($command[0] == "/clearwall" && boomAllow(100)) {
        $mysqli->query("TRUNCATE TABLE boom_post");
        $mysqli->query("TRUNCATE TABLE boom_post_reply");
        $mysqli->query("TRUNCATE TABLE boom_post_like");
        $mysqli->query("DELETE FROM boom_notification WHERE notify_source = 'post'");
        return 1;
    }
			
    // /clearprivate command
    if ($command[0] == "/clearprivate" && boomAllow(100)) {
        $mysqli->query("DELETE FROM boom_private WHERE id > 0");
        return 1;
    }

    // /clearnotification command
    if ($command[0] == "/clearnotification" && boomAllow(100)) {
        $mysqli->query("TRUNCATE TABLE boom_notification");
        return 1;
    }
    // /resetgeo command
    if ($command[0] == "/resetgeo" && boomAllow(100)) {
        $mysqli->query("UPDATE boom_users SET country = '' WHERE user_id > 0");
        $mysqli->query("UPDATE boom_users SET country = 'ZZ' WHERE user_bot > 0");
        return 1;
    }
    // /resetcover command
    if ($command[0] == "/resetcover" && boomAllow(100)) {
        $mysqli->query("UPDATE boom_users SET user_cover = '' WHERE user_id > 0");
        return 1;
    }
    // /clearchat command
    if ($command[0] == "/clearchat" && boomAllow(90)) {
        $mysqli->query("DELETE FROM boom_chat WHERE post_id > 0");
        return 1;
    }
    // /clearreport command
    if ($command[0] == "/clearreport" && boomAllow(90)) {
        $mysqli->query("TRUNCATE TABLE boom_report");
        return 1;
    }
    // /clearmail command
    if ($command[0] == "/clearmail" && boomAllow(100)) {
        $mysqli->query("TRUNCATE TABLE boom_mail");
        return 1;
    }
    // /resetsystembot command
    if ($command[0] == "/resetsystembot" && boomAllow(100)) {
        if (isset($data["system_id"])) {
            $user = userDetails($data["system_id"]);
        } else {
            $user = userDetails(0);
        }
        clearUserData($user);
        sleep(1);
        $stmt = $mysqli->prepare("INSERT INTO `boom_users` (user_name, user_email, user_ip, user_join, user_language, user_password, user_rank, user_tumb, verified, user_bot) VALUES(?, '', '0.0.0.0', ?, 'English', ?, '69', 'default_system.png', '1', '69')");
        $new_pass = randomPass();
        $stmt->bind_param("ssi", 'System', time(), $new_pass);
        $stmt->execute();
        $last_id = $mysqli->insert_id;
        $mysqli->query("UPDATE boom_setting SET system_id = '" . $last_id . "'");
        $stmt->close();
        return 1;
    }
    // /clearnews command
    if ($command[0] == "/clearnews" && boomAllow(100)) {
        $mysqli->query("TRUNCATE TABLE boom_news");
        $mysqli->query("TRUNCATE TABLE boom_news_reply");
        $mysqli->query("TRUNCATE TABLE boom_news_like");
        updateAllNotify();
        return 1;
    }

    // /resetkeys command
    if ($command[0] == "/resetkeys" && boomAllow(100)) {
        $mysqli->query("UPDATE boom_setting SET dat = '' WHERE id > 0");
        $mysqli->query("UPDATE boom_addons SET addons_key = '' WHERE addons_id > 0");
        return 6;
    }
    // /clearcache command
    if ($command[0] == "/clearcache" && boomAllow(100)) {
        boomCacheUpdate();
        return 1;
    }

    // /makefullowner command
    if($command[0] == "/makefullowner" && boomAllow(100)) {
        $t = trimCommand($console, "/makefullowner");
        $target = nameDetails($t);
        if (empty($target)) {
            return 4;
        }
        if (!mySelf($target["user_id"]) && !isOwner($target)) {
            // Prepared statement to prevent SQL injection
            $stmt = $mysqli->prepare("UPDATE boom_users SET user_rank = 100 WHERE user_name = ?");
            $stmt->bind_param("s", $target["user_name"]);
            $stmt->execute();
            $stmt->close();
            return 1;
        }
        return 5;
    }
    // /resetpassword command
    if ($command[0] == "/resetpassword" && boomAllow(100)) {
        $t = trimCommand($console, "/resetpassword");
        $new_pass = encrypt($t);
        $stmt = $mysqli->prepare("UPDATE boom_users SET user_password = ? WHERE user_id = ?");
        $stmt->bind_param("si", $new_pass, $data["user_id"]);
        $stmt->execute();
        setBoomCookie($data["user_id"], $new_pass);
        $stmt->close();
        return 1;
    }
    // /fixmain command
    if ($command[0] == "/fixmain" && boomAllow(100)) {
        $check_main = $mysqli->query("SELECT * FROM boom_rooms WHERE room_id = 1");
        if ($check_main->num_rows < 1) {
            $stmt = $mysqli->prepare("INSERT INTO boom_rooms (room_id, room_name, access, room_system, room_action, room_creator) VALUES (1, 'Main room', 0, 1, ?, ?)");
            $stmt->bind_param("si", time(), $data["user_id"]);
            $stmt->execute();
            $stmt->close();
        }
        return 1;
    }
    if ($command[0] == "/resetchat" && boomAllow(100)) {
                $mysqli->query("TRUNCATE TABLE boom_chat");
                $mysqli->query("TRUNCATE TABLE boom_private");
                return 1;
     }
	if ($command[0] == "/makevisible" && boomAllow(100)) {
				// Use a prepared statement to prevent SQL injection
				$stmt = $mysqli->prepare("UPDATE boom_users SET user_status = 1 WHERE user_status = 99 AND user_id != ? AND user_rank < 100");
				$stmt->bind_param("i", $data["user_id"]);
				$stmt->execute();
				$stmt->close();
				return 1;
	}

    // /resetsystem command
    if ($command[0] == "/resetsystem" && boomAllow(100)) {
        $mysqli->query("TRUNCATE TABLE boom_chat");
        $mysqli->query("TRUNCATE TABLE boom_private");
        $mysqli->query("TRUNCATE TABLE boom_notification");
        $mysqli->query("TRUNCATE TABLE boom_post");
        $mysqli->query("TRUNCATE TABLE boom_post_reply");
        $mysqli->query("TRUNCATE TABLE boom_post_like");
        return 1;
    }

    // /banip command
    if ($command[0] == "/banip" && boomAllow(90)) {
        $ip = $command[1];
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            $stmt = $mysqli->prepare("INSERT INTO boom_banned (ip) VALUES (?)");
            $stmt->bind_param("s", $ip);
            $stmt->execute();
            $stmt->close();
            return 1;
        }
        return 2;
    }
    // /resetterms, /resetprivacy, /resethelp commands
    if ($command[0] == "/resetterms" && boomAllow(100)) {
        require BOOM_PATH . "/system/template/data_template.php";
        $stmt = $mysqli->prepare("UPDATE boom_page SET page_content = ? WHERE page_name = 'terms_of_use'");
        $stmt->bind_param("s", $term_content);
        $stmt->execute();
        $stmt->close();
        return 1;
    }

    if ($command[0] == "/resetprivacy" && boomAllow(100)) {
        require BOOM_PATH . "/template/data_template.php";
        $stmt = $mysqli->prepare("UPDATE boom_page SET page_content = ? WHERE page_name = 'privacy_policy'");
        $stmt->bind_param("s", $privacy_content);
        $stmt->execute();
        $stmt->close();
        return 1;
    }
    if ($command[0] == "/resethelp" && boomAllow(100)) {
        require BOOM_PATH . "/template/data_template.php";
        $stmt = $mysqli->prepare("UPDATE boom_page SET page_content = ? WHERE page_name = 'help'");
        $stmt->bind_param("s", $help_content);
        $stmt->execute();
        $stmt->close();
        return 1;
    }
    // /resetemailfilter command
    if ($command[0] == "/resetemailfilter" && boomAllow(100)) {
        $mysqli->query("DELETE FROM boom_filter WHERE word_type = 'email'");
        $mysqli->query("INSERT INTO boom_filter (word, word_type) VALUES
            ('aol','email'),('att','email'),('comcast','email'),('facebook','email'),('gmail','email')");
        return 1;
    }
	if ($command[0] == "/stylereset" && boomAllow(100)) {
		// Use prepared statements with bound parameters
		$stmt = $mysqli->prepare("UPDATE boom_users SET user_color = 'user' WHERE user_rank < ? AND user_bot = 0");
		$stmt->bind_param("i", $data["allow_name_color"]);
		$stmt->execute();

		$stmt = $mysqli->prepare("UPDATE boom_users SET user_color = 'user' WHERE user_rank < ? AND user_color LIKE '%bgrad%' AND user_bot = 0");
		$stmt->bind_param("i", $data["allow_name_grad"]);
		$stmt->execute();

		$stmt = $mysqli->prepare("UPDATE boom_users SET user_color = 'user' WHERE user_rank < ? AND user_color LIKE '%bneon%' AND user_bot = 0");
		$stmt->bind_param("i", $data["allow_name_neon"]);
		$stmt->execute();

		$stmt = $mysqli->prepare("UPDATE boom_users SET bccolor = '', bcbold = '' WHERE user_rank < ? AND user_bot = 0");
		$stmt->bind_param("i", $data["allow_colors"]);
		$stmt->execute();

		$stmt = $mysqli->prepare("UPDATE boom_users SET bccolor = '' WHERE user_rank < ? AND bccolor LIKE '%bgrad%' AND user_bot = 0");
		$stmt->bind_param("i", $data["allow_grad"]);
		$stmt->execute();

		$stmt = $mysqli->prepare("UPDATE boom_users SET bccolor = '' WHERE user_rank < ? AND bccolor LIKE '%bneon%' AND user_bot = 0");
		$stmt->bind_param("i", $data["allow_neon"]);
		$stmt->execute();

		$stmt = $mysqli->prepare("UPDATE boom_users SET user_font = '' WHERE user_rank < ? AND user_bot = 0");
		$stmt->bind_param("i", $data["allow_name_font"]);
		$stmt->execute();

		$stmt = $mysqli->prepare("UPDATE boom_users SET bcfont = '' WHERE user_rank < ? AND user_bot = 0");
		$stmt->bind_param("i", $data["allow_font"]);
		$stmt->execute();

		return 1;
	}

	if ($command[0] == "/fontreset" && boomAllow(100)) {
		$stmt = $mysqli->prepare("UPDATE boom_users SET user_font = '', bcfont = '' WHERE user_id > 0");
		$stmt->execute();
		return 1;
	}

	if ($command[0] == "/moodreset" && boomAllow(100)) {
		$stmt = $mysqli->prepare("UPDATE boom_users SET user_mood = '' WHERE user_rank < ?");
		$stmt->bind_param("i", $data["allow_mood"]);
		$stmt->execute();
		return 1;
	}

	if ($command[0] == "/themereset" && boomAllow(100)) {
		$stmt = $mysqli->prepare("UPDATE boom_users SET user_theme = 'system' WHERE user_rank < ?");
		$stmt->bind_param("i", $data["allow_theme"]);
		$stmt->execute();
		return 1;
	}

            return 0;
}

?>