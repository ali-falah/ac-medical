<?php
require_once 'php_action/db_connect.php';
require_once 'includes/header.php';

// --- 1. Students Breakdown ---
// Total Students
$sql_total_students = "SELECT (SELECT COUNT(*) FROM student WHERE IsDeleted = 0) + (SELECT COUNT(*) FROM student_high WHERE IsDeleted = 0) as total";
$total_students = $connect->query($sql_total_students)->fetch_assoc()['total'];

// Bachelor Students
$sql_bach_students = "SELECT COUNT(*) as count FROM student WHERE IsDeleted = 0";
$total_bach_students = $connect->query($sql_bach_students)->fetch_assoc()['count'];

// High Students
$sql_high_students = "SELECT COUNT(*) as count FROM student_high WHERE IsDeleted = 0";
$total_high_students = $connect->query($sql_high_students)->fetch_assoc()['count'];


// --- 2. Today's Financials ---
// Today's Revenue
$sql_revenue_today = "SELECT IFNULL((SELECT SUM(payment_amount) FROM payment WHERE DATE(payment_date) = CURDATE() AND IsDeleted = 0), 0) + IFNULL((SELECT SUM(payment_amount) FROM payment_high WHERE DATE(payment_date) = CURDATE() AND IsDeleted = 0), 0) as total";
$today_revenue = $connect->query($sql_revenue_today)->fetch_assoc()['total'];

// Today's Count
$sql_count_today = "SELECT (SELECT COUNT(*) FROM payment WHERE DATE(payment_date) = CURDATE() AND IsDeleted = 0) + (SELECT COUNT(*) FROM payment_high WHERE DATE(payment_date) = CURDATE() AND IsDeleted = 0) as total";
$today_count = $connect->query($sql_count_today)->fetch_assoc()['total'];


// --- 3. Period Financials ---
// Monthly Revenue
$sql_revenue_month = "SELECT IFNULL((SELECT SUM(payment_amount) FROM payment WHERE MONTH(payment_date) = MONTH(CURDATE()) AND YEAR(payment_date) = YEAR(CURDATE()) AND IsDeleted = 0), 0) + IFNULL((SELECT SUM(payment_amount) FROM payment_high WHERE MONTH(payment_date) = MONTH(CURDATE()) AND YEAR(payment_date) = YEAR(CURDATE()) AND IsDeleted = 0), 0) as total";
$month_revenue = $connect->query($sql_revenue_month)->fetch_assoc()['total'];

// Yearly Revenue
$sql_revenue_year = "SELECT IFNULL((SELECT SUM(payment_amount) FROM payment WHERE YEAR(payment_date) = YEAR(CURDATE()) AND IsDeleted = 0), 0) + IFNULL((SELECT SUM(payment_amount) FROM payment_high WHERE YEAR(payment_date) = YEAR(CURDATE()) AND IsDeleted = 0), 0) as total";
$year_revenue = $connect->query($sql_revenue_year)->fetch_assoc()['total'];


// --- 4. Outstanding Debt ---
$sql_debt = "SELECT (SELECT SUM(total_remain) FROM student WHERE IsDeleted = 0) + (SELECT SUM(total_remain) FROM student_high WHERE IsDeleted = 0) as total";
$total_debt = $connect->query($sql_debt)->fetch_assoc()['total'];


// --- 5. Recent Activity (Last 10) ---
$sql_recent = "(SELECT p.payment_amount, p.payment_date, p.payment_num, p.img, s.name as student_name, 'Bachelor' as type, 'php_action/uploads/' as path
                FROM payment p 
                INNER JOIN student s ON p.student_id = s.student_id 
                WHERE p.IsDeleted = 0)
               UNION ALL
               (SELECT p.payment_amount, p.payment_date, p.payment_num, p.img, sh.name as student_name, 'High' as type, 'php_action/uploadsHigh/' as path
                FROM payment_high p 
                INNER JOIN student_high sh ON p.student_high_id = sh.student_high_id 
                WHERE p.IsDeleted = 0)
               ORDER BY payment_date DESC LIMIT 10";
$res_recent = $connect->query($sql_recent);
?>

