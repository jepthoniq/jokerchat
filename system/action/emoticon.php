<?php

if (isset($_POST['get_emo']) && isset($_POST['type'])) {
    // Sanitize and prepare input
    $emo = htmlspecialchars($_POST['get_emo']);
    $emo = str_replace(['/', '.'], '', $emo);
    $panel_type = htmlspecialchars($_POST['type']);

    // Determine action and close type based on panel type
    switch ($panel_type) {
        case 1:
            $emo_act = 'content';
            $closetype = 'closesmilies';
            break;
        case 2:
            $emo_act = 'message_content';
            $closetype = 'closesmilies_priv';
            break;
        default:
            echo "Invalid panel type.";
            exit;
    }

    // Determine emoticon type and path
    $emo_type = 'emoticon';
    $emo_link = ($emo !== 'base_emo') ? $emo . DIRECTORY_SEPARATOR : '';
    $emo_search = ($emo !== 'base_emo') ? $emo : '';

    if (stripos($emo, 'sticker') !== false) {
        $emo_type = 'sticker';
    } elseif (stripos($emo, 'custom') !== false) {
        $emo_type = 'custom_emo';
    }

    // Directory to scan
    $directory = '../../emoticon' . DIRECTORY_SEPARATOR . $emo_search;

    // Check if the directory exists
    if (!is_dir($directory)) {
        echo "Directory not found.";
        exit;
    }

    // Scan directory and build emoticon list
    $files = array_diff(scandir($directory), ['.', '..']);
    $supported = ['.png', '.svg', '.gif'];
    $load_emo = '';

    foreach ($files as $file) {
        $filePath = $directory . DIRECTORY_SEPARATOR . $file;
        if (!is_file($filePath)) continue;

        $smile = pathinfo($file, PATHINFO_FILENAME);
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        if (in_array('.' . $ext, $supported)) {
            $load_emo .= '<div title=":' . htmlspecialchars($smile) . ':" class="' . htmlspecialchars($emo_type) . ' ' . htmlspecialchars($closetype) . '">'
                      . '<img src="emoticon/' . htmlspecialchars($emo_link) . htmlspecialchars($smile) . '.' . htmlspecialchars($ext) . '" '
                      . 'onclick="emoticon(\'' . htmlspecialchars($emo_act) . '\', \':'. htmlspecialchars($smile) . ':\')" /></div>';
        }
    }

    echo $load_emo;
    exit;
}

?>
