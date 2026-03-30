<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php_action/../php_action/../php_action/transfer_students.php' === '' ? '' : 'php://input'), true);

if (!isset($input['students']) || !isset($input['targetStage'])) {
    echo json_encode(['success' => false, 'message' => 'Missing data']);
    exit();
}

$students = $input['students'];
$targetStage = (int) $input['targetStage'];
$transferredCount = 0;
$skippedCount = 0;
$errorMessages = [];

foreach ($students as $studentData) {
    // Expected format: "ID-Type"
    list($id, $type) = explode('-', $studentData);
    $id = (int) $id;

    if ($type === 'bach') {
        $sql = "UPDATE student SET stage = ? WHERE student_id = ?";
    } else if ($type === 'high') {
        $sql = "UPDATE student_high SET stage = ? WHERE student_high_id = ?";
    } else {
        $skippedCount++;
        continue;
    }

    $stmt = $connect->prepare($sql);
    $stmt->bind_param("ii", $targetStage, $id);

    if ($stmt->execute()) {
        $transferredCount++;
    } else {
        $errorMessages[] = "Error updating ID $id: " . $stmt->error;
        $skippedCount++;
    }
}

$message = "تم نقل $transferredCount طالب.";
if ($skippedCount > 0) {
    $message .= " تم تخطي $skippedCount طالب (بسبب رصيد متبقي أو خطأ).";
}

echo json_encode([
    'success' => true,
    'transferred' => $transferredCount,
    'skipped' => $skippedCount,
    'message' => $message,
    'errors' => $errorMessages
]);