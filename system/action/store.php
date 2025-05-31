<?php
$array_data = array();
function Edit_Pack($pack_id){
   global $db,$data,$mysqli;
    $store = [];
    $pack_data = FU_store_marketById($pack_id);
    $pack_name = escape(trim($_POST['packge_name']));
    $pack_amount = escape(trim($_POST['packge_amount'] ?? '0'));
    $pack_type =  isset($_POST['packge_type']) ? escape(trim($_POST['packge_type'])) : $pack_data['type'];
    $pack_rank =  isset($_POST['pack_rank']) ? escape(trim($_POST['pack_rank'])) : $pack_data['user_rank'];
    $pack_discount = escape(trim($_POST['packge_discount'] ?? '0'));
    $pack_price =  escape(trim($_POST['packge_price'])); 
    $pack_status =  escape(trim($_POST['packge_status']));
    $prim_end = isset($_POST['prim_end']) ? escape(trim($_POST['prim_end'])) : $pack_data['prim_end'];
	$rank_end = escape(trim($_POST['rank_end'] ?? '0'));
    $store['avatar_path'] = $pack_data['image'];
    $store['error'] = null;
    $store['msg'] = null;
    $store['status'] = 100;
     // Handle file upload
    if (isset($_FILES['avatar_pack']) && $_FILES['avatar_pack']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $fileInfo = pathinfo($_FILES['avatar_pack']['name']);
        $fileExt = strtolower($fileInfo['extension']);
        if (in_array($fileExt, $allowed)) {
            $uploadDir = 'upload/store/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $currentAvatar = $pack_data['image'];
            // Delete the existing avatar if it exists and is not the default one
            if ($currentAvatar && file_exists($currentAvatar) && $currentAvatar !== 'upload/store/default_pack.png') {
                unlink($currentAvatar);
            }

            $newFileName = uniqid('avatar_pack_', true) . '.' . $fileExt;
            $uploadFile = $uploadDir . $newFileName;

            if (move_uploaded_file($_FILES['avatar_pack']['tmp_name'], $uploadFile)) {
                $store['avatar_path'] = $uploadFile; // Set avatar path for database
                 $store['status'] = 200;
            } else {
                $store['msg'] = "Error uploading avatar.";
                 $store['status'] = 300;
                exit;
            }
        } else {
            $store['msg'] = "Invalid file type. Allowed types: " . implode(', ', $allowed);
            $store['status'] = 400;
            exit;
        }
    }
    $pack_data = Array (
    	'type' => $pack_type,
        'pack_name' => $pack_name,
    	'price' => $pack_price,
    	'p_amounts' => $pack_amount,
    	'verified_badge' => 0,
    	'discount' => $pack_discount,
    	'image' => $store['avatar_path'],
    	'color' => 0,
    	'description' => 0,
    	'status' => $pack_status,
    	'user_rank' => $pack_rank,
    	'rank_end' => $rank_end,
    	'prim_end' => $prim_end,
    );
    $db->where('id', $pack_id);
    $query = $db->update('store', $pack_data);
    if($query){
         $store['msg']= "Package Edit Successfully";
         $store['status'] = 200;
    }    
    return $store;
}
function addNewPack($pack_name, $pack_price, $pack_type) {
    global $db, $data, $mysqli;
    $store = [];
    
    // Escape user input
    $pack_name = escape(trim($pack_name));
    $pack_price = escape(trim($pack_price));
    $pack_amount = isset($_POST['packge_amount']) ? escape(trim($_POST['packge_amount'])) : 0;
    $pack_type = isset($_POST['packge_type']) ? escape(trim($_POST['packge_type'])) : '';
    $pack_rank = isset($_POST['pack_rank']) ? escape(trim($_POST['pack_rank'])) : 0;
    $pack_discount = isset($_POST['packge_discount']) ? escape(trim($_POST['packge_discount'])) : 0;
    $prim_end =  escape(trim($_POST['prim_end']));
    $store['avatar_path'] = 'upload/store/default_pack.png'; // Default image
    $store['status'] = 100;

    // Handle file upload
    if (isset($_FILES['avatar_pack']) && $_FILES['avatar_pack']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $fileInfo = pathinfo($_FILES['avatar_pack']['name']);
        $fileExt = strtolower($fileInfo['extension']);
        
        if (in_array($fileExt, $allowed)) {
            $uploadDir = 'upload/store/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $newFileName = uniqid('avatar_pack_', true) . '.' . $fileExt;
            $uploadFile = $uploadDir . $newFileName;
            
            if (move_uploaded_file($_FILES['avatar_pack']['tmp_name'], $uploadFile)) {
                $store['avatar_path'] = $uploadFile;
                $store['status'] = 200;
            } else {
                return ['status' => 300, 'msg' => 'Error uploading avatar.'];
            }
        } else {
            return ['status' => 400, 'msg' => 'Invalid file type. Allowed types: ' . implode(', ', $allowed)];
        }
    }

    // Insert package data
    $pack_data = [
        'type' => $pack_type,
        'pack_name' => $pack_name,
        'price' => $pack_price,
        'p_amounts' => $pack_amount,
        'verified_badge' => 0,
        'discount' => $pack_discount,
        'image' => $store['avatar_path'],
        'color' => 0,
        'description' => 0,
        'status' => 1,
        'user_rank' => $pack_rank,
		'prim_end' => $prim_end,
    ];

    $query = $db->insert('store', $pack_data);
    if ($query) {
        return [
            'status' => 200,
            'msg' => 'Package Added Successfully',
            'lastInsertedId' => $db->getInsertId()
        ];
    }

    return ['status' => 500, 'msg' => 'Database error.'];
}


