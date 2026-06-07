
<?php
$opinions = [];
if (isset($conn) && $conn) {
    $sql = "SELECT imie, nazwisko, opinia, ocena FROM users WHERE opinia IS NOT NULL AND opinia != ''";
    $result = mysqli_query($conn, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $opinions[] = $row;
        }
    }
}
?>

<div class="logo-banner">
    <img src="pic/logo_gym1.jpg" alt="Logo siłowni" class="logo-img">
</div>

<div class="main_container">
    <img src="pic/gym1.jpg" alt="Zdjęcie siłowni" class="main_image">
    <div class="overlay">
        <h1 class="overlay-text">ENERGYM</h1>
    </div>
</div>

<div class="opinion_section">
    <button class="arrow left-arrow" onclick="moveOpinions(-1)">&#9664;</button>
    
    <div class="opinion_container">
        <?php if (!empty($opinions)): ?>
            <?php foreach ($opinions as $op): ?>
                <div class="opinion_card">
                    <p class="opinion_text"><?php echo nl2br(htmlspecialchars($op['opinia'])); ?>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo str_repeat('⭐', (int)($op['ocena'] ?? 5)); ?></p>
                    <p class="customer_name">~<?php echo htmlspecialchars($op['imie'] . ' ' . $op['nazwisko']); ?>~</p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="opinion_card">
                <p class="opinion_text">Brak opinii. Bądź pierwszą osobą, która oceni naszą siłownię!</p>
                <p class="customer_name">~EnerGym~</p>
            </div>
        <?php endif; ?>
    </div>
    
    <button class="arrow right-arrow" onclick="moveOpinions(1)">&#9654;</button>
</div>