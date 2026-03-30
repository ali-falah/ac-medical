<?php
require_once 'php_action/db_connect.php';
require_once 'includes/header.php';


if (!isset($_SESSION['user_type'])) {

    header('location:' . $url . 'login.php');
}

?>

<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 16px;
        padding: 25px;
        box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        margin-bottom: 30px;
    }

    .filter-bar {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        align-items: end;
    }

    .studentCheckbox {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    h3 {
        color: var(--medical-teal);
        font-weight: 700;
        letter-spacing: 2px;
    }
</style>

<div class="container mt-5">

    <div class="text-center mb-5">
        <h3>نقل الطلبة بين المراحل</h3>
        <p class="text-muted">قم بتحديد الطلبة والمرحلة الجديدة للنقل بنقرة واحدة</p>
    </div>

    <!-- Filter Card -->
    <div class="glass-card">
        <div class="filter-bar">
            <!-- Search -->
            <div class="form-group">
                <label>بحث بالاسم أو الرقم</label>
                <input type="text" id="searchInput" placeholder="أدخل اسم الطالب...">
            </div>

            <!-- Study Type -->
            <div class="form-group">
                <label>نوع الدراسة</label>
                <select id="studyTypeFilter">
                    <option value="all">عرض الكل</option>
                    <option value="bach">بكلوريوس</option>
                    <option value="high">دراسات عليا</option>
                </select>
            </div>

            <!-- Current Stage -->
            <div class="form-group">
                <label>المرحلة الحالية</label>
                <select id="currentStageFilter">
                    <option value="all">عرض الكل</option>
                    <option value="1">الأولى</option>
                    <option value="2">الثانية</option>
                    <option value="3">الثالثة</option>
                    <option value="4">الرابعة</option>
                    <option value="5">الخامسة</option>
                    <option value="6">السادسة</option>
                </select>
            </div>

            <!-- Target Stage -->
            <div class="form-group">
                <label>المرحلة المستهدفة (للنقل)</label>
                <select id="targetStage" class="border-success">
                    <option value="0">اختر المرحلة الجديدة</option>
                    <option value="1">الأولى</option>
                    <option value="2">الثانية</option>
                    <option value="3">الثالثة</option>
                    <option value="4">الرابعة</option>
                    <option value="5">الخامسة</option>
                    <option value="6">السادسة</option>
                </select>
            </div>

            <div class="form-group">
                <button id="transferBtn" class="btn btn-transfer w-100">
                    بدء عملية النقل
                </button>
            </div>
        </div>
    </div>

    <!-- Students Table -->
    <div class="glass-card p-0" style="overflow: hidden;">
        <table class="table text-center mb-0">
            <thead>
                <tr>
                    <th width="50"><input type="checkbox" id="selectAll"></th>
                    <th width="80">#</th>
                    <th>اسم الطالب</th>
                    <th>المرحلة</th>
                    <th>الدراسة</th>
                    <th>المتبقي</th>
                </tr>
            </thead>
            <tbody id="studentsTable">
                <!-- Filled by JS -->
            </tbody>
        </table>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>

<script>
    let BachData = [];
    let HighData = [];
    const options = {
        style: "currency",
        // currency: "IQD",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    };

    $(document).ready(function () {
        loadAllData();

        // Listeners for filters
        $('#searchInput, #studyTypeFilter, #currentStageFilter').on('input change', renderTable);

        $('#selectAll').on('change', function () {
            $('.studentCheckbox').prop('checked', this.checked);
        });
    });

    function loadAllData() {
        $.when(
            $.ajax({ type: "POST", url: "php_action/accountQueries.php", data: { getBachAllStudents: "getBachAllStudents" }, dataType: "json" }),
            $.ajax({ type: "POST", url: "php_action/accountQueries.php", data: { GetAllHighStudents: "GetAllHighStudents" }, dataType: "json" })
        ).then(function (bachRes, highRes) {
            BachData = bachRes[0].map(s => ({ ...s, type: 'bach' }));
            HighData = highRes[0].map(s => ({ ...s, type: 'high' }));
            renderTable();
        });
    }

    function renderTable() {
        const searchTerm = $('#searchInput').val().toLowerCase();
        const studyType = $('#studyTypeFilter').val();
        const currentStage = $('#currentStageFilter').val();

        const allData = [...BachData, ...HighData];
        const filtered = allData.filter(s => {
            const matchesSearch = (
                String(s.name || "").toLowerCase().includes(searchTerm) ||
                String(s.std_id || "").includes(searchTerm) ||
                String(s.remarks || "").toLowerCase().includes(searchTerm)
            );
            const matchesType = (studyType === 'all' || s.type === studyType);
            const matchesStage = (currentStage === 'all' || String(s.stage) === currentStage);

            return matchesSearch && matchesType && matchesStage;
        });

        const $tbody = $('#studentsTable').empty();

        filtered.forEach(s => {
            const stageName = convertStageNumToWord(Number(s.stage));
            const typeName = s.type === 'bach' ? 'بكلوريوس' : 'دراسات عليا';
            const remain = Number(s.total_remain).toLocaleString('en-US').split('.')[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",") + ' د.ع';

            $tbody.append(`
            <tr>
                <td><input type="checkbox" class="studentCheckbox" value="${s.std_id}-${s.type}"></td>
                <td>${s.std_id}</td>
                <td>${s.name}</td>
                <td>${stageName}</td>
                <td>${typeName}</td>
                <td class="${Number(s.total_remain) > 0 ? 'text-warning font-weight-bold' : ''}">${remain}</td>
            </tr>
        `);
        });

        if (filtered.length === 0) {
            $tbody.append('<tr><td colspan="6" class="py-5 text-muted">لم يتم العثور على نتائج</td></tr>');
        }
    }

    $('#transferBtn').on('click', function () {
        const targetStage = $('#targetStage').val();
        if (targetStage == 0) {
            alert('يرجى اختيار المرحلة الجديدة أولاً');
            return;
        }

        const selected = $('.studentCheckbox:checked').map(function () { return this.value; }).get();
        if (selected.length === 0) {
            alert('يرجى تحديد طالب واحد على الأقل');
            return;
        }

        if (!confirm(`هل أنت متأكد من نقل ${selected.length} طالب؟`)) return;

        fetch('php_action/transfer_students.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ students: selected, targetStage: targetStage })
        })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                loadAllData();
                $('#selectAll').prop('checked', false);
            });
    });

    function convertStageNumToWord(stage) {
        const stages = ["", "الأولى", "الثانية", "الثالثة", "الرابعة", "الخامسة", "السادسة"];
        return stages[stage] || stage;
    }
</script>