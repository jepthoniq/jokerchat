<?php
$res = [];
	if ($f == 'login_as') {
		if ($s == 'login_as_username') {
	 header("Content-type: application/json");
    // Check if necessary data is provided
    if (isset($_POST['owner_switch'], $_POST['user_id'])) {
        // Validate and sanitize user_id to ensure it's a valid integer (assuming user_id is numeric)
        $user_id = (int)$_POST['user_id'];
        // Check if the user_id is valid
        if ($user_id <= 0) {
            $res = [
                'status' => "failure",
                'message' => "Invalid user ID."
            ];
            echo json_encode($res);
            exit();
        }      
        // Use prepared statement to prevent SQL injection
        $stmt = $mysqli->prepare("SELECT * FROM boom_users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id); // "i" for integer
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            // Set cookies to log in as the selected user securely
            setBoomCookie($user['user_id'], $user['user_password']);
            // Store the owner session before switching
            if (!isset($_SESSION['original_owner_id'])) {
                $_SESSION['original_owner_id'] = $data['user_id'];           // Store original owner user_id
                $_SESSION['original_owner_password'] = $data['user_password']; // Store original owner password
                $_SESSION['original_owner_name'] = $data['user_name'];       // Store original owner user_name
            }
            // Regenerate session ID to prevent session fixation attack
            session_regenerate_id(true);
            // Set the switched user session
            $_SESSION['switched_user_id'] = $user['user_id'];
            $_SESSION['switched_user_name'] = $user['user_name'];
            // Return success and message to switch back to the owner
            $res = [
                'status' => "success",
                'redirect_url' => "index.php", // URL to redirect to
                'message' => "You are now logged in as " . htmlspecialchars($user['user_name']) . 
                             ". You will be redirected to the home page."
            ];
        } else {
            // Return failure status if user is not found
            $res = [
                'status' => "failure",
                'message' => "User not found."
            ];
        }        
        $stmt->close(); // Close the prepared statement
    } else {
        // Return failure status if required data is missing
        $res = [
            'status' => "missing_data",
            'message' => "Missing required data."
        ];
    }
    // Return JSON response
    echo json_encode($res);
    exit();
}

		if($s == 'restore_owner' && isset($_SESSION['original_owner_id'])) {
				// Check if original owner password is set before using it
				if (isset($_SESSION['original_owner_password'])) {
					// Regenerate the session ID to prevent session fixation attacks
					session_regenerate_id(true);
					// Restore cookies to log back in as the original owner
					setBoomCookie($_SESSION['original_owner_id'], $_SESSION['original_owner_password']);
					// Clear the switched user session data securely
					unset($_SESSION['switched_user_id']);
					unset($_SESSION['switched_user_name']);
					// Optionally, remove the original owner session once switched back
					unset($_SESSION['original_owner_id']);
					unset($_SESSION['original_owner_password']);
					// Return success response with redirect URL
					$res = [
						'status' => "success",
						'redirect_url' => "admin.php" // URL to redirect to
					];
					// Redirect back to the original owner’s admin page
					header('Location: ' . $res['redirect_url']); // Redirect
					exit();
				} else {
					// Handle the case where the original owner password is not set
					$res = [
						'status' => "error",
						'message' => "Original owner password is not set. Please log in again."
					];
				}
			// Return JSON response if redirect is not used
			header("Content-type: application/json");
			echo json_encode($res);
			exit();
		}
	}
?>
