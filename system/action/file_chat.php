<?php
/**
 * FuseChat File Upload Handler
 *
 * @package FuseChat
 * @subpackage FileUpload
 * @license Proprietary
 * @version 2020
 */

require_once('./../config_session.php');

// Check permissions and conditions
if (!canUploadChat() || muted() || isRoomMuted($data) || checkFlood()) {
    echo 1;
    exit;
}

// Handle file upload
if (isset($_FILES['file'])) {
    ini_set('memory_limit', '128M');
    
    $info = pathinfo($_FILES['file']['name']);
    $extension = strtolower($info['extension']);
    $origin = escape(filterOrigin($info['filename']) . '.' . $extension);
    $uploadDir = 'upload/chat/';

    if (fileError()) {
        echo 1;
        exit;
    }

    $file_name = encodeFile($extension);
    $sourcePath = $uploadDir . $file_name;
    $file_url = $data['domain'] . "/$sourcePath";

    switch (true) {
        case isImage($extension):
            handleImageUpload($_FILES['file']['tmp_name'], $sourcePath, $file_url, $extension);
            break;

        case isFile($extension):
            handleFileUpload($sourcePath, $file_url, $origin);
            break;

        case isMusic($extension):
            handleMusicUpload($sourcePath, $file_url, $origin);
            break;
        //add in next update    
        //case isVideo($extension):
        //   handleVideoUpload($sourcePath, $file_url, $origin);
       //     break;

        default:
            echo 1;
            exit;
    }
} else {
    echo 1;
}

function handleImageUpload($tmpPath, $sourcePath, $file_url, $extension){
    global $data;
    $imginfo = getimagesize($tmpPath);
    if ($imginfo === false) {
        echo 1;
        exit;
    }

    $fname = encodeFileTumb($extension, $data);
    $file_name = $fname['full'];
    $file_tumb = $fname['tumb'];

    boomMoveFile($sourcePath);

    $tumbPath = 'upload/chat/' . $file_tumb;
    $tumb_url = $data['domain'] . "/$tumbPath";

    $create = imageTumb($sourcePath, $tumbPath, $imginfo['mime'], 180);
    $myimage = (sourceExist($sourcePath) && sourceExist($tumbPath) && validImageData($tumbPath))
        ? tumbLinking($file_url, $tumb_url)
        : linking($file_url);

    userPostChatFile($myimage, $file_name, 'image', ['file2' => $file_tumb]);
    echo 5;
    exit;
}

function handleFileUpload($sourcePath, $file_url, $origin){
    global $data;
    boomMoveFile($sourcePath);
    $myfile = fileProcess($file_url, $origin);
    userPostChatFile($myfile, basename($sourcePath), 'file');
    echo 5;
    exit;
}

function handleMusicUpload($sourcePath, $file_url, $origin){
    global $data;
    boomMoveFile($sourcePath);
    $myfile = musicProcess($file_url, $origin);
    userPostChatFile($myfile, basename($sourcePath), 'music');
    echo 5;
    exit;
}
?>