function buy_premium_pack($pack_id){
	global $mysqli,$data,$db,$lang;
	$pack_id = sanitizeOutput(trim($_POST['id']));
	$my_gold = $data['user_gold'];
	$sender_id = $data['user_id'];
	$pack_info =  FU_store_marketById($pack_id);
	$prim_end = Fu_premiumNewTime($pack_info['prim_end'], $data);
	if (!empty($pack_info)) {
		$pack_price = $pack_info['price'];
		if ($pack_price > $my_gold) {
            $array_data['message'] = [
                "pack_price" => $pack_price,
                "pack_id" => $pack_id,
                "buyer" => $data['user_name'],
                "time" => time(),
                "status" => 150,
                "pack_name" => $pack_info['pack_name'],
                "alert" => ' Your wallet needs to be charged..😒😒',
            ];
        }else{
            $array_data['message'] = [
                        "pack_price" => $pack_price,
                        "pack_id" => $pack_id,
                        "buyer" => $data['user_name'],
                        "time" => time(),
						"status" => 200,
                        "pack_name" => $pack_info['pack_name'],
                        "alert" => 'Your Membership is upgraded to  '.$pack_info['pack_name'].'..😉😉'
                    ];	
            $array_data['user_gold'] = $data['user_gold'] - $pack_price;
            $array_data['user_action'] = $data['user_action'] + 1;
			$array_data['user_query'] = cl_update_user_data($sender_id,array(
						"user_gold" => $array_data['user_gold'],
						"user_prim" => $pack_info['prim_end'],
						"prim_end" => $prim_end,
					));	
            $content_data = [
                        "pack_price" => $pack_price,
                        "pack_id" => $pack_id,
                        "buyer" => $data['user_name'],
                        "time" => time(),
                        "pack_name" => $pack_info['pack_name'],
						"user_prim" =>	$pack_info['prim_end'],
                        "content" => $data['user_name'].' Upgrade to '.$pack_info['pack_name'],
            ];
					
			$content2 =	boomTemplate('store/premium_up', $content_data);			
			systemPostChat($data['user_roomid'], $content2, array('type'=> 'system__action'));					
			$message = 'Your membership has been upgraded to Premium '.$pack_info['prim_end'].' days';
			postPrivate($data['system_id'], $data['user_id'], $message);
			$array_data['code'] = 1;
		}
	}
	return  $array_data;
}
function buy_gold_pack($pack_id){
	global $mysqli,$data,$db,$lang;
    $pack_id = sanitizeOutput(trim($_POST['id']));
    $my_wallet = $data['wallet'];
    $sender_id = $data['user_id'];

    $compare_credit = $mysqli->prepare("SELECT * FROM `boom_store` WHERE `id` = ?");
    $compare_credit->bind_param('i', $pack_id);
    $compare_credit->execute();
    $result = $compare_credit->get_result();

    if ($result->num_rows > 0) {
        $pack_info = $result->fetch_array(MYSQLI_ASSOC);
        $pack_price = $pack_info['price'];
        $pack_name = $pack_info['pack_name'];
        $p_amounts = $pack_info['p_amounts'];

        if ($pack_price > $my_wallet) {
            $array_data['message'] = [
                "pack_price" => $pack_price,
                "pack_id" => $pack_id,
                "buyer" => $data['user_name'],
                "time" => time(),
                "pack_name" => $pack_name,
                "alert" => ' Your wallet needs to be charged..😒😒',
            ];
             $array_data['status'] = 150;
        } else {
            $mysqli->begin_transaction(); // Start transaction
            try {
                // Deduct from user's wallet
                $update_wallet = $mysqli->prepare("UPDATE `boom_users` SET `wallet` = wallet - ? WHERE `user_id` = ?");
                $update_wallet->bind_param('ii', $pack_price, $sender_id);
                $update_wallet->execute();

                // Update user's points
                $update_points = $mysqli->prepare("UPDATE boom_users SET `user_gold` = user_gold + ? WHERE `user_id` = ?");
                $update_points->bind_param('ii', $p_amounts, $sender_id);
                $update_points->execute();

                // Increment package's sell counter
                $increment_sell = $mysqli->prepare("UPDATE `boom_store` SET `sell_counter` = sell_counter + 1 WHERE `id` = ?");
                $increment_sell->bind_param('i', $pack_id);
                $increment_sell->execute();
                $local_trans = generateRandomString(6);
                // Record the transaction
                $trans_data = [
                    "transaction_id" => $local_trans,
                    "hunter" => $sender_id,
                    "type" => $pack_info['type'],
                    "currency" => $pack_info['type'],
                    "payer_name" => $data['user_name'],
                    "amount" => $p_amounts,
                    "notes" => $pack_price .$data['currency'].' has been deducted from your wallet for Package -> ' . $pack_name,
                    "package_id" => $pack_id,
                ];
                $insert_transaction = $db->insert('payments', $trans_data);
                if ($insert_transaction) {
                    $mysqli->commit(); // Commit transaction
                    $array_data['status'] = 200;
                    $array_data['message'] = [
                        "pack_price" => $pack_price,
                        "pack_id" => $pack_id,
                        "buyer" => $data['user_name'],
                        "time" => time(),
                        "pack_name" => $pack_name,
                        "alert" => 'ADD NEW ' . $p_amounts . ' Gold To your account..😉😉'
                    ];
                    $array_data['new_wallet'] = $my_wallet - $pack_price;
                    $array_data['user_gold'] = $data['user_gold'] + $p_amounts;
                 //boomNotify("gold_share", array("target" => $sender_id, "source" => 'gold' ,"custom" => $p_amounts,"icon" => 'gold'));
                 $content = $data['user_name']. '<font color="green"> '.$p_amounts.' '.$lang['gold'].'</font>';
                 boomNotify('add_gold', array('target'=> $sender_id, 'custom'=> $content,"icon" => 'gold'));
                } else {
                    throw new Exception('Transaction failed');
                }
            } catch (Exception $e) {
                $mysqli->rollback(); // Rollback transaction on error
                $array_data['status'] = 700;
                $array_data['message'] = 'Contact support. Code 700';
            }
        }
    }
    return $array_data;	
}
function checkExistingPackByRank($user_rank) {
    global $db;
		$db->where ("user_rank", $user_rank);
		$rank = $db->getOne("store");
		if ($db->count > 0){
			return true; 	
		}else{
			return false; 
		}

}
function buy_rank($pack_id){
	global $mysqli,$data,$db,$lang;
    $pack_id = sanitizeOutput(trim($_POST['id']));
    $my_wallet = $data['user_gold'];
    $sender_id = $data['user_id'];
	$pack_info =  FU_store_marketById($pack_id);
	$sup_end = Fu_premiumNewTime($pack_info['rank_end'], $data);
	if (!empty($pack_info)) {
	     $pack_price = $pack_info['price'];
        $pack_name = $pack_info['pack_name'];
        $p_amounts = $pack_info['p_amounts'];
        $rank = $pack_info['user_rank'];
		if ($pack_price > $my_wallet) {
            $array_data['message'] = [
                "pack_price" => $pack_price,
                "pack_id" => $pack_id,
                "buyer" => $data['user_name'],
                "time" => time(),
                "pack_name" => $pack_name,
                "alert" => ' Your Gold needs to be charged..😒😒',
                "status" => 150,
            ];
        } else {
                $local_trans = generateRandomString(6);
                // Record the transaction
                $trans_data = [
                    "transaction_id" => $local_trans,
                    "hunter" => $sender_id,
                    "type" => $pack_info['type'],
                    "currency" => $pack_info['type'],
                    "payer_name" => $data['user_name'],
                    "amount" => $p_amounts,
                    "notes" => $pack_price .' has been deducted from your Gold for Package -> ' . $pack_name,
                    "package_id" => $pack_id,
                ];
                $insert_transaction = $db->insert('payments', $trans_data);		
				if ($insert_transaction) {
                    $array_data['message'] = [
                        "pack_price" => $pack_price,
                        "pack_id" => $pack_id,
                        "buyer" => $data['user_name'],
                        "time" => time(),
                        "pack_name" => $pack_name,
                        "alert" => 'Upgrade completed successfully',
                        "status" => 200,
                    ];

                    $array_data['user_gold'] = $data['user_gold'] - $p_amounts;
					//boomNotify("gold_share", array("target" => $sender_id, "source" => 'gold' ,"custom" => $p_amounts,"icon" => 'gold'));
					$content = $data['user_name']. '<font color="red"> '.$p_amounts.' '.$lang['gold'].' '.$pack_name.'</font>';
					boomNotify('remove_gold', array('target'=> $sender_id, 'custom'=> $content,"icon" => 'gold'));
					userReset($data, $rank);
					boomNotify("rank_change", ["target" => $sender_id, "source" => "rank_change", "rank" => $rank]);
					if (isStaff($rank)) {
						$mysqli->query("UPDATE boom_users SET room_mute = '0', user_private = 1, user_mute = 0, user_regmute = 0 WHERE user_id = '" . $sender_id . "'");
						$mysqli->query("DELETE FROM boom_room_action WHERE action_user = '" . $sender_id . "'");
						$mysqli->query("DELETE FROM boom_ignore WHERE ignored = '" . $sender_id . "'");
					}
					boomConsole("change_rank", ["target" => $data["user_id"], "rank" => $rank]);
					$mysqli->query("UPDATE `boom_store` SET `sell_counter` = sell_counter + 1 WHERE `id` = '$pack_id'");
                    //$content2 = '<div color="red" class="upgrade_rank_msg"> '.$data['user_name']. ' Upgrade to '.$pack_name.' successful </div>';
                    $content_data = [
                        "pack_price" => $pack_price,
                        "pack_id" => $pack_id,
                        "buyer" => $data['user_name'],
                        "time" => time(),
                        "pack_name" => $pack_name,
						"user_rank" =>	$rank,
                        "content" => $data['user_name'].' Upgrade to '.$pack_name,
                    ];

					$array_data['query'] = cl_update_user_data($sender_id,array(
						"user_gold" => $array_data['user_gold'],
						"user_rank" => $rank,
					));
					$content2 =	boomTemplate('store/rank_up', $content_data);			
                    systemPostChat($data['user_roomid'], $content2, array('type'=> 'system__action'));
					
                } else {
                    throw new Exception('Transaction failed');
					$array_data['status'] = 700;
					$array_data['message'] = 'Contact support. Code 700';
					
                }				
		}			
		
	}
    return $array_data;	
}
function generateStoreCard($packId, $packName, $backgroundImage, $logoImage, $price) {
    $fileExtension = strtolower(pathinfo($logoImage, PATHINFO_EXTENSION));
    return '
    <label for="frame_pack_' . htmlspecialchars($packId) . '" data-pack-name="' . htmlspecialchars($packId) . '" class="store_card" 
           style="background-image: url(' . $backgroundImage . ');">
        <input data-ext="' . $fileExtension . '" data-type="frame_tab" type="radio" 
               data-id="' . htmlspecialchars($packId) . '" name="pack_selection" 
               id="frame_pack_' . htmlspecialchars($packId) . '" 
               value="' . htmlspecialchars($price) . '" style="display:none;">
        <div class="store_content store_content_rank">
            <div class="store_logo">
                <img src="' . $logoImage . '" alt="' . htmlspecialchars($packName) . '" class="image">
            </div>
            <div class="store_main_text">
                <p><i class="ri-shield-star-line"></i>' . htmlspecialchars($packId) . '</p>
            </div>
            <div class="pack_amount"><i class="ri-copper-diamond-fill"></i></div>
            <div class="store_price_text">
                <button class="btn"> Price <i class="ri-money-dollar-circle-line"></i> ' . htmlspecialchars($price) . '</button>
            </div>
            <i class="ri-check-line check-icon"></i>
        </div>
    </label>';
}

