<?php
// Strona treningu personalnego z listą trenerów i odblokowaniem numeru telefonu.
$trainers = [];
if (isset($conn) && $conn) {
    $trainerResult = mysqli_query($conn, "SHOW TABLES LIKE 'trenerzy'");
    if ($trainerResult && mysqli_num_rows($trainerResult) > 0) {
        $trainerQuery = mysqli_query($conn, "SELECT id, imie, nazwisko, specjalizacja, numer_telefonu, cena FROM trenerzy ORDER BY nazwisko ASC, imie ASC");
        if ($trainerQuery) {
            while ($row = mysqli_fetch_assoc($trainerQuery)) {
                $trainers[] = $row;
            }
        }
    }
}
?>

<style>
    .training-page {
        max-width: 1100px;
        margin: 40px auto;
        padding: 0 20px;
    }
    .training-hero {
        background: linear-gradient(135deg, #5f2d91 0%, #ac4fc8 100%);
        color: white;
        padding: 38px 24px;
        border-radius: 18px;
        text-align: center;
        box-shadow: 0 16px 35px rgba(95, 45, 145, 0.16);
        margin-bottom: 35px;
    }
    .training-hero h1 {
        font-size: 36px;
        margin-bottom: 12px;
    }
    .training-hero p {
        margin: 0 auto;
        max-width: 760px;
        font-size: 17px;
        line-height: 1.7;
        opacity: 0.95;
    }
    .trainer-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 22px;
    }
    .trainer-card {
        background: #ffffff;
        border-radius: 18px;
        border: 1px solid #e8e6f1;
        box-shadow: 0 18px 40px rgba(108, 83, 171, 0.08);
        padding: 26px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .trainer-card h3 {
        margin: 0 0 8px;
        color: #2a1747;
        font-size: 22px;
    }
    .trainer-card p {
        margin: 8px 0;
        color: #5e4a7b;
        line-height: 1.6;
    }
    .trainer-card .detail {
        font-weight: 600;
        color: #3b2750;
    }
    .trainer-card .price {
        font-size: 18px;
        font-weight: 700;
        margin-top: 12px;
        color: #7c3aed;
    }
    .trainer-card button {
        margin-top: 18px;
        padding: 12px 18px;
        border: none;
        border-radius: 12px;
        color: white;
        background: #7c3aed;
        cursor: pointer;
        transition: transform 0.2s ease, background 0.2s ease;
    }
    .trainer-card button:hover {
        transform: translateY(-1px);
        background: #5f27d4;
    }
    .trainer-card .phone-number {
        margin-top: 12px;
        font-size: 16px;
        color: #2e1157;
        display: none;
        word-break: break-word;
    }
    .trainer-card .phone-number.visible {
        display: block;
    }
    .training-empty {
        background: #fff4e6;
        border: 1px solid #ffd7a8;
        color: #7a4a14;
        padding: 22px;
        border-radius: 16px;
        text-align: center;
    }
</style>

<div class="training-page">
    <section class="training-hero">
        <h1>Trening personalny</h1>
        <p>Wybierz trenera personalnego dopasowanego do Twoich potrzeb i umów się na bezpośredni telefoniczny kontakt. Każdy trener został opisany, a numer telefonu wyświetlisz jednym kliknięciem.</p>
    </section>

    <?php if (!empty($trainers)): ?>
        <div class="trainer-grid">
            <?php foreach ($trainers as $trainer): ?>
                <article class="trainer-card">
                    <div>
                        <h3><?php echo htmlspecialchars($trainer['nazwisko'] . ' ' . $trainer['imie']); ?></h3>
                        <p class="detail">Specjalizacja: <?php echo htmlspecialchars($trainer['specjalizacja']); ?></p>
                        <p class="price">Cena sesji: <?php echo number_format((float)$trainer['cena'], 2, ',', ' '); ?> zł</p>
                    </div>
                    <div>
                        <button type="button" onclick="togglePhone(this)">Pokaż numer telefonu</button>
                        <p class="phone-number"><?php echo htmlspecialchars($trainer['numer_telefonu'] ?? 'Brak numeru'); ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="training-empty">
            <p>Aktualnie nie ma dostępnych trenerów personalnych w bazie danych. Spróbuj ponownie później lub skontaktuj się z nami bezpośrednio.</p>
        </div>
    <?php endif; ?>
</div>

<script>
    function togglePhone(button) {
        const card = button.closest('.trainer-card');
        if (!card) return;
        const phone = card.querySelector('.phone-number');
        if (!phone) return;

        phone.classList.toggle('visible');
        button.textContent = phone.classList.contains('visible') ? 'Ukryj numer telefonu' : 'Pokaż numer telefonu';
    }
</script>
