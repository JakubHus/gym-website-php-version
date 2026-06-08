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
            <li><a href="/gym-website-php-version-main/home" class="pic"><img src="pic/logo_gym1.jpg" alt="Logo siłowni" style="height: 40px;"></a></li>
            <li><a href="/gym-website-php-version-main/karnety">Karnety</a></li>
            <li><a href="/gym-website-php-version-main/onas">O nas</a></li>
            <li><a href="/gym-website-php-version-main/kluby">Kluby</a></li>
            <li><a href="/gym-website-php-version-main/bmi">BMI</a></li>
            <li><a href="/gym-website-php-version-main/zajecia">Zajęcia</a></li>
            <li><a href="/gym-website-php-version-main/trening_personalny">Trening personalny</a></li>
            <li><a href="/gym-website-php-version-main/kontakt">Kontakt i FAQ</a></li>
            <li><a href="/gym-website-php-version-main/regulamin">Regulamin</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li class="nav-actions">
                    <span style="color: #7a17cb; font-weight: bold; font-size: 16px; white-space: nowrap;">👤 <?php echo htmlspecialchars($_SESSION['imie']); ?><?php echo (isset($_SESSION['role']) && $_SESSION['role'] === 1) ? ' (Admin)' : ''; ?></span>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 1): ?>
                        <a href="/gym-website-php-version-main/admin">Panel Admina</a>
                    <?php endif; ?>
                    <a href="/gym-website-php-version-main/profil">Mój Profil</a>
                    <a href="/gym-website-php-version-main/wylogowanie">Wyloguj</a>
                </li>
            <?php else: ?>
                <li class="nav-actions">
                    <a href="/gym-website-php-version-main/rejestracja">Rejestracja</a>
                    <a href="/gym-website-php-version-main/logowanie">Logowanie</a>
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