function list_frames($dir = 'system/store/frames', $webPath = 'system/store/frames/', $allowedExtensions = ['jpg', 'png', 'gif', 'jpeg'], $jsonFilePath = 'system/store/frames/frames.json') {
    if (!is_dir($dir)) {
        return '<div>Directory not found.</div>';
    }

    // Get current file list from the directory
    $files = array_diff(scandir($dir), array('.', '..'));
    $imageFiles = [];
    foreach ($files as $file) {
        $filePath = $dir . '/' . $file;
        $fileExtension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (is_file($filePath) && in_array($fileExtension, $allowedExtensions)) {
            $imageFiles[] = [
                'file_name' => $file,
                'url' => $webPath . $file,
                'file_name_without_extension' => pathinfo($file, PATHINFO_FILENAME),
                'extension' => $fileExtension,
                'price' => rand(50, 1500) // Random price
            ];
        }
    }
    // Sort alphabetically
    usort($imageFiles, fn($a, $b) => strcmp($a['file_name'], $b['file_name']));

    // Check if the JSON file exists
    if (file_exists($jsonFilePath)) {
        $jsonData = json_decode(file_get_contents($jsonFilePath), true);
        if ($jsonData && count($jsonData) === count($imageFiles)) {
            // If JSON is up-to-date, return HTML without rewriting JSON
            return generateHTML($jsonData);
        }
    }

    // Save new JSON if folder contents have changed
    file_put_contents($jsonFilePath, json_encode($imageFiles, JSON_PRETTY_PRINT));

    return generateHTML($imageFiles);
}

function generateHTML($imageFiles) {
    if (!$imageFiles) return '<div>No images found in the directory.</div>';

    $html = '<div class="store_grid-container">';
    foreach ($imageFiles as $image) {
        $html .= generateStoreCard(
            htmlspecialchars($image['file_name_without_extension']),
            htmlspecialchars($image['file_name']),
            $image['url'],  // No htmlspecialchars for URLs unless user-generated
            $image['url'],  
            (int)$image['price'] // Ensure price is an integer
        );
    }
    return $html . '</div>';
}



function renameFrames() {
    $dir = BOOM_PATH . '/system/store/frames';
    
    // Check if the directory exists
    if (!is_dir($dir)) {
        return false; // Exit if the directory doesn't exist
    }
    
    // Get all files in the directory except '.' and '..'
    $files = array_diff(scandir($dir), array('.', '..')); 
    $counter = 1; // Start counter from 1
    
    // Step 1: Rename all files to a temporary name to avoid overwriting
    foreach ($files as $file) {
        $filePath = $dir . '/' . $file;
        $fileExtension = pathinfo($file, PATHINFO_EXTENSION); // Get the file extension
        $tempFileName = 'temp_frame_' . $counter . '.' . $fileExtension; // Create the temporary file name
        $tempFilePath = $dir . '/' . $tempFileName;

        // Rename the file to the temporary name if it exists
        if (file_exists($filePath)) {
            rename($filePath, $tempFilePath); // Rename the file to temp name
            $counter++; // Increment the counter
        }
    }
    
    $counter = 1; // Reset counter for final renaming

    // Step 2: Rename from temporary names to the final names
    foreach ($files as $file) {
        $tempFilePath = $dir . '/temp_frame_' . $counter . '.' . pathinfo($file, PATHINFO_EXTENSION);
        $newFileName = 'frame_' . $counter . '.' . pathinfo($file, PATHINFO_EXTENSION); // Create the final file name
        $newFilePath = $dir . '/' . $newFileName;

        // Rename the temporary file to the final name
        if (file_exists($tempFilePath)) {
            rename($tempFilePath, $newFilePath); // Rename to final name
            $counter++; // Increment the counter
        }
    }

    return true; // Return true on success
}
function systemPostNews($user, $news){
	global $mysqli, $data;
	$mysqli->query("INSERT INTO `boom_news` (news_poster, news_message, news_date) VALUES ('$user', '$news', '" . time() . "')");
	$mysqli->query("UPDATE boom_users SET user_news = '". time() ."', naction = naction + 1 WHERE user_id = '2'");
	$mysqli->query("UPDATE boom_users SET user_action = user_action + 1");
	chatAction($data['user_roomid']);
	return true;
}

