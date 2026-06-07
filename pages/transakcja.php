<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: ?page=logowanie");
    exit();
}

$user_id = $_SESSION['user_id'];
$user = null;

if (isset($conn) && $conn) {
    $sql = "SELECT * FROM users WHERE id = '$user_id'";
    $result = mysqli_query($conn, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
    }
}

$plans = [
    'basic' => ['name' => 'Karnet Basic', 'price' => '79.99', 'auto' => false],
    'standard' => ['name' => 'Karnet Standard', 'price' => '119.99', 'auto' => false],
    'standard_auto' => ['name' => 'Karnet Standard Odnawialny', 'price' => '94.99', 'auto' => true],
    'premium' => ['name' => 'Karnet Premium', 'price' => '199.99', 'auto' => false],
    'premium_auto' => ['name' => 'Karnet Premium Odnawialny', 'price' => '174.99', 'auto' => true]
];

$planKey = $_GET['plan'] ?? 'basic';
$selectedPlan = $plans[$planKey] ?? $plans['basic'];

$startDate = date('Y-m-d');
$endDate = date('Y-m-d', strtotime($selectedPlan['auto'] ? '+6 months' : '+1 month'));
$nextPaymentDate = $selectedPlan['auto'] ? date('Y-m-d', strtotime('+1 month')) : 'Brak';

$phpErrorMsg = '';
$transactionSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_submitted'])) {
    if (empty($user['numer_telefonu']) || empty($user['pesel']) || empty($user['miasto']) || empty($user['ulica']) || empty($user['numer_domu']) || empty($user['kod_pocztowy'])) {
        $phpErrorMsg = "Nie uzupełniłeś wszystkich wymaganych danych w profilu (PESEL, Adres, Telefon). Transakcja odrzucona.";
    } else {
        $pass1 = $_POST['password_check'] ?? '';
        
        if (password_verify($pass1, $user['haslo'])) {
            $typ_safe = mysqli_real_escape_string($conn, $selectedPlan['name']);
            $updateSql = "UPDATE users SET karnet_typ = '$typ_safe', karnet_zakup = '$startDate', karnet_koniec = '$endDate' WHERE id = '$user_id'";
            if(mysqli_query($conn, $updateSql)) {
                $transactionSuccess = true;
            } else {
                $phpErrorMsg = "Błąd zapisu do bazy danych: " . mysqli_error($conn);
            }
        } else {
            $phpErrorMsg = "Podane hasło użytkownika jest nieprawidłowe. Spróbuj ponownie.";
        }
    }
}
?>

