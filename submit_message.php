<?php
session_start();
include('database.php');

if (!isset($_POST['sender']) || !isset($_POST['receiver'])) {
    exit();
}

$sender = mysqli_real_escape_string($conn, $_POST['sender']);
$receiver = mysqli_real_escape_string($conn, $_POST['receiver']);
$message = isset($_POST['message']) ? mysqli_real_escape_string($conn, $_POST['message']) : '';
$image = null;

$allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'ppt', 'pptx'];

if (!empty($_FILES['file']['name'])) {
    $targetDir = "uploads/";
    $fileName = time() . "_" . basename($_FILES['file']['name']);
    $filePath = $targetDir . $fileName;
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (in_array($fileExt, $allowedTypes)) {
        if (move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
            $image = $fileName;  // Save file name in DB
        }
    }
}



$query = "INSERT INTO messages (sender, receiver, message, image) VALUES ('$sender', '$receiver', '$message', '$image')";
mysqli_query($conn, $query);
?>
