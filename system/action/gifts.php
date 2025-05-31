<?php
$gift_array = array();
$data_array = array();
    // Function to handle errors
function handleError($code, $message) {
   header("Content-type: application/json");
    echo json_encode(['code' => $code, 'message' => $message]);
     exit();
}

if ($f == 'gifts') {
	if($s == 'gifts_access') {
		if (isset($_POST['set_gifts_access']) && boomAllow($cody['can_manage_addons'])) {
			// Sanitize and validate the gift access value
			$gifts_access = (int) $_POST['set_gifts_access']; // Assuming it's an integer
			$stmt = $mysqli->prepare("UPDATE boom_addons SET addons_access = ? WHERE addons = 'gifts'");
			$stmt->bind_param("i", $gifts_access); // Bind as integer
			$update = $stmt->execute();
			if ($update) {
				echo 5;
				die();
			} else {
				// Optional: handle failure scenario here
				echo "Error updating gifts access.";
				die();
			}
		}
		if (isset($_POST['set_use_gift']) && boomAllow($cody['can_manage_addons'])) {
			// Sanitize and validate the use gift value
			$use_gift = (int) $_POST['set_use_gift']; // Assuming it's an integer
			$stmt = $mysqli->prepare("UPDATE boom_setting SET use_gift = ? WHERE id = 1");
			$stmt->bind_param("i", $use_gift); // Bind as integer
			$update = $stmt->execute();
			if ($update) {
				echo 5;
				die();
			} else {
				// Optional: handle failure scenario here
				echo "Error updating use gift setting.";
				die();
			}
		}
	}
	if($s == 'search_box') {
		if (isset($_POST['search_box'], $_POST['q'])) {
			// Set the response type to JSON and return the result
			header("Content-type: application/json");			
			// Sanitize the search input
			$text = trim($_POST['q']); // Trim to avoid leading/trailing spaces
			$text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); // Convert special characters to HTML entities
			// Validate the input (e.g., checking if it's non-empty and not too long)
			if (!empty($text) && strlen($text) <= 255) {
				// Assuming runGiftSearch() takes sanitized input and returns a result
				$search_query = runGiftSearch($text);
				echo json_encode($search_query);
				exit(); 
			} else {
				// Optional: return an error message if validation fails
				echo json_encode(['error' => 'Invalid search query']);
				exit();
			}
		}
	}
	if($s === 'send_gift') {
		header("Content-type: application/json");
		// Check for conditions that prevent sending a gift
		if (checkFlood()) {
			echo json_encode(['status' => 100, 'msg' => 'Flood detected']);
			exit();
		}
		if (muted() || isRoomMuted($data)) {
			echo json_encode(['status' => 100, 'msg' => 'User is muted or room is muted']);
			exit(); // User is muted or room is muted
		}
		if (!canGift()) {
			echo json_encode(['status' => 100, 'msg' => 'User does not have permission to send gifts']);
			exit(); // User doesn't have permission to send gifts
		}
		// Validate required POST parameters
		if (isset($_POST['type'], $_POST['target'], $_POST['gift_id'])) {
			// Sanitize inputs
			$gift_array = [
				'type' => escape($_POST['type']),
				'target_id' => (int)$_POST['target'],  // Cast target_id as integer to avoid type issues
				'gift_id' => (int)$_POST['gift_id'],  // Cast gift_id as integer
			];
			// Fetch target user details safely
			$gift_array['target'] = userDetails($gift_array['target_id']);
			if (empty($gift_array['target'])) {
				echo json_encode(['status' => 400, 'msg' => 'Target user not found']);
				exit(); // Target user not found
			}
			// Prevent sending gifts to self
			if (mySelf($gift_array['target']['user_id'])) {
				echo json_encode(['status' => 400, 'msg' => 'Cannot send a gift to yourself']);
				exit(); // Cannot send a gift to oneself
			}
			// Get sender details
			$my_points = (int)$data['user_gold'];  // Ensure it's treated as an integer
			$my_userId = (int)$data['user_id'];   // Ensure it's treated as an integer
			$receiver_id = (int)$gift_array['target']['user_id'];  // Ensure it's treated as an integer
			// Fetch gift details safely
			$compare_credit = gift_list_byId($gift_array['gift_id']);
			if ($compare_credit === null) {
				echo json_encode(['status' => 400, 'msg' => 'Invalid gift']);
				exit(); // Gift not found or invalid
			}
			$gift_thumb = $compare_credit['gift_url'];
			$gift_price = (int)$compare_credit['gift_cost'];  // Ensure gift price is treated as integer
			// Check if the user has exceeded the gift limit in the last minute
			$current_time = time();
			$time_limit = 60; // 1 minute
			$gift_limit = 1; // Max 3 gifts per minute
			// Initialize gift history if not already set in the session
			if (!isset($_SESSION['gift_history'])) {
				$_SESSION['gift_history'] = [];
			}
			// Remove old gifts (older than 1 minute)
			$_SESSION['gift_history'] = array_filter($_SESSION['gift_history'], function($timestamp) use ($current_time, $time_limit) {
				return ($current_time - $timestamp) < $time_limit;
			});
			// Check how many gifts were sent in the last minute
			if (count($_SESSION['gift_history']) >= $gift_limit) {
				echo json_encode(['status' => 400, 'msg' => 'You have reached the gift limit for this minute.']);
				exit(); // Limit reached
			}
			// Add the current gift timestamp to the session history
			$_SESSION['gift_history'][] = $current_time;
			// Check if the gift is valid and affordable
			if ($gift_price <= $my_points) {
				// Update points for sender and receiver
				$sum_points = $my_points - $gift_price;
				$divide_price = ($gift_price / 2) + (int)$gift_array['target']['user_gold'];  // Ensure target gold is treated as integer
				// Use prepared statements to update sender and receiver points securely
				$update_sender = $mysqli->prepare("UPDATE `boom_users` SET `user_gold` = ? WHERE `user_id` = ?");
				$update_sender->bind_param('ii', $sum_points, $my_userId);
				$update_sender->execute();
				$update_receiver = $mysqli->prepare("UPDATE `boom_users` SET `user_gold` = ? WHERE `user_id` = ?");
				$update_receiver->bind_param('ii', $divide_price, $receiver_id);
				$update_receiver->execute();
				// Record the gift transaction
				$insert_gift_record = [
					"target_id" => $receiver_id,
					"gift_id" => $gift_array['gift_id'],
					"room_id" => (int)$data['user_roomid'], // Ensure room_id is treated as integer
					"hunter_id" => $my_userId,
				];
				$update_record = record_gift($insert_gift_record);
				// Notify chat of the gift
				$content = giftContentSendedOk($compare_credit, $data['user_name'], $gift_array['target']['user_name']);
				systemPostChat($data['user_roomid'], $content);
				// Send notification about the gift
				boomNotify("gift", [
					"hunter" => $my_userId,
					"target" => $receiver_id,
					"source" => 'gift',
					"custom" => $compare_credit['gift_title'],
					"icon" => 'gift'
				]);
				// Prepare response data
				$data_array['status'] = 200;
				$data_array['gift_data'] = $compare_credit;
				$data_array['msg'] = 'The gift has been sent successfully';
				$data_array['cl'] = 'success';
			} else {
				echo json_encode(['status' => 300, 'msg' => 'You do not have enough credit.']);
				exit(); // Not enough credit
			}
			// Send response as JSON
			echo json_encode($data_array);
			exit();
		}
	}
	if ($s == 'public_box') {
		// Sanitize the input if needed
		// Ensure that $data contains valid information before using it
		if (isset($data) && !empty($data)) {
			// Fetch the content using the template
			$res['content'] = boomTemplate('gifts/public_gift_panel', $data);
			$res['status'] = 1;  // Success status
		} else {
			// Handle the case where $data is invalid or missing
			$res['status'] = 0;
			$res['msg'] = 'Invalid data provided for public gift panel.';
		}
		// Set response type and return the JSON response
		header("Content-type: application/json");
		echo json_encode($res);
		exit();
	}
	if($s == 'my_gift') {
		// Sanitize the input if needed
		// Ensure that $data contains valid information before using it
		if (isset($data) && !empty($data)) {
			// Fetch the content using the template
			$res['content'] = boomTemplate('gifts/my_gift', $data);
			$res['status'] = 1;  // Success status
		} else {
			// Handle the case where $data is invalid or missing
			$res['status'] = 0;
			$res['msg'] = 'Invalid data provided for my gift.';
		}
		// Set response type and return the JSON response
		header("Content-type: application/json");
		echo json_encode($res);
		exit();
	}
	if($s == 'getUserGift') {
		// Sanitize the input
		$user_id = isset($_POST['user_id']) ? escape($_POST['user_id']) : '';
		// Validate the user_id
		if (!empty($user_id)) {
			// Assuming boomTemplate() is a function to fetch user gift data
			$res['content'] = boomTemplate('gifts/my_gift', $user_id);
			$res['status'] = 1; // Success
		} else {
			// Handle the case where user_id is empty or null
			$res['status'] = 0;
			$res['msg'] = 'User ID is missing or invalid.';
		}
		// Set response type and return the JSON response
		header("Content-type: application/json");
		echo json_encode($res);
		exit();
	}
	if ($s == 'gift_panel') {
		// Sanitize or validate the $data if needed
		if (isset($data) && !empty($data)) {
			// Generate the content using the template
			$res['content'] = boomTemplate('gifts/gift_panel', $data);
			$res['status'] = 1;  // Success status
		} else {
			// Handle the case where $data is invalid or missing
			$res['status'] = 0;
			$res['msg'] = 'Invalid data provided for gift panel.';
		}
		// Set response type and return the JSON response
		header("Content-type: application/json");
		echo json_encode($res);
		exit();
	}
 
    if ($s == 'admin_save_gift') {
    if (isset($_POST['save_gift'], $_POST['gift_title'])) {
        // Sanitize inputs
        $gift_id = escape($_POST['save_gift']);      // Gift ID
        $gift_title = escape($_POST['gift_title']);  // Gift title
        $gift_rank = escape($_POST['gift_rank']);    // Gift rank
        $gift_gold = escape($_POST['gift_gold']);    // Gift cost (gold)
        // Initialize file variables
        $thumb_file_path = '';
        $gif_file_path = '';
        $uploadDir = 'system/gifts/files/media/gift_box/'; // Directory for thumbnail files
        $gifUploadDir = 'system/gifts/files/media/gift_box/gif/'; // Directory for GIF files
        // Fetch the current file paths from the database
        $db->where('id', $gift_id);
        $existingGift = $db->getOne('gift', ['gift_image', 'gif_file']);
        // Ensure the GIF directory exists
        if (!file_exists($gifUploadDir)) {
            mkdir($gifUploadDir, 0755, true);
        }
        // Handle thumb_file upload if submitted
        if (isset($_FILES['thumb_file']) && $_FILES['thumb_file']['error'] === UPLOAD_ERR_OK) {
            // File properties for thumb_file
            $fileTmpPath = $_FILES['thumb_file']['tmp_name'];
            $fileType = $_FILES['thumb_file']['type'];
            $fileSize = $_FILES['thumb_file']['size'];
            // Define allowed file types and size limits
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $maxFileSize = 5 * 1024 * 1024; // 5 MB
            // Validate file type
            if (!in_array($fileType, $allowedTypes)) {
                handleError(1, 'Invalid thumbnail file type.');
            }
            // Validate file size
            if ($fileSize > $maxFileSize) {
                handleError(2, 'Thumbnail file size exceeds the limit.');
            }
            // Generate a unique file name
            $fileName = 'thumb_' . uniqid() . '.' . pathinfo($_FILES['thumb_file']['name'], PATHINFO_EXTENSION);
            // Define the full path for the uploaded file
            $thumb_file_path = $uploadDir . $fileName;
            // Move the uploaded file to the designated directory
            if (move_uploaded_file($fileTmpPath, $thumb_file_path)) {
                // Delete the old thumbnail if it exists
                if (!empty($existingGift['gift_image'])) {
                    $oldThumbPath = $uploadDir . basename($existingGift['gift_image']);
                    if (file_exists($oldThumbPath)) {
                        unlink($oldThumbPath); // Delete the old file
                    }
                }
            } else {
                handleError(3, 'Failed to move the thumbnail file.');
            }
            // Prepare the file path for database storage
            $thumb_file_path = 'gift_box/' . $fileName;
        }
        // Handle gif_file upload if submitted
        if (isset($_FILES['gif_file']) && $_FILES['gif_file']['error'] === UPLOAD_ERR_OK) {
            // File properties for gif_file
            $fileTmpPath = $_FILES['gif_file']['tmp_name'];
            $fileType = $_FILES['gif_file']['type'];
            $fileSize = $_FILES['gif_file']['size'];
            // Define allowed file types and size limits for GIFs
            $allowedGifTypes = ['image/gif'];
            $maxFileSize = 10 * 1024 * 1024; // 10 MB (as GIFs might be larger)
            // Validate file type
            if (!in_array($fileType, $allowedGifTypes)) {
                handleError(1, 'Invalid GIF file type.');
            }
            // Validate file size
            if ($fileSize > $maxFileSize) {
                handleError(2, 'GIF file size exceeds the limit.');
            }
            // Generate a unique file name
            $gifFileName = 'gif_' . uniqid() . '.' . pathinfo($_FILES['gif_file']['name'], PATHINFO_EXTENSION);
            // Define the full path for the uploaded GIF file
            $gif_file_path = $gifUploadDir . $gifFileName;
            // Move the uploaded file to the designated directory
            if (move_uploaded_file($fileTmpPath, $gif_file_path)) {
                // Delete the old GIF if it exists
                if (!empty($existingGift['gif_file'])) {
                    $oldGifPath = $gifUploadDir . basename($existingGift['gif_file']);
                    if (file_exists($oldGifPath)) {
                        unlink($oldGifPath); // Delete the old file
                    }
                }
            } else {
                handleError(3, 'Failed to move the GIF file.');
            }
            // Prepare the GIF file path for database storage
            $gif_file_path = 'gift_box/gif/' . $gifFileName;
        }
        // Prepare data for updating the gift
        $updata = Array (
            'gift_title' => $gift_title,
            'gift_cost' => $gift_gold,
            'gift_rank' => $gift_rank,
            'time' => time(),
        );
        // Add file paths if new files were uploaded
        if (!empty($thumb_file_path)) {
            $updata['gift_image'] = $thumb_file_path;
        }
        if (!empty($gif_file_path)) {
            $updata['gif_file'] = $gif_file_path;
        }
        // Update the gift in the database
        $db->where('id', $gift_id);
        $update = $db->update('gift', $updata);
        // Check if the update was successful
        if ($update === true) {
            $gift_array['code'] = 200;
            $gift_array['id'] = $gift_id;
            $gift_array['message'] = 'Gift Updated successfully';
            $gift_array['data'] = boomTemplate('element/admin_gift', giftDetails($gift_id));
        }
        // Return JSON response
        header("Content-type: application/json");
        echo json_encode($gift_array);
        exit();
    }
}
  

