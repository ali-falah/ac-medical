<?php

require_once 'db_connect.php';



if (isset($_POST['acceptTypeIdToGetAmount'])) {
    $id = (int) $_POST['acceptTypeIdToGetAmount'];

    $sql = "SELECT amount FROM accept_type WHERE accept_type_id = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();

    echo json_encode($res->fetch_assoc());
}

if (isset($_POST['GetAllAcceptsStudent'])) {
    $sql = "SELECT * FROM accept_type";
    $res = $connect->query($sql);
    echo json_encode($res->fetch_all(MYSQLI_ASSOC));
}

// insert student bachelors on registration
if (isset($_POST['studentNameInput'])) {
    // Sanitize and validate user input
    $studentName = $_POST['studentNameInput'];
    $total = (float) $_POST['total'];
    $discountPercentage = (float) $_POST['discountPercentage'];
    $studentStageBachelor = (int) $_POST['studentStageBachelor'];
    $accept_type_id = (int) $_POST['accept_type_id'];
    $RemainInput = (float) $_POST['RemainInput'];
    $studentRemarks = $_POST['studentRemarks'];

    // Construct the SQL query with prepared statements
    $sql = "INSERT INTO student (`name`, total_amount, discount_percentage, stage, accept_type_id, total_remain, remarks, num_of_payments, IsDeleted) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 0, 0)";

    $stmt = $connect->prepare($sql);
    $stmt->bind_param("sddiids", $studentName, $total, $discountPercentage, $studentStageBachelor, $accept_type_id, $RemainInput, $studentRemarks);

    // Execute the SQL query
    if ($stmt->execute()) {
        $LastStudent_id = $connect->insert_id;
        echo $LastStudent_id;
    } else {
        echo "Error: registering student bachelor: " . $stmt->error;
        exit();
    }
}


?>