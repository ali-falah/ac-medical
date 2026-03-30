<?php

require_once 'db_connect.php';



if (isset($_POST['acceptTypeHighIdToGetAmount'])) {
    $id = (int)$_POST['acceptTypeHighIdToGetAmount'];
    $sql = "SELECT amount FROM accept_type_high WHERE accept_type_high_id = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    echo json_encode($res->fetch_assoc());
}

if (isset($_POST['GetAllAcceptsStudentHigh'])) {
    $sql = "SELECT * FROM accept_type_high";
    $res = $connect->query($sql);
    echo json_encode($res->fetch_all(MYSQLI_ASSOC));
}

// insert student high on registration خاص
if (isset($_POST['studentNameInput'])) {
    $studentName = $_POST['studentNameInput'];
    $total = (float)$_POST['total'];
    $discountPercentage = (float)$_POST['discountPercentage'];
    $studentStageBachelor = (int)$_POST['studentStageBachelor'];
    $accept_type_id = (int)$_POST['accept_type_high_id'];
    $RemainInput = (float)$_POST['RemainInput'];
    $studentRemarks = $_POST['studentRemarks'];
    $TheOnlyCert = $_POST['TheOnlyCert'];
    $StudyCert = $_POST['StudyCert'];
    $DateOfLaunch = $_POST['DateOfLaunch'];
    $UniversityComand = $_POST['UniversityComand'];

    $sql = "INSERT INTO student_high (`name`, total_amount, discount_percentage, stage, accept_type_high_id, total_remain, remarks, num_of_payments, IsPublic, IsDeleted, UniversityComand, DateOfLaunch, StudyCert, TheOnlyCert) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 0, 0, 0, ?, ?, ?, ?)";

    $stmt = $connect->prepare($sql);
    $stmt->bind_param("sddiidsssss", $studentName, $total, $discountPercentage, $studentStageBachelor, $accept_type_id, $RemainInput, $studentRemarks, $UniversityComand, $DateOfLaunch, $StudyCert, $TheOnlyCert);

    if ($stmt->execute()) {
        echo $connect->insert_id;
    }
    else {
        echo "Error: registering student high: " . $stmt->error;
        exit();
    }
}

if (isset($_POST['IsPublic'])) { // عام
    $studentName = $_POST['studentNameInput1'];
    $studentStageBachelor = (int)$_POST['studentStageBachelor1'];
    $studentRemarks = $_POST['studentRemarks1'];
    $accept_type_id = (int)$_POST['accept_type_high_id1'];
    $TheOnlyCert = $_POST['TheOnlyCert'];
    $StudyCert = $_POST['StudyCert'];
    $DateOfLaunch = $_POST['DateOfLaunch'];
    $UniversityComand = $_POST['UniversityComand'];

    $sql = "INSERT INTO student_high (`name`, total_amount, discount_percentage, stage, accept_type_high_id, total_remain, remarks, num_of_payments, IsPublic, IsDeleted, UniversityComand, DateOfLaunch, StudyCert, TheOnlyCert) 
            VALUES (?, 0, 0, ?, ?, 0, ?, 0, 1, 0, ?, ?, ?, ?)";

    $stmt = $connect->prepare($sql);
    $stmt->bind_param("siisssss", $studentName, $studentStageBachelor, $accept_type_id, $studentRemarks, $UniversityComand, $DateOfLaunch, $StudyCert, $TheOnlyCert);

    if ($stmt->execute()) {
        echo $connect->insert_id;
    }
    else {
        echo "Error: registering student high public: " . $stmt->error;
    }
}




?>