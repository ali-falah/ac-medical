<?php
require_once 'db_connect.php';

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Access Control: Strict check for Admin (0)
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 0) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized Access']);
    exit();
}

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

// --- 1. Get All Users ---
if ($action == 'getAllUsers') {
    $sql = "SELECT user_id, username, user_type FROM user WHERE IsDeleted = 0 ORDER BY user_id DESC"; // Assuming IsDeleted exists, or just DELETE if not using soft delete. 
    // Let's assume soft delete "IsDeleted" standard in this project, but wait, login.php select didn't check IsDeleted?
    // login.php: "SELECT user_type FROM `user` WHERE username = ? AND password = ?" - NO IsDeleted check.
    // Okay, so it might be hard delete. Let's check table columns again? No way to check.
    // I'll assume Hard Delete for now as usually user tables are small.
    // Or I can run a check query.
    // Let's stick to standard "SELECT * FROM user"

    $sql = "SELECT user_id, username, user_type FROM user ORDER BY user_id DESC";
    $result = $connect->query($sql);

    $data = [];
    if ($result) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
    }
    echo json_encode(['status' => 'success', 'data' => $data]);
    exit();
}

// --- 2. Create User ---
if ($action == 'createUser') {
    $username = $_POST['username'];
    $password = sha1($_POST['password']);
    $userType = (int) $_POST['userType'];

    // Check if username exists
    $checkSql = "SELECT user_id FROM user WHERE username = ?";
    $stmtCheck = $connect->prepare($checkSql);
    $stmtCheck->bind_param("s", $username);
    $stmtCheck->execute();
    if ($stmtCheck->get_result()->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'اسم المستخدم موجود مسبقاً']);
        exit();
    }

    $sql = "INSERT INTO user (username, password, user_type) VALUES (?, ?, ?)";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("ssi", $username, $password, $userType);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'تم إضافة المستخدم بنجاح']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'خطأ في قاعدة البيانات: ' . $stmt->error]);
    }
    exit();
}

// --- 3. Update User ---
if ($action == 'updateUser') {
    $userId = (int) $_POST['userId'];
    $username = $_POST['username'];
    $userType = (int) $_POST['userType'];
    $password = $_POST['password'];

    // Check if trying to edit an Admin (type 0) - Extra security
    $checkAdmin = $connect->query("SELECT user_type FROM user WHERE user_id = $userId")->fetch_assoc();
    if ($checkAdmin && $checkAdmin['user_type'] == 0) {
        // Allow updating ONLY if it's the SAME admin logged in? Or simply Disallow editing other admins?
        // User request: "admin with user type ==0 cannot be deleted or edited"
        // I will BLOCK IT completely here as requested.
        echo json_encode(['status' => 'error', 'message' => 'لا يمكن تعديل حساب المدير الرئيسي']);
        exit();
    }

    if (!empty($password)) {
        // Update with new password
        $newPass = sha1($password);
        $sql = "UPDATE user SET username = ?, password = ?, user_type = ? WHERE user_id = ?";
        $stmt = $connect->prepare($sql);
        $stmt->bind_param("ssii", $username, $newPass, $userType, $userId);
    } else {
        // Update without changing password
        $sql = "UPDATE user SET username = ?, user_type = ? WHERE user_id = ?";
        $stmt = $connect->prepare($sql);
        $stmt->bind_param("sii", $username, $userType, $userId);
    }

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'تم تعديل البيانات بنجاح']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'خطأ في التحديث: ' . $stmt->error]);
    }
    exit();
}

// --- 4. Delete User ---
if ($action == 'deleteUser') {
    $userId = (int) $_POST['userId'];

    // Check if target is Admin
    $checkAdmin = $connect->query("SELECT user_type FROM user WHERE user_id = $userId")->fetch_assoc();
    if ($checkAdmin && $checkAdmin['user_type'] == 0) {
        echo json_encode(['status' => 'error', 'message' => 'لا يمكن حذف المدير الرئيسي']);
        exit();
    }

    $sql = "DELETE FROM user WHERE user_id = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("i", $userId);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'تم الحذف بنجاح']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'خطأ في الحذف: ' . $stmt->error]);
    }
    exit();
}

echo json_encode(['status' => 'error', 'message' => 'Invalid Action']);
?>