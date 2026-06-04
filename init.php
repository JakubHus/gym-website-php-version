<?php
    if( session_status() === PHP_SESSION_NONE ) 
    {
        session_start();
    }

    $poprzedniaWizyta = $_COOKIE['energym_ostatnia_wizyta'] ?? null;

    setcookie(
        'energym_ostatnia_wizyta',
        date('Y-m-d H:i:s'),
        [
            'expires'  => time() + 60 * 60 * 24 * 30, // wazne 30 dni
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]
    );

    $host = "localhost";
    $user = "root";
    $pass = "";
    
    $db_name = "silownia"; 

    $conn = mysqli_connect($host, $user, $pass, $db_name);

    if (!$conn) {
        die("Błąd połączenia z bazą danych: " . mysqli_connect_error());
    }
?>
