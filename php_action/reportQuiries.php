<!DOCTYPE html>
<html lang="ar">

<head>
    <title>تقرير</title>

    <!-- fonts -->
    <link rel="stylesheet" href="assests/font-awesome/css/font-awesome.css" />
    <link href="https://fonts.googleapis.com/css2?family=Readex+Pro:wght@200;400&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Cairo" rel="stylesheet">
    <link rel="icon" href="2.png">

    <script src="assests/jquery.slim.js"></script>
    <script src="assests/jquery/jquery.min.js"></script>

    <!-- DataTables -->
    <link rel="stylesheet" href="assests/plugins/datatables/jquery.dataTables.min.css">

    <!-- file input -->
    <link rel="stylesheet" href="assests/plugins/fileinput/css/fileinput.min.css">

    <!-- jquery ui -->
    <link rel="stylesheet" href="assests/jquery-ui/jquery-ui.min.css">
    <script src="assests/jquery-ui/jquery-ui.min.js"></script>

    <link rel="stylesheet" href="assests/bootstrap/dist/css/bootstrap.min.css">
    <script src="assests/bootstrap/dist/js/bootstrap.bundle.min.js"></script>

    <script src="assests/xlsx.js"></script>
    <script src="assests/proper.js"></script>

    <style type="text/css">
        #PrintTheReportIcon {
            position: absolute;
            top: 1%;
            right: 1%;
            cursor: pointer;
        }

        #export-report-to-excel-btn {
            position: absolute;
            top: 1%;
            left: 1%;
            cursor: pointer;
        }

        body {
            direction: rtl;
        }

        @media print {

            table th,
            table td,
            table tr {
                border: 1px solid black !important;
            }

            #PrintTheReportIcon,
            #export-report-to-excel-btn {
                display: none;
            }
        }

        * {
            font-family: 'Cairo', 'sans-serif';
        }
    </style>
</head>

