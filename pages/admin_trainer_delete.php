<?php
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 1) {
    header("Location: ?page=home");
    exit();
}

$delete_id = (int)($_GET['id'] ?? 0);
if ($delete_id === 0) {
    header('Location: ?page=admin');
    exit();
}

$sql = "DELETE FROM trenerzy WHERE id = $delete_id";
if (mysqli_query($conn, $sql)) {
    header('Location: ?page=admin&msg=trainer_deleted');
    exit();
} else {
    header('Location: ?page=admin&msg=db_error');
    exit();
}
