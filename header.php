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
            <li><a href="home" class="pic"><img src="pic/logo_gym1.jpg" alt="Logo siłowni" style="height: 40px;"></a></li>
            <li><a href="karnety">Karnety</a></li>
            <li><a href="onas">O nas</a></li>
            <li><a href="kluby">Kluby</a></li>
            <li><a href="bmi">BMI</a></li>
            <li><a href="zajecia">Zajęcia</a></li>
            <li><a href="kontakt">Kontakt i FAQ</a></li>
            <li><a href="regulamin">Regulamin</a></li>
        </ul>
    </nav>
