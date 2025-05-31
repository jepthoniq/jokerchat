<?php
/**
* FuseChat
*
* @package FuseChat
* @author www.nemra-1.com
* @copyright 2020
* @terms any use of this script without a legal license is prohibited
* all the content of FuseChat is the propriety of FuseChat and Cannot be 
* used for another project.
*/
if (isset($_POST["remail"])) {
    require_once(__DIR__ . "./../config.php");
    $email = trim($_POST['remail']); // Trim spaces
    $email = filter_var($email, FILTER_SANITIZE_EMAIL); // Sanitize the email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {  // Validate email format
        // Return JSON response for invalid email format
        echo fu_json_results([
            'status' => 'error',
            'code' => 3,
            'message' => 'Invalid email format.'
        ]);
        die();
    }
    // Use prepared statements to prevent SQL Injection
    $stmt = $mysqli->prepare("SELECT * FROM boom_users WHERE user_email = ? AND user_bot = 0 LIMIT 1");
    $stmt->bind_param("s", $email); // 's' indicates the parameter type is a string
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
		// Call the resetUserPass function
		$resetResult = resetUserPass($user);
		$_SESSION["force_password_change"] = true;	
		// Return the result of resetUserPass as JSON
		echo fu_json_results([
					'code' => 1,
					'success' => true,
					'message' => 'Password reset link sent successfully',
					'result' => $resetResult,
					'force_password' => $_SESSION["force_password_change"],
					'user_id' => $user['user_id'] // You can return additional data if needed
		]);
		die();
    } else {
        // Return JSON response for user not found
        echo fu_json_results([
            'status' => 'error',
            'code' => 2,
            'message' => 'Email not found in the system.'
        ]);
        die();
    }
    $stmt->close(); // Close the prepared statement
} else {
    // Return JSON response for missing parameter
    echo fu_json_results([
        'status' => 'error',
        'code' => 99,
        'message' => 'Missing email parameter.'
    ]);
    die();
}


?>