// Check if the script should handle file uploads
if ($s == 'admin_add_gift') {
    // Define the directory to store uploaded files
    $uploadDir = 'system/gifts/files/media/gift_box/';
    // Create the upload directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    // Check if a file is uploaded
    if (isset($_FILES['thumb_file']) && $_FILES['thumb_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['thumb_file']['tmp_name'];
        $fileSize = $_FILES['thumb_file']['size'];
        $fileType = $_FILES['thumb_file']['type'];
        // Define the file name with a unique identifier and a fixed prefix
        $fileName = 'thumb_' . uniqid() . '.' . pathinfo($_FILES['thumb_file']['name'], PATHINFO_EXTENSION);
        // Define allowed file types and size limits
        $allowedTypes = ['image/jpeg', 'image/png'];
        $maxFileSize = 5 * 1024 * 1024; // 5 MB
        if (!in_array($fileType, $allowedTypes)) {
            handleError(1, 'Invalid file type jpeg or png only in thumb file');
        }
        if ($fileSize > $maxFileSize) {
            handleError(2, 'File size exceeds the limit.');
        }
        // Move the file to the desired directory
        $destPath = $uploadDir . $fileName;
        if (move_uploaded_file($fileTmpPath, $destPath)) {
            // Prepare the gift data
            $add_gift_array = [
                "gift_title" => "New Gift",
                "gift_image" => "gift_box/" . $fileName,
                "gift_method" => '1',
                "gift_cost" => '100',
                "gift_rank" => '1',
                "video_file" => '',
                "gif_file" => '',
                "time" => time(),
            ];
            // Insert the gift data into the database
            $add_gift_query = $db->insert('gift', $add_gift_array);
            if ($add_gift_query) {
                // Retrieve the last inserted ID
                $lastInsertId = $add_gift_query;
                header("Content-type: application/json");
                echo json_encode([
                    'code' => 5,
                    'last_id' => $lastInsertId,
                    'data' =>  boomTemplate('element/admin_gift', giftDetails($lastInsertId)),
                ]);
            } else {
                handleError(5, 'Failed to insert data into the database.');
            }
        } else {
            handleError(3, 'Failed to move the uploaded file.');
        }
    } else {
        handleError(4, 'No file uploaded or there was an upload error.');
    }
}
if ($s == 'admin_delete_gift') {
    // Ensure that the gift ID is provided and validate it
    if (isset($_POST['gift_id']) && !empty($_POST['gift_id'])) {
        // Sanitize the gift_id and ensure it is an integer
        $giftId = intval($_POST['gift_id']);
        // Check if giftId is valid (greater than 0)
        if ($giftId > 0) {
            // Fetch the gift data to get the file paths for both gift_image and gif_file
            $gift = $db->where('id', $giftId)->getOne('gift', ['gift_image', 'gif_file']);
            if ($gift) {
                // Define the directories for uploaded files
                $uploadDir = 'system/gifts/files/media/gift_box/';
                $gifUploadDir = 'system/gifts/files/media/gift_box/gif/';
                // Prepare file paths for both thumbnail and gif
                $thumbFilePath = $uploadDir . $gift['gift_image'];
                $gifFilePath = $gifUploadDir . $gift['gif_file'];
                // Delete the thumbnail file if it exists
                if (!empty($gift['gift_image']) && file_exists($thumbFilePath)) {
                    if (!unlink($thumbFilePath)) {
                        handleError(8, 'Failed to delete thumbnail image.');
                        exit;
                    }
                }
                // Delete the gif file if it exists
                if (!empty($gift['gif_file']) && file_exists($gifFilePath)) {
                    if (!unlink($gifFilePath)) {
                        handleError(9, 'Failed to delete gif file.');
                        exit;
                    }
                }
                // Delete the gift record from the database
                $deleteGiftResult = $db->where('id', $giftId)->delete('gift');
                $deleteUserGiftResult = $db->where('gift', $giftId)->delete('users_gift');
                // If deletion from database was successful, return success response
                if ($deleteGiftResult || $deleteUserGiftResult) {
                    header("Content-type: application/json");
                    echo json_encode([
                        'code' => 5,
                        'message' => 'Gift has been deleted successfully.',
                        'thumbFilePath' => $thumbFilePath,
                        'gifFilePath' => $gifFilePath
                    ]);
                } else {
                    handleError(10, 'Failed to delete the gift from the database.');
                }
            } else {
                handleError(6, 'Gift not found.');
            }
        } else {
            handleError(7, 'Invalid gift ID.');
        }
    } else {
        handleError(7, 'No gift ID provided.');
    }
}

}
?>