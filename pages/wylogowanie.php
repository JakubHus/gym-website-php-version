<?php
// Sprawdzenie czy użytkownik jest zalogowany
if (!isset($_SESSION['user_id'])) {
    header("Location: ?page=home");
    exit();
}

// Wylogowanie
session_destroy();
header("Location: ?page=home");
exit();
?>
