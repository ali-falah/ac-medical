<?php
require_once 'db_connect.php';


if (isset($_POST['studentHighIdToGetInfo'])) {
    $id = (int)$_POST['studentHighIdToGetInfo'];

    $sql = "SELECT student_high.`name`, student_high.stage, student_high.remarks, student_high.UniversityComand, student_high.DateOfLaunch, student_high.num_of_payments,
            student_high.StudyCert, student_high.TheOnlyCert, student_high.total_amount, student_high.total_remain,
            accept_type_high.amount, accept_type_high.accept_type_high_id, student_high.IsPublic
            FROM student_high
            JOIN accept_type_high ON accept_type_high.accept_type_high_id = student_high.accept_type_high_id
            WHERE student_high.student_high_id = ? AND student_high.IsDeleted = 0";

    $stmt = $connect->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    echo json_encode($res->fetch_assoc());
}

if (isset($_POST['studentBachIdToGetInfo'])) {
    $id = (int)$_POST['studentBachIdToGetInfo'];

    $sql = "SELECT student.`name`, student.stage, student.remarks, student.num_of_payments,
            accept_type.amount, accept_type.accept_type_id, student.total_amount, student.total_remain
            FROM student
            JOIN accept_type ON accept_type.accept_type_id = student.accept_type_id
            WHERE student.student_id = ? AND student.IsDeleted = 0";

    $stmt = $connect->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    echo json_encode($res->fetch_assoc());
}

if (isset($_POST['BachStudentIdToEditWithOutAmountAndAcceptType'])) {
    $id = (int)$_POST['BachStudentIdToEditWithOutAmountAndAcceptType'];
    $studentName = $_POST['studentName'];
    $BachStage = (int)$_POST['BachStage'];
    $RemarksInput = $_POST['RemarksInput'];

    $sql = "UPDATE student SET `name` = ?, stage = ?, remarks = ? WHERE student_id = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("sisi", $studentName, $BachStage, $RemarksInput, $id);

    if ($stmt->execute()) {
        echo 'ok';
    }
    else {
        echo 'Error updating student: ' . $stmt->error;
    }
}

if (isset($_POST['HighStudentIdToEditWithOutAmountAndAcceptType'])) {
    $id = (int)$_POST['HighStudentIdToEditWithOutAmountAndAcceptType'];
    $studentName = $_POST['studentName'];
    $HighStage = (int)$_POST['HighStage'];
    $RemarksInput = $_POST['RemarksInput'];
    $StudyCertInput = $_POST['StudyCertInput'];
    $UniCommandInput = $_POST['UniCommandInput'];
    $DateOfLunchInput = $_POST['DateOfLunchInput'];
    $TheOnlyCertInput = $_POST['TheOnlyCertInput'];

    $sql = "UPDATE student_high SET `name` = ?, stage = ?, remarks = ?, UniversityComand = ?, DateOfLaunch = ?, StudyCert = ?, TheOnlyCert = ? WHERE student_high_id = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("sisssssi", $studentName, $HighStage, $RemarksInput, $UniCommandInput, $DateOfLunchInput, $StudyCertInput, $TheOnlyCertInput, $id);

    if ($stmt->execute()) {
        echo 'ok';
    }
    else {
        echo 'Error updating high student: ' . $stmt->error;
    }
}

// with amount and accept type Bach
if (isset($_POST['BachStudentIdToEditWithAmountAndAcceptType'])) {
    $id = (int)$_POST['BachStudentIdToEditWithAmountAndAcceptType'];
    $studentName = $_POST['studentName'];
    $BachStage = (int)$_POST['BachStage'];
    $RemarksInput = $_POST['RemarksInput'];
    $amountBach = (float)$_POST['amountBach'];
    $AcceptTypeBach = (int)$_POST['AcceptTypeBach'];

    $sql = "UPDATE student SET `name` = ?, stage = ?, discount_percentage = 0, remarks = ?, total_amount = ?, accept_type_id = ?, total_remain = ? WHERE student_id = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("sisdidi", $studentName, $BachStage, $RemarksInput, $amountBach, $AcceptTypeBach, $amountBach, $id);

    if ($stmt->execute()) {
        echo 'ok';
    }
    else {
        echo 'Error updating student with amount: ' . $stmt->error;
    }
}

// with amount and accept type High
if (isset($_POST['HighStudentIdToEditWithAmountAndAcceptType'])) {
    $id = (int)$_POST['HighStudentIdToEditWithAmountAndAcceptType'];
    $studentName = $_POST['studentName'];
    $HighStage = (int)$_POST['HighStage'];
    $RemarksInput = $_POST['RemarksInput'];
    $StudyCertInput = $_POST['StudyCertInput'];
    $UniCommandInput = $_POST['UniCommandInput'];
    $DateOfLunchInput = $_POST['DateOfLunchInput'];
    $TheOnlyCertInput = $_POST['TheOnlyCertInput'];
    $IsPublic = (int)$_POST['IsPublic'];
    $amountHigh = (float)$_POST['amountHigh'];
    $AcceptTypeHigh = (int)$_POST['AcceptTypeHigh'];

    $sql = "UPDATE student_high SET `name` = ?, stage = ?, remarks = ?, UniversityComand = ?, DateOfLaunch = ?, StudyCert = ?, TheOnlyCert = ?, total_amount = ?, IsPublic = ?, accept_type_high_id = ?, discount_percentage = 0, total_remain = ? WHERE student_high_id = ?";
    $stmt = $connect->prepare($sql);
    // Correct order: name(s), stage(i), remarks(s), Uni(s), Date(s), Study(s), Only(s), total_amount(d), IsPublic(i), accept_type(i), remain(d), id(i)
    $stmt->bind_param("sisssssdiidi", $studentName, $HighStage, $RemarksInput, $UniCommandInput, $DateOfLunchInput, $StudyCertInput, $TheOnlyCertInput, $amountHigh, $IsPublic, $AcceptTypeHigh, $amountHigh, $id);

    if ($stmt->execute()) {
        echo 'ok';
    }
    else {
        echo 'Error updating high student with amount: ' . $stmt->error;
    }
}

if (isset($_POST['StudentBachIdToDelete'])) {
    $id = (int)$_POST['StudentBachIdToDelete'];
    $sql = "UPDATE student SET IsDeleted = 1 WHERE student_id = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo 'ok';
    }
    else {
        echo 'Error deleting student: ' . $stmt->error;
    }
}

if (isset($_POST['StudentHighIdToDelete'])) {
    $id = (int)$_POST['StudentHighIdToDelete'];
    $sql = "UPDATE student_high SET IsDeleted = 1 WHERE student_high_id = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo 'ok';
    }
    else {
        echo 'Error deleting high student: ' . $stmt->error;
    }
}


?>