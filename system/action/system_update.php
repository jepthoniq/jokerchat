<?php
require __DIR__ . "./../config_session.php";

if (isset($_POST["version_install"]) && boomAllow(100)) {
    // Sanitize the version input
    $version = sanitizeInput($_POST["version_install"]);
    echo boomUpdateChat($version);
    exit;
}

exit;

function boomUpdateChat($v){
 global $mysqli,$data,$cody;
    // Ensure the version is valid and greater than the current version
    if ($v <= $data["version"]) {
        return boomCode(0, ["error" => "Version is already installed"]);
    }
    // Prepare the install parameters securely
    $install = [
        "key" => sanitizeInput($data["boom"]),
        "domain" => sanitizeInput($data["domain"])
    ];

    // Initialize and configure cURL securely
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, "https://your-api-url.com/update");
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($install));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true); // Enable SSL certificate verification
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // Ensure the host matches the certificate
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_REFERER, $_SERVER["HTTP_HOST"]);

    // Execute cURL request
    $result = curl_exec($curl);
    if (curl_errno($curl)) {
        curl_close($curl);
        return boomCode(0, ["error" => "cURL error: " . curl_error($curl)]);
    }
    curl_close($curl);

    // Validate the response to ensure it contains JSON
    if (!isBoomJson($result)) {
        return boomCode(0, ["error" => "Unable to install the update at this time. Please contact us for support."]);
    }

    $udata = json_decode($result);
    if ($udata->code != 99) {
        return boomCode(0, ["error" => $udata->error]);
    }

    // Define the paths for the update files
    $fpath = BOOM_PATH . "/updates/" . $v . "/files.zip";
    $upath = BOOM_PATH . "/updates/" . $v . "/update.php";
    $epath = BOOM_PATH . "/";

    // Check if the update file exists and extract it
    if (file_exists($fpath)) {
        $zip = new ZipArchive();
        if ($zip->open($fpath) !== true) {
            return boomCode(0, ["error" => "Unable to process the automatic update. Please refer to the manual update procedure or contact us for support."]);
        }
        $zip->extractTo($epath);
        $zip->close();
    }

    // Execute the update script if it exists
    if (file_exists($upath)) {
        require $upath;
    }

    return boomCode(2);
}

function sanitizeInput($input) {
    // Sanitize the input to remove harmful characters
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}
?>
