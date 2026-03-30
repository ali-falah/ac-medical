<?php
require_once 'php_action/db_connect.php';
require_once 'includes/header.php';

// Access Control: Only Admin (user_type == 0) can access
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 0) {
    echo "<script>location.href='index.php';</script>";
    exit();
}
?>

<div class="dashboard-container">
    <div class="container-fluid">
        <!-- Header Section -->
        <div class="welcome-section row align-items-center animate-up" style="animation-delay: 0.1s;">
            <div class="col-md-8">
                <h1>إدارة المستخدمين</h1>
                <p class="text-muted">إدارة حسابات النظام والصلاحيات</p>
            </div>
            <div class="col-md-4 text-left">
                <button class="btn btn-grad-teal shadow-lg" data-toggle="modal" data-target="#addUserModal"
                    onclick="resetForm()">
                    <i class="fa fa-plus-circle ml-2"></i> إضافة مستخدم جديد
                </button>
            </div>
        </div>

        <!-- Users Table Card -->
        <div class="row animate-up" style="animation-delay: 0.2s;">
            <div class="col-12">
                <div class="recent-activity-card">
                    <div class="card-header">
                        <i class="fa fa-users mr-2"></i> قائمة المستخدمين
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-modern align-middle" id="usersTable">
                                <thead>
                                    <tr class="text-dark">
                                        <th width="10%">#</th>
                                        <th width="30%">اسم المستخدم</th>
                                        <th width="30%">الصلاحية</th>
                                        <th width="30%">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Add/Edit User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content glass-modal">
            <div class="modal-header border-0">
                <h5 class="modal-title font-weight-bold" id="modalTitle">إضافة مستخدم جديد</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="userForm">
                    <input type="hidden" id="userId" name="userId">

                    <div class="form-group">
                        <label>اسم المستخدم <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="username" name="username" required
                            placeholder="example_user">
                    </div>

                    <div class="form-group">
                        <label>كلمة المرور <span id="passHint" class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password" name="password"
                            placeholder="********">
                        <small class="text-muted" id="editPassNote" style="display:none;">اترك الحقل فارغاً اذا كنت لا
                            تريد تغيير كلمة المرور</small>
                    </div>

                    <div class="form-group">
                        <label>الصلاحية <span class="text-danger">*</span></label>
                        <select class="form-control" id="userType" name="userType" required>
                            <option value="">-- اختر الصلاحية --</option>
                            <option value="0">مدير نظام (Admin)</option>
                            <option value="1">حسابات (Finance)</option>
                            <option value="2">تسجيل (Registration)</option>
                            <option value="3">مدير (Manager)</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-grad-teal" onclick="submitUserForm()">حفظ البيانات</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content glass-modal bg-danger-light">
            <div class="modal-header border-0">
                <h5 class="modal-title text-danger font-weight-bold">حذف المستخدم</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fa fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                <p class="mb-0 font-weight-bold">هل أنت متأكد من حذف هذا المستخدم؟</p>
                <p class="text-muted small">لا يمكن التراجع عن هذا الإجراء</p>
                <input type="hidden" id="deleteUserId">
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-outline-secondary px-4" data-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-danger px-4" onclick="confirmDeleteUser()">نعم، حذف</button>
            </div>
        </div>
    </div>
</div>

<script src="custom/users.js"></script>

<?php require_once 'includes/footer.php'; ?>