<style>
    .form-container {
        height: auto !important;
        min-height: max-content !important;
        padding: 40px !important;
        margin: 60px auto 60px auto !important;
        background: #ffffff !important;
        border-radius: 15px !important;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2) !important;
        max-width: 800px !important;
        box-sizing: border-box !important;
        overflow: visible !important;
        position: relative !important;
    }
    .form-container form {
        height: auto !important;
        min-height: auto !important;
        background: transparent !important;
        box-shadow: none !important;
        padding: 0 !important;
        margin: 0 !important;
        position: static !important;
    }
    .form-container h2 {
        margin-top: 0 !important;
        margin-bottom: 25px !important;
        padding-top: 0 !important;
    }
    .form-row {
        margin-bottom: 18px !important;
    }
    .error-banner {
        background-color: #ff4d4d; color: white; padding: 20px; border-radius: 8px;
        margin: 80px auto 20px auto !important;
        max-width: 800px;
        font-weight: bold; text-align: center; border: 2px solid #cc0000; display: none;
        line-height: 1.6;
        box-shadow: 0 4px 15px rgba(204, 0, 0, 0.2);
    }
    .readonly-input { background: #f0f0f0 !important; cursor: not-allowed !important; color: #555 !important; }
    .summary-box {
        background: #fdfdfd; border: 2px dashed #7a17cb; border-radius: 12px;
        padding: 20px; margin-top: 30px; margin-bottom: 30px;
    }
    .summary-box h3 { color: #7a17cb; margin-top: 0; }
    .summary-row { display: flex; justify-content: space-between; margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 5px;}
    .blik-container { display: flex; align-items: center; gap: 15px; }
    .blik-container input { flex-grow: 1; letter-spacing: 5px; font-size: 18px; font-weight: bold;}
    .blik-logo { height: 40px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
    .submit-btn {
        width: 100% !important;
        margin-top: 10px !important;
        position: static !important;
        transform: none !important;
        display: block !important;
    }
</style>

<div class="logo-banner">
    <img src="pic/logo_gym1.jpg" alt="Logo siłowni" class="logo-img">
</div>

<?php if ($transactionSuccess): ?>
    <div id="processing-message" style="display: block; text-align:center; margin-top: 80px; font-size:20px; font-weight:bold;">
        Przetwarzanie płatności. Proszę czekać...
    </div>
    <div id="success-message" style="display: none; text-align:center; margin-top: 80px; font-size:20px; font-weight:bold; color: #2d8f29;"></div>
    
    <script>
        setTimeout(function() {
            document.getElementById('processing-message').style.display = 'none';
            const successMsg = document.getElementById('success-message');
            successMsg.innerHTML = 'Transakcja przebiegła pomyślnie.<br>Karnet został przypisany i wysłany na Twój e-mail: <b><?php echo htmlspecialchars($user['mail']); ?></b>.<br>Dziękujemy za zakup!';
            successMsg.style.display = 'block';
        }, 5000);

        setTimeout(function() {
            window.location.href = '?page=profil';
        }, 10000);
    </script>

<?php else: ?>
    <div id="js-error-banner" class="error-banner" <?php if(!empty($phpErrorMsg)) echo 'style="display:block;"'; ?>>
        <?php echo $phpErrorMsg; ?>
    </div>

    <div class="form-container">
        <form id="transaction-form" method="POST" action="">
            <input type="hidden" name="form_submitted" value="1">
            
            <h2>Dane kupującego:</h2>
            <div class="form-row">
                <div class="form-group">
                    <label>Imię i Nazwisko</label>
                    <input type="text" class="readonly-input" value="<?php echo htmlspecialchars($user['imie'] . ' ' . $user['nazwisko']); ?>" readonly>
                </div>
                <div class="form-group">
                    <label>E-mail</label>
                    <input type="email" class="readonly-input" value="<?php echo htmlspecialchars($user['mail']); ?>" readonly>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Numer telefonu</label>
                    <input type="tel" class="readonly-input js-required-data" 
                        value="<?php echo htmlspecialchars($user['numer_telefonu'] ?? ''); ?>" 
                        placeholder="Uzupełnij w 'Mój profil'" readonly>
                </div>
                <div class="form-group">
                    <label>PESEL</label>
                    <input type="text" class="readonly-input js-required-data" 
                        value="<?php echo htmlspecialchars($user['pesel'] ?? ''); ?>" 
                        placeholder="Uzupełnij w 'Mój profil'" readonly>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Kod pocztowy i Miasto</label>
                    <input type="text" class="readonly-input" 
                        value="<?php echo htmlspecialchars(trim(($user['kod_pocztowy'] ?? '') . ' ' . ($user['miasto'] ?? ''))); ?>" 
                        placeholder="Uzupełnij w 'Mój profil'" readonly>
                    <input type="hidden" class="js-required-data" value="<?php echo htmlspecialchars($user['kod_pocztowy'] ?? ''); ?>">
                    <input type="hidden" class="js-required-data" value="<?php echo htmlspecialchars($user['miasto'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Ulica, nr domu / lokalu</label>
                    <input type="text" class="readonly-input" 
                        value="<?php echo htmlspecialchars(trim(($user['ulica'] ?? '') . ' ' . ($user['numer_domu'] ?? '') . (!empty($user['numer_lokalu']) ? '/'.$user['numer_lokalu'] : ''))); ?>" 
                        placeholder="Uzupełnij w 'Mój profil'" readonly>
                    <input type="hidden" class="js-required-data" value="<?php echo htmlspecialchars($user['ulica'] ?? ''); ?>">
                    <input type="hidden" class="js-required-data" value="<?php echo htmlspecialchars($user['numer_domu'] ?? ''); ?>">
                </div>
            </div>

            <h2>Weryfikacja konta i płatność:</h2>
            <div class="form-row">
                <div class="form-group">
                    <label for="password_check">Wprowadź hasło do konta</label>
                    <input type="password" id="password_check" name="password_check" placeholder="Hasło" required>
                </div>
                <div class="form-group">
                    <label for="password_confirm">Powtórz hasło</label>
                    <input type="password" id="password_confirm" placeholder="Powtórz hasło" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group" style="width: 100%;">
                    <label for="blik">Wprowadź kod BLIK z aplikacji bankowej (6 cyfr)</label>
                    <div class="blik-container">
                        <input type="text" id="blik" name="blik" required placeholder="000000" maxlength="6">
                        <img src="pic/blik.jpg" alt="Logo BLIK" class="blik-logo">
                    </div>
                </div>
            </div>

            <div class="summary-box">
                <h3>Podsumowanie zamówienia</h3>
                <div class="summary-row"><span>Wybrany karnet:</span> <strong><?php echo $selectedPlan['name']; ?></strong></div>
                <div class="summary-row"><span>Cena:</span> <strong style="color: #2d8f29;"><?php echo $selectedPlan['price']; ?> PLN / mies.</strong></div>
                <div class="summary-row"><span>Data rozpoczęcia:</span> <strong><?php echo date('d.m.Y', strtotime($startDate)); ?></strong></div>
                <div class="summary-row"><span>Data zakończenia:</span> <strong><?php echo date('d.m.Y', strtotime($endDate)); ?></strong></div>
                <div class="summary-row" style="border:none;"><span>Następna płatność:</span> <strong><?php echo $selectedPlan['auto'] ? date('d.m.Y', strtotime($nextPaymentDate)) : 'Brak'; ?></strong></div>
                
                <?php if($selectedPlan['auto']): ?>
                    <div style="margin-top: 15px; background:#e6f7ff; color:#0056b3; padding:10px; border-radius:5px; font-size:14px; text-align:center;">
                        ℹ️ Wybrałeś karnet samoodnawialny. Kwota będzie pobierana z Twojego konta automatycznie co miesiąc począwszy od dzisiaj.
                    </div>
                <?php endif; ?>
            </div>

            <button type="submit" id="submit-btn" class="submit-btn">Zatwierdź transakcję</button>
        </form>
    </div>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const phpBanner = document.getElementById('js-error-banner');
            if (phpBanner && phpBanner.style.display === 'block') {
                setTimeout(() => {
                    phpBanner.style.display = 'none';
                }, 3000);
            }
        });

        document.getElementById('transaction-form').addEventListener('submit', function(e) {
            const errorBanner = document.getElementById('js-error-banner');
            errorBanner.style.display = 'none';
            let errors = [];

            let isDataMissing = false;
            document.querySelectorAll('.js-required-data').forEach(input => {
                if (input.value.trim() === '') {
                    isDataMissing = true;
                }
            });

            if (isDataMissing) {
                errors.push('⛔ Nie uzupełniłeś wszystkich wymaganych danych (Adres, PESEL, Nr telefonu).<br>Przejdź do zakładki <a href="?page=profil" style="color:#ffcccc; text-decoration:underline;">Mój profil</a>, uzupełnij je i zapisz zmiany, a następnie wróć tutaj.');
            }

            const p1 = document.getElementById('password_check').value;
            const p2 = document.getElementById('password_confirm').value;
            if (p1 !== p2) {
                errors.push('Podane hasła się nie zgadzają.');
            }

            const blik = document.getElementById('blik').value.trim();
            if (!/^\d{6}$/.test(blik)) {
                errors.push('Kod BLIK jest nieprawidłowy. Musi składać się dokładnie z 6 cyfr.');
            }

            if (errors.length > 0) {
                e.preventDefault();
                errorBanner.innerHTML = errors.join('<br><br>');
                errorBanner.style.display = 'block';
                window.scrollTo({ top: 0, behavior: 'smooth' });

                setTimeout(() => {
                    errorBanner.style.display = 'none';
                }, 3000);
            }
        });
    </script>
<?php endif; ?>