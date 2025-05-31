<?php
// Main script: requests.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (isset($_POST['f']) && $_POST['f'] === 'admin_actions') {
		 if(isset($_POST['s']) && $_POST['s'] === 'logo_Setup') {
					// Define the upload directory as an absolute path
					$uploadDir = __DIR__ . '/../../upload/logo/';
					// Ensure the upload directory exists
					if (!is_dir($uploadDir)) {
						mkdir($uploadDir, 0755, true); // Create the directory if it doesn't exist
					}
					// Validate file upload
					if (isset($_FILES['logoFile']) && $_FILES['logoFile']['error'] === UPLOAD_ERR_OK) {
						$file = $_FILES['logoFile'];
						// Validate file type (only allow images)
						$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
						if (!in_array($file['type'], $allowedTypes)) {
							echo fu_json_results(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and GIF are allowed.']);
							exit;
						}
						// Validate file size (e.g., limit to 2MB)
						$maxFileSize = 2 * 1024 * 1024; // 2MB
						if ($file['size'] > $maxFileSize) {
							echo fu_json_results(['success' => false, 'message' => 'File size exceeds the maximum limit of 2MB.']);
							exit;
						}
						// Generate a unique filename to avoid overwriting
						$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
						$newFileName = 'logo_' . uniqid() . '.' . $extension;
						$destination = $uploadDir . $newFileName;
						// Retrieve the old logo path from the database
						$stmt = $mysqli->prepare("SELECT website_logo FROM boom_setting WHERE id = ?");
						if (!$stmt) {
							echo fu_json_results(['success' => false, 'message' => 'Failed to prepare SQL statement for retrieving old logo.']);
							exit;
						}
						$settingId = 1; // ID of the record in `boom_setting`
						$stmt->bind_param("i", $settingId);
						$stmt->execute();
						$result = $stmt->get_result();
						$oldLogoPath = '';
						if ($row = $result->fetch_assoc()) {
							$oldLogoPath = $row['website_logo'];
						}
						$stmt->close();
						// Move the uploaded file to the destination
						if (move_uploaded_file($file['tmp_name'], $destination)) {
							// Delete the old logo file if it exists
							if (!empty($oldLogoPath)) {
								// Resolve the full server-side file path
								$oldFilePath = __DIR__ . '/../../' . ltrim($oldLogoPath, '/');
								// Debugging: Log the resolved file path
								error_log("Resolved old file path: " . $oldFilePath);
								// Check if the file exists and delete it
								if (file_exists($oldFilePath) && is_file($oldFilePath)) {
									unlink($oldFilePath); // Delete the old file
									error_log("Old file deleted: " . $oldFilePath);
								} else {
									error_log("Old file not found or invalid: " . $oldFilePath);
								}
							}
							// Update the database with the new logo path
							$newLogoPath = 'upload/logo/' . $newFileName;
							// Prepare and execute the SQL query
							$stmt = $mysqli->prepare("UPDATE boom_setting SET website_logo = ? WHERE id = ?");
							if (!$stmt) {
								echo fu_json_results(['success' => false, 'message' => 'Failed to prepare SQL statement.']);
								exit;
							}
							$stmt->bind_param("si", $newLogoPath, $settingId);
							$stmt->execute();
							if ($stmt->affected_rows > 0) {
								echo fu_json_results([
									'success' => true,
									'message' => 'Logo uploaded and database updated successfully!',
									'url' => $data['domain'] . '/' . $newLogoPath,
									'update_cache' => boomCacheUpdate(),
								]);
							} else {
								echo fu_json_results(['success' => false, 'message' => 'Failed to update the database.']);
							}
							$stmt->close();
							exit;
						} else {
							echo fu_json_results(['success' => false, 'message' => 'Failed to move the uploaded file.']);
							exit;
						}
					} else {
						echo fu_json_results(['success' => false, 'message' => 'No file uploaded or an error occurred during upload.']);
						exit;
					}
		 }else {
				echo fu_json_results(['success' => false, 'message' => 'Invalid request parameters.']);
        exit;
    }
		
	}

}
?>