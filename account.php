<link rel="stylesheet" href="custom/account.css?v=<?php echo time(); ?>">


<?php
require_once 'php_action/db_connect.php';
require_once 'includes/header.php';



if (!isset($_SESSION['user_type'])) {

    header('location:' . $url . 'login.php');
}


?>

<style type="text/css" media="print">
    #PaytheRemainMainDiv {
        display: none !important;
    }

    th,
    td,
    tr {
        color: black !important;

    }



    #view-Student {

        position: fixed;
        top: 0%;
        bottom: 0%;
        width: 100%;
        height: 100%;

    }


    #topTools {
        display: none !important;
    }

    #MainStudentTable {
        display: none !important;
    }
</style>


<div class="container-fluid mt-3" style="max-height: calc(100vh - 100px); overflow-y: auto;">
    <!-- top tools -->
    <div id="topTools" class="d-flex justify-content-between ">
        <div class="input-wrapper">
            <input id="SearchStudentInput" class="shadow" type="text" placeholder="بحث معلومات الطالب" />
            <i id="searchIcon" class="fa fa-search" aria-hidden="true"></i>
        </div>
        <div class="btn-group">
            <label class="">
                عرض الكل
                <input type="radio" id="AllRadioButton">
            </label>
            <label class="mx-4">
                بكلوريوس
                <input type="radio" id="BachRadioButton">
            </label>
            <label class="">
                دراسات عليا
                <input type="radio" id="HighRadioButton">
            </label>
        </div>
    </div>

    <!-- table -->
    <table id="MainStudentTable" class="table mt-5 text-center table-hover">
        <thead>
            <tr>
                <th>#ID</th>
                <th>اسم الطالب</th>
                <th>المرحلة</th>
                <th>الدراسة</th>
                <th>نوع القبول</th>
                <th>المبلغ الكلي</th>
                <th>الدين المتبقي</th>
                <th>تاريخ التسجيل</th>
            </tr>
        </thead>
        <tbody id="students_table"></tbody>
    </table>
    <!-- table -->
</div>


