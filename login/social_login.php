<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

require_once('../system/config_bridge.php');
require_once '../vendor/autoload.php';

use Hybridauth\Hybridauth;
use Hybridauth\HttpClient;

// Load config
$config = include 'social_config.php';
$config['callback'] = HttpClient\Util::getCurrentUrl();

// Get provider from query or session
if (isset($_GET['provider'])) {
    $_SESSION['provider'] = $_GET['provider'];
}
$providerId = $_SESSION['provider'] ?? null;

try {
    if (!$providerId) {
        throw new Exception('Social login provider not specified.');
    }

    $hybridauth = new Hybridauth($config);
    $adapter = $hybridauth->authenticate($providerId);
    $userProfile = $adapter->getUserProfile();

    if ($userProfile) {
        // Get user profile data safely, check if each field exists
        $socialUser = [
            'id'     => $userProfile->identifier ?? null,
            'name'   => $userProfile->displayName ?? null,
            'email'  => $userProfile->email ?? null,
            'avatar' => $userProfile->photoURL ?? null
        ];

        // Check if the necessary fields exist
        if (empty($socialUser['id']) || empty($socialUser['name'])) {
            throw new Exception('Required user profile data missing.');
        }

        // Store the user in session
        $_SESSION['user'] = $socialUser;

        // Create or update user in your system
        $user = createBridgeUser($providerId, $socialUser);

        // Clear temporary session data
        unset($_SESSION['provider'], $_SESSION['token']);

        // Redirect to chat/home page (make sure $bdata['domain'] is set)
        if (!empty($bdata['domain'])) {
            header("Location: " . $bdata['domain']);
            exit;
        } else {
            throw new Exception('Redirect domain not configured.');
        }
    }
} catch (\Exception $e) {
    echo "<h3>Authentication Error</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    error_log("Social Login Error: " . $e->getMessage());
}
?>
