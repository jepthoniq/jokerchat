<?php
$res = array();
if ($f == 'one_signal') {
         if ($s == 'mass_notifications') {
            $message = $_POST['mass_message'];
             $res['content']= sendNotificationToAll($message);
             $res['status']= 200;
             header("Content-type: application/json");
             echo json_encode($res);
             exit();             
         
    }
}
?>