function list_and_pair_wings(
    $dir = 'system/store/wing',
    $webPath = 'system/store/wing/',
    $allowedExtensions = ['png', 'jpg', 'jpeg', 'gif'],
    $jsonFilePath = 'system/store/wing/wings.json'
) {
    global $data;
    
    // Load existing JSON data if available
    $wings = [];
    if (file_exists($jsonFilePath)) {
        $jsonData = file_get_contents($jsonFilePath);
        $wings = json_decode($jsonData, true) ?: [];
    }

    // Scan directory for images
    $files = scandir($dir);
    $newWings = [];
    
    foreach ($files as $file) {
        $filePath = $dir . '/' . $file;
        $fileExtension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        
        if (is_file($filePath) && in_array($fileExtension, $allowedExtensions)) {
            if (preg_match('/(.+)-wing([12])\.' . $fileExtension . '$/', $file, $matches)) {
                $baseName = $matches[1];
                $wingNumber = $matches[2];

                if (!isset($newWings[$baseName])) {
                    $newWings[$baseName] = [];
                }
                $newWings[$baseName]['wing' . $wingNumber] = $file;
            }
        }
    }

    // Compare and update JSON only if new images are detected
    if ($newWings !== $wings) {
        file_put_contents($jsonFilePath, json_encode($newWings, JSON_PRETTY_PRINT));
    }

    // Generate HTML
    $html = '<div class="store_grid-container">';
    
    foreach ($newWings as $baseName => $pair) {
        if (isset($pair['wing1'], $pair['wing2'])) {
            $wing1Url = $webPath . $pair['wing1'];
            $wing2Url = $webPath . $pair['wing2'];
            $goldPrice = rand(50, 500);
            
            $html .= generateWingCard($baseName, $wing1Url, $wing2Url, $goldPrice, $data);
        } else {
            $html .= '<div class="wing-card unpaired"><h3>Unpaired: ' . htmlspecialchars($baseName) . '</h3></div>';
        }
    }

    $html .= '</div>';
    return $html;
}

// Function to generate HTML from wings data


function generateWingCard($baseName, $wing1Url, $wing2Url, $goldPrice, $userData) {
    // Extract user data
    $userName = $userData['user_name'];
    $userMood = $userData['user_mood'];
    // Get file information for passing to JavaScript
    $d_wing1 = pathinfo($wing1Url, PATHINFO_FILENAME);
    $d_wing2 = pathinfo($wing2Url, PATHINFO_FILENAME);
    $ext = pathinfo($wing1Url, PATHINFO_EXTENSION);
    // Prepare the enter parameter for JavaScript's `selectWing` function
    $enter = "'$baseName','$d_wing1','$d_wing2','$goldPrice','$ext'";
	$card_template='<label for="wing_pack_' . $baseName . '" data-pack-name="' . htmlspecialchars($baseName) . '" class="store_card">
        <input  onclick="selectWing('.$enter.')" data-type="wings_tab" data-ext="'.$ext.'" type="radio" data-id="' . $baseName . '" name="pack_selection" id="wing_pack_' . $baseName . '" value="' . htmlspecialchars($goldPrice) . '" style="display:none;">
        <div class="store_content store_content_rank">
            <div class="store_logo">
				<div class="online_user">
					<div class="avtrig user_item" style=" padding: 0; ">
						<img class="wing-images" src="' . htmlspecialchars($wing1Url) . '" alt="Wing 1 of ' . htmlspecialchars($baseName) . '" />
						<div class="user_item_data">
						<p class="username bgif17 bnfont15">' . htmlspecialchars($userName) . '</p>
						<p class="text_xsmall bustate bellips">' . htmlspecialchars($userMood) . '</p>
					</div>
					<img class="wing-images" src="' . htmlspecialchars($wing2Url) . '" alt="Wing 2 of ' . htmlspecialchars($baseName) . '" />
					</div>
				</div>                
            </div>
            <div class="store_main_text">
                <p><i class="ri-shield-star-line"></i>' . htmlspecialchars($baseName) . '</p>
            </div>
            <div class="store_price_text">
                <button class="btn"> Price <i class="ri-money-dollar-circle-line"></i>' . htmlspecialchars($goldPrice) . '</button>
            </div>
            <i class="ri-check-line check-icon"></i>
        </div>
    </label>';
  return   $card_template;
}

function deleteOldFile($filePath) {
    // Check if the file exists
    if (file_exists($filePath)) {
        // Try to delete the file
        if (unlink($filePath)) {
            //error_log("File deleted successfully: " . $filePath);
            return true;
        } else {
            //error_log("Failed to delete file: " . $filePath);
            return false;
        }
    } else {
        //error_log("File does not exist: " . $filePath);
        return false;
    }
}

