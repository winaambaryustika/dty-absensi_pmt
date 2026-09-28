<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'config/database.php';

if (!isset($_GET['id'])) {
    header("Location: karyawan.php");
    exit;
}

$id = intval($_GET['id']);

$stmt = mysqli_prepare(
    $conn,
    "DELETE FROM karyawan WHERE id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

header("Location: karyawan.php");
exit;

?>