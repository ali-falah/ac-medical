
$(document).ready(function () {
    getAllUsers();
});

// Fetch all users
function getAllUsers() {
    $.ajax({
        url: 'php_action/users.php',
        type: 'post',
        data: { action: 'getAllUsers' },
        dataType: 'json',
        success: function (response) {
            let html = '';
            if (response.data && response.data.length > 0) {
                response.data.forEach((user, index) => {
                    let roleBadge = getRoleBadge(user.user_type);
                    let buttons = '';

                    // Prevent editing/deleting Admin (type 0)
                    if (user.user_type == 0) {
                        buttons = `<span class="badge badge-light text-muted"><i class="fa fa-lock"></i> محمي</span>`;
                    } else {
                        buttons = `
                            <button class="btn btn-sm btn-outline-info ml-1" onclick="editUser(${user.user_id}, '${user.username}', ${user.user_type})">
                                <i class="fa fa-edit"></i> تعديل
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="openDeleteModal(${user.user_id})">
                                <i class="fa fa-trash"></i> حذف
                            </button>
                        `;
                    }

                    html += `
                        <tr>
                            <td>${index + 1}</td>
                            <td><span class="font-weight-bold">${user.username}</span></td>
                            <td>${roleBadge}</td>
                            <td>${buttons}</td>
                        </tr>
                    `;
                });
            } else {
                html = '<tr><td colspan="4" class="text-center py-4 text-muted">لا يوجد مستخدمين مسجلين</td></tr>';
            }
            $('#usersTable tbody').html(html);
        },
        error: function (err) {
            console.log(err);
            showError('خطأ في جلب البيانات');
        }
    });
}

function getRoleBadge(type) {
    switch (Number(type)) {
        case 0: return '<span class="badge badge-danger">مدير نظام (Admin)</span>';
        case 1: return '<span class="badge badge-success">حسابات (Finance)</span>';
        case 2: return '<span class="badge badge-info">تسجيل (Registration)</span>';
        case 3: return '<span class="badge badge-warning">مدير (Manager)</span>';
        default: return '<span class="badge badge-secondary">غير معروف</span>';
    }
}

// Reset Form for Adding
function resetForm() {
    $('#userForm')[0].reset();
    $('#userId').val('');
    $('#modalTitle').text('إضافة مستخدم جديد');
    $('#editPassNote').hide();
    $('#passHint').show(); // Asterisk required
    $('#password').attr('required', true);
}

// Open Edit Modal
function editUser(id, username, type) {
    $('#userId').val(id);
    $('#username').val(username);
    $('#userType').val(type);
    
    // UI Adjustments for Edit
    $('#modalTitle').text('تعديل المستخدم');
    $('#editPassNote').show();
    $('#passHint').hide(); // Password not required on edit
    $('#password').removeAttr('required');
    
    $('#addUserModal').modal('show');
}

// Submit Form (Add or Edit)
function submitUserForm() {
    // Basic Validation
    const username = $('#username').val().trim();
    const userType = $('#userType').val();
    const password = $('#password').val();
    const userId = $('#userId').val();

    if (!username || userType === "") {
        showWarning("يرجى ملء جميع الحقول المطلوبة");
        return;
    }

    // Password Validation for New User
    if (!userId && !password) {
        showWarning("كلمة المرور مطلوبة للمستخدم الجديد");
        return;
    }

    const formData = {
        action: userId ? 'updateUser' : 'createUser',
        userId: userId,
        username: username,
        password: password,
        userType: userType
    };

    $.ajax({
        url: 'php_action/users.php',
        type: 'post',
        data: formData,
        dataType: 'json',
        success: function (response) {
            if (response.status === 'success') {
                $('#addUserModal').modal('hide');
                getAllUsers();
                showSuccess(response.message);
            } else {
                showError('خطأ: ' + response.message);
            }
        },
        error: function (err) {
            console.log(err);
            showError('حدث خطأ أثناء حفظ البيانات');
        }
    });
}

// Delete Handling
function openDeleteModal(id) {
    $('#deleteUserId').val(id);
    $('#deleteUserModal').modal('show');
}

function confirmDeleteUser() {
    const id = $('#deleteUserId').val();
    if (!id) return;

    $.ajax({
        url: 'php_action/users.php',
        type: 'post',
        data: { action: 'deleteUser', userId: id },
        dataType: 'json',
        success: function (response) {
            $('#deleteUserModal').modal('hide');
            if (response.status === 'success') {
                getAllUsers();
                showSuccess(response.message);
            } else {
                showError('خطأ: ' + response.message);
            }
        },
        error: function (err) {
            console.log(err);
            showError('حدث خطأ أثناء الحذف');
        }
    });
}
