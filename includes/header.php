<?php ?>
<!DOCTYPE html>
<html>

<head>



  <title>الادارة</title>

  <!-- fonts -->
  <link rel="stylesheet" href="assests/font-awesome/css/font-awesome.css" />

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Readex+Pro:wght@200;400&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css?family=Cairo" rel="stylesheet">
  <link rel="icon" href="2.png">




  <script src="assests/jquery.slim.js"></script>
  <!-- jquery -->
  <script src="assests/jquery/jquery.min.js"></script>

  <!-- DataTables -->
  <link rel="stylesheet" href="assests/plugins/datatables/jquery.dataTables.min.css">

  <!-- file input -->
  <link rel="stylesheet" href="assests/plugins/fileinput/css/fileinput.min.css">

  <!-- jquery ui -->
  <link rel="stylesheet" href="assests/jquery-ui/jquery-ui.min.css">
  <script src="assests/jquery-ui/jquery-ui.min.js"></script>

  <link rel="stylesheet" href="assests/bootstrap/dist/css/bootstrap.min.css">
  <script src="assests/bootstrap/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Global Medical Theme -->
  <link rel="stylesheet" href="custom/medical-theme.css">

  <script src="assests/xlsx.js"></script>
  <script src="assests/proper.js"></script>

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">

<body style="overflow-x: hidden !important;">

  <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container-fluid">
      <a class="navbar-brand d-flex align-items-center" href="index.php">
        <img src="2.png" alt="Logo" class="mr-2" style="max-height: 60px;">
        <div class="d-none d-sm-block">
          <h6 class="mb-0 text-white font-weight-bold" style="font-size: 14px;">نظام إدارة كلية الطب</h6>
          <small class="text-info" style="font-size: 10px; opacity: 0.8;">تسجيل و دفعات الطلاب</small>
        </div>
      </a>

      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <i class="fa fa-bars text-white"></i>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto align-items-center">
          <?php
          if (isset($_SESSION['user_type'])) {
            $ADMIN = 0;
            $FINANCE = 1;
            $REGISTRATION = 2;
            $MANAGER = 3;

            $userRole = $_SESSION['user_type'];
            $currentPage = basename($_SERVER['PHP_SELF']);

            // Helper function for active class
            function isActive($page, $currentPage)
            {
              return $page == $currentPage ? 'active' : '';
            }

            echo '<li class="nav-item ' . isActive('index.php', $currentPage) . '">
              <a class="nav-link" href="index.php"><i class="fa fa-home"></i> الرئيسية</a>
            </li>';

            // Registration & Edit (Admin, Registration)
            if ($userRole == $ADMIN || $userRole == $REGISTRATION) {
              echo '<li class="nav-item ' . isActive('reqisteration.php', $currentPage) . '">
                <a class="nav-link" href="reqisteration.php"><i class="fa fa-user-plus"></i> تسجيل الطلاب</a>
              </li>';
              echo '<li class="nav-item ' . isActive('EditStudent.php', $currentPage) . '">
                <a class="nav-link" href="EditStudent.php"><i class="fa fa-edit"></i> تعديل الطلاب</a>
              </li>';
            }

            // Accounts (Admin, Finance)
            if ($userRole == $ADMIN || $userRole == $FINANCE) {
              echo '<li class="nav-item ' . isActive('account.php', $currentPage) . '">
                <a class="nav-link" href="account.php"><i class="fa fa-calculator"></i> الحسابات</a>
              </li>';
            }

            // User Management (Admin ONLY)
            if ($userRole == $ADMIN) {
              echo '<li class="nav-item ' . isActive('users.php', $currentPage) . '">
                <a class="nav-link" href="users.php"><i class="fa fa-users"></i> المستخدمين</a>
              </li>';
            }

            // Reports & Transfers (Admin, Registration, Manager, Finance)
            if ($userRole == $ADMIN || $userRole == $MANAGER || $userRole == $REGISTRATION || $userRole == $FINANCE) {
              echo '<li class="nav-item ' . isActive('report.php', $currentPage) . '">
                <a class="nav-link" href="report.php"><i class="fa fa-file-text-o"></i> تقرير</a>
              </li>';
            }

            // Excel Import & Transfer (Admin, Registration)
            if ($userRole == $ADMIN || $userRole == $REGISTRATION) {
              echo '<li class="nav-item ' . isActive('transfer.php', $currentPage) . '">
                <a class="nav-link" href="transfer.php"><i class="fa fa-exchange"></i> ترحيل</a>
              </li>';

              echo '<li class="nav-item ' . isActive('ImportStudents.php', $currentPage) . '">
                <a class="nav-link" href="ImportStudents.php"><i class="fa fa-upload"></i> رفع الطلاب</a>
              </li>';
            }

          } else {
            echo '<li class="nav-item text-white-50 small px-3">Undefined User</li>';
          }
          ?>
          <li class="nav-item ml-lg-3">
            <button id="darkModeToggle" class="btn btn-outline-light btn-sm px-3"
              style="border-radius: 20px; border: 1px solid rgba(255,255,255,0.3);">
              <i class="fa fa-moon-o"></i>
            </button>
          </li>
          <li class="nav-item ml-lg-2">
            <a class="btn btn-outline-danger btn-sm px-3" href="logout.php" style="border-radius: 20px;">
              <i class="fa fa-sign-out"></i> خروج
            </a>
          </li>
        </ul>
      </div>
    </div>

  </nav>