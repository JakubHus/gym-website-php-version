<?php
$hasActivePass = false;
$activePassType = '';
$activePassEnd = '';

// Sprawdzenie czy zalogowany user ma juz karnet
if (isset($_SESSION['user_id']) && isset($conn)) {
    $uid = $_SESSION['user_id'];
    $checkSql = "SELECT karnet_typ, karnet_koniec FROM users WHERE id = '$uid'";
    $result = mysqli_query($conn, $checkSql);
    if ($row = mysqli_fetch_assoc($result)) {
        if (!empty($row['karnet_koniec']) && $row['karnet_koniec'] >= date('Y-m-d')) {
            $hasActivePass = true;
            $activePassType = $row['karnet_typ'];
            $activePassEnd = date('d.m.Y', strtotime($row['karnet_koniec']));
        }
    }
}
?>

<style>
    .warning-banner {
        background-color: #ffeb3b;
        color: #856404;
        padding: 15px;
        text-align: center;
        font-weight: bold;
        border: 2px solid #ffeeba;
        border-radius: 10px;
        margin: 20px auto;
        max-width: 800px;
        display: none;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
</style>

<div class="logo-banner">
    <img src="pic/logo_gym1.jpg" alt="Logo siłowni" class="logo-img">
</div>

<div id="yellow-warning-banner" class="warning-banner">
    ⚠️ Masz już aktywny karnet: <strong><?php echo htmlspecialchars($activePassType); ?></strong> 
    ważny do <strong><?php echo htmlspecialchars($activePassEnd); ?></strong>. <br>
    <a href="?page=profil" style="color: #856404; text-decoration: underline;">Sprawdź "Mój profil"</a>
</div>

<div class="naglowek">
    <h1>Poznaj naszą ofertę!</h1>
</div>

<div class="karnety_section">
    <div class="karnet_card" style="background-image: url('pic/basic.webp'); background-size: cover; background-position: center;">
        <h2 style="font-size: 3rem; color: white;">Karnet Basic</h2>
        <p style="font-size: 1.5rem; color: white;">Idealny dla osób rozpoczynających swoją przygodę z siłownią</p>
        <p class="karnet_price">79,99 PLN / mies.</p>
        <button onclick="openModal('basicModal')" class="karnet_button">Sprawdź</button>
    </div>
    
    <div class="karnet_card" style="background-image: url('pic/standard.webp'); background-size: cover; background-position: center;">
        <h2 style="font-size: 3rem; color: white;">Karnet Standard</h2>
        <p style="font-size: 1.5rem; color: white;">Dopasowany do potrzeb osób zaawansowanych w treningu siłowym</p>
        <p class="karnet_price">119,99 PLN / mies.</p>
        <button onclick="openModal('standardModal')" class="karnet_button">Sprawdź</button>
    </div>

    <div class="karnet_card" style="background-image: url('pic/samostandard.jpg'); background-size: cover; background-position: center;">
        <h2 style="font-size: 3rem; color: white;">Standard Odnawialny</h2>
        <p style="font-size: 1.5rem; color: white;">Karnet Standard w niższej cenie przy subskrypcji na 6 miesięcy</p>
        <p class="karnet_price">94,99 PLN / mies.</p>
        <button onclick="openModal('standardAutoModal')" class="karnet_button">Sprawdź</button>
    </div>
    
    <div class="karnet_card" style="background-image: url('pic/premium.webp'); background-size: cover; background-position: center;">
        <h2 style="font-size: 3rem; color: white;">Karnet Premium</h2>
        <p style="font-size: 1.5rem; color: white;">Spełni oczekiwania nawet najbardziej wymagających klientów</p>
        <p class="karnet_price">199,99 PLN / mies.</p>
        <button onclick="openModal('premiumModal')" class="karnet_button">Sprawdź</button>
    </div>

    <div class="karnet_card" style="background-image: url('pic/samopremium.jpg'); background-size: cover; background-position: center;">
        <h2 style="font-size: 3rem; color: white;">Premium Odnawialny</h2>
        <p style="font-size: 1.5rem; color: white;">Pełny pakiet korzyści Premium z gwarancją niższej ceny przez pół roku</p>
        <p class="karnet_price">174,99 PLN / mies.</p>
        <button onclick="openModal('premiumAutoModal')" class="karnet_button">Sprawdź</button>
    </div>
</div>

<div id="basicModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('basicModal')">&times;</span>
        <h2>Karnet Basic</h2>
        <ul>
            <li>Możliwość wejścia do wybranego klubu do godz. 16:00 przez 7 dni w tygodniu.</li>
            <li>Darmowy trening z trenerem personalnym jednorazowo w miesiącu.</li>
            <li>Wybór jednego dowolnego klubu w ramach jednego karnetu.</li>
        </ul>
        <button class="buy-button" onclick="handleBuyClick('basic')">Dokonaj zakupu</button>
    </div>
</div>

<div id="standardModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('standardModal')">&times;</span>
        <h2>Karnet Standard</h2>
        <ul>
            <li>Możliwość wejścia do jednego klubu przez 7 dni w tyg. o dowolnej porze w godzinach otwarcia klubu.</li>
            <li>Dostęp do zapisu na jedne wybrane zajęcia grupowe w ramach jednego karnetu.</li>
            <li>Możliwość otrzymania darmowych planów treningowych od naszych ekspertów.</li>
        </ul>
        <button class="buy-button" onclick="handleBuyClick('standard')">Dokonaj zakupu</button>
    </div>
</div>

<div id="standardAutoModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('standardAutoModal')">&times;</span>
        <h2>Karnet Standard Odnawialny</h2>
        <ul>
            <li>Możliwość wejścia do jednego klubu przez 7 dni w tyg. o dowolnej porze w godzinach otwarcia klubu.</li>
            <li>Dostęp do zapisu na jedne wybrane zajęcia grupowe w ramach jednego karnetu.</li>
            <li>Gwarancja stałej, niższej ceny przez cały okres trwania umowy.</li>
            <li>Subskrypcja na okres 6 miesięcy – opłata pobierana automatycznie co miesiąc.</li>
        </ul>
        <button class="buy-button" onclick="handleBuyClick('standard_auto')">Dokonaj zakupu</button>
    </div>
</div>

<div id="premiumModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('premiumModal')">&times;</span>
        <h2>Karnet Premium</h2>
        <ul>
            <li>Możliwość wejścia do dowolnego klubu przez 7 dni w tyg. o dowolnej porze w godzinach otwarcia klubu.</li>
            <li>Dostęp do zapisu na wszystkie zajęcia grupowe w ramach jednego karnetu.</li>
            <li>Jedne darmowe konsultacje z dietetykiem raz w obrębie okresu rozliczeniowego karnetu.</li>
        </ul>
        <button class="buy-button" onclick="handleBuyClick('premium')">Dokonaj zakupu</button>
    </div>
</div>

<div id="premiumAutoModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('premiumAutoModal')">&times;</span>
        <h2>Karnet Premium Odnawialny</h2>
        <ul>
            <li>Możliwość wejścia do dowolnego klubu przez 7 dni w tyg. o dowolnej porze w godzinach otwarcia klubu.</li>
            <li>Dostęp do zapisu na wszystkie zajęcia grupowe w ramach jednego karnetu.</li>
            <li>Gwarancja stałej, niższej ceny przez cały okres trwania umowy.</li>
            <li>Subskrypcja na okres 6 miesięcy – opłata pobierana automatycznie co miesiąc.</li>
        </ul>
        <button class="buy-button" onclick="handleBuyClick('premium_auto')">Dokonaj zakupu</button>
    </div>
</div>

<script>
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.classList.add('show');
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.classList.remove('show');
    }

    window.onclick = function(event) {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            if (event.target === modal) modal.classList.remove('show');
        });
    };

    function handleBuyClick(planId) {
        const userHasPass = <?php echo $hasActivePass ? 'true' : 'false'; ?>;
        
        document.querySelectorAll('.modal').forEach(modal => modal.classList.remove('show'));

        if (userHasPass) {
            const banner = document.getElementById('yellow-warning-banner');
            banner.style.display = 'block';
            window.scrollTo({ top: 0, behavior: 'smooth' });
            
            setTimeout(() => {
                banner.style.display = 'none';
            }, 3000);
        } else {
            window.location.href = '?page=transakcja&plan=' + planId;
        }
    }
</script>