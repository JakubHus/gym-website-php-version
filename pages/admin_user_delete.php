<?php
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 1) {
    header("Location: ?page=home");
    exit();
}

$delete_id = (int)($_GET['id'] ?? 0);
$current_admin = (int)$_SESSION['user_id'];

if ($delete_id === 0) {
    header('Location: ?page=admin');
    exit();
}

if ($delete_id === $current_admin) {
    header('Location: ?page=admin&msg=delete_self_error');
    exit();
}

$sql = "DELETE FROM users WHERE id = $delete_id";
if (mysqli_query($conn, $sql)) {
    header('Location: ?page=admin&msg=user_deleted');
    exit();
} else {
    header('Location: ?page=admin&msg=db_error');
    exit();
}
