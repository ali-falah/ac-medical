<?php

require_once 'db_connect.php';



// insert student bachelors on registartion
if (isset($_POST['studentNameInput'])) {




    // Sanitize and validate user input
    $studentName = htmlspecialchars($_POST['studentNameInput'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $total = filter_var($_POST['total'], FILTER_VALIDATE_FLOAT);
    $discountPercentage = filter_var($_POST['discountPercentage'], FILTER_VALIDATE_FLOAT);
    //$FirstPaymentInput = filter_var($_POST['FirstPaymentInput'], FILTER_VALIDATE_FLOAT);
    $studentStageBachelor = filter_var($_POST['studentStageBachelor'], FILTER_VALIDATE_INT);
    $accept_type_id = filter_var($_POST['accept_type_id'], FILTER_VALIDATE_INT);
    $RemainInput = filter_var($_POST['RemainInput'], FILTER_VALIDATE_FLOAT);
    $studentRemarks = htmlspecialchars($_POST['studentRemarks'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    //$PaymentRemarksinput = htmlspecialchars($_POST['PaymentRemarksinput'], ENT_QUOTES | ENT_HTML5, 'UTF-8');





    // Construct the SQL query
    $sql = "INSERT INTO student (`name`, total_amount, discount_percentage, stage, accept_type_id, total_remain, remarks, num_of_payments,IsDeleted) 
    VALUES ('$studentName', $total, $discountPercentage, $studentStageBachelor, $accept_type_id, $RemainInput, '$studentRemarks', 0,0)";

    // Execute the SQL query
    if ($connect->query($sql) == TRUE) {

        $LastStudent_id = mysqli_insert_id($connect);
        echo $LastStudent_id;

    } else {
        echo "Error: registring student bachelor " . $sql . "<br>" . $connect->error;
        exit();
    }


    //close db connection

    $connect->close();


}



?>