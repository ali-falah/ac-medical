<?php
require_once 'db_connect.php';
// error_reporting(E_ALL);
// ini_set('display_errors', 0); // Disable display errors to avoid breaking JSON



if (isset($_POST['getBachAllStudents'])) {


    $sql = 'SELECT student.student_id AS std_id, student.name, student.stage, accept_type.name AS accept_type_name, student.total_remain, student.remarks, student.create_date, student.total_amount
    FROM student 
    JOIN accept_type ON student.accept_type_id = accept_type.accept_type_id 
    WHERE student.IsDeleted = 0
    ORDER BY student.student_id desc
    ';


    $res = $connect->query($sql);

    echo json_encode($res->fetch_all(MYSQLI_ASSOC));
}


if (isset($_POST['GetAllHighStudents'])) {


    $sql = 'SELECT student_high.student_high_id AS std_id, student_high.name, student_high.stage, accept_type_high.name AS accept_type_name, student_high.total_remain, student_high.remarks, student_high.create_date, student_high.total_amount
    FROM student_high 
    JOIN accept_type_high ON student_high.accept_type_high_id = accept_type_high.accept_type_high_id 
    WHERE student_high.IsDeleted = 0
    ORDER BY student_high.student_high_id desc';


    $res = $connect->query($sql);

    echo json_encode($res->fetch_all(MYSQLI_ASSOC));
}


if (isset($_POST['StudentHighIdToGetHisInfo'])) {
    $id = $_POST['StudentHighIdToGetHisInfo'];

    $sql = "SELECT student_high.IsPublic, student_high.UniversityComand, student_high.DateOfLaunch, student_high.StudyCert, student_high.TheOnlyCert, student_high.student_high_id, student_high.name, student_high.total_amount, student_high.total_remain, student_high.stage, student_high.remarks, student_high.discount_percentage, student_high.create_date, accept_type_high.amount, accept_type_high.name As AcceptTypeName
    FROM student_high 
    JOIN accept_type_high ON accept_type_high.accept_type_high_id = student_high.accept_type_high_id
    WHERE student_high.student_high_id = ?";

    $stmt = $connect->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();

    echo json_encode($res->fetch_assoc());
}


if (isset($_POST['StudentBachIdToGetHisInfo'])) {
    $id = $_POST['StudentBachIdToGetHisInfo'];

    try {
        $sql = "SELECT student.student_id, student.name, student.total_amount, student.total_remain, student.stage, student.discount_percentage, student.remarks, student.create_date, accept_type.amount, accept_type.name As AcceptTypeName
        FROM student 
        JOIN accept_type ON accept_type.accept_type_id = student.accept_type_id
        WHERE student.student_id = ?";

        $stmt = $connect->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res) {
            echo json_encode($res->fetch_assoc());
        } else {
            throw new Exception("Error executing query: " . $stmt->error);
        }
    } catch (Exception $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }
}

if (isset($_POST['StudentBachIdToGetHisPayments'])) {
    $id = $_POST['StudentBachIdToGetHisPayments'];

    try {
        $sql = "SELECT payment.payment_id, payment.payment_num, payment.payment_amount, payment.remain, payment.payment_date, payment.remarks, payment.img
        FROM payment 
        WHERE payment.student_id = ? AND IsDeleted = 0";

        $stmt = $connect->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res) {
            echo json_encode($res->fetch_all(MYSQLI_ASSOC));
        } else {
            throw new Exception("Error executing query: " . $stmt->error);
        }
    } catch (Exception $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }
}


if (isset($_POST['StudentHighIdToGetHisPayments'])) {
    $id = $_POST['StudentHighIdToGetHisPayments'];

    try {
        $sql = "SELECT payment_high.payment_high_id, payment_high.payment_num, payment_high.payment_amount, payment_high.remain, payment_high.payment_date, payment_high.remarks, payment_high.img
        FROM payment_high
        WHERE payment_high.student_high_id = ? AND IsDeleted = 0";

        $stmt = $connect->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res) {
            echo json_encode($res->fetch_all(MYSQLI_ASSOC));
        } else {
            throw new Exception("Error executing query: " . $stmt->error);
        }
    } catch (Exception $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }
}


