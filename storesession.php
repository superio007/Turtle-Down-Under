<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jsonInput = file_get_contents('php://input');
    $data = json_decode($jsonInput, true);
    if (isset($data['selectedExtras'])) {
        $_SESSION['selectedExtras'] = json_decode($data['selectedExtras'], true);
        echo "Session data stored successfully.";
    } else {
        echo "No data provided.";
    }
    exit;
}
?>