<link rel="stylesheet" href="custom/dashboard.css">
<style>
    /* Custom tweaks for the new layout (if items are missing from dashboard.css) */
    .stat-detail-row {
        display: flex;
        justify-content: space-between;
        font-size: 0.85rem;
        margin-top: 8px;
        padding-top: 8px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .img-thumbnail-icon {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #dee2e6;
        cursor: pointer;
        transition: transform 0.2s;
    }

    .img-thumbnail-icon:hover {
        transform: scale(1.5);
        z-index: 10;
    }

    .no-img-text {
        font-size: 0.75rem;
        color: #adb5bd;
    }
</style>

<div class="dashboard-container">
    <div class="container-fluid">

        <!-- Welcome Section -->
        <div class="welcome-section row align-items-center animate-up" style="animation-delay: 0.1s;">
            <div class="col-md-8">
                <h1>أهلاً بك في لوحة التحكم</h1>
                <p class="text-muted">نظرة عامة شاملة على إحصائيات النظام والطلاب</p>
            </div>
            <div class="col-md-4 text-left">
                <span class="badge bg-glass text-info shadow-sm p-3 rounded-pill">
                    <i class="fa fa-calendar mr-2"></i> <?php echo date('Y-m-d'); ?>
                </span>
            </div>
        </div>

        <!-- Stats Cards Grid -->
        <div class="row animate-up" style="animation-delay: 0.2s;">

            <!-- Card 1: Students Breakdown -->
            <div class="col-md-3 mb-4">
                <div class="stats-card bg-grad-teal">
                    <div class="card-title">إجمالي الطلاب</div>
                    <div class="card-value"><?php echo number_format($total_students); ?></div>
                    <div class="stat-detail-row">
                        <span><i class="fa fa-user-graduate"></i> بكلوريوس: <?php echo $total_bach_students; ?></span>
                        <span><i class="fa fa-user-tie"></i> عليا: <?php echo $total_high_students; ?></span>
                    </div>
                </div>
            </div>

            <!-- Card 2: Today's Activity -->
            <div class="col-md-3 mb-4">
                <div class="stats-card bg-grad-purple">
                    <div class="card-title">حركة اليوم</div>
                    <div class="card-value"><?php echo number_format($today_revenue); ?> د.ع</div>
                    <div class="stat-detail-row">
                        <span><i class="fa fa-receipt"></i> عدد العمليات: <?php echo $today_count; ?></span>
                    </div>
                </div>
            </div>

            <!-- Card 3: Financial Period -->
            <div class="col-md-3 mb-4">
                <div class="stats-card bg-grad-green">
                    <div class="card-title">الإيرادات المحصلة</div>
                    <div class="card-value"><?php echo number_format($month_revenue); ?> <small
                            style="font-size: 1rem">شهرياً</small></div>
                    <div class="stat-detail-row">
                        <span>سنوياً: <?php echo number_format($year_revenue); ?></span>
                    </div>
                </div>
            </div>

            <!-- Card 4: Detailed Debt Tracking -->
            <div class="col-md-3 mb-4">
                <div class="stats-card bg-grad-orange"
                    style="background: linear-gradient(135deg, #fd7e14 0%, #d63384 100%); color: white;">
                    <div class="card-title">الديون المستحقة</div>
                    <div class="card-value"><?php echo number_format($total_debt); ?> د.ع</div>
                    <div class="stat-detail-row">
                        <span>مبالغ بانتظار التحصيل</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Recent Payments Table -->
        <div class="row animate-up" style="animation-delay: 0.3s;">
            <div class="col-12">
                <div class="recent-activity-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div><i class="fa fa-history mr-2"></i> آخر 10 دفعات مسجلة</div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-modern align-middle">
                                <thead>
                                    <tr class="text-dark">
                                        <th>الطالب</th>
                                        <th>نوع الدراسة</th>
                                        <th>المبلغ</th>
                                        <th>التاريخ</th>
                                        <th>رقم الوصل</th>
                                        <th>الوصل</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $res_recent->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo $row['student_name']; ?></strong>
                                            </td>
                                            <td>
                                                <span
                                                    class="badge badge-light"><?php echo ($row['type'] == 'Bachelor') ? 'بكلوريوس' : 'عليا'; ?></span>
                                            </td>
                                            <td class="text-success font-weight-bold">
                                                <?php echo number_format($row['payment_amount']); ?> د.ع
                                            </td>
                                            <td><?php echo date('Y-m-d', strtotime($row['payment_date'])); ?></td>
                                            <td><span class="text-muted">#<?php echo $row['payment_num']; ?></span></td>
                                            <td>
                                                <?php if (!empty($row['img'])): ?>
                                                    <a href="<?php echo $row['path'] . $row['img']; ?>" target="_blank">
                                                        <img src="<?php echo $row['path'] . $row['img']; ?>" alt="وصل"
                                                            class="img-thumbnail-icon">
                                                    </a>
                                                <?php else: ?>
                                                    <span class="no-img-text">لا يوجد</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                    <?php if ($res_recent->num_rows == 0): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">لا توجد دفعات مسجلة حالياً
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once 'includes/footer.php'; ?>