if ($f == 'store') {
    if ($s == 'edit_pack_form' && boomLogged() === true) {
        $html  = '';
        $pack_id = escape($_POST['pack_id']);
        $pack_data = FU_store_marketById($pack_id);
        $imagePath = $pack_data['image']; // Full path to the image

        if (is_array($pack_data) && !empty($pack_data)) {
            $html .= boomTemplate('store/edit_pack_form', $pack_data);
        } else {
            $html .= emptyZone($lang['empty']);
        }

        $array_data['status'] = 200;
        $array_data['html']   = $html;
        header("Content-type: application/json");
        echo json_encode($array_data);
        exit();
    } 

    if ($s == 'add_pack_form' && boomLogged() === true) {
        $html  = '';
        $html .= boomTemplate('store/new_pack_form', $data);
        $array_data['status'] = 200;
        $array_data['html']   = $html;
        header("Content-type: application/json");
        echo json_encode($array_data);
        exit();
    }
if ($s == 'add_pack' && boomLogged() === true) {
    header("Content-type: application/json");

    $pack_name = escape($_POST['packge_name']);
    $pack_price = isset($_POST['packge_price']) ? (float)$_POST['packge_price'] : null;
    $user_rank = isset($_POST['pack_rank']) ? escape($_POST['pack_rank']) : null;
    $pack_type = escape($_POST['packge_type']);
    $pack_discount = isset($_POST['packge_discount']) ? (int)$_POST['packge_discount'] : 0;
    $pack_status = isset($_POST['packge_status']) ? (int)$_POST['packge_status'] : 1;

    // Validate required fields
    if (empty($pack_name) || empty($pack_type)) {
        echo json_encode(['status' => 400, 'message' => 'Missing required fields.']);
        exit();
    }

    // Validate pack type
    if (!in_array($pack_type, ['rank', 'gold', 'premium'])) {
        echo json_encode(['status' => 400, 'message' => 'Invalid pack type.']);
        exit();
    }

    // Validate price conditions
    if ($pack_type === 'gold' && ($pack_price === null || $pack_price < 1000)) {
        echo json_encode(['status' => 400, 'message' => 'Gold pack price must be at least 1000.']);
        exit();
    }
    if ($pack_type === 'rank' && $pack_price != 0) {
        echo json_encode(['status' => 400, 'message' => 'Rank pack price should be 0.']);
        exit();
    }

    // Validate discount (0-100)
    if ($pack_discount < 0 || $pack_discount > 100) {
        echo json_encode(['status' => 400, 'message' => 'Discount must be between 0 and 100.']);
        exit();
    }

    // Handle file upload (avatar)
    $avatar_name = null;
    if (!empty($_FILES['avatar_pack']['name'])) {
        $avatar_name = time() . '_' . basename($_FILES['avatar_pack']['name']);
        $upload_path = __DIR__ . '/uploads/' . $avatar_name;

        if (!move_uploaded_file($_FILES['avatar_pack']['tmp_name'], $upload_path)) {
            echo json_encode(['status' => 500, 'message' => 'Failed to upload avatar.']);
            exit();
        }
    }

    // Prevent duplicate rank packs
    if ($pack_type === 'rank' && !empty($user_rank)) {
        if (checkExistingPackByRank($user_rank)) {
            echo json_encode(['status' => 409, 'message' => 'A pack with this rank already exists.']);
            exit();
        }
    }

    // Add pack to the database
    $result = addNewPack($pack_name, $pack_price, $pack_type, $pack_discount, $pack_status, $avatar_name);

    if ($result['status'] == 200) {
        echo json_encode(['status' => 200, 'message' => $result['msg'], 'result' => $result]);
    } else {
        echo json_encode(['status' => 500, 'message' => 'Failed to add pack.']);
    }

    exit();
}
  if ($s == 'edit_pack' && boomLogged() === true) {
        $pack_id = escape($_POST['pack_id']);

        if (!empty($pack_id)) {
            // Assuming addNewPack is a function that adds the new pack to the database
            $result = Edit_Pack($pack_id);
            if ($result['status']==200) {
                $array_data['status'] = 200;
                $array_data['message'] = $result['msg'];
            } else {
                $array_data['status'] = 500;
                $array_data['message'] = 'pack_edit_failed';
            }
        } else {
            $array_data['status'] = 400;
            $array_data['message'] = 'missing_packID';
        }

        header("Content-type: application/json");
        echo json_encode($array_data);
        exit();
    } 
if ($s == 'store_panel' && boomLogged() === true) {
        $html  = '';
        $html .= boomTemplate('store/store_panel', $data);
        $array_data['status'] = 200;
        $array_data['html']   = $html;
        header("Content-type: application/json");
        echo json_encode($array_data);
        exit();
    }  
if ($s == 'buy_pack' && boomLogged() === true) {
    $array_data = ['query' => '', 'message' => '', 'status' => 400];
    // Only users above guest rank can buy
    if ($data['user_rank'] <= 0) {
        $array_data['message'] = 'Guest Levels not allowed to Use the Store.';
        echo fu_json_results($array_data);
        exit();
    }
    $pack_id = sanitizeOutput(trim($_POST['id'] ?? ''));
    if (empty($pack_id)) {
        $array_data['message'] = 'Pack ID is empty.';
        echo fu_json_results($array_data);
        exit();
    }
    $pack_info = FU_store_marketById($pack_id);
    if (empty($pack_info) || !isset($pack_info['type'])) {
        $array_data['message'] = 'Invalid pack information.';
        echo fu_json_results($array_data);
        exit();
    }
    $pack_type = $pack_info['type'];
    if ($pack_type === 'rank') {
        if ((int)$data['user_rank'] === (int)$pack_info['user_rank']) {
            $array_data['message'] = 'You are already at this rank.';
            $array_data['status'] = 403;
        } elseif (in_array((int)$data['user_rank'], [100, 90, 80, 70])) {
            $array_data['message'] = 'You are at a higher level than the chosen rank.';
            $array_data['status'] = 403;
        } else {
            $buy_result = buy_rank($pack_id);
            if (!empty($buy_result) && isset($buy_result['message'])) {
                $query = $buy_result['message'];
                $array_data['status'] =  $query['status'] ?? 200;
                $array_data['message'] = $query['alert'] ?? 'Rank purchased successfully.';
            } else {
                $array_data['message'] = 'Failed to purchase rank.';
                $array_data['status'] = 500;
            }
        }
    } elseif ($pack_type === 'gold') {
        $array_data['type'] = 'gold';
        $buy_result = buy_gold_pack($pack_id);
        if (!empty($buy_result) && isset($buy_result['message'])) {
            $query = 				$buy_result['message'];
            $array_data['message'] = $query['alert'] ?? 'Gold pack purchased successfully.';
            $array_data['status'] = $query['status'] ?? 200;
        } else {
            $array_data['message'] = 'Failed to purchase gold pack.';
            $array_data['status'] = 500;
        }
    } elseif ($pack_type === 'premium') {
        if (in_array((int)$data['user_rank'], [0])) {
            $array_data['message'] = 'Guest Levels not allowed to Use the Store.';
            $array_data['status'] = 403;
        } else {
            $buy_result = buy_premium_pack($pack_id);
            if (!empty($buy_result) && isset($buy_result['message'])) {
                 $query = 					$buy_result['message'];
                $array_data['status'] = 	$query['status'] ?? 200;
                $array_data['message'] = 	$query['alert'] ?? 'Premium pack purchased successfully.';
            } else {
                $array_data['message'] = 'Failed to purchase premium pack.';
                $array_data['status'] = $query['status'] ?? 500;
            }
        }
    } else {
        $array_data['message'] = 'Invalid pack type.';
    }
    echo fu_json_results($array_data);
    exit();
}

if ($s == 'delete_pack' && boomLogged() === true) {
    header("Content-type: application/json");
    // Get pack ID from POST request
    $pack_id = escape($_POST['pack_id']);
    // Validate that the pack_id is not empty and is numeric
    if (!empty($pack_id) && is_numeric($pack_id) && $pack_id > 0) {
        // Check if the pack with the provided ID exists
        $existingPack = FU_store_marketById($pack_id); // Assuming this function retrieves pack data by ID
        if ($existingPack) {
            // Pack exists, get the image path
            $imagePath = BOOM_PATH . '/' . $existingPack['image'];
            // Define the default image path (adjust this according to your setup)
            $defaultImage = 'upload/store/default_pack.png'; 
            // Check if the image is NOT the default image before deleting
            if (!empty($existingPack['image']) && $existingPack['image'] !== $defaultImage && file_exists($imagePath)) {
                unlink($imagePath); // Deletes the image file from the server
            }
            // Proceed to delete the pack from the database
            $deleteQuery = $mysqli->query("DELETE FROM `boom_store` WHERE `id` = '$pack_id'");
            if ($deleteQuery) {
                $array_data['status'] = 200; // Success
                $array_data['message'] = 'Pack deleted successfully.';
            } else {
                // Error occurred during deletion
                $array_data['status'] = 500;
                $array_data['message'] = 'Failed to delete the pack.';
            }
        } else {
            // Pack does not exist
            $array_data['status'] = 404;
            $array_data['message'] = 'Pack not found.';
        }
    } else {
        // Invalid pack_id or missing fields
        $array_data['status'] = 400;
        $array_data['message'] = 'Invalid or missing pack ID.';
    }
    // Return response as JSON
    echo json_encode($array_data);
    exit();
}

if ($s == 'reset_frames' && boomLogged() === true) {
		// Get pack ID from POST request
		$reset_cmd =  renameFrames();
		$array_data['html'] ='';
		if($reset_cmd){
             $array_data['status'] = 200; // Success
             $array_data['message'] = 'Frames folder Reset successfully.';	
			$array_data['html'] = $reset_cmd;		
		}else{
			$array_data['message'] = 'Failed to Reset the Frames Folder.';
		}
		// Return response as JSON
		header("Content-type: application/json");
		echo json_encode($array_data);
		exit();
		
	}
if ($s == 'get_frames' && boomLogged() === true) {
    $array_data = [
        'status' => 500,
        'message' => 'Failed to Load the Frames Folder.',
        'html' => '',
        'frames_data' => []
    ];
    // Get frames HTML and data
    $frames_html = list_frames();
    $jsonFilePath = 'system/store/frames/frames.json';
    if ($frames_html) {
        $array_data['status'] = 200;
        $array_data['message'] = 'Frames Loaded successfully.';
        $array_data['html'] = $frames_html;
        // Get fresh frames data (no need to re-check JSON existence)
        $jsonData = file_get_contents($jsonFilePath);
        $imageDataArray = json_decode($jsonData, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($imageDataArray)) {
            foreach ($imageDataArray as $image) {
                $array_data['frames_data'][] = [
                    'file_name' => htmlspecialchars($image['file_name'] ?? ''),
                    'url' => $image['url'] ?? '',
                    'price' => htmlspecialchars($image['price'] ?? '')
                ];
            }
        } else {
            $array_data['message'] = 'Invalid JSON format in frames.json.';
        }
    }

    header("Content-Type: application/json");
    echo json_encode($array_data);
    exit();
}

if ($s == 'get_wings' && boomLogged() === true) {
    // Initialize response array
    $array_data = [
        'status' => 500,  
        'message' => 'Failed to Load the Wings Folder.',  
        'html' => '',  
        'wings_data' => []  
    ];
    // Path to wings JSON
    $jsonFilePath = 'system/store/wings/wings.json';
    // Generate HTML and check if JSON exists
    $wings_html = list_and_pair_wings();
    if ($wings_html) {
        $array_data['status'] = 200;
        $array_data['message'] = 'Wings Loaded successfully.';
        $array_data['html'] = $wings_html;
        // Ensure wings.json exists before reading
        if (file_exists($jsonFilePath)) {
            $jsonData = file_get_contents($jsonFilePath);
            $wingsDataArray = json_decode($jsonData, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($wingsDataArray)) {
                foreach ($wingsDataArray as $wing) {
                    $array_data['wings_data'][] = [
                        'file_name' => htmlspecialchars($wing['file_name'] ?? ''),
                        'url' => $wing['url'] ?? '',
                        'price' => htmlspecialchars($wing['price'] ?? '')
                    ];
                }
            } else {
                $array_data['message'] = 'Invalid JSON format in wings.json.';
            }
        } else {
            $array_data['message'] = 'Wings JSON file not found.';
        }
    }
    header("Content-Type: application/json");
    echo json_encode($array_data);
    exit();
}
if ($s == 'buy_frame' && boomLogged() === true) {
    header("Content-type: application/json");
		if (!canPhotoFrame()) {
			$array_data['message'] = 'You do not have permission to use this feature.';
			$array_data['status'] = 401;
			echo json_encode($array_data);
			exit();
		}	
    // Get pack ID, extension, and amount from POST request
    $pack_id = escape($_POST['fid']);
    $ext = strtolower(escape($_POST['ext'])); // Ensure extension is lowercase
    $ant = intval(escape($_POST['ant'])); // Ensure amount is an integer
    // Define allowed image extensions
    $allowed_extensions = ['jpg', 'png', 'gif', 'jpeg'];
    // Validate extension and sanitize filename
    if (!in_array($ext, $allowed_extensions)) {
        $array_data['message'] = 'Invalid file extension.';
        $array_data['status'] = 400;
        echo json_encode($array_data);
        exit();
    }
    // Construct the file path and sanitize
    $frame = basename($pack_id) . '.' . $ext; // Use basename to prevent directory traversal
    $webPath = 'system/store/frames/';
    $imageUrl = $webPath . $frame;
    // Check if the image exists in the directory
    if (!file_exists($webPath . $frame)) {
        $array_data['message'] = 'Image file not found.';
        $array_data['status'] = 404;
        echo json_encode($array_data);
        exit();
    }
    // Default message to the image URL
    $array_data['imageUrl'] = $imageUrl; 
    $my_gold = $data['user_gold']; // User's current gold balance
    // Price manipulation check
    if ($ant < 50) {
        $array_data['message'] = 'Price manipulation detected.';
        $array_data['status'] = 400; // Price manipulation status code
    } elseif ($ant > $my_gold) {
        // Insufficient funds
        $array_data['message'] = [
            "pack_price" => $ant,
            "pack_id" => $pack_id,
            "buyer" => $data['user_name'],
            "time" => time(),
            "alert" => 'Your wallet needs to be charged..😒😒',
        ];
        $array_data['status'] = 150; // Insufficient funds status
    } elseif ($my_gold >= $ant) {
        // Successful purchase
        $array_data['status'] = 200; // Success status
        $array_data['message'] = [
            "pack_price" => $ant,
            "pack_id" => $pack_id,
            "buyer" => $data['user_name'],
            "time" => time(),
            "pack_name" => $frame, // Frame name as pack_name
            "alert" => 'The royal framework has been updated..😉😉'
        ];
        // Update user data
        $update = cl_update_user_data($data['user_id'], array(
            "user_gold" => $my_gold - $ant,    // Deduct gold
            "photo_frame" => $frame,        // Update user's photo frame
        ));
		if($update){
			$content = $data['user_name']. '<font color="red"> '.$ant.' '.$lang['gold'].' '.$pack_id.'</font>';
			boomNotify('remove_gold', array('target'=> $data['user_id'], 'custom'=> $content,"icon" => 'gold'));
		}
    }
    // Return response as JSON
    echo json_encode($array_data);
    exit();
}
if ($s === 'send_reward' && boomLogged() === true) {
	header("Content-Type: application/json");
    // Check if required POST parameters are set
    if (isset($_POST['set_coins_gift'], $_POST['set_coins_code'])) {
        // Escape input data
        $set_coinsgift = escape($_POST['set_coins_gift']);
        $set_coinscode = escape($_POST['set_coins_code']);
        // Ensure only admins with the required level can execute this
        if (!boomAllow(90)) {
            die();
        }
        // Check if the gift code matches a username in the system
        $checkUserQuery = "SELECT COUNT(*) FROM boom_users WHERE user_name = ?";
        $stmt = $mysqli->prepare($checkUserQuery);
        $stmt->bind_param('s', $set_coinsgift); // Use the gift code for the username check
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        // If the gift code matches a username, exit
        if ($count > 0) {
			$array_data['message'] = 'The gift code cannot be the same as a username. Username allready exist.';
			$array_data['status'] = 100; 
			echo json_encode($array_data);
			exit();
        }else{
			$array_data['message'] = 'The code has been sent successfully';
			$array_data['status'] = 200; 
		}
        // Update the gift text and code in the database
        $updateQuery = "UPDATE boom_setting SET coins_gift_text = ?, coins_gift_code = ?";
        $stmt = $mysqli->prepare($updateQuery);
        $stmt->bind_param('ss', $set_coinsgift, $set_coinscode);
        $stmt->execute();
        $stmt->close();
        // Function to handle message generation
        function generateGiftMessage($template, $gift, $code) {
            return str_replace(
                array('@gift@', '@code@'),
                array($gift, $code),
                $template
            );
        }
        // Escape gift text and code for safe HTML output
        $escapedGiftText = htmlspecialchars($set_coinsgift, ENT_QUOTES, 'UTF-8');
        $escapedCode = htmlspecialchars($set_coinscode, ENT_QUOTES, 'UTF-8');
        // Define the message templates
        $newsTemplate = 'Administration sent a gift, Gift code is <strong data="' . $escapedGiftText . '" class="copy_gift_code" style="color:#b70606;">[ ' . $escapedGiftText . ' ]</strong>. Copy it and paste it in the main room to get <strong style="color:#b70606;">[ ' . $escapedCode . ' Coins ]</strong>. We wish you a good time.';
        $chatTemplate = 'Administration sent a gift, Gift code is <strong data="" class="copy_gift_code" style="color:#b70606;">[ ' . $escapedGiftText . ' ]</strong>. Click on the code or copy and paste it in the main room to get <strong style="color:#b70606;"><i class="ri-copper-coin-line"></i>[ ' . $escapedCode . ' Coins ]</strong>. We wish you a good time.';
        // Generate the final messages
        $news_msg = generateGiftMessage($newsTemplate, $escapedGiftText, $escapedCode);
        $msg4 = generateGiftMessage($chatTemplate, $escapedGiftText, $escapedCode);
        // Post the generated messages
        systemPostChat($data['user_roomid'], $msg4, array('color' => 'reward_message', 'type' => 'reward_bg animate__animated animate__backInDown'));
        systemPostNews($data['system_id'], $news_msg);
        // Return a JSON response
        echo json_encode($array_data);
        exit();
    }
}

if ($s == 'buy_wings' && boomLogged() === true) {
    header("Content-type: application/json");
    // Retrieve the POSTed wing data
    $wing = $_POST['wing'];
    // Sanitize and validate the data
    if (!isset($wing) || !is_array($wing)) {
        echo json_encode(['status' => 400, 'message' => 'Invalid request data.']);
        exit();
    }
    // Sanitize each field of the wing data
    $baseName = htmlspecialchars(trim($wing['baseName'] ?? ''), ENT_QUOTES, 'UTF-8');
    $wing1Url = filter_var(trim($wing['wing1Url'] ?? ''), FILTER_SANITIZE_URL);
    $wing2Url = filter_var(trim($wing['wing2Url'] ?? ''), FILTER_SANITIZE_URL);
    $goldPrice = filter_var(trim($wing['goldPrice'] ?? 0), FILTER_SANITIZE_NUMBER_INT);
    $ext = htmlspecialchars(trim($wing['ext'] ?? ''), ENT_QUOTES, 'UTF-8');
    // Check user's current gold balance
    $user_gold = $data['user_gold'];
    // Allowed file extensions
    $allowedExtensions = ['png', 'jpg', 'jpeg', 'gif'];
    // Validate file extension
    if (!in_array(strtolower($ext), $allowedExtensions)) {
        echo json_encode(['status' => 400, 'message' => 'Invalid file extension.']);
        exit();
    }
    // Validate sanitized data
    if (empty(escape($baseName)) || empty($wing1Url) || empty($wing2Url) || !is_numeric($goldPrice) || $goldPrice <= 0) {
        echo json_encode(['status' => 400, 'message' => 'Invalid wing data.']);
        exit();
    }
    // Check if user has enough gold
    if ($user_gold < $goldPrice) {
        echo json_encode(['status' => 400, 'message' => 'You do not have enough gold to buy this wing.']);
        exit();
    }
    // 3. Deduct the gold from the user's balance
    $new_gold_balance = $user_gold - $goldPrice;
    // Update the user's gold balance and the wings they have purchased
    $array_data['update'] = cl_update_user_data($data['user_id'], array(
        "user_gold" => $new_gold_balance,
        "name_wing1" => $baseName . '-wing1.' . $ext,  // Store wing1 filename
        "name_wing2" => $baseName . '-wing2.' . $ext  // Store wing2 filename
    ));
    // Check if the update was successful
    if ($array_data['update']) {
        // Return a success response
        echo json_encode([
            'status' => 200,
            'message' => 'Wing purchase successful!',
            'new_gold_balance' => $new_gold_balance,
            'wing' => $wing,
        ]);
         $content = $data['user_name']. '<font color="green"> '.$goldPrice.' '.$lang['gold'].'</font>';
         boomNotify('remove_gold', array('target'=> $data['user_id'], 'custom'=> $content,"icon" => 'gold'));		
    } else {
        // Handle the case where the update failed
        echo json_encode([
            'status' => 500,
            'message' => 'Failed to update your gold balance or wings. Please try again later.',
        ]);
    }
    exit();
}
	if ($s == 'premium_panel' && boomLogged() === true) {
		header("Content-type: application/json");
		
		$array_data['html'] = boomTemplate('store/premium_panel', $data);
		echo json_encode($array_data);
		exit();

	}
	if($s == 'profile_music' && boomLogged() === true) {
		header("Content-type: application/json");
		if ($data['user_prim'] == 0) {
			die();
		}
		// Define the target directory
		$targetDir = 'upload/premium/profile_music/';
		// Check if the directory exists, if not, create it
		if (!file_exists($targetDir)) {
			mkdir($targetDir, 0777, true);
		}
		// Check if a file has been uploaded
    if (isset($_FILES['file'])) {
        $file = $_FILES['file'];
        $info = pathinfo($file['name']);
        $extension = strtolower($info['extension']); // Consistent lowercase extension
        $allowedTypes = ['mp3']; // Define allowed file types
        // Check for file upload errors
        if (fileError()) {
            echo json_encode(['status' => 'error', 'message' => 'File error occurred']);
            die();
        }
        if (isImage($extension)) {
            echo json_encode(['code' => 4, 'status' => 'error', 'message' => 'Images are not allowed']);
            die();
        }
        if (isFile($extension)) {
            echo json_encode(['code' => 6, 'status' => 'error', 'message' => 'Invalid file type']);
            die();
        }
        // Validate file type
        if (!in_array($extension, $allowedTypes)) {
            echo json_encode(['status' => 'error', 'message' => 'Only MP3 files are allowed']);
            die();
        }
        // Validate file size (max 20MB)
        if ($file['size'] > 20 * 1024 * 1024) {
            echo json_encode(['status' => 'error', 'message' => 'File size must be less than 10MB']);
            die();
        }
        // Construct the new file name
        $file_name = encodeFile($extension);
        $targetFilePath = $targetDir . $file_name;
        // Check if the file already exists
        if (file_exists($targetFilePath)) {
            echo json_encode(['status' => 'error', 'message' => 'File already exists']);
            die();
        }
        // Check for existing song in the database
        $old_file = $data['pro_song']; // Get the current file name from the database
        if (!empty($old_file)) {
            $oldFilePath = $targetDir . $old_file; // Path of the old file
            if (file_exists($oldFilePath)) {
                // Attempt to delete the old file
                if (!unlink($oldFilePath)) {
                    echo json_encode(['code' => 3,'status' => 'error', 'message' => 'Failed to delete old file']);
                    die();
                }
            }
        }
        // Move the uploaded file to the target directory
        if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
            // Update the database with the new file name
			$query = cl_update_user_data($data['user_id'],array(
				"pro_song" =>$file_name,
            ));
            echo json_encode([
                'status' => 'success',
                'message' => 'File uploaded successfully',
                'file' => $targetFilePath,
				'code' => 1,
				'html' => musicProcess($targetFilePath, $file_name),
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'File upload failed']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No file uploaded']);
    }
}

	if($s == 'user_upgrade_premium' && boomLogged() === true) {
		$premium_plan = escape($_POST['premium_plan']);
		$premium_target = escape($_POST['premium_target']);
		$user = userDetails($premium_target);
		$new_time = Fu_premiumNewTime($premium_plan, $user);
		
		echo json_encode($array_data);
		exit();
	}
	if($s == 'admin_upgrade_premium' && boomLogged() === true) {
		$premium_plan = escape($_POST['premium_plan']);
		$premium_target = escape($_POST['premium_target']);
		$user = userDetails($premium_target);
		$new_time = Fu_premiumNewTime($premium_plan, $user);
		if(!boomAllow(100)){
			die();
		}
		
		if($premium_plan == 7){
			$mysqli->query("UPDATE boom_users SET user_prim = '7', prim_end = '$new_time', user_action = user_action + 1 WHERE user_id = '$premium_target'");
			$message = 'Your membership has been upgraded to Premium 7 days';
			postPrivate($data['system_id'], $user['user_id'], $message);
			$array_data['code'] = 1; 
		}elseif($premium_plan == 15){
			$mysqli->query("UPDATE boom_users SET user_prim = '15', prim_end = '$new_time', user_action = user_action + 1 WHERE user_id = '$premium_target'");
			$message = 'Your membership has been upgraded to Premium Extended 15 days';
			postPrivate($data['system_id'], $user['user_id'], $message);
			$array_data['code'] = 1; 
		
		}elseif($premium_plan == 30){
			$mysqli->query("UPDATE boom_users SET user_prim = '30', prim_end = '$new_time', user_action = user_action + 1 WHERE user_id = '$premium_target'");
			$message = 'Your membership has been upgraded to Premium 1 Month';
			postPrivate($data['system_id'], $user['user_id'], $message);
			$array_data['code'] = 1; 
		}elseif($premium_plan == 180){
			$mysqli->query("UPDATE boom_users SET user_prim = '180', prim_end = '$new_time', user_action = user_action + 1 WHERE user_id = '$premium_target'");
			$message = 'Your membership has been upgraded to Premium for a period 6 Months';
			postPrivate($data['system_id'], $user['user_id'], $message);
			$array_data['code'] = 1; 
		
		}elseif($premium_plan == 365){
			$mysqli->query("UPDATE boom_users SET user_prim = '365', prim_end = '$new_time', user_action = user_action + 1 WHERE user_id = '$premium_target'");
			$message = 'Your membership has been upgraded to Premium 1 Year';
			postPrivate($data['system_id'], $user['user_id'], $message);
			$array_data['code'] = 1; 
		}elseif($premium_plan == 0){
			$mysqli->query("UPDATE boom_users SET user_prim = '0', prim_end = '0', user_action = user_action + 1 WHERE user_id = '$premium_target'");
			$message = 'Your Premium Membership has expired';
			postPrivate($data['system_id'], $user['user_id'], $message);
			$array_data['code'] = 1; 
		}

		header("Content-type: application/json");
		echo json_encode($array_data);
		exit();
		
	}
	if($s == 'pro_background' && boomLogged() === true) {
	// Check user permissions
	if ($data['user_prim'] == 0 || !boomAllow(1)) {
		echo json_encode(['code' => 0, 'status' => 'error', 'message' => 'Unauthorized']);
		die();
	}
		
		// Check if file is uploaded
		if (isset($_FILES["file"])) {
			ini_set('memory_limit', '128M');
			$info = pathinfo($_FILES["file"]["name"]);
			$extension = strtolower($info['extension']); // Convert to lowercase for consistency
			$origin = escape(filterOrigin($info['filename']) . '.' . $extension);
			$avatarDir ='upload/premium/premium_background/'; 

			// Check if the user has an existing background
			if (!empty($data['pro_background'])) {
				// Get old file extension and file paths
				$oldFileExt = strtolower(pathinfo($data['pro_background'], PATHINFO_EXTENSION));
				$oldFilePath = $avatarDir . '/' . $data['pro_background'];
				$oldTumbPath = $avatarDir . '/' . pathinfo($data['pro_background'], PATHINFO_FILENAME) . '_tumb.' . $oldFileExt;

				// Delete old files based on the conditions
				if ($oldFileExt === 'png' || $oldFileExt === 'jpg' || $oldFileExt === 'jpeg') {
					// If the old file is PNG or JPG, delete both old file and thumbnail
					deleteOldFile($oldFilePath);
					deleteOldFile($oldTumbPath);
				} elseif ($oldFileExt === 'gif' && ($extension === 'png' || $extension === 'jpg' || $extension === 'jpeg')) {
					// If the old file is GIF and the new file is PNG or JPG, delete the old GIF
					deleteOldFile($oldFilePath);
				} elseif ($extension === 'gif') {
					// If the new file is GIF, delete the existing GIF file
					deleteOldFile($oldFilePath);
				}
			}

			// Handle new image upload
			if (isImage($extension)) {
				$imginfo = getimagesize($_FILES["file"]["tmp_name"]);
				if ($imginfo !== false) {
					$width = $imginfo[0];
					$height = $imginfo[1];
					$type = $imginfo['mime'];

					$fname = encodeFileTumb($extension, $data);
					$file_name = $fname['full'];
					$file_tumb = $fname['tumb'];

					// Move the uploaded file
					boomMoveFile('upload/premium/premium_background/' . $file_name);

					// If the new file is not a GIF, create a thumbnail
					if ($extension !== 'gif') {
						imageTumb('upload/premium/premium_background/' . $file_name, 'upload/premium/premium_background/' . $file_tumb, $type, 180);
					}

					// Update the user's background in the database
					$mysqli->query("UPDATE boom_users SET pro_background = '$file_name' WHERE user_id = '{$data['user_id']}'");

					echo json_encode(['code' => 5, 'status' => 'success', 'message' => 'Background uploaded successfully']);
					die();

				} else {
					echo json_encode(['code' => 1, 'status' => 'error', 'message' => 'Invalid image data']);
					die();
				}
			} elseif (isFile($extension) || isMusic($extension)) {
				echo json_encode(['code' => 1, 'status' => 'error', 'message' => 'Invalid file type']);
				die();
			} else {
				echo json_encode(['code' => 1, 'status' => 'error', 'message' => 'File upload failed']);
			}
		} else {
			echo json_encode(['code' => 1, 'status' => 'error', 'message' => 'No file uploaded']);
		}		
		exit();	
	}	
	if($s == 'pro_style' && boomLogged() === true) {
		header("Content-type: application/json");
		// Check user permissions
		if ($data['user_prim'] == 0 || !boomAllow(1)) {
			echo json_encode(['code' => 0, 'status' => 'error', 'message' => 'Unauthorized']);
			die();
		}
	    $pro_text_main = htmlspecialchars(trim($_POST['pro_text_main'] ?? ''), ENT_QUOTES, 'UTF-8');
		$pro_text_sub = filter_var(trim($_POST['pro_text_sub'] ?? ''), FILTER_SANITIZE_URL);
		$array_data['main'] = escape($pro_text_main);
		$array_data['sub'] =  escape($pro_text_sub);
        // Update user data
        $update = cl_update_user_data($data['user_id'], array(
            "pro_text_main" => $array_data['main'],    // Deduct gold
            "pro_text_sub" => $array_data['sub'],        // Update user's photo frame
        ));	
		if($update){
			echo json_encode([
				'status' => 200,
				'message' => 'Profile Updated successful.',
			]);			
		}

        exit();

	}
	if($s == 'reset_style' && boomLogged() === true) {
		header("Content-type: application/json");
        // Update user data
		$backgroundDir ='upload/premium/premium_background/'.$data['pro_background'];
		$songDir ='upload/premium/profile_music/'.$data['pro_song'];
		// Delete old background file if the path is not empty
		if (!empty($data['pro_background'])) {
			deleteOldFile($backgroundDir);
		}
		
		// Delete old song file if the path is not empty
		if (!empty($data['pro_song'])) {
			deleteOldFile($songDir);
		}
        $update = cl_update_user_data($data['user_id'], array(
            "pro_text_main" => '', 
            "pro_text_sub" => '',
            "pro_background" => '',
            "pro_song" => '',
            "name_wing2" => '',
            "name_wing1" => '',
            "photo_frame" => '',
        ));	
		if($update){
			echo json_encode([
				'status' => 200,
				'message' => 'Profile Reseted successful.',
			]);			
		}

        exit();

	}	

}

?>