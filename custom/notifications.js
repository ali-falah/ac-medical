// Custom Notification Helpers using SweetAlert2

const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
});

function showSuccess(message) {
    Toast.fire({
        icon: 'success',
        title: message
    });
}

function showError(message) {
    Toast.fire({
        icon: 'error',
        title: message
    });
}

function showWarning(message) {
    Toast.fire({
        icon: 'warning',
        title: message
    });
}

function showConfirm(title, text, confirmBtnText, callback) {
    Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#17a2b8', // medical-teal
        cancelButtonColor: '#dc3545',
        confirmButtonText: confirmBtnText,
        cancelButtonText: 'إلغاء'
    }).then((result) => {
        if (result.isConfirmed) {
            callback();
        }
    });
}
