<?php
// security_check.php

function get_hwid()
{
    $system = PHP_OS_FAMILY;
    try {
        if ($system === "Windows") {
            // Try WMIC (Older Windows 7/10)
            $output = shell_exec("wmic csproduct get uuid");
            if ($output) {
                $lines = explode("\n", trim($output));
                if (isset($lines[1])) {
                    return trim($lines[1]);
                }
            }
            // Last resort: Volume Serial Number
            $vol = shell_exec("vol c:");
            if ($vol) {
                $parts = explode(" ", trim($vol));
                return end($parts);
            }
        }
        elseif ($system === "Linux") {
            // Standard machine-id
            foreach (["/etc/machine-id", "/var/lib/dbus/machine-id"] as $path) {
                if (file_exists($path)) {
                    return trim(file_get_contents($path));
                }
            }
            // Fallback for Linux
            return trim(shell_exec("hostname"));
        }
    }
    catch (Exception $e) {
        return "Unknown_" . $system;
    }
    return "Unknown_" . $system;
}

function base64UrlEncode($data)
{
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
}

function get_firestore_docs($config)
{
    $project_id = $config['project_id'];
    $collection = "AuthorizedMachines";

    // 1. Create JWT for Auth
    $header = json_encode(['typ' => 'JWT', 'alg' => 'RS256']);
    $now = time();
    $payload = json_encode([
        'iss' => $config['client_email'],
        'scope' => 'https://www.googleapis.com/auth/datastore',
        'aud' => 'https://oauth2.googleapis.com/token',
        'exp' => $now + 3600,
        'iat' => $now
    ]);

    if (!$header || !$payload)
        return [];

    $base64UrlHeader = base64UrlEncode($header);
    $base64UrlPayload = base64UrlEncode($payload);
    $signatureInput = $base64UrlHeader . "." . $base64UrlPayload;

    // Key is obfuscated in config, so we decode it here
    $privateKey = base64_decode($config['private_key']);
    if (!$privateKey)
        return [];

    openssl_sign($signatureInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);
    $base64UrlSignature = base64UrlEncode($signature ?: '');

    $jwt = $signatureInput . "." . $base64UrlSignature;

    // 2. Exchange JWT for Access Token
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => $jwt
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $token_data = json_decode($response, true);
    $access_token = $token_data['access_token'] ?? null;
    curl_close($ch);

    if (!$access_token)
        return [];

    // 3. Get Documents from Firestore
    $url = "https://firestore.googleapis.com/v1/projects/$project_id/databases/(default)/documents/$collection";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $access_token
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $data = json_decode($response, true);
    curl_close($ch);

    $authorized_ids = [];
    if (isset($data['documents'])) {
        foreach ($data['documents'] as $doc) {
            $fields = $doc['fields'];
            $m_id = $fields['MachineId']['stringValue'] ?? null;
            $activated = $fields['Activated']['booleanValue'] ?? false;
            if ($m_id && $activated) {
                $authorized_ids[] = $m_id;
            }
        }
    }
    return $authorized_ids;
}

// Security Enforcement
if (isset($GLOBALS['SECURITY_CHECK_INCLUDED']))
    return;
$GLOBALS['SECURITY_CHECK_INCLUDED'] = true;

// 1. Code Integrity Check (Prevent tampering with db_connect.php)
$target_file = __DIR__ . '/db_connect.php';
$expected_hash = '37eb82881835cc9d2d9d231b737d2756';
if (!file_exists($target_file) || md5(file_get_contents($target_file)) !== $expected_hash) {
    // If someone modifies db_connect.php (e.g. to remove this include), it will fail
    // Note: Since this file is included BY db_connect.php, we check its integrity here.
    $tampering_detected = true;
}
else {
    $tampering_detected = false;
}

// Ensure session is started for caching
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_id = get_hwid();
$is_authorized = false;
$cache_lifetime = 300; // 5 minutes in seconds

// Check Session Cache
if (
isset($_SESSION['hwid_auth_status']) &&
isset($_SESSION['hwid_auth_time']) &&
(time() - $_SESSION['hwid_auth_time']) < $cache_lifetime &&
$_SESSION['hwid_cached_id'] === $current_id
) {

    $is_authorized = $_SESSION['hwid_auth_status'];
}
else {
    // Cache expired or missing, check Firestore
    $firebase_config = require_once __DIR__ . '/firebase_config.php';
    $authorized_ids = get_firestore_docs($firebase_config);
    $is_authorized = in_array($current_id, $authorized_ids);

    // Update Cache
    $_SESSION['hwid_auth_status'] = $is_authorized;
    $_SESSION['hwid_auth_time'] = time();
    $_SESSION['hwid_cached_id'] = $current_id;
}

if (!$is_authorized) { // || $tampering_detected
    // Lockdown UI (Persistent)
    $error_title = $tampering_detected ? "TAMPERING DETECTED" : "SECURITY BREACH";
    $error_msg = $tampering_detected ? "SYSTEM INTEGRITY COMPROMISED" : "UNAUTHORIZED COPY DETECTED";
?>
<!DOCTYPE html>
<html lang="ar">

<head>
    <meta charset="UTF-8">
    <title>
        <?php echo $error_title; ?>
    </title>
    <style>
        body {
            background-color: #000;
            color: #ff3333;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            text-align: center;
            overflow: hidden;
        }

        .lockdown-container {
            border: 2px solid #ff3333;
            padding: 60px;
            border-radius: 12px;
            background: rgba(255, 0, 0, 0.05);
            box-shadow: 0 0 50px rgba(255, 0, 0, 0.2);
        }

        h1 {
            font-size: 60px;
            margin-bottom: 30px;
            letter-spacing: 5px;
        }

        p {
            font-size: 26px;
            line-height: 1.6;
            color: #ddd;
        }

        .machine-id {
            display: inline-block;
            background: #222;
            padding: 10px 20px;
            border-radius: 5px;
            color: #00ff00;
            font-family: monospace;
            margin-top: 20px;
            font-size: 20px;
        }

        .contact {
            font-weight: bold;
            color: #ff3333;
            font-size: 30px;
            display: block;
            margin: 20px 0;
        }
    </style>
</head>

<body oncontextmenu="return false;" onkeydown="return false;">
    <div class="lockdown-container">
        <h1>
            <?php echo $error_title; ?>
        </h1>
        <p>
            <?php echo $error_msg; ?>
        </p>
        <p>This software is locked. Contact support:</p>
        <span class="contact">+964 776 742 6185</span>
        <p>Your Machine ID:</p>
        <div class="machine-id">
            <?php echo htmlspecialchars($current_id); ?>
        </div>
    </div>
</body>

</html>
<?php
    exit;
}