<?php
$array_data = array();
if ($f == 'wallet') {
// Database connection details
    require_once("system/wallet/paypal_config.php");
           
    if ($s == 'get_wallet') {
        $xhr['content']= boomTemplate('wallet/my_wallet', $data);
         header("Content-type: application/json");
        echo json_encode($xhr);
        exit();
    }
    if ($s == 'pay_paypal') {
        try {
            $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
        
        $apiContext = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential(
                PAYPAL_CLIENT_ID,
                PAYPAL_CLIENT_SECRET
            )
        );
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_payment') {
            $token = $_POST['token'];
            $amount = cl_rn_strip($_POST['amount']);
            // Create payment
            $payment = new \PayPal\Api\Payment();
            $payment->setIntent('sale')
                ->setPayer(array('payment_method' => 'paypal'))
                ->setRedirectUrls(array(
                    'return_url' => $data['domain'].'/requests.php?f=wallet&s=pay_paypal&success=true', // Update with your actual return URL
                    'cancel_url' => $data['domain'].'/requests.php?f=wallet&s=pay_paypal&cancel', // Update with your actual cancel URL
                ))
                ->setTransactions(array(array(
                    'amount' => array('total' => $amount, 'currency' => $data['currency']),
                    'name' => 'Wallet Replenishment',
                    'description' => 'Pay For Fuse Chat',
                )));
        
            try {
                $payment->create($apiContext);
                foreach ($payment->getLinks() as $link) {
                    if ($link->getRel() == 'approval_url') {
                        // Return approval URL as JSON
                       echo json_encode(['approvalUrl' => $link->getHref()]);
                      exit;
                    }
                }
            } catch (Exception $ex) {
                echo json_encode(['error' => $ex->getMessage()]);
                exit;
            }
        }
        if (isset($_GET['success']) && $_GET['success'] === 'true') {
            // Handle successful payment
            if (isset($_GET['paymentId']) && isset($_GET['PayerID'])) {
                $paymentId = $_GET['paymentId'];
                $payerId = $_GET['PayerID'];
                $payment = \PayPal\Api\Payment::get($paymentId, $apiContext);
        
                $execution = new \PayPal\Api\PaymentExecution();
                $execution->setPayerId($payerId);
        
               try {
					// Execute the payment
					$result = $payment->execute($execution, $apiContext);
					// Record payment details in the database
					$transactionId = $result->getId();
					$amount = $result->getTransactions()[0]->getAmount()->getTotal();
					$currency = $result->getTransactions()[0]->getAmount()->getCurrency();
					$status = $result->getState();
					$payerEmail = $result->getPayer()->getPayerInfo()->getEmail();
					$payerName = $result->getPayer()->getPayerInfo()->getFirstName() . ' ' . $result->getPayer()->getPayerInfo()->getLastName();
					// Use prepared statement for the insert query
					$stmt = $pdo->prepare("INSERT INTO boom_payments (transaction_id, amount, currency, status, payer_email, payer_name, hunter, type, notes) 
										   VALUES (:transaction_id, :amount, :currency, :status, :payer_email, :payer_name, :hunter, :type, :notes)");
					$stmt->execute([
						':transaction_id' => $transactionId,
						':amount' => $amount,
						':currency' => $currency,
						':status' => $status,
						':payer_email' => $payerEmail,
						':payer_name' => $payerName,
						':hunter' => $data['user_id'],
						':type' => 'deposit',
						':notes' => 'Deposit successful! Transaction ID: ' . $transactionId,
					]);
					// Update wallet securely using prepared statement
					$update_wallet = $data['wallet'] + $amount;
					$stmt_update = $mysqli->prepare("UPDATE boom_users SET wallet = wallet + ? WHERE user_id = ?");
					$stmt_update->bind_param('di', $update_wallet, $data['user_id']);
					$stmt_update->execute();
					// Prepare content for notification
					$content = $data['user_name'] . '<font color="red" class="withdraw_msg"> Deposit ' . $amount . ' ' . $data['currency'] . ' successful </font>';
					// Send system message and notification
					systemPostChat($data['user_roomid'], $content, ['type' => 'system__action']);
					boomNotify('withdraw', ['target' => $data['user_id'], 'custom' => $content]);
					// Redirect to the user's domain securely
					header("Location: {$data['domain']}");
					exit();
				} catch (Exception $ex) {
					// Log error for debugging (do not expose to the user)
					error_log("Error executing payment: " . $ex->getMessage());
					
					// Optionally, you could return a custom error message to the user
					echo "An error occurred during the transaction. Please try again later.";
					exit();
				}

            } else {
                echo "Payment failed. No payment ID or Payer ID found.";
            }
        }
        
        if (isset($_GET['cancel'])) {
            header("Location: {$data['domain']}");
            //echo "Payment was cancelled. Please try again.";
        }
    }
     if ($s == 'send_money_search') {
          if (isset($_POST['search_box'], $_POST['q'])){
           $text = escape($_POST['q']);
          $search_query = runWalletSearch($text);
            header("Content-type: application/json");
            echo json_encode($search_query);
            exit(); 
        }
     }
if ($s == 'send' && boomLogged() === true) {
    // Sanitize and validate the input
    $user_id = (!empty($_POST['user_id']) && is_numeric($_POST['user_id'])) ? (int)$_POST['user_id'] : 0;
    $amount = (!empty($_POST['amount_to_user']) && is_numeric($_POST['amount_to_user'])) ? (float)$_POST['amount_to_user'] : 0;
    // Fetch user data
    $userdata = fuse_user_data($user_id);
    $my_wallet = floatval($data['wallet']);
    // Validation checks
    if (empty($user_id) || empty($amount) || empty($userdata) || $amount <= 0) {
        $array_data['message'] = [
            "amount" => $amount,
            "alert" => 'Amount OR Receiver cannot be 0 or Empty',
        ];
        $array_data['status'] = 150;
    } else if ($my_wallet < $amount) {
        $array_data['message'] = [
            "amount" => $amount,
            "alert" => 'You do not have enough money to send',
        ];
        $array_data['status'] = 100;
    } else {
        $me = $data['user_id'];
        $him = $user_id;
        // Check if the sender is trying to send money to themselves
        if ($me == $him) {
            $array_data['message'] = [
                "amount" => $amount,
                "alert" => 'You cannot send money to yourself.',
            ];
            $array_data['status'] = 300;
        } else {
            // Prepare SQL statements securely
            $update_receiver = $mysqli->prepare("UPDATE `boom_users` SET `wallet` = wallet + ? WHERE `user_id` = ?");
            $update_sender = $mysqli->prepare("UPDATE `boom_users` SET `wallet` = wallet - ? WHERE `user_id` = ?");           
            // Bind parameters securely
            $update_receiver->bind_param('di', $amount, $user_id);
            $update_sender->bind_param('di', $amount, $me);            
            if ($update_receiver->execute() && $update_sender->execute()) {
                $recipient_name = $userdata['user_name'];
                $local_transactionId = generateRandomString(6);
                $donation_msg = $data['user_name'] . ' Sent ' . $amount . ' ' . $data['currency'] . ' To ' . $recipient_name;                
                // Insert transaction details securely
                $insert_trans = $mysqli->prepare("INSERT INTO `boom_payments` 
                    (transaction_id, amount, currency, status, payer_email, payer_name, hunter, type, notes, target) 
                    VALUES (?, ?, ?, 'approved', ?, ?, ?, 'donation', ?, ?)");
                $insert_trans->bind_param('sdsssiss', $local_transactionId, $amount, $data['currency'], $data['user_email'], $data['user_name'], $data['user_id'], $donation_msg, $userdata['user_id']);
                
                // Check if the transaction insertion was successful
                if ($insert_trans->execute()) {
                    $array_data['message'] = [
                        "amount" => $amount,
                        "alert" => 'Your money was successfully sent to ' . $userdata['user_name'],
                    ];
                    $array_data['status'] = 200;
                } else {
                    // Transaction insertion failed
                    $array_data['message'] = [
                        "alert" => 'Transaction failed. Please try again later.',
                    ];
                    $array_data['status'] = 500;
                }
            } else {
                // Wallet update failed
                $array_data['message'] = [
                    "alert" => 'Failed to update wallet balance.',
                ];
                $array_data['status'] = 500;
            }
        }
    }

    // Send JSON response
    header("Content-type: application/json");
    echo json_encode($array_data);
    exit();
}

     if ($s == 'transaction' && boomLogged() === true) {
         $trans_content= get_translations();
         if (!empty($trans_content)) {
          $array_data['content'] = $trans_content; 
         }else{
               $array_data['content'] = emptyZone($lang['empty']);
            }
        header("Content-type: application/json");
       echo json_encode($array_data);
       exit();        
     }
     if ($s == 'my_points' && boomLogged() === true) {
        $res['content']= boomTemplate('wallet/my_points', $data);
         header("Content-type: application/json");
        echo json_encode($res);
        exit();
       
     }     
}
?>