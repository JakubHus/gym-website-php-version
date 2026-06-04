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
?>
