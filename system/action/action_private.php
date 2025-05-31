<?php
if ($f == 'action_private') {
	if($s == 'delete_msg') {
		// Check msg deleted
		if(isset($_POST['msg_id'])){
			if ($_SERVER['REQUEST_METHOD'] === 'POST') {
				$messageId = $_POST['msg_id'];
				$userId = $data['user_id']; // Current user ID
				// Fetch the message from the database
				$query = "SELECT * FROM boom_private WHERE id = ?";
				$stmt = $mysqli->prepare($query);
				$stmt->bind_param("i", $messageId);
				$stmt->execute();
				$result = $stmt->get_result();
				$message = $result->fetch_assoc();
				// Check if the message exists and belongs to the current user
				if ($message && $message['hunter'] == $userId) {
					// Delete the message
					$deleteQuery = "DELETE FROM boom_private WHERE id = ?";
					$deleteStmt = $mysqli->prepare($deleteQuery);
					$deleteStmt->bind_param("i", $messageId);
					$deleteStmt->execute();
					echo fu_json_results(['success' => true]);
				} else {
					echo fu_json_results(['success' => false, 'error' => 'Unauthorized']);
				}
			}
		}		
	}
}
?>