<div id="view-Student">
    <!-- Drawer Header -->
    <div class="drawer-header">
        <div class="d-flex align-items-center">
            <h3 class="text-white mb-0 mr-4">معلومات الطالب</h3>
            <span id="studentRegisDate" class="badge badge-info px-3 py-2 rounded-pill shadow-sm">
                <i class="fa fa-calendar mr-2"></i> 20-2-2023
            </span>
        </div>
        <div class="d-flex align-items-center">
            <span id="studentAcceptType" class="text-white-50 mr-4" style="font-size: 18px;">نوع القبول: <span
                    class="text-white">...</span></span>
            <button id="printIcon" class="btn btn-outline-light mr-3 rounded-circle" style="width: 45px; height: 45px;">
                <i class="fa fa-print"></i>
            </button>
            <button id="closeTimesButton" class="btn btn-danger rounded-circle" style="width: 45px; height: 45px;">
                <i class="fa fa-times"></i>
            </button>
        </div>
    </div>

    <div class="drawer-content">
        <!-- Stats Grid -->
        <div class="stats-grid animate-up">
            <div class="summary-card">
                <span class="label">المبلغ الكلي</span>
                <span class="value" id="dispTotalAmount">0 د.ع</span>
            </div>
            <div class="summary-card">
                <span class="label">نسبة الخصم</span>
                <span class="value text-info" id="dispDiscountPer">0%</span>
            </div>
            <div class="summary-card">
                <span class="label">تم دفع</span>
                <span class="value text-success" id="dispTotalPaid">0 د.ع</span>
            </div>
            <div class="summary-card">
                <span class="label">الصافي</span>
                <span class="value text-light" id="dispNetAmount">0 د.ع</span>
            </div>
            <div class="summary-card" style="background: rgba(220, 53, 69, 0.1);">
                <span class="label">المتبقي</span>
                <span class="value text-danger" id="dispTotalRemain">0 د.ع</span>
            </div>
        </div>

        <!-- Academic Info -->
        <div class="action-pane mb-4 animate-up" style="animation-delay: 0.1s;">
            <h4 class="text-white mb-4">المعلومات الدراسية</h4>
            <div class="table-responsive">
                <table class="table table-hover mb-0 glass-table">
                    <thead>
                        <tr>
                            <th>اسم الطالب</th>
                            <th>المبلغ الكلي</th>
                            <th>الخصم</th>
                            <th>الصافي</th>
                            <th>المتبقي</th>
                            <th>المرحلة</th>
                            <th>ملاحظات</th>
                        </tr>
                    </thead>
                    <tbody id="studentInfoTable"></tbody>
                </table>
            </div>

            <!-- High Student Specific Info -->
            <div id="HighStudentOnlyData" class="mt-4" style="display: none;">
                <h5 class="text-white-50 mb-3 small">معلومات الدراسات العليا</h5>
                <div class="table-responsive">
                    <table class="table table-hover glass-table border-0">
                        <thead>
                            <tr class="text-info small uppercase">
                                <th>الامر الجامعي</th>
                                <th>تاريخ المباشرة</th>
                                <th>الاجازة الدراسية</th>
                                <th>الاجازة</th>
                                <th>قناة القبول</th>
                            </tr>
                        </thead>
                        <tbody id="HighStudentOnlyDataTableBody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Payment History -->
        <div class="action-pane mb-4 animate-up" style="animation-delay: 0.2s;">
            <h4 class="text-white mb-4">سجل الدفعات</h4>
            <div class="table-responsive">
                <table class="table table-hover glass-table">
                    <thead>
                        <tr>
                            <th>رقم الدفعة</th>
                            <th>التاريخ</th>
                            <th>قيمة الدفعة</th>
                            <th>المتبقي</th>
                            <th>ملاحظات</th>
                            <th>الوصل</th>
                            <th>الاجراءات</th>
                        </tr>
                    </thead>
                    <tbody id="paymentInfoTable"></tbody>
                </table>
            </div>
        </div>

        <!-- Action Center -->
        <div class="action-center animate-up" id="PaytheRemainMainDiv" style="animation-delay: 0.3s;">
            <!-- Payment Pane -->
            <div class="action-pane">
                <h4 class="text-white">تسجيل دفعة جديدة</h4>
                <div id="PayTheRemainDiv">
                    <div class="form-group">
                        <label class="text-white">قيمة الدفعة</label>
                        <input type="text" id="PaytheRemainInput" placeholder="أدخل المبلغ">
                    </div>
                    <div class="form-group">
                        <label class="text-white">المتبقي بعد الدفعة</label>
                        <input type="text" id="RemainInput" placeholder="المتبقي التلقائي" readonly
                            class="bg-dark border-0 text-warning font-weight-bold">
                    </div>
                    <div class="form-group">
                        <label class="text-white">التاريخ + رقم الوصل</label>
                        <input type="text" id="PayTheRemainRemarks" placeholder="اكتب التاريخ ورقم الوصل">
                    </div>
                    <div class="form-group">
                        <label class="text-white">صورة الوصل</label>
                        <input accept=".png,.jpg,.jpeg" type="file" id="PayTheRemainFileInput">
                    </div>
                    <button type="button" id="PaytheRemainSaveButton"
                        class="btn btn-info btn-block py-3 mt-3 shadow-premium">
                        <i class="fa fa-save mr-2"></i> حفظ الدفعة
                    </button>
                    <div class="mt-3 text-center">
                        <img id="PayTheRemainImgPreview" class="img-thumbnail bg-dark border-secondary"
                            style="max-height: 200px; display: none;">
                    </div>
                </div>
            </div>

            <!-- Discount Pane -->
            <div class="action-pane">
                <h4 class="text-white">إدارة الخصومات</h4>
                <div class="form-group">
                    <label class="text-white">نسبة الخصم (%)</label>
                    <div class="input-group">
                        <input type="number" id="DiscountPerInput" min="0" max="100"
                            class="form-control bg-dark border-secondary text-white">
                        <div class="input-group-append">
                            <span class="input-group-text bg-dark border-secondary text-info">%</span>
                        </div>
                    </div>
                </div>
                <div class="form-group mt-3">
                    <label class="text-white">المبلغ الصافي بعد الخصم</label>
                    <input type="text" id="grandTotal" disabled class="bg-dark border-0 text-success font-weight-bold"
                        style="font-size: 20px;">
                </div>
                <button class="btn btn-outline-info btn-block py-3 mt-4" id="SaveDiscountToDB" type="button">
                    <i class="fa fa-percent mr-2"></i> تطبيق الخصم
                </button>
            </div>
        </div>
    </div>
</div>

<!-- row clicked -->
<?php
if ($_SESSION['user_type'] != 0) {
    echo '<style>
                #DeletePaymentButtonOnTable{
                    display: none !important;
                }
          </style>';
}

?>

<script src="custom/account.js?v=<?php echo time(); ?>" type="text/javascript"></script>

<?php
require_once 'includes/footer.php';
?>