<body>
    <?php
    require_once 'db_connect.php';

    if (isset($_POST['studentId'])) {
        $id = (int) $_POST['studentId'];
        $type = (int) $_POST['studentAcceptType'];
        $PaidOrNot = (int) $_POST['studentPaidOrNot'];

        function getReportData($connect, $id, $type, $PaidOrNot)
        {
            $params = [];
            $types = "";

            if ($type == 0 || $type == 1) {
                $sql = "SELECT student.student_id, student.name, student.create_date, student.stage,
                        accept_type.name AS accept_type_name, accept_type.amount, 
                        student.discount_percentage, student.total_amount, student.total_remain, 
                        student.remarks, student.IsDeleted 
                        FROM student 
                        INNER JOIN accept_type ON student.accept_type_id = accept_type.accept_type_id 
                        WHERE student.IsDeleted = 0 ";
                if ($id != 0) {
                    $sql .= " AND student.student_id = ? ";
                    $params[] = $id;
                    $types .= "i";
                }
                if ($PaidOrNot == 1) {
                    $sql .= " AND student.total_remain = 0 ";
                } elseif ($PaidOrNot == 2) {
                    $sql .= " AND student.total_remain != 0 ";
                }
            } elseif ($type == 2) {
                $sql = "SELECT student_high.student_high_id, student_high.name, student_high.create_date, 
                        student_high.stage, accept_type_high.name AS accept_type_name, 
                        accept_type_high.amount, student_high.discount_percentage, 
                        student_high.total_amount, student_high.total_remain, 
                        student_high.remarks, student_high.IsDeleted, 
                        student_high.UniversityComand, student_high.DateOfLaunch, 
                        student_high.StudyCert, student_high.TheOnlyCert 
                        FROM student_high 
                        INNER JOIN accept_type_high ON student_high.accept_type_high_id = accept_type_high.accept_type_high_id 
                        WHERE student_high.IsDeleted = 0 ";
                if ($id != 0) {
                    $sql .= " AND student_high.student_high_id = ? ";
                    $params[] = $id;
                    $types .= "i";
                }
                if ($PaidOrNot == 1) {
                    $sql .= " AND student_high.total_remain = 0 ";
                } elseif ($PaidOrNot == 2) {
                    $sql .= " AND student_high.total_remain != 0 ";
                }
            }

            $stmt = $connect->prepare($sql);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            return $stmt->get_result();
        }

        $res = getReportData($connect, $id, $type, $PaidOrNot);

        // Prepare Payment Query if specific student is selected
        $PaymentSQL = "";
        $paymentParams = [];
        $paymentTypes = "";
        if ($id != 0) {
            if ($type == 1) {
                $PaymentSQL = "SELECT payment_date, payment_amount, remain, payment_num, img, remarks FROM payment WHERE student_id = ? AND IsDeleted = 0";
                $paymentParams = [$id];
                $paymentTypes = "i";
            } elseif ($type == 2) {
                $PaymentSQL = "SELECT payment_date, payment_amount, remain, payment_num, img, remarks FROM payment_high WHERE student_high_id = ? AND IsDeleted = 0";
                $paymentParams = [$id];
                $paymentTypes = "i";
            }
        }

        echo '<div class="container-fluid mt-4 px-5">

                <i id="PrintTheReportIcon" class="fa fa-print" aria-hidden="true"></i>
                <i id="export-report-to-excel-btn" class="fa fa-file-excel-o" aria-hidden="true"></i>

                <table id="report-table" class="table table-striped table-hover text-center  table-bordered" style="font-size: 14px;cursor: pointer;">
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>اسم الطالب</th>
                            <th>تاريخ التسجيل</th>
                            <th>المرحلة</th>
                            <th>الدراسة</th>
                            <th>الكلي</th>
                            <th>الخصم</th>
                            <th>الصافي</th>
                            <th>المتبقي</th>
                            <th>ملاحظات</th>
                        </tr>
                    </thead>
                    <tbody>';

        $total = 0;
        $remain = 0;
        $highStudentMetadata = null;

        while ($value = $res->fetch_assoc()) {
            if ($type == 2 && $id != 0) {
                $highStudentMetadata = [
                    'UniCommand' => $value['UniversityComand'],
                    'dateOfLaunch' => $value['DateOfLaunch'],
                    'StudyCert' => $value['StudyCert'],
                    'TheOnlyCer' => $value['TheOnlyCert']
                ];
            }

            $isDeleted = $value['IsDeleted'];
            $total += $value['total_amount'];
            $remain += $value['total_remain'];

            $rowClass = $isDeleted ? 'text-danger font-weight-bolder' : '';

            echo '<tr class="' . $rowClass . '">
                    <td>' . $value[($type == 2 ? 'student_high_id' : 'student_id')] . '</td>
                    <td>' . $value['name'] . '</td>
                    <td>' . $value['create_date'] . '</td>
                    <td>' . $value['stage'] . '</td>
                    <td>' . $value['accept_type_name'] . '</td>
                    <td>' . number_format($value['amount']) . ' د.ع</td>
                    <td>' . $value['discount_percentage'] . ' %</td>
                    <td>' . number_format($value['total_amount']) . ' د.ع</td>
                    <td>' . number_format($value['total_remain']) . ' د.ع</td>
                    <td style="word-wrap: break-word;">' . $value['remarks'] . '</td>
                </tr>';
        }

        $UniCommand = $highStudentMetadata ? $highStudentMetadata['UniCommand'] : "";
        $dateOfLaunch = $highStudentMetadata ? $highStudentMetadata['dateOfLaunch'] : "";
        $StudyCert = $highStudentMetadata ? $highStudentMetadata['StudyCert'] : "";
        $TheOnlyCer = $highStudentMetadata ? $highStudentMetadata['TheOnlyCer'] : "";

        $formatted_total = number_format($total) . ' د.ع';
        $formatted_remain = number_format($remain) . ' د.ع';
        echo '<tr>
                <td colspan="9">مجموع المبلغ الكلي بعد الخصم</td>
                <td colspan="2" style="letter-spacing: 2px;" class="font-weight-bolder">' . $formatted_total . '</td>
            </tr>
            <tr>
                <td colspan="9">مجموع الدين المتبقي</td>
                <td colspan="2" style="letter-spacing: 2px;" class="font-weight-bolder">' . $formatted_remain . '</td>
            </tr>';

        echo '</tbody></table>';

        //append high student additional info 
        if ($id != 0 && $type == 2) {
            echo '<table class="table table-bordered table-striped text-center" style="font-size: 18px;">
                    <thead>
                        <tr>
                            <th>الامر الجامعي</th>
                            <th>تاريخ المباشرة</th>
                            <th>الشهادة الدراسية</th>
                            <th>الشهادة</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>' . $UniCommand . '</td>
                            <td>' . $dateOfLaunch . '</td>
                            <td>' . $StudyCert . '</td>
                            <td>' . $TheOnlyCer . '</td>
                        </tr>
                    </tbody>
                </table>';
        }
        // high student additional ends here 
    
        if ($PaymentSQL != "") {
            echo '<table class="table text-center" style="font-size: 18px;">
                    <thead>
                        <tr>
                            <th>رقم الدفعة</th>
                            <th>تاريخ الدفعة</th>
                            <th>قيمة الدفعة</th>
                            <th>المتبقي حتى تاريخ الدفعة</th>
                            <th>صورة للوصل</th>
                            <th>ملاحظات</th>
                        </tr>
                    </thead>
                    <tbody>';

            $stmtP = $connect->prepare($PaymentSQL);
            $stmtP->bind_param($paymentTypes, ...$paymentParams);
            $stmtP->execute();
            $queryP = $stmtP->get_result();

            while ($value = $queryP->fetch_assoc()) {
                $uploadDir = ($type == 1) ? 'uploads' : 'uploadsHigh';
                $imgn = '<a href="php_action/' . $uploadDir . '/' . $value['img'] . '" target="_blank">اضفط هنا</a>';

                echo '<tr>
                        <td>' . $value['payment_num'] . '</td>
                        <td>' . $value['payment_date'] . '</td>
                        <td>' . number_format($value['payment_amount']) . ' د.ع</td> 
                        <td>' . number_format($value['remain']) . ' د.ع</td>
                        <td>' . $imgn . '</td>
                        <td>' . $value['remarks'] . '</td>
                    </tr>';
            }
            echo '</tbody></table></div>';
        }
    }
    ?>

    <script>
        $(document).ready(function () {
            $('#PrintTheReportIcon').click(function (e) {
                e.preventDefault();
                window.print();
            });

            $('#export-report-to-excel-btn').click(function () {
                // Get the table data as an array of arrays
                var tableData = [];
                $('#report-table').find('tr').each(function () {
                    var rowData = [];
                    $(this).find('th, td').each(function () {
                        rowData.push($(this).text());
                    });
                    tableData.push(rowData);
                });

                // Create a worksheet
                var ws = XLSX.utils.aoa_to_sheet(tableData);

                // Create a workbook
                var wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, 'Sheet1');

                // Save the workbook as an Excel file
                XLSX.writeFile(wb, 'report.xlsx');
            });
        });
    </script>
</body>

</html>