<?php


require "../config_session.php";

if (isset($_POST["store_name"]) && isset($_POST["store_pass"])) {
    if (!checkToken($_POST['utk'])) {
        exit('Invalid CSRF token');
    }   
    exit;
}
if (isset($_POST["save_admin_section"])) {
    $section = escape($_POST["save_admin_section"]);
    echo saveadminpanel($section);
    //echo sendDataToSocket();
    exit;
}
if (isset($_POST["test_mail"]) && isset($_POST["test_email"])) {
    $data["user_email"] = escape($_POST["test_email"]);
    if (!boomAllow(90)) {
        exit;
    }
    echo sendEmail("test", $data);
    exit;
}
if (isset($_POST["save_page"]) && isset($_POST["page_content"]) && isset($_POST["page_target"])) {
    $content = softEscape($_POST["page_content"]);
    $target = escape($_POST["page_target"]);
    echo boompagecontent($content, $target);
    exit;
}
function saveAdminPanel($section){
global $mysqli,$data,$lang,$cody;
    if (!boomAllow(90)) {
        return 99;
    }


if($section == "main_settings" && boomAllow(90)) {
    // Ensure all necessary inputs are set
    if (isset($_POST["set_index_path"], $_POST["set_title"], $_POST["set_timezone"], $_POST["set_default_language"], $_POST["set_site_description"], $_POST["set_site_keyword"])) {
        // Sanitize and escape inputs
        $index = trim(escape($_POST["set_index_path"]));
        $title = trim(escape($_POST["set_title"]));
        $timezone = trim(escape($_POST["set_timezone"]));
        $language = trim(escape($_POST["set_default_language"]));
        $description = trim(escape($_POST["set_site_description"]));
        $keyword = trim(escape($_POST["set_site_keyword"]));
        $google_analytics = trim(escape($_POST["set_google_analytics"]));
        // Ensure the language is different from the current one before updating
        if ($language !== $data["language"]) {
            // Use a prepared statement to prevent SQL injection
            $stmt = $mysqli->prepare("UPDATE boom_users SET user_language = ? WHERE user_id > 0");
            $stmt->bind_param("s", $language);
            $stmt->execute();
            $stmt->close();
        }
        // Prepare data for updating the dashboard settings
        $data_query = array(
            "domain" => $index,
            "title" => $title,
            "site_description" => $description,
            "site_keyword" => $keyword,
            "timezone" => $timezone,
            "language" => $language,
            "google_analytics" => $google_analytics,
        );
        // Update the dashboard settings
        $update = fu_update_dashboard($data_query);
        // Check if the language was changed
        if ($language !== $data["language"]) {
            return 2; // Language has been changed
        }
        // Check if the update was successful
        if ($update === true) {
            return 1; // Settings updated successfully
        }
    }
    return 99; // Missing or invalid parameters
}

if ($section == "maintenance" && boomAllow(100)) {
    if (isset($_POST["set_maint_mode"])) {
        // Sanitize and validate input
        $maint_mode = trim(escape($_POST["set_maint_mode"]));
        // Ensure the input is either 0 or 1 (for maintenance mode)
        if ($maint_mode != 0 && $maint_mode != 1) {
            return 99; // Invalid maintenance mode value
        }
        // Update user action only if maintenance mode is enabled and is different from the current state
        if ($maint_mode == 1 && $maint_mode != $data["maint_mode"]) {
            // Use prepared statement to prevent SQL injection
            $stmt = $mysqli->prepare("UPDATE boom_users SET user_action = user_action + 1 WHERE user_rank < 70");
            $stmt->execute();
            $stmt->close();
        }
        // Use prepared statement to update the maintenance mode in the settings table
        $stmt = $mysqli->prepare("UPDATE boom_setting SET maint_mode = ? WHERE id = 1");
        $stmt->bind_param("i", $maint_mode); // "i" denotes integer type
        $stmt->execute();
        $stmt->close();
        return 1; // Success
    }
    return 99; // Missing parameter or invalid request
}

if ($section == "display" && boomAllow(100)) {
    if (isset($_POST["set_login_page"]) && isset($_POST["set_main_theme"])) {
        // Sanitize and escape user inputs
        $login_page = trim(escape($_POST["set_login_page"]));
        $theme = trim(escape($_POST["set_main_theme"]));
        // Validate login page filename
        $login_page_path = BOOM_PATH . "/control/login/" . $login_page . "/login.php";
        // Check if the login page file exists
        if (file_exists($login_page_path)) {
            // Prepare data for update
            $data_query = array(
                "login_page" => $login_page,
                "default_theme" => $theme,
            );
            // Update settings using the update function
            $update = fu_update_dashboard($data_query);
            // Check the result of the update
            if ($update === true) {
                // Return 2 if the theme has changed
                if ($theme != $data["default_theme"]) {
                    return 2;
                }
                return 1; // Successful update
            }
            return 99; // Update failure
        }
        return 99; // File does not exist or is not valid
    }
    return 99; // Missing parameters
}

if($section == "data_setting" && boomAllow(90)) {
    // Check if all required POST parameters are set
    if (isset($_POST["set_max_avatar"]) && isset($_POST["set_max_cover"]) && isset($_POST["set_max_file"])) {
        // Sanitize and validate the input values
        $max_avatar = trim(escape($_POST["set_max_avatar"]));
        $max_cover = trim(escape($_POST["set_max_cover"]));
        $max_file = trim(escape($_POST["set_max_file"]));
        // Ensure the values are numeric and positive
        if (!is_numeric($max_avatar) || $max_avatar <= 0 || !is_numeric($max_cover) || $max_cover <= 0 || !is_numeric($max_file) || $max_file <= 0) {
            return 99; // Invalid input values (must be positive numbers)
        }
        // Prepare data for update
        $data_query = array(
            "max_avatar" => $max_avatar,
            "max_cover" => $max_cover,
            "file_weight" => $max_file,
        );
        // Update settings using the update function
        $update = fu_update_dashboard($data_query);
        // Check the result of the update
        if ($update === true) {
            return 1; // Successful update
        }
    }
    return 99; // Missing parameter or update failure
}

if ($section == "player" && boomAllow(90)) {
    if (isset($_POST["set_default_player"])) {
        $default_player = escape($_POST["set_default_player"]);
        // Ensure the default player ID is numeric and a valid player ID (greater than or equal to 0)
        if (!is_numeric($default_player) || $default_player < 0) {
            return 99; // Invalid player ID
        }
        // Prepare data for update
        $data_query = array(
            "player_id" => $default_player,
        );
        // Update settings using the update function
        $update = fu_update_dashboard($data_query);
        // Check the result of the update and compare values
        if ($update === true) {
            // Specific condition if the player ID was updated or changed
            if ($default_player == 0 || $default_player != $data["player_id"]) {
                return 2; // Player ID mismatch condition
            }
            return 1; // Successful update
        }
    }
    return 99; // Missing parameter or update failure
}

if ($section == "registration" && boomAllow(90)) {
    if (isset($_POST["set_activation"]) && isset($_POST["set_registration"]) && isset($_POST["set_regmute"]) && isset($_POST["set_max_username"]) && isset($_POST["set_min_age"]) && isset($_POST["set_max_reg"])) {
        // Sanitize inputs
        $registration = escape($_POST["set_registration"]);
        $regmute = escape($_POST["set_regmute"]);
        $activation = escape($_POST["set_activation"]);
        $max_name = escape($_POST["set_max_username"]);
        $min_age = escape($_POST["set_min_age"]);
        $max_reg = escape($_POST["set_max_reg"]);
        // Validate the fields
        if (!is_numeric($activation) || !in_array($activation, [0, 1])) {
            return 99;  // Invalid activation value
        }
        if (!is_numeric($regmute) || !in_array($regmute, [0, 1])) {
            return 99;  // Invalid regmute value
        }
        if (!is_numeric($max_name) || $max_name <= 0) {
            return 99;  // Invalid max_username value
        }
        if (!is_numeric($min_age) || $min_age < 13) {
            return 99;  // Invalid min_age value (assuming 13 is the minimum valid age)
        }
        if (!is_numeric($max_reg) || $max_reg <= 0) {
            return 99;  // Invalid max_reg value
        }
        // Update user verification if activation is disabled
        if ($activation == 0) {
            $mysqli->query("UPDATE boom_users SET user_verify = 0 WHERE user_id > 0");
        }
        // Prepare data for update
        $data_query = array(
            "registration" => $registration,
            "regmute" => $regmute,
            "activation" => $activation,
            "max_username" => $max_name,
            "min_age" => $min_age,
            "max_reg" => $max_reg,
        );
        // Update the settings
        $update = fu_update_dashboard($data_query);
        // Check if the update was successful
        if ($update === true) {
            return 1;  // Successful update
        }
    }
    // Return 99 if any parameter is missing or update failed
    return 99;
}

if ($section == "guest" && boomAllow(90)) {
    if (isset($_POST["set_allow_guest"]) && isset($_POST["set_guest_form"]) && isset($_POST["set_guest_talk"]) && isset($_POST["set_guest_per_day"])) {
        // Sanitize inputs
        $allow_guest = escape($_POST["set_allow_guest"]);
        $guest_form = escape($_POST["set_guest_form"]);
        $guest_talk = escape($_POST["set_guest_talk"]);
        $guest_per_day = escape($_POST["set_guest_per_day"]);
        // Validate the values
        if (!in_array($allow_guest, [0, 1])) {
            return 99;  // Invalid allow_guest value
        }
        if (!in_array($guest_form, [0, 1])) {
            return 99;  // Invalid guest_form value
        }
        if (!in_array($guest_talk, [0, 1])) {
            return 99;  // Invalid guest_talk value
        }
        if (!is_numeric($guest_per_day) || $guest_per_day <= 0) {
            return 99;  // Invalid max guest per day value
        }
        // Handle logic for cleaning guest list if guest allowance is disabled
        if ($allow_guest == 0 && $allow_guest != $data["allow_guest"]) {
            cleanList("guest");
        }
        // Prepare data for update
        $data_query = array(
            "allow_guest" => $allow_guest,
            "guest_form" => $guest_form,
            "guest_talk" => $guest_talk,
            "guest_per_day" => $guest_per_day,
        );
        // Update the dashboard settings
        $update = fu_update_dashboard($data_query);
        // Check if the update was successful
        if ($update === true) {
            return 1;  // Successful update
        }
    }
    return 99;  // Missing parameters or update failure
}

if ($section == "bridge_registration" && boomAllow(90)) {
    if(isset($_POST["set_use_bridge"])) {
        // Sanitize the input
        $use_bridge = escape($_POST["set_use_bridge"]);
        // Validate use_bridge input (expecting a numeric value of 0 or 1)
        if (!in_array($use_bridge, [0, 1])) {
            return 99;  // Invalid value for use_bridge
        }
        // If use_bridge is enabled (1) and the bridge file does not exist, return 404
        if ($use_bridge == 1 && !file_exists(BOOM_PATH . "/../boom_bridge.php")) {
            return 404;  // File not found or missing bridge configuration
        }
        // Prepare data for update
        $data_query = array(
            "use_bridge" => $use_bridge,
        );
        // Update the settings using the update function
        $update = fu_update_dashboard($data_query);
        // Check if the update was successful
        if ($update === true) {
            return 1;  // Successful update
        }
    }
    return 99;  // Missing parameter or update failure
}

if ($section == "social_registration" && boomAllow(90)) {
    // Check if all required fields are set
    if (isset($_POST["set_facebook_login"], $_POST["set_facebook_id"], $_POST["set_facebook_secret"], 
              $_POST["set_twitter_login"], $_POST["set_twitter_id"], $_POST["set_twitter_secret"], 
              $_POST["set_google_login"], $_POST["set_google_id"], $_POST["set_google_secret"])) {
        // Sanitize the inputs
        $data_query = array(
            "facebook_login" => escape($_POST["set_facebook_login"]),
            "facebook_id" => escape($_POST["set_facebook_id"]),
            "facebook_secret" => escape($_POST["set_facebook_secret"]),
            "google_login" => escape($_POST["set_google_login"]),
            "google_id" => escape($_POST["set_google_id"]),
            "google_secret" => escape($_POST["set_google_secret"]),
            "twitter_login" => escape($_POST["set_twitter_login"]),
            "twitter_id" => escape($_POST["set_twitter_id"]),
            "twitter_secret" => escape($_POST["set_twitter_secret"]),
        );
        // Update settings using the update function
        $update = fu_update_dashboard($data_query);
        // Check if the update was successful
        if ($update === true) {
            return 1;  // Successful update
        } else {
            return 0;  // Update failed
        }
    }
    // If required fields are missing, return 99
    return 99;  // Missing parameters
}
 
if ($section == "limitation" && boomAllow(90)) {
    // Check if all required parameters are set
    if (isset(
        $_POST["set_allow_cupload"], $_POST["set_allow_pupload"], $_POST["set_allow_wupload"], $_POST["set_allow_cover"], 
        $_POST["set_allow_gcover"], $_POST["set_emo_plus"], $_POST["set_allow_direct"], $_POST["set_allow_room"], 
        $_POST["set_allow_theme"], $_POST["set_allow_history"], $_POST["set_allow_colors"], $_POST["set_allow_name_color"], 
        $_POST["set_allow_name_neon"], $_POST["set_allow_name_font"], $_POST["set_allow_verify"], $_POST["set_allow_name"], 
        $_POST["set_allow_avatar"], $_POST["set_allow_mood"], $_POST["set_allow_grad"], $_POST["set_allow_neon"], 
        $_POST["set_allow_font"], $_POST["set_allow_name_grad"], $_POST["set_allow_gift"], $_POST["set_allow_frame"]
    )) {
        // Sanitize and prepare data for update
        $data_query = array(
            "allow_main" => escape($_POST["set_allow_main"]),
            "allow_private" => escape($_POST["set_allow_private"]),
            "allow_pquote" => escape($_POST["set_allow_pquote"]),
            "allow_quote" => escape($_POST["set_allow_quote"]),
            "allow_avatar" => escape($_POST["set_allow_avatar"]),
            "allow_cover" => escape($_POST["set_allow_cover"]),
            "allow_gcover" => escape($_POST["set_allow_gcover"]),
            "allow_cupload" => escape($_POST["set_allow_cupload"]),
            "allow_pupload" => escape($_POST["set_allow_pupload"]),
            "allow_wupload" => escape($_POST["set_allow_wupload"]),
            "emo_plus" => escape($_POST["set_emo_plus"]),
            "allow_direct" => escape($_POST["set_allow_direct"]),
            "allow_room" => escape($_POST["set_allow_room"]),
            "allow_theme" => escape($_POST["set_allow_theme"]),
            "allow_history" => escape($_POST["set_allow_history"]),
            "allow_verify" => escape($_POST["set_allow_verify"]),
            "allow_name" => escape($_POST["set_allow_name"]),
            "allow_mood" => escape($_POST["set_allow_mood"]),
            "allow_colors" => escape($_POST["set_allow_colors"]),
            "allow_grad" => escape($_POST["set_allow_grad"]),
            "allow_neon" => escape($_POST["set_allow_neon"]),
            "allow_font" => escape($_POST["set_allow_font"]),
            "allow_name_color" => escape($_POST["set_allow_name_color"]),
            "allow_name_grad" => escape($_POST["set_allow_name_grad"]),
            "allow_name_neon" => escape($_POST["set_allow_name_neon"]),
            "allow_name_font" => escape($_POST["set_allow_name_font"]),
            "can_gift" => escape($_POST["set_allow_gift"]),
            "can_frame" => escape($_POST["set_allow_frame"]),
            "can_store" => escape($_POST["set_allow_store"]),
        );
        // Perform the update
        $update = fu_update_dashboard($data_query);
        // Check the result of the update
        if ($update === true) {
            return 1;  // Successful update
        } else {
            return 0;  // Update failed
        }
    }
    // If any parameter is missing, return 99
    return 99;  // Missing parameters
}

if ($section == "staff_limitation" && boomAllow(100)) {
    // Check if the essential parameters are set
    if (isset(
        $_POST["set_can_mute"], $_POST["set_can_ghost"], $_POST["set_can_vghost"], $_POST["set_can_kick"], 
        $_POST["set_can_ban"], $_POST["set_can_delete"], $_POST["set_can_rank"], $_POST["set_can_raction"], 
        $_POST["set_can_modavat"], $_POST["set_can_modcover"], $_POST["set_can_modmood"], $_POST["set_can_modabout"], 
        $_POST["set_can_modcolor"], $_POST["set_can_modname"], $_POST["set_can_modemail"], $_POST["set_can_modpass"], 
        $_POST["set_can_modvpn"], $_POST["set_can_flood"], $_POST["set_can_warn"]
    )) {
        // Escape and prepare data for update
        $data_query = array(
            "can_mute" => escape($_POST["set_can_mute"]),
            "can_ghost" => escape($_POST["set_can_ghost"]),
            "can_vghost" => escape($_POST["set_can_vghost"]),
            "can_kick" => escape($_POST["set_can_kick"]),
            "can_ban" => escape($_POST["set_can_ban"]),
            "can_delete" => escape($_POST["set_can_delete"]),
            "can_rank" => escape($_POST["set_can_rank"]),
            "can_raction" => escape($_POST["set_can_raction"]),
            "can_modavat" => escape($_POST["set_can_modavat"]),
            "can_modcover" => escape($_POST["set_can_modcover"]),
            "can_modmood" => escape($_POST["set_can_modmood"]),
            "can_modabout" => escape($_POST["set_can_modabout"]),
            "can_modcolor" => escape($_POST["set_can_modcolor"]),
            "can_modname" => escape($_POST["set_can_modname"]),
            "can_modemail" => escape($_POST["set_can_modemail"]),
            "can_modpass" => escape($_POST["set_can_modpass"]),
            "can_modvpn" => escape($_POST["set_can_modvpn"]),
            "can_flood" => escape($_POST["set_can_flood"]),
            "can_warn" => escape($_POST["set_can_warn"]),
			"can_dj" => escape($_POST["set_can_dj"]),
			"can_news" => escape($_POST["set_can_news"]),
            "can_mcontact" => escape($_POST["set_can_mcontact"]),	
            "can_mip" => escape($_POST["set_can_mip"]),
            "can_mplay" => escape($_POST["set_can_mplay"]),			
			
        );
        // Perform the update
        $update = fu_update_dashboard($data_query);
        // Check the result of the update and return appropriate response
        if ($update === true) {
            return 1;  // Successful update
        } else {
            return 0;  // Update failed
        }
    }
    // If any required parameter is missing, return 99
    return 99;  // Missing parameters
}

if ($section == "email_settings" && boomAllow(100)) {
    // Check if all required parameters are set
    if (isset(
        $_POST["set_mail_type"], $_POST["set_site_email"], $_POST["set_email_from"], $_POST["set_smtp_host"],
        $_POST["set_smtp_username"], $_POST["set_smtp_password"], $_POST["set_smtp_port"], $_POST["set_smtp_type"]
    )) {
        // Escape and prepare data for update
        $mail_type = escape($_POST["set_mail_type"]);
        $site_email = escape($_POST["set_site_email"]);
        $email_from = escape($_POST["set_email_from"]);
        $smtp_host = escape($_POST["set_smtp_host"]);
        $smtp_username = escape($_POST["set_smtp_username"]);
        $smtp_password = escape($_POST["set_smtp_password"]);
        $smtp_port = escape($_POST["set_smtp_port"]);
        $smtp_type = escape($_POST["set_smtp_type"]);
        // Prepare data for update
        $data_query = array(
            "mail_type" => $mail_type,
            "site_email" => $site_email,
            "email_from" => $email_from,
            "smtp_host" => $smtp_host,
            "smtp_username" => $smtp_username,
            "smtp_password" => $smtp_password,
            "smtp_port" => $smtp_port,
            "smtp_type" => $smtp_type,
        );
        // Perform the update
        $update = fu_update_dashboard($data_query);

        // Return appropriate response based on the update result
        if ($update === true) {
            return 1;  // Successful update
        } else {
            return 0;  // Update failed
        }
    }
    // If any required parameter is missing, return 99
    return 99;  // Missing parameters
}

if ($section == "chat" && boomAllow(90)) {
    // Check if all required parameters are set
    if (isset(
        $_POST["set_gender_ico"], $_POST["set_flag_ico"], $_POST["set_max_main"], $_POST["set_max_private"],
        $_POST["set_speed"], $_POST["set_max_offcount"], $_POST["set_allow_logs"]
    )) {
        // Escape and prepare data for update
        $gender_ico = escape($_POST["set_gender_ico"]);
        $flag_ico = escape($_POST["set_flag_ico"]);
        $max_main = escape($_POST["set_max_main"]);
        $max_private = escape($_POST["set_max_private"]);
        $max_offcount = escape($_POST["set_max_offcount"]);
        $speed = escape($_POST["set_speed"]);
        $allow_logs = escape($_POST["set_allow_logs"]);
        $chat_display = escape($_POST["set_chat_display"]);
        $max_public_history = escape($_POST["set_max_public_history"]);
        $max_private_history = escape($_POST["set_max_private_history"]);
        // Prepare data for update
        $data_query = array(
            "gender_ico" => $gender_ico,
            "flag_ico" => $flag_ico,
            "max_main" => $max_main,
            "max_private" => $max_private,
            "speed" => $speed,
            "max_offcount" => $max_offcount,
            "allow_logs" => $allow_logs,
            "chat_display" => $chat_display,
            "max_public_history" => $max_public_history,
            "max_private_history" => $max_private_history,
        );
        // Perform the update
        $update = fu_update_dashboard($data_query);

        // Return appropriate response based on the update result
        if ($update === true) {
            return 1;  // Successful update
        } else {
            return 0;  // Update failed
        }
    }
    // If any required parameter is missing, return 99
    return 99;  // Missing parameters
}

if ($section == "delays" && boomAllow(90)) {
    // Check if all required parameters are set
    if (isset(
        $_POST["set_chat_delete"], $_POST["set_private_delete"], $_POST["set_wall_delete"], $_POST["set_member_delete"],
        $_POST["set_room_delete"], $_POST["set_act_delay"]
    )) {
        // Escape and prepare data for update
        $act_delay = escape($_POST["set_act_delay"]);
        $chat = escape($_POST["set_chat_delete"]);
        $private = escape($_POST["set_private_delete"]);
        $wall = escape($_POST["set_wall_delete"]);
        $member = escape($_POST["set_member_delete"]);
        $room = escape($_POST["set_room_delete"]);
        $online_forever = escape($_POST["set_online_forever"]);
        // Prepare data for update
        $data_query = array(
            "act_delay" => $act_delay,
            "chat_delete" => $chat,
            "private_delete" => $private,
            "wall_delete" => $wall,
            "last_clean" => '0',  // Set to 0 as per the original logic
            "member_delete" => $member,
            "room_delete" => $room,
            "online_forever" => $online_forever,
        );
        // Perform the update
        $update = fu_update_dashboard($data_query);
        // Return appropriate response based on the update result
        if ($update === true) {
            return 1;  // Successful update
        } else {
            return 0;  // Update failed
        }
    }
    // If any required parameter is missing, return 99
    return 99;  // Missing parameters
}

if ($section == "modules" && boomAllow(90)) {
    // Check if all required parameters are set
    if (isset(
        $_POST["set_use_wall"], $_POST["set_use_lobby"], $_POST["set_cookie_law"], 
        $_POST["set_use_like"], $_POST["set_use_geo"]
    )) {
        // Escape and prepare data for update
        $use_like = escape($_POST["set_use_like"]);
        $use_lobby = escape($_POST["set_use_lobby"]);
        $use_wall = escape($_POST["set_use_wall"]);
        $cookie_law = escape($_POST["set_cookie_law"]);
        $use_geo = escape($_POST["set_use_geo"]);
        // Perform deletions if 'use_wall' is set to 0
        if ($use_wall == 0) {
            $mysqli->query("DELETE FROM boom_notification WHERE notify_source = 'post'");
            $mysqli->query("DELETE FROM boom_report WHERE report_type = '2'");
        }
        // Prepare data for update
        $data_query = array(
            "use_geo" => $use_geo,
            "use_like" => $use_like,
            "use_lobby" => $use_lobby,
            "use_wall" => $use_wall,
            "cookie_law" => $cookie_law,
        );
        // Perform the update operation
        $update = fu_update_dashboard($data_query);
        // Return appropriate response based on the update result
        if ($update === true) {
            return 1;  // Successful update
        } else {
            return 0;  // Update failed
        }
    }
    // If any required parameter is missing, return 99
    return 99;  // Missing parameters
}

// OneSignal settings
if ($section == "setting_notifications" && boomAllow(100)) {
    // Check if all required POST parameters are set
    if (isset($_POST["onesignal_web_push_id"], $_POST["onesignal_web_reset_key"], $_POST["allow_onesignal"])) {
        // Escape and prepare data for update
        $onesignal_web_push_id = escape($_POST["onesignal_web_push_id"]);
        $onesignal_web_reset_key = escape($_POST["onesignal_web_reset_key"]);
        $allow_onesignal = escape($_POST["allow_onesignal"]);

        // Prepare data for update
        $data_query = array(
            "onesignal_web_push_id" => $onesignal_web_push_id,
            "onesignal_web_reset_key" => $onesignal_web_reset_key,
            "allow_onesignal" => $allow_onesignal,
        );

        // Update the dashboard settings
        $update = fu_update_dashboard($data_query);

        // Return success if update is successful
        if ($update === true) {
            return 1;  // Success
        } else {
            return 0;  // Update failed
        }
    }

    // If any required parameter is missing, return 99
    return 99;  // Missing parameters
}
// Gold settings
if ($section == "admin_gold" && boomAllow(100)) {
    // Check if all required parameters are set
    if (isset($_POST["set_use_gold"], $_POST["set_can_sgold"], $_POST["set_can_rgold"])) {
        // Escape and prepare data for update
        $use_gold = escape($_POST["set_use_gold"]);
        $can_sgold = escape($_POST["set_can_sgold"]);
        $can_rgold = escape($_POST["set_can_rgold"]);
        $allow_gold = escape($_POST["set_allow_gold"]);
        $gold_delay = escape($_POST["set_gold_delay"]);
        $gold_base = escape($_POST["set_gold_base"]);
        $can_vgold = escape($_POST["set_can_vgold"]);

        // Prepare data for update
        $data_query = array(
            "use_gold" => $use_gold,
            "can_sgold" => $can_sgold,
            "can_rgold" => $can_rgold,
            "gold_delay" => $gold_delay,
            "gold_base" => $gold_base,
            "can_vgold" => $can_vgold,
            "allow_gold" => $allow_gold,
        );

        // Update the dashboard settings
        $update = fu_update_dashboard($data_query);

        // Return success if update is successful
        if ($update === true) {
            return 1;  // Success
        } else {
            return 0;  // Update failed
        }
    }

    // If any required parameter is missing, return 99
    return 99;  // Missing parameters
}

if ($section === "security" && boomAllow(100)) {
        // Check if required POST variables are set
        if (
            isset($_POST["set_use_recapt"], $_POST["set_recapt_key"], $_POST["set_recapt_secret"],
                  $_POST["set_flood_action"], $_POST["set_max_flood"], $_POST["set_flood_delay"],
                  $_POST["set_vpn_key"], $_POST["set_use_vpn"], $_POST["set_vpn_delay"])
        ) {
            // Sanitize input data
            $data_query = array(
                "use_recapt"    => escape($_POST["set_use_recapt"]),
                "recapt_key"    => escape($_POST["set_recapt_key"]),
                "recapt_secret" => escape($_POST["set_recapt_secret"]),
                "flood_action"  => escape($_POST["set_flood_action"]),
                "max_flood"     => escape($_POST["set_max_flood"]),
                "flood_delay"   => escape($_POST["set_flood_delay"]),
                "vpn_key"       => escape($_POST["set_vpn_key"]),
                "use_vpn"       => escape($_POST["set_use_vpn"]),
                "vpn_delay"     => escape($_POST["set_vpn_delay"]),
            );
            // Update settings in the dashboard
            $update = fu_update_dashboard($data_query);
            // Return success or error code
            return $update === true ? 1 : 99;
        } else {
            // Missing required POST parameters
            return 99;
        }
    }
   if ($section == "gateway_mods" && boomAllow(100)) {
         if(isset($_POST["gateway_mods"])) {
			// Process PayPal settings if provided
			if(isset($_POST["allow_paypal"]) && 
				isset($_POST["paypal_mode"]) && 
				isset($_POST["paypalTestingClientKey"]) &&
				isset($_POST["paypalTestingSecretKey"]) &&
				isset($_POST["paypalLiveClientKey"]) &&
				isset($_POST["paypalLiveSecretKey"])) {
				$data_query = array(
					"allow_paypal" => escape($_POST["allow_paypal"]),
					"paypal_mode" => escape($_POST["paypal_mode"]),
					"paypalTestingClientKey" => escape($_POST["paypalTestingClientKey"]),
					"paypalTestingSecretKey" => escape($_POST["paypalTestingSecretKey"]),
					"paypalLiveClientKey" => escape($_POST["paypalLiveClientKey"]),
					"paypalLiveSecretKey" => escape($_POST["paypalLiveSecretKey"]),
				);
        $update = fu_update_dashboard($data_query);
        if ($update === true) {
            return 1;
        } else {
            return 0; // Handle update failure
        }
    }
    // Process wallet settings if provided
            if (isset($_POST["allow_wallet"]) && 
                isset($_POST["dollar_to_point_cost"]) && 
                isset($_POST["currency"])) {
        
                $data_query = array(
                    "use_wallet" => escape($_POST["allow_wallet"]),
                    "point_cost" => escape($_POST["dollar_to_point_cost"]),
                    "currency" => escape($_POST["currency"]),
                );
        
                $update = fu_update_dashboard($data_query);
        
                if ($update === true) {
                    return 1;
                } else {
                    return 0; // Handle update failure
                }
            }
        } 
    }

if($section === "websocket" && boomAllow(100)) {
    // CSRF token validation (make sure it's included in the form)
    if (isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
        // Check for required POST parameters
        if (isset($_POST["set_websocket_path"], $_POST["set_websocket_port"], $_POST["set_websocket_mode"], $_POST["set_websocket_protocol"])) {
            // Validate inputs
            $websocket_path = filter_var($_POST["set_websocket_path"], FILTER_SANITIZE_URL);
            $websocket_port = filter_var($_POST["set_websocket_port"], FILTER_VALIDATE_INT);
            $websocket_mode = filter_var($_POST["set_websocket_mode"], FILTER_VALIDATE_INT);
			$websocket_protocol = in_array($_POST["set_websocket_protocol"], ['https://', 'wss://'], true) ? $_POST["set_websocket_protocol"] : null;
			$istyping_mode = filter_var($_POST["set_istyping_mode"], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
			$prive_line = filter_var($_POST["set_del_prive_line"], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
			$public_announcement = filter_var($_POST["set_public_announcement"], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
			$enable_monitor = filter_var($_POST["set_enable_monitor"], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
			$privateTyping = filter_var($_POST["set_privateTyping"], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            // Prepare the data query
            $data_query = array(
                "websocket_path"    => $websocket_path,
                "websocket_port"    => $websocket_port,
                "websocket_mode"    => $websocket_mode,
                "websocket_protocol"=> $websocket_protocol,
                "istyping_mode"     => $istyping_mode,
                "del_prive_line"     => $prive_line,
                "public_announcement"     => $public_announcement,
                "enable_monitor"     => $enable_monitor,
                "privateTyping"     => $privateTyping,
            );
            // Update the settings
            $update = fu_update_dashboard($data_query);
            // Return success or failure code
            return $update === true ? 1 : 99; // Return success or error
			
        } else {
            // Missing required POST parameters
            return 99;
        }
    } else {
        // CSRF token mismatch
        return 99; // Token validation failed
    }
}
if ($section === "call_system" && boomAllow(100)) {
    // CSRF token validation (make sure it's included in the form)
    if (isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
        // Check for required POST parameters
        if (
            isset(
                $_POST["set_use_call"],
                $_POST["set_can_vcall"],
                $_POST["set_can_acall"],
                $_POST["set_call_max"],
                $_POST["set_call_method"],
                $_POST["set_call_cost"]
            )
        ) {
            // Validate inputs
            $use_call = filter_var($_POST['set_use_call'], FILTER_VALIDATE_INT); // Call system status (integer)
            $can_vcall = filter_var($_POST['set_can_vcall'], FILTER_VALIDATE_INT); // Can initiate video call (integer)
            $can_acall = filter_var($_POST['set_can_acall'], FILTER_VALIDATE_INT); // Can initiate audio call (integer)
            $call_max = filter_var($_POST['set_call_max'], FILTER_VALIDATE_INT); // Maximum call duration (integer)
            $call_method = filter_var($_POST['set_call_method'], FILTER_VALIDATE_INT); // Payment method (integer)
            $call_cost = filter_var($_POST['set_call_cost'], FILTER_VALIDATE_FLOAT); // Cost per minute of call (float)
            $call_secret = escape($_POST['set_call_secret']); // Call agora secret of call (float)
            $call_appid = escape($_POST['set_call_appid']); // Call agora secret of call (float)
            $call_server_type = escape($_POST['set_call_server_type']); // Call agora secret of call (float)
            // Ensure all inputs are valid
            if (
                $use_call !== false &&
                $can_vcall !== false &&
                $can_acall !== false &&
                $call_max !== false &&
                $call_method !== false &&
                $call_cost !== false
            ) {
                // Prepare the data array
                $data_query = array(
                    'use_call' => $use_call,
                    'can_vcall' => $can_vcall,
                    'can_acall' => $can_acall,
                    'call_max' => $call_max,
                    'call_method' => $call_method,
                    'call_cost' => $call_cost,
                    'call_secret' => $call_secret,
                    'call_appid' => $call_appid,
                    'call_server_type' => $call_server_type,
                );
                // Update the settings using a function (assuming `fu_update_dashboard` exists)
                $update = fu_update_dashboard($data_query);
                // Return success or failure code
                return $update === true ? 1 : 99; // Return success or error
            } else {
                // Validation failed for one or more inputs
                return 99;
            }
        } else {
            // Missing required POST parameters
            return 99;
        }
    } else {
        // CSRF token mismatch
        return 99; // Token validation failed
    }
}

if ($section === "store_control" && boomAllow(100)) {
    // CSRF token validation (ensure it is included in the form)
    if (isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
        // Check if required POST parameters are set
        if (isset($_POST["set_use_store"], $_POST["set_use_frame"], $_POST["set_use_wings"])) {
            // Validate inputs
            $use_store = filter_var($_POST["set_use_store"], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            $use_frame = filter_var($_POST["set_use_frame"], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            $use_wings = filter_var($_POST["set_use_wings"], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            // Check if any validation failed
            if (is_null($use_store) || is_null($use_frame) || is_null($use_wings)) {
                return 99; // Invalid input data
            }
            // Prepare the data query
            $data_query = array(
                "use_store"  => $use_store,
                "use_frame"  => $use_frame,
                "use_wings"  => $use_wings,
            );
            // Update the dashboard settings
            $update = fu_update_dashboard($data_query);
            // Return success or error code
            return $update === true ? 1 : 99; // Return success or failure
        } else {
            // Missing required POST parameters
            return 99;
        }
    } else {
        // CSRF token mismatch
        return 99; // Token validation failed
    }
}

if ($section === "xp_system" && boomAllow(100)) {
    // CSRF token validation (ensure it is included in the form)
    if (isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {

        // Check if required POST parameters are set
        if (isset($_POST["set_use_level"], $_POST["set_exp_gift"], $_POST["set_exp_post"], $_POST["set_exp_priv"], $_POST["set_exp_chat"])) {
            // Validate inputs
            $use_level = filter_var($_POST["set_use_level"], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            $exp_gift = filter_var($_POST["set_exp_gift"], FILTER_VALIDATE_FLOAT);
            $exp_post = filter_var($_POST["set_exp_post"], FILTER_VALIDATE_FLOAT);
            $exp_priv = filter_var($_POST["set_exp_priv"], FILTER_VALIDATE_FLOAT);
            $exp_chat = filter_var($_POST["set_exp_chat"], FILTER_VALIDATE_FLOAT);
            // Validate that all required fields are valid
            if (is_null($use_level) || $exp_gift === false || $exp_post === false || $exp_priv === false || $exp_chat === false) {
                return 99; // Invalid data
            }
            // Prepare the data query for update
            $data_query = array(
                "use_level"  => $use_level,
                "exp_gift"   => $exp_gift,
                "exp_post"   => $exp_post,
                "exp_priv"   => $exp_priv,
                "exp_chat"   => $exp_chat,
            );
            // Update the dashboard settings
            $update = fu_update_dashboard($data_query);

            // Return success or error code
            return $update === true ? 1 : 99; // Return 1 on success, 99 on failure
        } else {
            // Missing required POST parameters
            return 99;
        }
    } else {
        // CSRF token mismatch
        return 99; // Token validation failed
    }
}

if($section === "gold_reward" && boomAllow(100)) {
    // CSRF token validation (ensure it is included in the form)
    if (isset($_POST['csrf_token'])) {
        // Check if required POST parameters are set
        if (isset($_POST["set_allow_sendcoins"], $_POST["set_allow_takecoins"])) {
            // Validate and sanitize inputs
            $allow_sendcoins = escape($_POST["set_allow_sendcoins"]);
            $allow_takecoins = escape($_POST["set_allow_takecoins"]);
            // Validate if both are valid booleans
            if (is_null($allow_sendcoins) || is_null($allow_takecoins)) {
                return 99; // Invalid input data
            }
            // Prepare the data query
            $data_query = array(
                "allow_sendcoins"  => $allow_sendcoins,
                "allow_takecoins"  => $allow_takecoins,
            );
            // Update the dashboard settings
            $update = fu_update_dashboard($data_query);
            // Return success or error code
            return $update === true ? 1 : 99; // Return success (1) or failure (99)
        } else {
            // Missing required POST parameters
            return 99;
        }
    } else {
        // CSRF token mismatch
        return 99; // Token validation failed
    }
}

}

function get_settings() {
    global $db;
    // Fetch settings where 'setting' equals 1
    $db->where('id', 1);
    $settings = $db->get('setting');
    // Check if any results were returned
    if ($db->count > 0) {
        return $settings;
    }
    // Return an empty array or null if no settings are found
    //return [];
}

function sendDataToSocket() {
    global $data;
    // Retrieve WebSocket server settings
    $s_protocol = isset($data['websocket_protocol']) ? $data['websocket_protocol'] : 'https';
    $s_server = isset($data['websocket_path']) ? $data['websocket_path'] : '';
    $s_port = isset($data['websocket_port']) ? $data['websocket_port'] : '';
    $s_auth_token = 'lucifer666';
    // Ensure that server and port are valid
    if (empty($s_server) || empty($s_port)) {
        return 'Invalid WebSocket server or port';
    }
    // Construct the URL to the Socket.IO server endpoint
    $url = "{$s_protocol}://{$s_server}:{$s_port}/update-config"; // Use HTTPS by default
    // Fetch settings to send in the request
    $data_query = get_settings();
    // Ensure data_query is an array and not empty
    if (empty($data_query)) {
        return 'No settings to send';
    }
    // Initialize cURL session
    $ch = curl_init($url);
    // Set cURL options
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
		'Content-Type: application/json',
		'x-auth-token: ' . $s_auth_token  // Use the token retrieved securely
	]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_query));
    // Security: Ensure SSL verification (for production environments)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // Verify the host matches the certificate
    // Execute the request and capture the response
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    // Handle cURL errors (if any)
    if ($response === false) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        return 'cURL error: ' . $error_msg;
    }
    // Close the cURL session
    curl_close($ch);
    // Handle different HTTP response codes
    switch ($httpCode) {
        case 200:
            // Success - Handle success response (optional)
            return $response;
        case 403:
            return 'Forbidden: Access Denied';
        case 500:
            return 'Internal Server Error: Server failed to process the request';
        default:
            return 'Error: HTTP Code ' . $httpCode . ' - ' . $response;
    }
}
function boomPageContent($content, $target){
    global $mysqli;
    // Check user permission
    if (!boomAllow(90)) {
        return "";
    }
    // Ensure content is not empty
    if (empty($content)) {
        $content = "";
    }
    // Sanitize the target (assuming escape is a function to sanitize input)
    $target = escape($_POST["page_target"]); // Assuming you have escape function to sanitize input
    // Validate target (to prevent potential issues like XSS or unwanted characters)
    if (!preg_match("/^[a-zA-Z0-9-_]+$/", $target)) {
        return "Invalid target name";
    }
    // Prepare and execute the SELECT query securely using a prepared statement
    $stmt = $mysqli->prepare("SELECT * FROM boom_page WHERE page_name = ?");
    if ($stmt === false) {
        return "Error preparing SELECT statement";
    }
    $stmt->bind_param("s", $target); // Bind parameter as a string
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        // Page exists, update the content
        $stmt_update = $mysqli->prepare("UPDATE boom_page SET page_content = ? WHERE page_name = ?");
        if ($stmt_update === false) {
            return "Error preparing UPDATE statement";
        }
        $stmt_update->bind_param("ss", $content, $target); // Bind both parameters as strings
        $stmt_update->execute();
        $stmt_update->close();
    } else {
        // Page doesn't exist, insert new page
        $stmt_insert = $mysqli->prepare("INSERT INTO boom_page (page_name, page_content) VALUES (?, ?)");
        if ($stmt_insert === false) {
            return "Error preparing INSERT statement";
        }
        $stmt_insert->bind_param("ss", $target, $content); // Bind both parameters as strings
        $stmt_insert->execute();
        $stmt_insert->close();
    }
    $stmt->close(); // Close the select statement
    return 1; // Success
}



?>