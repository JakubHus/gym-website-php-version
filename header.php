<?php
    $pageTitle       = $pageTitle       ?? 'EnerGym';
    $pageCss         = $pageCss         ?? 'silownia1.css';
    $pageDescription = $pageDescription ?? '';
    $pageHead        = $pageHead        ?? '';
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="<?php echo htmlspecialchars($pageDescription); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="css/<?php echo htmlspecialchars($pageCss); ?>">
    <?php echo $pageHead; ?>
</head>
<body>
    <nav class="navbar">
        <ul>
            <li><a href="?page=home" class="pic"><img src="pic/logo_gym1.jpg" alt="Logo siłowni" style="height: 40px;"></a></li>
            <li><a href="?page=karnety">Karnety</a></li>
            <li><a href="?page=onas">O nas</a></li>
            <li><a href="?page=kluby">Kluby</a></li>
            <li><a href="?page=bmi">BMI</a></li>
            <li><a href="?page=zajecia">Zajęcia</a></li>
            <li><a href="?page=trening_personalny">Trening personalny</a></li>
            <li><a href="?page=kontakt">Kontakt i FAQ</a></li>
            <li><a href="?page=regulamin">Regulamin</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li class="nav-actions">
                    <span style="color: #7a17cb; font-weight: bold; font-size: 16px; white-space: nowrap;">👤 <?php echo htmlspecialchars($_SESSION['imie']); ?><?php echo (isset($_SESSION['role']) && $_SESSION['role'] === 1) ? ' (Admin)' : ''; ?></span>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 1): ?>
                        <a href="?page=admin">Panel Admina</a>
                    <?php endif; ?>
                    <a href="?page=profil">Mój Profil</a>
                    <a href="?page=wylogowanie">Wyloguj</a>
                </li>
            <?php else: ?>
                <li class="nav-actions">
                    <a href="?page=rejestracja">Rejestracja</a>
                    <a href="?page=logowanie">Logowanie</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
    <style>
        .nav-actions {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 16px;
            white-space: nowrap;
        }
        .nav-actions a {
            white-space: nowrap;
        }
    </style>