if (isset($_FILES['paymentImg']) && isset($_POST['BachStudentId'])) {
    $LastStudent_id = (int) $_POST['BachStudentId'];

    if ($LastStudent_id <= 0) {
        echo "Error: Invalid Bach Student ID";
        exit();
    }

    $PaymenAmount = (float) $_POST['paidAmount'];
    $PaymentRemarksinput = $_POST['remarks'];
    $PaymenyNum = (int) $_POST['paymentNum'];
    $RemainInput = (float) $_POST['remain'];

    try {
        $targetDirectory = __DIR__ . "/uploads/";
        $imgName = date("h_i_s_A") . "_" . basename($_FILES["paymentImg"]["name"]);
        $imgName = preg_replace("/[^A-Za-z0-9.]/", "_", $imgName);
        $targetFile = $targetDirectory . $imgName;

        if (!is_dir($targetDirectory) || !is_writable($targetDirectory)) {
            throw new Exception("Target directory is not writable or does not exist.");
        }

        if ($_FILES["paymentImg"]["error"] !== UPLOAD_ERR_OK) {
            throw new Exception("File upload failed. Error code: " . $_FILES["paymentImg"]["error"]);
        }

        if (move_uploaded_file($_FILES["paymentImg"]["tmp_name"], $targetFile)) {
            $connect->begin_transaction();

            // Atomic update: subtract from current balance instead of setting absolute value from client
            $sqlInsert = "INSERT INTO payment (student_id, payment_amount, remain, payment_num, img, remarks, IsDeleted) 
                          VALUES (?, ?, (SELECT total_remain - ? FROM student WHERE student_id = ?), ?, ?, ?, 0)";
            $stmtInsert = $connect->prepare($sqlInsert);
            $stmtInsert->bind_param("ididiss", $LastStudent_id, $PaymenAmount, $PaymenAmount, $LastStudent_id, $PaymenyNum, $imgName, $PaymentRemarksinput);

            if ($stmtInsert->execute()) {
                $sqlUpdate = "UPDATE student SET total_remain = total_remain - ?, num_of_payments = ? WHERE student_id = ?";
                $stmtUpdate = $connect->prepare($sqlUpdate);
                $stmtUpdate->bind_param("dii", $PaymenAmount, $PaymenyNum, $LastStudent_id);

                if ($stmtUpdate->execute()) {
                    $connect->commit();
                    echo 'ok';
                } else {
                    $connect->rollback();
                    throw new Exception("Error updating student balance: " . $stmtUpdate->error);
                }
            } else {
                $connect->rollback();
                throw new Exception("Error recording payment: " . $stmtInsert->error);
            }
        } else {
            throw new Exception("Failed to move uploaded file.");
        }
    } catch (Exception $e) {
        echo $e->getMessage();
        exit();
    }
}



