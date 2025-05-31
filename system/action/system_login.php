<?php
if ($f == "system_login") {
    $res = [];
    if ($s == "member_login") {
        // Initialize response
        $res["code"] = 2;
        // Sanitize input data
        $input_password = escape($_POST["password"]);
        $username = escape($_POST["username"]);
        $user_ip = getIp();
        // Validate input
        if (empty($input_password) || empty($username)) {
            $res["msg"] = "Bad login";
            $res["code"] = 1;
            echo fu_json_results($res);
            exit();
        }
        // Determine query type (email or username)
        $query = isEmail($username)
            ? "SELECT * FROM boom_users WHERE user_email = ?"
            : "SELECT * FROM boom_users WHERE user_name = ?";
        // Prepare and execute query
        if ($stmt = $mysqli->prepare($query)) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $validate = $stmt->get_result();
            // If user found, validate password
            if ($validate->num_rows > 0) {
                $valid = $validate->fetch_assoc();
                $db_password = $valid["user_password"];
                $temp_pass = $valid["temp_pass"];
                $id = $valid["user_id"];
                // Check if the temporary password matches
                $is_temp_pass_valid =
                    !empty($temp_pass) &&
                    password_verify($input_password, $temp_pass);
                // Check if password needs upgrading from old hash
                if (
                    !$is_temp_pass_valid &&
                    (strlen($db_password) === 40 ||
                        strpos($db_password, '$2y$') !== 0)
                ) {
                    if (encrypt($input_password) === $db_password) {
                        // Old hash, upgrade to bcrypt
                        $new_hash = password_hash(
                            $input_password,
                            PASSWORD_BCRYPT
                        );
                        $stmt_update = $mysqli->prepare(
                            "UPDATE boom_users SET user_password = ? WHERE user_id = ?"
                        );
                        $stmt_update->bind_param("si", $new_hash, $id);
                        $stmt_update->execute();
                        $db_password = $new_hash;
                    }
                }
                // Validate password with bcrypt
                if (
                    password_verify($input_password, $db_password) ||
                    $is_temp_pass_valid
                ) {
                    // Successful login
                    $post_time = date("H:i", time());
                    $ssesid = $valid["session_id"] + 1;
                    // Reset the temporary password after successful login
                    if ($is_temp_pass_valid) {
                        $reset_temp_pass = $mysqli->prepare(
                            "UPDATE boom_users SET temp_pass = NULL WHERE user_id = ?"
                        );
                        $reset_temp_pass->bind_param("i", $id);
                        $reset_temp_pass->execute();
                        // Set session flag to force password change
                        $_SESSION["force_password_change"] = true;
                    }
                    // Update user session details
                    $stmt_update_ip = $mysqli->prepare(
                        "UPDATE boom_users SET user_ip = ?, session_id = ?, join_msg = '0', user_roomid = '0' WHERE user_id = ?"
                    );
                    $stmt_update_ip->bind_param("sii", $user_ip, $ssesid, $id);
                    $stmt_update_ip->execute();
                    // Set session and cookies
                    setBoomCookie($id, $db_password);
                    $_SESSION["user_id"] = $id;
                    session_regenerate_id(true); // Regenerate session ID
                    // Return success message
                    $res["code"] = 3;
                    $res["msg"] = "You have been logged in successfully";
                    $res["reload_delay"] = 2; // Delay before reload
                } else {
                    $res["code"] = 1;
                    $res["msg"] = "Invalid password";
                }
            } else {
                $res["code"] = 1;
                $res["msg"] = "User not found";
            }
            $stmt->close(); // Close prepared statement
        } else {
            $res["code"] = 1;
            $res["msg"] = "Database query error";
        }
        // Return the response as JSON
        echo fu_json_results($res);
        exit();
    }

    if ($s == "guest_login") {
        $res = [
            "code" => 1,
            "guest_lang" => getLanguage(),
            "guest_ip" => getIp(),
        ];
        // Check for conditions that prevent login
        if (!allowGuest()) {
            $res["code"] = 0;
            $res["msg"] = "Guest login is not allowed.";
        }
        if (!okGuest($res["guest_ip"])) {
            $res["code"] = 16; // Prevent new guest login if already exists
            $res["msg"] = "A guest is already logged in from this IP address.";
            $res["ip"] = $res["guest_ip"];
        }
        // Sanitize and validate user input
        $res["guest_name"] = trim(escape($_POST["guest_name"] ?? ""));
        $res["guest_gender"] = trim(escape($_POST["guest_gender"] ?? ""));
        $res["guest_age"] = trim(escape($_POST["guest_age"] ?? ""));
        $res["recaptcha"] = trim(escape($_POST["recaptcha"] ?? ""));
        if (boomRecaptcha()) {
            // Validate reCAPTCHA
            if ($res["recaptcha"] && !boomCheckRecaptcha($res["recaptcha"])) {
                $res["code"] = 6; // Recaptcha verification failed
                $res["msg"] = "Please complete the reCAPTCHA.";
            }
        }
        // Name validation
        if (!validName($res["guest_name"])) {
            $res["code"] = 4; // Invalid name
            $res["msg"] = "Invalid name. Please use a valid name.";
        }
        // Username availability check
        if (!boomUsername($res["guest_name"])) {
            $res["code"] = 5; // Invalid username
            $res["msg"] = "This username is already taken or invalid.";
        }
        // Guest form validation
        if (guestForm()) {
            if (!validAge($res["guest_age"])) {
                $res["code"] = 13; // Invalid age
                $res["msg"] = "Please enter a valid age.";
            }
            if (!validGender($res["guest_gender"])) {
                $res["code"] = 14; // Invalid gender
                $res["msg"] = "Please select a valid gender.";
            }
        }
        // Only proceed if no errors
        if ($res["code"] == 1) {
            // Create guest user array
            $guest_user = [
                "name" => $res["guest_name"],
                "password" => randomPass(), // Make sure randomPass is secure
                "language" => $res["guest_lang"],
                "ip" => $res["guest_ip"],
                "rank" => 0,
                "avatar" => "default_guest.svg",
                "email" => "", // Empty email as it's a guest
            ];
            if (guestForm()) {
                // If age and gender are provided, include them
                $guest_user["age"] = $res["guest_age"];
                $guest_user["gender"] = $res["guest_gender"];
            }
            // Insert user into database
            $user = boomInsertUser($guest_user);
            // Handle failure to insert user
            if (empty($user)) {
                $res["code"] = 2; // Database insert failed
                $res["msg"] = "Failed to create guest user. Please try again.";
            } else {
                // Success: Guest user created
                $res["code"] = 200; // Successful guest creation
                $res["msg"] = "Guest user successfully created.";
                $res["guest_id"] = $user["user_id"]; // Assuming $user contains the newly created user
                $res["reload_delay"] = 2; // Delay before reload
            }
        }

        // Send response as JSON
        echo fu_json_results($res);
        exit();
    }

    if ($s == "system_register") {
        $user_ip = getIp();
        $user_name = trim(escape($_POST["username"] ?? ""));
        $user_password = trim(escape($_POST["password"] ?? ""));
        $dlang = getLanguage();
        $user_email = trim(escape($_POST["email"] ?? ""));
        $user_gender = escape($_POST["gender"] ?? "");
        $user_age = escape($_POST["age"] ?? "");
        $res["recaptcha"] = isset($_POST["recaptcha"])
            ? $_POST["recaptcha"]
            : "";
        $referrer_id = isset($_POST["referrer_id"])
            ? intval(escape($_POST["referrer_id"]))
            : null;
        // Check for empty fields
        if (empty($user_password) || empty($user_name) || empty($user_email)) {
            $res["code"] = 3; // Empty field validation
            $res["msg"] = "All fields are required";
            echo fu_json_results($res);
            exit();
        }
        // Validate username, password, and email not being only whitespace
        if (
            preg_match('/^\s+$/', $user_name) ||
            preg_match('/^\s+$/', $user_password) ||
            preg_match('/^\s+$/', $user_email)
        ) {
            $res["code"] = 3;
            $res["msg"] = "Fields cannot contain only whitespace";
            echo fu_json_results($res);
            exit();
        }
        if (boomRecaptcha()) {
            // Validate reCAPTCHA
            if ($res["recaptcha"] && !boomCheckRecaptcha($res["recaptcha"])) {
                $res["code"] = 6; // Recaptcha verification failed
                $res["msg"] = "Please complete the reCAPTCHA.";
            }
        }
        // Further validation checks (username, email, password, etc.)
        if (!validName($user_name)) {
            $res["code"] = 4; // Invalid username
            $res["msg"] = "Invalid username";
            echo fu_json_results($res);
            exit();
        }
        if (!validEmail($user_email)) {
            $res["code"] = 6; // Invalid email
            $res["msg"] = "Invalid email format";
            echo fu_json_results($res);
            exit();
        }
        if (!checkEmail($user_email)) {
            $res["code"] = 10; // Email already exists
            $res["msg"] = "Email already exists";
            echo fu_json_results($res);
            exit();
        }
        if (!boomValidPassword($user_password)) {
            $res["code"] = 17; // Short password
            $res["msg"] = "Password is too short";
            echo fu_json_results($res);
            exit();
        }
        if (!validAge($user_age)) {
            $res["code"] = 13; // Invalid age
            $res["msg"] = "Please select a valid age";
            echo fu_json_results($res);
            exit();
        }
        if (!validGender($user_gender)) {
            $res["code"] = 14; // Invalid gender
            $res["msg"] = "Please select a gender";
            echo fu_json_results($res);
            exit();
        }
        if (!boomOkRegister($user_ip)) {
            $res["code"] = 16; // Max registration attempts or system issue
            $res["msg"] = "Registration is temporarily unavailable";
            echo fu_json_results($res);
            exit();
        }
        if (!boomUsername($user_name)) {
            $res["code"] = 5; // Username already exists
            $res["msg"] = "Username already exists";
            echo fu_json_results($res);
            exit();
        }
        // Encrypt password securely
        $user_password = password_hash($user_password, PASSWORD_BCRYPT); // Use bcrypt for secure password storage
        // Insert new user into the database securely
        $system_user = [
            "name" => $user_name,
            "password" => $user_password,
            "email" => $user_email,
            "language" => $dlang,
            "gender" => $user_gender,
            "avatar" => genderAvatar($user_gender),
            "age" => $user_age,
            "verify" => $data["activation"], // Make sure $data is properly defined elsewhere
            "ip" => $user_ip,
        ];
        $user = boomInsertUser($system_user);
        if (empty($user)) {
            $res["code"] = 0; // Registration failed
            $res["msg"] = "Registration failed, please try again";
            echo fu_json_results($res);
            exit();
        }
        $res["code"] = 1; // Registration successful
        $res["msg"] = "You have been successfully registered";
        echo fu_json_results($res);
        exit();
    }
}

?>
