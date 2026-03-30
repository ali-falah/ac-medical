<?php
require_once 'php_action/db_connect.php';
require_once 'includes/header.php';

if (!isset($_SESSION['user_type'])) {
    header('location:' . $url . 'login.php');
}
?>

<style>
    /* Drawer Container & Backend */
    #EditStudentView {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100vh;
        z-index: 10000;
        visibility: hidden;
        transition: visibility 0.4s;
    }

    #EditStudentView.active {
        visibility: visible;
    }

    /* Backdrop Blur */
    .drawer-backdrop {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.4);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        opacity: 0;
        transition: opacity 0.4s ease;
    }

    #EditStudentView.active .drawer-backdrop {
        opacity: 1;
    }

    /* The Glass Panel */
    .drawer-panel {
        position: absolute;
        top: 0;
        right: 0;
        width: 600px;
        max-width: 100%;
        height: 100%;
        background: rgba(4, 25, 28, 0.95);
        border-left: 1px solid rgba(23, 162, 184, 0.3);
        box-shadow: -10px 0 30px rgba(0, 0, 0, 0.5);
        transform: translateX(100%);
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        z-index: 2;
    }

    #EditStudentView.active .drawer-panel {
        transform: translateX(0);
    }

    /* Header */
    .drawer-header {
        padding: 20px 30px;
        border-bottom: 1px solid rgba(23, 162, 184, 0.2);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: rgba(23, 162, 184, 0.05);
    }

    .drawer-header h3 {
        margin: 0;
        font-size: 1.5rem;
        color: var(--medical-teal);
        font-weight: 600;
    }

    .close-btn {
        background: transparent;
        border: none;
        color: #dc3545;
        font-size: 1.5rem;
        cursor: pointer;
        opacity: 0.8;
        transition: all 0.2s;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .close-btn:hover {
        background: rgba(220, 53, 69, 0.1);
        opacity: 1;
        transform: rotate(90deg);
    }

    /* Body scrollable */
    .drawer-body {
        flex: 1;
        overflow-y: auto;
        padding: 30px;
    }

    /* Student Badge - Minimal Redesign */
    .student-badge {
        display: flex;
        align-items: center;
        padding: 0 10px;
        margin-bottom: 30px;
        border-left: 3px solid var(--medical-teal);
        /* Simple accent line */
    }

    .badge-icon {
        font-size: 2rem;
        color: var(--medical-teal);
        margin-left: 15px;
        /* Removed circle background */
    }

    .badge-info {
        display: flex;
        flex-direction: column;
    }

    .badge-info h5 {
        margin: 0;
        color: white;
        font-size: 1.1rem;
        font-weight: 600;
    }

    .meta-tag {
        font-size: 0.8rem;
        color: #888;
        margin-top: 2px;
    }

    /* Form Styles */
    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        color: #ccc;
        margin-bottom: 8px;
        font-size: 0.9rem;
    }

    .section-divider {
        display: flex;
        align-items: center;
        margin: 30px 0 20px 0;
        color: var(--medical-teal);
        font-size: 0.9rem;
        letter-spacing: 1px;
    }

    .section-divider::after {
        content: "";
        flex: 1;
        height: 1px;
        background: rgba(23, 162, 184, 0.3);
        margin-right: 15px;
    }

    /* Footer Actions */
    .drawer-footer {
        padding: 20px 30px;
        border-top: 1px solid rgba(23, 162, 184, 0.2);
        background: rgba(4, 25, 28, 0.98);
        display: flex;
        gap: 15px;
    }

    .drawer-footer .btn {
        flex: 1;
        padding: 12px;
        border-radius: 8px;
        font-weight: 600;
    }

    /* Custom Scrollbar */
    .drawer-body::-webkit-scrollbar {
        width: 6px;
    }

    .drawer-body::-webkit-scrollbar-thumb {
        background: rgba(23, 162, 184, 0.3);
        border-radius: 3px;
    }
</style>

<div class="container-fluid mt-4 mx-3">
    <!-- top tools -->
    <div id="topTools" class="d-flex justify-content-between">
        <div class="input-wrapper">
            <input id="SearchStudentInput" class="shadow" type="text" placeholder="بحث معلومات الطالب" />
            <i id="searchIcon" class="fa fa-search" aria-hidden="true"></i>
        </div>
    </div>

    <!-- table -->
    <table id="MainStudentTable" class="table mt-4 text-center table-hover">
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
        <tbody id='students_table'></tbody>
    </table>
</div>