if (isset($_FILES['paymentImg']) && isset($_POST['HighStudentId'])) {
    $LastStudent_id = (int) $_POST['HighStudentId'];

    if ($LastStudent_id <= 0) {
        echo "Error: Invalid High Student ID";
        exit();
    }

    $PaymenAmount = (float) $_POST['paidAmount'];
    $PaymentRemarksinput = $_POST['remarks'];
    $PaymenyNum = (int) $_POST['paymentNum'];
    $RemainInput = (float) $_POST['remain'];

    try {
        $targetDirectory = __DIR__ . "/uploadsHigh/";
        $imgName = date("h_i_s_A") . "_" . basename($_FILES["paymentImg"]["name"]);
        $imgName = preg_replace("/[^A-Za-z0-9.]/", "_", $imgName);
        $targetFile = $targetDirectory . $imgName;

        if (!is_dir($targetDirectory) || !is_writable($targetDirectory)) {
            throw new Exception("Target directory is not writable or does not exist.");
        }

        if ($_FILES["paymentImg"]["error"] !== UPLOAD_ERR_OK) {
            throw new Exception("File upload failed. Error code: " . $_FILES["paymentImg"]["error"]);
        }

        if (move_uploaded_file($_FILES["paymentImg"]["tmp_name"], $targetFile)) {
            $connect->begin_transaction();

            $sqlInsert = "INSERT INTO payment_high (student_high_id, payment_amount, remain, payment_num, img, remarks, IsDeleted) 
                          VALUES (?, ?, (SELECT total_remain - ? FROM student_high WHERE student_high_id = ?), ?, ?, ?, 0)";
            $stmtInsert = $connect->prepare($sqlInsert);
            $stmtInsert->bind_param("ididiss", $LastStudent_id, $PaymenAmount, $PaymenAmount, $LastStudent_id, $PaymenyNum, $imgName, $PaymentRemarksinput);

            if ($stmtInsert->execute()) {
                $sqlUpdate = "UPDATE student_high SET total_remain = total_remain - ?, num_of_payments = ? WHERE student_high_id = ?";
                $stmtUpdate = $connect->prepare($sqlUpdate);
                $stmtUpdate->bind_param("dii", $PaymenAmount, $PaymenyNum, $LastStudent_id);

                if ($stmtUpdate->execute()) {
                    $connect->commit();
                    echo 'ok';
                } else {
                    $connect->rollback();
                    throw new Exception("Error updating student_high balance: " . $stmtUpdate->error);
                }
            } else {
                $connect->rollback();
                throw new Exception("Error recording high payment: " . $stmtInsert->error);
            }
        } else {
            throw new Exception("Failed to move uploaded file.");
        }
    } catch (Exception $e) {
        echo $e->getMessage();
        exit();
    }
}






if (isset($_POST['PaymentIdToDelete'])) {
    $id = (int) $_POST['PaymentIdToDelete'];
    $remainAfterDeletion = (float) $_POST['remainAfterDeletion'];
    $selectedStudentId = (int) $_POST['selectedStudentId'];

    $connect->begin_transaction();
    try {
        // Get the payment amount before deleting the record
        $getPaymentSql = "SELECT payment_amount FROM payment WHERE payment_id = ?";
        $stmtGet = $connect->prepare($getPaymentSql);
        $stmtGet->bind_param("i", $id);
        $stmtGet->execute();
        $paymentAmount = $stmtGet->get_result()->fetch_assoc()['payment_amount'];

        $sql1 = "UPDATE payment SET IsDeleted = 1 WHERE payment_id = ?";
        $stmt1 = $connect->prepare($sql1);
        $stmt1->bind_param("i", $id);

        if ($stmt1->execute()) {
            // Atomic update: add back the payment amount to the balance
            $sql2 = "UPDATE student SET total_remain = total_remain + ?, num_of_payments = num_of_payments - 1 WHERE student_id = ?";
            $stmt2 = $connect->prepare($sql2);
            $stmt2->bind_param("di", $paymentAmount, $selectedStudentId);

            if ($stmt2->execute()) {
                $connect->commit();
                echo 'ok';
            } else {
                throw new Exception("Error updating student after payment deletion: " . $stmt2->error);
            }
        } else {
            throw new Exception("Error deleting payment: " . $stmt1->error);
        }
    } catch (Exception $e) {
        $connect->rollback();
        echo $e->getMessage();
    }
}

if (isset($_POST['PaymentHighIdToDelete'])) {
    $id = (int) $_POST['PaymentHighIdToDelete'];
    $remainAfterDeletion = (float) $_POST['remainAfterDeletion'];
    $selectedStudentId = (int) $_POST['selectedStudentId'];

    $connect->begin_transaction();
    try {
        // Get high payment amount before deletion
        $getPaymentSql = "SELECT payment_amount FROM payment_high WHERE payment_high_id = ?";
        $stmtGet = $connect->prepare($getPaymentSql);
        $stmtGet->bind_param("i", $id);
        $stmtGet->execute();
        $paymentAmount = $stmtGet->get_result()->fetch_assoc()['payment_amount'];

        $sql1 = "UPDATE payment_high SET IsDeleted = 1 WHERE payment_high_id = ?";
        $stmt1 = $connect->prepare($sql1);
        $stmt1->bind_param("i", $id);

        if ($stmt1->execute()) {
            // Atomic update for student_high
            $sql2 = "UPDATE student_high SET total_remain = total_remain + ?, num_of_payments = num_of_payments - 1 WHERE student_high_id = ?";
            $stmt2 = $connect->prepare($sql2);
            $stmt2->bind_param("di", $paymentAmount, $selectedStudentId);

            if ($stmt2->execute()) {
                $connect->commit();
                echo 'ok';
            } else {
                throw new Exception("Error updating student_high after payment deletion: " . $stmt2->error);
            }
        } else {
            throw new Exception("Error deleting payment_high: " . $stmt1->error);
        }
    } catch (Exception $e) {
        $connect->rollback();
        echo $e->getMessage();
    }
}



