<?php
require_once 'php_action/db_connect.php';

/* =========================
   Redirect if already logged in
   ========================= */
if (isset($_SESSION['user_type'])) {
    header('Location: ' . $url . 'index.php');
    exit;
}

/* =========================
   Handle Login
   ========================= */
$errorMessage = '';

if (isset($_POST['password'], $_POST['username'])) {

    $username = $_POST['username'];
    $password = sha1($_POST['password']);

    $sql = "SELECT user_type FROM `user` WHERE username = ? AND password = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['user_type'] = $row['user_type'];

        header('Location: ' . $url . 'index.php');
        exit;
    } else {
        $errorMessage = 'خطأ في كلمة السر او اسم المستخدم';
    }
}
?>


<!DOCTYPE html>
<html lang="ar">

<head>
    <meta charset="UTF-8">
    <title>تسجيل الدخول</title>

    <link rel="icon" href="2.png">
    <link href="https://fonts.googleapis.com/css?family=Cairo" rel="stylesheet">

    <style>
    :root {
        --medical-teal: #17a2b8;
        --medical-dark: #04191c;
    }

    * {
        font-family: 'Cairo', sans-serif;
    }

    html,
    body {
        height: 100%;
    }

    body {
        direction: rtl;
        overflow: hidden;
        background-color: #f4f7f6;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    #top_header {
        position: absolute;
        top: 20px;
        width: 100%;
        text-align: center;
        opacity: 0.9;
        transition: 0.8s;
        color: var(--medical-dark);
    }

    #left_img,
    #right_img {
        width: 300px;
        object-fit: contain;
        opacity: 0.7;
        transition: 0.8s;
        position: fixed;
    }

    .container {
        background-color: #fff;
        max-width: 450px;
        width: 90%;
        padding: 40px;
        border-radius: 16px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        z-index: 10;
        border-top: 5px solid var(--medical-teal);
    }

    h2 {
        text-align: center;
        margin-bottom: 30px;
        color: var(--medical-dark);
        font-weight: 700;
    }

    label {
        display: block;
        margin-top: 15px;
        font-weight: 600;
        color: #555;
    }

    input[type="text"],
    input[type="password"] {
        width: 100%;
        padding: 12px 15px;
        margin: 8px 0;
        border: 1px solid #ddd;
        border-radius: 8px;
        transition: all 0.3s;
    }

    input:focus {
        border-color: var(--medical-teal);
        box-shadow: 0 0 0 0.2rem rgba(23, 162, 184, 0.25);
        outline: none;
    }

    button {
        width: 100%;
        padding: 14px;
        margin-top: 25px;
        background-color: var(--medical-teal);
        color: #fff;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 18px;
        font-weight: 700;
        transition: 0.3s;
    }

    button:hover {
        background-color: #138496;
        transform: translateY(-2px);
    }

    .error {
        position: absolute;
        bottom: 50px;
        width: 100%;
        text-align: center;
        color: #dc3545;
        font-weight: 600;
        background: rgba(220, 53, 69, 0.1);
        padding: 10px 0;
    }

    .main-wrapper {
        display: flex;
        flex-direction: row;
        width: 100%;
        justify-content: center;
        align-items: center;
    }
    </style>
</head>

<body>

    <!-- Header -->
    <div id="top_header">
        <h3 style="font-size:45px;">كلية الطب - جامعة البصرة</h3>
        <h4>نظام دفع وتسجيل الطلبة</h4>
    </div>

    <div class="main-wrapper">
        <!-- Left Image -->
        <img id="left_img" src="2.png" alt="">

        <!-- Login Form -->
        <div class="container">
            <h2>تسجيل الدخول</h2>

            <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                <label for="username">اسم المستخدم</label>
                <input type="text" name="username" id="username" required placeholder="ادخل اسم المستخدم">

                <label for="password">كلمة السر</label>
                <input type="password" name="password" id="password" required placeholder="ادخل كلمة المرور">

                <button type="submit">تسجيل الدخول</button>
            </form>
        </div>

        <!-- Right Image -->
        <img id="right_img" src="2.png" alt="">
    </div>

    <!-- Error -->
    <?php if (!empty($errorMessage)): ?>
        <div class="error">
            <p><?php echo $errorMessage; ?></p>
        </div>
    <?php endif; ?>

    <script>
        window.onload = function () {
            document.getElementById("left_img").style.left = "3%";
            document.getElementById("right_img").style.right = "3%";
            document.getElementById("top_header").style.top = "-5%";
        };
    </script>

</body>

</html>