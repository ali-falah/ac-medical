// Select all
document.getElementById('selectAll').addEventListener('change', function () {
    document.querySelectorAll('.studentCheckbox').forEach(cb => {
        cb.checked = this.checked;
    });
});

// Transfer
document.getElementById('transferBtn').addEventListener('click', function () {

    const targetStage = document.getElementById('targetStage').value;
    if (targetStage == 0) {
        alert('اختر المرحلة الجديدة');
        return;
    }

    let students = [];
    document.querySelectorAll('.studentCheckbox:checked').forEach(cb => {
        students.push(cb.value);
    });

    if (students.length === 0) {
        alert('اختر طالب واحد على الأقل');
        return;
    }

    fetch('php_action/transfer_students.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            students: students,
            targetStage: targetStage
        })
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        location.reload();
    });

});
