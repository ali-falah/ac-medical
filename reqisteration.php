<?php
require_once 'php_action/db_connect.php';
require_once 'includes/header.php';


if (!isset($_SESSION['user_type'])) {

    header('location:' . $url . 'login.php');


}



?>

<style>
    #boot-alert-adding-student {
        position: fixed;
        bottom: 3%;
        left: 50%;
        transform: translateX(-50%);
        z-index: 2000;
        display: none;
        width: 80%;
    }
</style>

<div class="dashboard-container">
    <div class="mt-5 row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="text-center mb-4">
                <h2 class="font-weight-bold text-dark mb-2">تسجيل طالب جديد</h2>
                <p class="text-muted">يرجى ملء بيانات الطالب بدقة لإتمام عملية التسجيل</p>
            </div>

            <div class="card glass-card border-0">
                <div class="card-body p-5">
                    <div class="row">
                        <!-- Main Info Section -->
                        <div class="col-md-6 border-left-custom">
                            <h5 class="text-info mb-4"><i class="fa fa-user-circle ml-2"></i> المعلومات الأساسية</h5>

                            <div class="form-group mb-4">
                                <label class="text-secondary small">اسم الطالب الرباعي</label>
                                <input class="form-control form-control-lg bg-light border-0" required type="text"
                                    placeholder="مثال: علي محمد حسين علي" id='studentNameInput'>
                            </div>

                            <div class="form-group mb-4">
                                <label class="text-secondary small">نوع الدراسة</label>
                                <select id="studentStudyTypeSelect"
                                    class="form-control form-control-lg bg-light border-0" required="required">
                                    <option value="0" selected disabled>اختر نوع الدراسة</option>
                                    <option value="1">بكلوريوس</option>
                                    <option id-high="1" value="2">دبلوم عالي</option>
                                    <option id-high="2" value="2">ماجستير</option>
                                    <option id-high="3" value="2">دكتوراه</option>
                                </select>
                            </div>

                            <div class="form-group mb-4" id="studentStageBachelorWrapper" style="display: none;">
                                <label class="text-secondary small">المرحلة الدراسية</label>
                                <select id="studentStageBachelor"
                                    class="form-control form-control-lg bg-light border-0">
                                    <option value="0" selected disabled>اختر المرحلة</option>
                                    <option value="1">المرحلة الاولى</option>
                                    <option value="2">المرحلة الثانية</option>
                                    <option value="3">المرحلة الثالثة</option>
                                    <option value="4">المرحلة الرابعة</option>
                                    <option value="5">المرحلة الخامسة</option>
                                    <option value="6">المرحلة السادسة</option>
                                </select>
                            </div>

                            <div class="form-group mb-4" id="studentStageHighWrapper" style="display: none;">
                                <label class="text-secondary small">المرحلة الدراسية</label>
                                <select id="studentStageHigh" class="form-control form-control-lg bg-light border-0">
                                    <option value="0" selected disabled>اختر المرحلة</option>
                                    <option value="1">المرحلة الاولى</option>
                                    <option value="2">المرحلة الثانية</option>
                                </select>
                            </div>

                            <div class="form-group" id="remarksWrapper" style="display: none;">
                                <label class="text-secondary small">ملاحظات إضافية</label>
                                <textarea id="StudentRemarksinput" class="form-control bg-light border-0" rows="4"
                                    placeholder="أي ملاحظات مهمة حول الطالب..."></textarea>
                            </div>
                        </div>

                        <!-- Financial/Details Section (Loaded Dynamically) -->
                        <div class="col-md-6">
                            <h5 class="text-info mb-4"><i class="fa fa-file-invoice-dollar ml-2"></i> تفاصيل القبول
                                والمالية</h5>

                            <div id="reg-data-container"
                                class="d-flex flex-column justify-content-center h-100 text-muted">
                                <div class="text-center p-4" style="opacity: 0.6;">
                                    <i class="fa fa-arrow-right fa-3x mb-3"></i>
                                    <p>يرجى اختيار نوع الدراسة لعرض باقي الحقول</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Removed #boot-alert-adding-student as we use SweetAlert2 now -->


<?php
require_once 'includes/footer.php';


?>


<script src="custom/reqisteration.js" type="text/javascript"></script>