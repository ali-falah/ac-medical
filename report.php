<link rel="stylesheet" href="custom/account.css">


<?php
require_once 'php_action/db_connect.php';
require_once 'includes/header.php';

if (!isset($_SESSION['user_type'])) {

    header('location:' . $url . 'login.php');

}

?>





<div class="dashboard-container">
    <div class="mt-4 row justify-content-center">
        <div class="col-md-8 col-lg-6">

            <div class="text-center mb-5">
                <h2 class="font-weight-bold text-dark mb-2">تقارير الطلاب</h2>
                <p class="text-muted">توليد تقارير تفصيلية حسب المعايير المحددة</p>
            </div>

            <div class="card glass-card border-0">
                <div class="card-body p-5">

                    <form>
                        <div class="form-group mb-4">
                            <label class="text-info font-weight-bold"><i class="fa fa-id-card ml-2"></i> الرمز التعريفي
                                (Student ID)</label>
                            <input id="IdStudentSearchInput" type="number"
                                class="form-control form-control-lg bg-light border-0"
                                placeholder="أدخل رقم الطالب (اختياري)">
                        </div>

                        <div class="form-group mb-4">
                            <label class="text-info font-weight-bold"><i class="fa fa-graduation-cap ml-2"></i> نوع
                                الدراسة</label>
                            <select class="form-control form-control-lg bg-light border-0" id="SelectStudyTypeReport">
                                <option value="0" selected>الكل</option>
                                <option value="1">بكلوريوس</option>
                                <option value="2">دراسات عليا</option>
                            </select>
                        </div>

                        <div class="form-group mb-5">
                            <label class="text-info font-weight-bold"><i class="fa fa-money-bill-wave ml-2"></i> حالة
                                الدفع</label>
                            <select class="form-control form-control-lg bg-light border-0" id="SelectAgeTypeReport">
                                <option value="0" selected>الكل</option>
                                <option value="1">تم الدفع بالكامل</option>
                                <option value="2">غير مدفوع / متبقي</option>
                            </select>
                        </div>

                        <button type="button" id="SearchStudentReportButton"
                            class="btn btn-info btn-block btn-lg py-3 shadow-lg hover-scale">
                            <i class="fa fa-search ml-2"></i> توليد التقرير
                        </button>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>



<?php
require_once 'includes/footer.php';
?>

<script src="custom/report.js" type="text/javascript"></script>