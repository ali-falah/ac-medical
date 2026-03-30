<?php

require_once 'php_action/db_connect.php';
require_once 'includes/header.php';


if (!isset($_SESSION['user_type'])) {

    header('location:' . $url . 'login.php');

}

?>



<div class="container mt-5">
    <div class="glass-card text-center mb-5">
        <h3>رفع بيانات الطلاب عبر Excel</h3>
        <p class="text-muted">استخدم النموذج المخصص لإضافة الطلاب بشكل جماعي إلى النظام</p>

        <div class="d-flex justify-content-center align-items-center gap-4 mt-4">
            <a href="Student_Template.xlsx" class="btn btn-outline-info mx-3" download>
                <i class="fa fa-download"></i> تنزيل نموذج Excel
            </a>

            <div class="custom-file w-50">
                <input id="fileInput" type="file" class="custom-file-input" accept=".xls, .xlsx">
                <label class="custom-file-label text-right" for="fileInput">اختر ملف الاكسل</label>
            </div>

            <button id="register-all-btn" class="btn btn-transfer mx-3">
                <i class="fa fa-upload"></i> تسجيل جميع الطلاب
            </button>
        </div>
    </div>

    <div id="tableContainer" class="glass-card p-0" style="overflow: hidden;">
        <div class="p-5 text-center text-muted">لم يتم اختيار أي ملف بعد</div>
    </div>
</div>

<div id="importSpinner" class="spinner-border text-info d-none"
    style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 100px; height: 100px; z-index: 2000; border-width: 8px;">
</div>

<script>
    // Define an array to hold Excel data
    let excelData = [];

    // Function to handle file input change
    $("#fileInput").change(function (e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();

            reader.onload = function (e) {
                const data = new Uint8Array(e.target.result);

                // Parse the Excel file
                const workbook = XLSX.read(data, { type: "array" });

                // Assuming the first sheet in the workbook is the one you want to read
                const sheet = workbook.Sheets[workbook.SheetNames[0]];

                // Convert sheet data to JSON
                const jsonData = XLSX.utils.sheet_to_json(sheet, { header: 1 });

                // Store the Excel data in the array
                excelData = jsonData;



                // Generate HTML table
                const table = $("<table>").addClass("table table-bordered");
                const thead = $("<thead>").appendTo(table);
                const tbody = $("<tbody>").appendTo(table);

                // Create table headers from the first row of Excel data
                const headers = jsonData[0];
                headers.forEach((header) => {
                    $("<th>").text(header).appendTo(thead);
                });

                // Create table rows from Excel data
                for (let i = 1; i < jsonData.length; i++) {
                    const row = $("<tr>").appendTo(tbody);
                    jsonData[i].forEach((cell) => {
                        $("<td>").text(cell).appendTo(row);
                    });
                }

                // Display the table in the tableContainer div
                $("#tableContainer").empty().append(table);
            };

            reader.readAsArrayBuffer(file);
        }
    });


    $('#register-all-btn').click(function (e) {
        e.preventDefault();
        if ($("#fileInput").val() != "") {

            $('#importSpinner').removeClass('d-none');
            excelData.forEach((element, index) => {
                var formData = new FormData()
                e
                formData.append("total", Number(element[3]));
                formData.append("discountPercentage", element[4]);

                formData.append("studentStageBachelor", element[1]);
                formData.append("accept_type_id", element[2]);

                formData.append("RemainInput", Number(element[3]));
                formData.append("studentRemarks", element[5]);
                formData.append("studentNameInput", element[0]);

                if (index > 0) {
                    $.ajax({
                        url: "php_action/RegisQueries.php",
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function (response) {
                            console.log('====================================');
                            console.log(response);
                            console.log('====================================');
                        }
                    });
                }
            });


            setTimeout(() => {
                $('#importSpinner').addClass('d-none');
                alert('تمت اضافة الطلاب')
            }, 1500);

        } else {
            alert('يرجى اختيار ملف اكسل حسب النموذج')
        }
    });

    // You can now use the excelData array for further processing
</script>

<?php require_once 'includes/footer.php'; ?>