//discount update or update
//bach
if (isset($_POST['StudentBachIdToAddOrUpdateDiscount'])) {
    $id = (int) $_POST['StudentBachIdToAddOrUpdateDiscount'];
    $disPer = (float) $_POST['disPer'];

    if ($id <= 0) {
        echo "Error: Invalid Student ID";
        exit();
    }

    $connect->begin_transaction();
    try {
        // Get original accept type amount
        $sqlInfo = "SELECT accept_type.amount FROM student JOIN accept_type ON student.accept_type_id = accept_type.accept_type_id WHERE student_id = ?";
        $stmtInfo = $connect->prepare($sqlInfo);
        $stmtInfo->bind_param("i", $id);
        $stmtInfo->execute();
        $baseAmount = (float) $stmtInfo->get_result()->fetch_assoc()['amount'];
        $totalAfterDis = $baseAmount * (1 - ($disPer / 100));

        $sql = "UPDATE student
                SET discount_percentage = ?,
                    total_amount = ?,
                    total_remain = (? - (SELECT COALESCE(SUM(payment_amount), 0) FROM payment WHERE student_id = ? AND IsDeleted = 0))
                WHERE student_id = ?";

        $stmt = $connect->prepare($sql);
        $stmt->bind_param("ddddi", $disPer, $totalAfterDis, $totalAfterDis, $id, $id);

        if ($stmt->execute()) {
            $connect->commit();
            echo "ok";
        } else {
            throw new Exception("Error updating student discount: " . $stmt->error);
        }
    } catch (Exception $e) {
        $connect->rollback();
        echo $e->getMessage();
    }
    exit();
}

//High
if (isset($_POST['StudentHighIdToAddOrUpdateDiscount'])) {
    $id = (int) $_POST['StudentHighIdToAddOrUpdateDiscount'];
    $disPer = (float) $_POST['disPer'];

    if ($id <= 0) {
        echo "Error: Invalid Student ID";
        exit();
    }

    $connect->begin_transaction();
    try {
        // Get original high accept type amount
        $sqlInfo = "SELECT accept_type_high.amount FROM student_high JOIN accept_type_high ON student_high.accept_type_high_id = accept_type_high.accept_type_high_id WHERE student_high_id = ?";
        $stmtInfo = $connect->prepare($sqlInfo);
        $stmtInfo->bind_param("i", $id);
        $stmtInfo->execute();
        $baseAmount = (float) $stmtInfo->get_result()->fetch_assoc()['amount'];
        $totalAfterDis = $baseAmount * (1 - ($disPer / 100));

        $sql = "UPDATE student_high
                SET discount_percentage = ?,
                    total_amount = ?,
                    total_remain = (? - (SELECT COALESCE(SUM(payment_amount), 0) FROM payment_high WHERE student_high_id = ? AND IsDeleted = 0))
                WHERE student_high_id = ?";

        $stmt = $connect->prepare($sql);
        $stmt->bind_param("ddddi", $disPer, $totalAfterDis, $totalAfterDis, $id, $id);

        if ($stmt->execute()) {
            $connect->commit();
            echo "ok";
        } else {
            throw new Exception("Error updating student_high discount: " . $stmt->error);
        }
    } catch (Exception $e) {
        $connect->rollback();
        echo $e->getMessage();
    }
    exit();
}