<!-- The Modern Right-Side Drawer -->
<div id="EditStudentView">
    <div class="drawer-backdrop" id="drawerBackdrop"></div>

    <div class="drawer-panel">
        <div class="drawer-header">
            <h3>تعديل بيانات الطالب</h3>
            <button class="close-btn" id="TimesToCloseEditView">
                <i class="fa fa-times"></i>
            </button>
        </div>

        <div class="drawer-body">
            <!-- Student Header Badge -->
            <div class="student-badge">
                <div class="badge-icon">
                    <i class="fa fa-user-graduate"></i>
                </div>
                <div class="text-white">
                    <h5 id="studentIdDisplay">...</h5>
                    <span id="studyTypeDisplay" class="meta-tag">...</span>
                </div>
            </div>

            <!-- Main Form -->
            <div class="form-group">
                <label>اسم الطالب الكامل</label>
                <input type="text" id="StudentName" class="form-control" placeholder="اسم الرباعي واللقب">
            </div>

            <!-- Dynamic Stage Selects -->
            <div class="form-group" id="BachStageSelectDiv" style="display: none;">
                <label>المرحلة الدراسية</label>
                <select id="BachStageSelect" class="form-control">
                    <option id="0" value="0"> اختر المرحلة </option>
                    <option id="1" value="1">الاولى</option>
                    <option id="2" value="2">الثانية</option>
                    <option id="3" value="3">الثالثة</option>
                    <option id="4" value="4">الرابعة</option>
                    <option id="5" value="5">الخامسة</option>
                    <option id="6" value="6">السادسة</option>
                </select>
            </div>

            <div class="form-group" id="HighStageSelectDiv" style="display: none;">
                <label>المرحلة (دراسات عليا)</label>
                <select id="HighStageSelect" class="form-control">
                    <option id="0" value="0"> اختر المرحلة </option>
                    <option id="1" value="1">الاولى</option>
                    <option id="2" value="2">الثانية</option>
                </select>
            </div>

            <!-- Financial Section -->
            <div class="section-divider">
                <span>البيانات المالية</span>
            </div>

            <!-- Bach Financials -->
            <div id="bachFinancialSection" style="display: none;">
                <div class="form-group" id="acceptTypeChanelDivBach">
                    <label>نوع القبول</label>
                    <select id="AcceptTypeBach" class="form-control">
                        <option id="0" value="0">اختر نوع</option>
                        <!-- populated via JS usually? -->
                    </select>
                </div>
            </div>

            <!-- High Financials & Details -->
            <div id="HighStudentDetailsOnEditView" style="display: none;">
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label>الشهادة الدراسية</label>
                            <select id="AcceptChanelHigh" class="form-control">
                                <option id="0" selected>اختر</option>
                                <option id="1" value="1">دبلوم</option>
                                <option id="2" value="2">ماجستير</option>
                                <option id="3" value="3">دكتوراه</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label>قناة القبول</label>
                            <select id="HighIsPublic" class="form-control">
                                <option selected>اختر</option>
                                <option id="pubOption" value="1">عام</option>
                                <option id="PriOption" value="2">خاص</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>الامر الجامعي</label>
                    <input type="text" id="UniCommandInput" class="form-control">
                </div>

                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label>تاريخ المباشرة</label>
                            <input type="date" id="DateOfLunchInput" class="form-control">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label>الشهادة السابقة</label>
                            <input type="text" id="StudyCertInput" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>الشهادة</label>
                    <input type="text" id="TheOnlyCertInput" class="form-control">
                </div>
            </div>

            <div class="form-group" id="AmountDivAfterChangeAcceptType" style="display: none;">
                <label>المبلغ الكلي (للسنة الواحدة)</label>
                <input type="text" id="AmountInputAfterChangeAcceptType" class="form-control" disabled
                    style="color: var(--medical-teal); font-weight: bold; border-color: var(--medical-teal);">
            </div>

            <!-- Notes Section -->
            <div class="section-divider">
                <span>ملاحظات إضافية</span>
            </div>

            <div class="form-group">
                <textarea id="RemarksInput" class="form-control" rows="3" placeholder="أي ملاحظات إدارية..."></textarea>
            </div>
        </div>

        <!-- Footer Actions -->
        <div class="drawer-footer">
            <button id="EditStudentButton" class="btn btn-primary">
                <i class="fa fa-save ml-2"></i> حفظ التغييرات
            </button>
            <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 0): ?>
                <button id="DeleteStudentButton" class="btn btn-danger">
                    <i class="fa fa-trash ml-2"></i> حذف الطالب
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="custom/EditStudent.js?v=<?php echo time(); ?>" type="text/javascript"></script>

<?php
require_once 'includes/footer.php';
?>