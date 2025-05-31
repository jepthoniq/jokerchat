<?php
/**
 * FuseChat
 *
 * @package FuseChat
 * @author www.nemra-1.com
 * @copyright 2020
 * @terms Unauthorized use of this script without a valid license is prohibited.
 * All content of FuseChat is the property of BoomCoding and cannot be used in another project.
 */

if ($f == 'room_icon') {
    if ($s == 'add_room_icon') {
        echo addRoomIcon();
        exit();
    }
}
if (isset($_POST['remove_icon'])) {
    echo removeRoomIcon();
    die();
}

function addRoomIcon() {
    global $mysqli, $data;

    // Check if user has permission to edit room
    if (!canEditRoom()) {
        return boomCode(0); // No permission
    }

    // Validate and sanitize the room ID
    $room_id = escape($_POST['admin_add_icon'], true);
    $room = roomDetails($room_id);
    if (empty($room)) {
        return boomCode(0); // Room not found
    }

    // Increase memory limit if necessary
    ini_set('memory_limit', '128M');

    // Validate uploaded file
    if (fileError(4)) {
        return boomCode(1); // File upload error
    }

    // Handle the file and its extension
    $info = pathinfo($_FILES["file"]["name"]);
    $extension = strtolower($info['extension']); // Ensure extension is lowercase
    $allowed_extensions = ['jpg', 'jpeg', 'png'];
    if (!in_array($extension, $allowed_extensions)) {
        return boomCode(1,['msg'=>'jpg - jpeg - png Only']); // Invalid file extension
    }

    $file_tumb = "room_icon" . $room["room_id"] . "_" . time() . ".jpg";
    $file_icon = "temporary_room_icon_" . $room["room_id"] . "." . $extension;

    // Paths for the new files
    $filepath_tumb = 'upload/room_icon/' . $file_tumb;
    $filepath_icon = 'upload/room_icon/' . $file_icon;

    // Check if the file already exists
    if (sourceExist($filepath_tumb) || sourceExist($filepath_icon)) {
        return boomCode(6,['msg'=>'Image already exists']); // Image already exists
    }

    // Check if the current room icon is the default image, and don't delete it
    $default_image = 'default_images/rooms/default_room.svg';
    if ($room['room_icon'] !== $default_image) {
        unlinkRoomIcon($room['room_icon']); // Remove old file if it's not the default
    }

    // Process image if it's valid
    if (isImage($extension)) {
        $image_info = getimagesize($_FILES["file"]["tmp_name"]);
        if ($image_info !== false) {
            $width = $image_info[0];
            $height = $image_info[1];
            $type = $image_info['mime'];

            // Move the uploaded file to the temporary location
            boomMoveFile('upload/room_icon/' . $file_icon);

            // Create the thumbnail
            $create = createTumbnail($filepath_icon, $filepath_tumb, $type, $width, $height, 200, 200);

            // Validate file existence and image data
            if (sourceExist($filepath_tumb) && sourceExist($filepath_icon)) {
                if (validImageData($filepath_tumb)) {
                    // Update the database with the new room icon
                    $stmt = $mysqli->prepare("UPDATE boom_rooms SET room_icon = ? WHERE room_id = ?");
                    $stmt->bind_param("ss", $file_tumb, $room['room_id']);
                    $stmt->execute();

                    // Optional: Update the room cache (if needed)
                    // redisUpdateRoom($room['room_id']);

                    // Return the success code with the updated room icon
                    return boomCode(5, array('data' => myRoomIcon($file_tumb)));
                } else {
                    unlinkRoomIcon($filepath_icon);
                    return boomCode(7); // Invalid image data
                }
            } else {
                unlinkRoomIcon($filepath_icon);
                return boomCode(7); // File validation failed
            }
        } else {
            return boomCode(7); // Invalid image
        }
    } else {
        return boomCode(1); // Not an image
    }
}

function removeRoomIcon() {
    global $mysqli, $data;

    // Check if user has permission to edit room
    if (!canEditRoom()) {
        return boomCode(0); // No permission
    }

    // Fetch room details
    $room = roomDetails($data['user_roomid']);
    if (empty($room)) {
        return boomCode(0); // Room not found
    }

    $default_image = 'default_images/rooms/default_room.svg'; // Path of default image

    // Only remove the icon if it's not the default one
    if ($room['room_icon'] !== $default_image) {
        unlinkRoomIcon($room['room_icon']); // Remove the existing room icon
    }

    // Update the room icon to default
    $mysqli->query("UPDATE boom_rooms SET room_icon = 'default_room.png' WHERE room_id = '{$data['user_roomid']}'");

    // Optional: Update the room cache (if needed)
    // redisUpdateRoom($data['user_roomid']);

    // Return success code with updated room icon
    return boomCode(1, array('data' => myRoomIcon('default_room.png')));
}
?>