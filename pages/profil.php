<?php
// Włączyć error reporting do debugowania
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Sprawdzenie czy użytkownik jest zalogowany
if (!isset($_SESSION['user_id'])) {
    header("Location: ?page=logowanie");
    exit();
}

// // Debug: sprawdzenie dostępności zmiennych
// $debug = [
//     'session_active' => session_status() === PHP_SESSION_ACTIVE,
//     'user_id_set' => isset($_SESSION['user_id']),
//     'user_id' => $_SESSION['user_id'] ?? null,
//     'imie' => $_SESSION['imie'] ?? null,
//     'conn_exists' => isset($conn),
//     'conn_valid' => isset($conn) && $conn !== false,
// ];

// Pobranie danych użytkownika z bazy
$user_id = $_SESSION['user_id'];
$user = null;
$error_msg = "";
$profile_message = "";
$profile_message_type = "";

$baseCols = ['id','imie','nazwisko','mail','role'];
$optionalCols = ['pesel','numer_telefonu','miasto','ulica','numer_domu','numer_lokalu','kod_pocztowy','opinia','karnet_typ','karnet_zakup','karnet_koniec','waga','wzrost','bmi', 'ocena'];

// Obsługa opinii
if (isset($conn) && $conn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['opinion_action'])) {
    $action = $_POST['opinion_action'];
    if ($action === 'save') {
        $opiniaText = trim($_POST['opinia_text'] ?? '');
        if (strlen($opiniaText) > 500) {
            $opiniaText = substr($opiniaText, 0, 500);
        }
        $ocena = (int)($_POST['opinia_ocena'] ?? 5);
        if ($ocena < 1) $ocena = 1;
        if ($ocena > 5) $ocena = 5;
        $opiniaSafe = mysqli_real_escape_string($conn, $opiniaText);
        $sql = "UPDATE users SET opinia = '$opiniaSafe', ocena = $ocena WHERE id = '$user_id'";
        mysqli_query($conn, $sql);
    } elseif ($action === 'delete') {
        $sql = "UPDATE users SET opinia = NULL, ocena = NULL WHERE id = '$user_id'";
        mysqli_query($conn, $sql);
    }
    header("Location: ?page=profil");
    exit();
}

// Obsługa zapisu profilu
if (isset($conn) && $conn && $_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['opinion_action'])) {
    $pesel_input = trim($_POST['pesel'] ?? '');
    $pesel_valid = true;
    
    if ($pesel_input !== '') {
        if (!preg_match('/^[0-9]{11}$/', $pesel_input)) {
            $pesel_valid = false;
        } else {
            $digits = str_split($pesel_input);
            $weights = [1, 3, 7, 9, 1, 3, 7, 9, 1, 3];
            $sum = 0;
            foreach ($weights as $index => $weight) {
                $sum += $weight * (int)$digits[$index];
            }
            $control = (10 - ($sum % 10)) % 10;
            if ($control !== (int)$digits[10]) {
                $pesel_valid = false;
            }
        }
    }

    if (!$pesel_valid) {
        $profile_message = "Błędny PESEL. Upewnij się, że ma 11 cyfr i zawiera poprawną datę.";
        $profile_message_type = "error";
    } else {
        $mail_input = trim($_POST['mail'] ?? '');
        $mail_safe = mysqli_real_escape_string($conn, $mail_input);
        $email_check_sql = "SELECT id FROM users WHERE mail = '$mail_safe' AND id != '$user_id'";
        $email_check_result = mysqli_query($conn, $email_check_sql);

        if ($email_check_result && mysqli_num_rows($email_check_result) > 0) {
            $profile_message = "Użytkownik z takim adresem e-mail już istnieje!";
            $profile_message_type = "error";
        } else {
            $updatedCols = ['imie', 'nazwisko', 'mail'];
            foreach ($optionalCols as $col) {
                $check = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE '$col'");
                if ($check && mysqli_num_rows($check) > 0) {
                    $updatedCols[] = $col;
                }
            }

            // Obliczanie BMI automatycznie przy podaniu wagi i wzrostu
            $waga_input = trim($_POST['waga'] ?? '');
            $wzrost_input = trim($_POST['wzrost'] ?? '');
            if ($waga_input !== '' && $wzrost_input !== '' && is_numeric($waga_input) && is_numeric($wzrost_input) && (float)$wzrost_input > 0) {
                $_POST['bmi'] = round((float)$waga_input / (((float)$wzrost_input / 100) ** 2), 1);
            }

            $blockedFields = ['karnet_typ', 'karnet_zakup', 'karnet_koniec', 'opinia', 'ocena'];
            $updates = [];
            foreach ($updatedCols as $col) {
                if ($col === 'role' || $col === 'id' || in_array($col, $blockedFields, true)) {
                    continue;
                }

                $value = trim($_POST[$col] ?? '');
                $escaped = mysqli_real_escape_string($conn, $value);

                if ($escaped === '') {
                    $updates[] = "$col = NULL";
                } else {
                    $updates[] = "$col = '$escaped'";
                }

                if ($col === 'imie') {
                    $_SESSION['imie'] = $value;
                }
            }

            if (!empty($updates)) {
                $sqlUpdate = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = '$user_id'";
                if (mysqli_query($conn, $sqlUpdate)) {
                    $profile_message = "Dane w profilu zostały zapisane.";
                    $profile_message_type = "success";
                } else {
                    $profile_message = "Błąd podczas zapisu: " . mysqli_error($conn);
                    $profile_message_type = "error";
                }
            }
        }
    }
}

if (!isset($conn) || !$conn) {
    $error_msg = "Baza danych nie jest dostępna (conn nie ustawione)";
} else {
    $selectCols = $baseCols;
    foreach ($optionalCols as $col) {
        $check = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE '$col'");
        if ($check && mysqli_num_rows($check) > 0) {
            $selectCols[] = $col;
        }
    }

    $sql = "SELECT " . implode(',', $selectCols) . " FROM users WHERE id = '$user_id'";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        $error_msg = "Błąd zapytania: " . mysqli_error($conn);
    } elseif (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
    } else {
        $error_msg = "Użytkownik o ID $user_id nie znaleziony w bazie";
    }
}

// Pobranie aktywnych karnetów użytkownika (opcjonalne)
$karnety = [];
if ($user && $conn) {
    // Najpierw sprawdźmy, czy tabela istnieje, żeby uniknąć fatalnego błędu
    $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'user_karnety'");
    if ($check_table && mysqli_num_rows($check_table) > 0) {
        $sql_karnety = "SELECT * FROM user_karnety WHERE user_id = '$user_id' AND data_konca >= NOW()";
        $result_karnety = mysqli_query($conn, $sql_karnety);
        if ($result_karnety && mysqli_num_rows($result_karnety) > 0) {
            while ($karnet = mysqli_fetch_assoc($result_karnety)) {
                $karnety[] = $karnet;
            }
        }
    } else {
        // tabela user_karnety nie istnieje — pozostawiamy listę pustą
        $karnety = [];
    }
}
?>

<style>
    .profile-container {
        width: 95%;
        max-width: 900px;
        box-sizing: border-box;
        margin: 40px auto;
        padding: 0 20px;
    }

    .profile-edit-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
    }

    .debug-panel {
        background: #fff3cd;
        border: 2px solid #ffc107;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 30px;
        color: #856404;
        font-family: monospace;
        font-size: 13px;
    }

    .debug-panel h3 {
        margin-top: 0;
        color: #856404;
    }

    .debug-item {
        padding: 8px;
        border-bottom: 1px solid #ffe69c;
    }

    .debug-item:last-child {
        border-bottom: none;
    }

    .debug-value {
        color: #004085;
        font-weight: bold;
    }

    .profile-header {
        background: linear-gradient(135deg, #7a17cb 0%, #b45ed6 100%);
        color: white;
        padding: 40px;
        border-radius: 15px;
        text-align: center;
        margin-bottom: 30px;
        box-shadow: 0 8px 20px rgba(122, 23, 203, 0.3);
    }

    .profile-header h1 {
        margin: 0;
        font-size: 36px;
        margin-bottom: 10px;
    }

    .profile-header p {
        margin: 5px 0;
        font-size: 16px;
        opacity: 0.95;
    }

    .profile-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 30px;
    }

    .profile-edit-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(280px, 1fr));
        gap: 16px;
    }

    @media (max-width: 820px) {
        .profile-edit-grid {
            grid-template-columns: 1fr;
        }
    }

    .profile-card textarea {
        width: 100%;
        min-height: 100px;
        padding: 12px 15px;
        border: 2px solid #ddd;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.3s ease;
        box-sizing: border-box;
        font-family: 'Franklin Gothic Medium', Arial, sans-serif;
        resize: vertical;
    }

    .profile-card textarea:focus,
    .profile-card input:focus {
        outline: none;
        border-color: #7a17cb;
        background-color: #f5f0ff;
        box-shadow: 0 0 10px rgba(122, 23, 203, 0.2);
    }

    .profile-card input {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #ddd;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.3s ease;
        box-sizing: border-box;
        font-family: 'Franklin Gothic Medium', Arial, sans-serif;
    }

    .profile-card input:focus {
        outline: none;
        border-color: #7a17cb;
        background-color: #f5f0ff;
        box-shadow: 0 0 10px rgba(122, 23, 203, 0.2);
    }

    .profile-card .form-group {
        margin: 0;
    }

    .profile-card .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #333;
        font-weight: bold;
        font-size: 14px;
    }

    .profile-card .submit-btn {
        width: 100%;
        padding: 14px;
        background: linear-gradient(135deg, #7a17cb 0%, #b45ed6 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .profile-card .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(122, 23, 203, 0.4);
    }

    .profile-card .submit-btn:active {
        transform: translateY(0);
    }

    .profile-card .success-message,
    .profile-card .error-message {
        margin: 0 0 16px 0;
        padding: 12px 15px;
        border-radius: 8px;
        font-weight: 500;
        color: white;
    }

    .profile-card .success-message {
        background-color: #51cf66;
        border-left: 4px solid #2f9e44;
    }

    .profile-card .error-message {
        background-color: #ff6b6b;
        border-left: 4px solid #d63031;
    }

    .profile-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        padding: 25px;
        border-radius: 12px;
        border: 2px solid #7a17cb;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .profile-card h3 {
        color: #7a17cb;
        margin-top: 0;
        margin-bottom: 15px;
        font-size: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .profile-card p {
        margin: 10px 0;
        color: #333;
        font-size: 15px;
    }

    .profile-card .label {
        color: #666;
        font-weight: bold;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .profile-card .value {
        color: #1a1a1a;
        font-size: 16px;
        font-weight: 500;
    }

    .karnety-section {
        grid-column: 1 / -1;
    }

    .karnety-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 15px;
        margin-top: 15px;
    }

    .karnet-item {
        background: white;
        padding: 15px;
        border-radius: 8px;
        border-left: 4px solid #7a17cb;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .karnet-item h4 {
        margin: 0 0 10px 0;
        color: #7a17cb;
        font-size: 14px;
    }

    .karnet-item p {
        margin: 5px 0;
        font-size: 13px;
        color: #666;
    }

    .empty-message {
        grid-column: 1 / -1;
        text-align: center;
        padding: 20px;
        background: #f0f0f0;
        border-radius: 8px;
        color: #666;
    }

    .action-buttons {
        grid-column: 1 / -1;
        display: flex;
        gap: 15px;
        justify-content: center;
        margin-top: 20px;
    }

    .btn {
        padding: 12px 25px;
        border: none;
        border-radius: 8px;
        font-size: 15px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
    }

    .btn-primary {
        background: linear-gradient(135deg, #7a17cb 0%, #b45ed6 100%);
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(122, 23, 203, 0.4);
    }

    .btn-secondary {
        background: white;
        color: #7a17cb;
        border: 2px solid #7a17cb;
    }

    .btn-secondary:hover {
        background: #f5f0ff;
        transform: translateY(-2px);
    }

    .error-box {
        background: #ffebee;
        color: #c62828;
        padding: 20px;
        border-radius: 8px;
        border-left: 4px solid #d32f2f;
    }

    .error-box h2 {
        margin-top: 0;
        color: #d32f2f;
    }

    .error-box ul {
        margin: 10px 0;
        padding-left: 20px;
    }

    .error-box li {
        margin: 8px 0;
    }

    .opinion-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 999;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    }

    .opinion-overlay.active {
        opacity: 1;
        visibility: visible;
    }
    .opinion-sheet {
        position: fixed;
        bottom: -100%;
        left: 0;
        width: 100%;
        background: #ffffff;
        border-radius: 20px 20px 0 0;
        z-index: 1000;
        transition: bottom 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        box-shadow: 0 -5px 20px rgba(0,0,0,0.2);
        padding: 30px;
        box-sizing: border-box;
    }
    .opinion-sheet.active {
        bottom: 0;
    }
    .opinion-sheet-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    .opinion-sheet-header h2 {
        margin: 0;
        color: #7a17cb;
    }
    .close-sheet {
        background: none;
        border: none;
        font-size: 28px;
        cursor: pointer;
        color: #333;
        font-weight: bold;
    }
    .opinion-textarea {
        width: 100%;
        height: 150px;
        padding: 15px;
        border: 2px solid #ddd;
        border-radius: 10px;
        resize: none;
        font-family: inherit;
        font-size: 15px;
        box-sizing: border-box;
    }
    .opinion-textarea:focus {
        outline: none;
        border-color: #7a17cb;
        background: #f5f0ff;
    }
    .char-counter {
        text-align: right;
        font-size: 13px;
        color: #666;
        margin-top: 5px;
    }
    .sheet-actions {
        display: flex;
        gap: 15px;
        margin-top: 20px;
        justify-content: flex-end;
    }
    .sheet-actions button:disabled {
        background: #ccc;
        cursor: not-allowed;
        box-shadow: none;
    }
    .btn-delete {
        background: #ff6b6b;
        color: white;
    }
    .btn-delete:hover {
        background: #fa5252;
    }

    .star-rating-wrapper {
    display: flex;
    gap: 5px;
    font-size: 28px;
    color: #ccc;
    cursor: pointer;
    margin-right: auto;
    margin-left: 20px;
    user-select: none;
    }
    .star-rating-wrapper .star-btn.active {
        color: #ffc107;
    }

    @media (max-width: 768px) {
        .profile-grid {
            grid-template-columns: 1fr;
        }

        .profile-header h1 {
            font-size: 28px;
        }

        .karnety-list {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="opinion-overlay" id="opinionOverlay" onclick="closeOpinionSheet()"></div>
<div class="opinion-sheet" id="opinionSheet">
    <form method="POST" action="">
        <div class="opinion-sheet-header">
            <h2>Twoja opinia</h2>
            <div class="star-rating-wrapper">
                <span class="star-btn" data-value="1">★</span>
                <span class="star-btn" data-value="2">★</span>
                <span class="star-btn" data-value="3">★</span>
                <span class="star-btn" data-value="4">★</span>
                <span class="star-btn" data-value="5">★</span>
                <input type="hidden" name="opinia_ocena" id="opinia_ocena" value="<?php echo htmlspecialchars($user['ocena'] ?? '5'); ?>">
            </div>
            <button type="button" class="close-sheet" onclick="closeOpinionSheet()">×</button>
        </div>
        <input type="hidden" name="opinion_action" value="save" id="opinionActionInput">
        <textarea name="opinia_text" id="opinia_text" class="opinion-textarea" maxlength="500" placeholder="Napisz co myślisz o EnerGym..."><?php echo htmlspecialchars($user['opinia'] ?? ''); ?></textarea>
        <div class="char-counter" id="charCounter">
            <?php echo strlen($user['opinia'] ?? ''); ?> / 500
        </div>
        <div class="sheet-actions">
            <?php if (!empty($user['opinia'])): ?>
                <button type="submit" class="btn btn-delete" onclick="document.getElementById('opinionActionInput').value='delete'">Usuń</button>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary" id="saveOpinionBtn" disabled>
                <?php echo empty($user['opinia']) ? 'Dodaj opinię' : 'Zapisz'; ?>
            </button>
        </div>
    </form>
</div>

<script>
    const opinionOverlay = document.getElementById('opinionOverlay');
    const opinionSheet = document.getElementById('opinionSheet');
    const opinionTextarea = document.getElementById('opinia_text');
    const charCounter = document.getElementById('charCounter');
    const saveOpinionBtn = document.getElementById('saveOpinionBtn');
    const starBtns = document.querySelectorAll('.star-btn');
    const opiniaOcenaInput = document.getElementById('opinia_ocena');
    
    let originalOpinion = opinionTextarea.value;
    let originalRating = parseInt(opiniaOcenaInput.value) || 5;

    function setStars(rating) {
        starBtns.forEach(btn => {
            if (parseInt(btn.getAttribute('data-value')) <= rating) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
    }

    starBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const val = parseInt(this.getAttribute('data-value'));
            opiniaOcenaInput.value = val;
            setStars(val);
            checkOpinionChanges();
        });
    });

    function openOpinionSheet() {
        opinionOverlay.classList.add('active');
        opinionSheet.classList.add('active');
        originalOpinion = opinionTextarea.value;
        originalRating = parseInt(opiniaOcenaInput.value) || 5;
        setStars(originalRating);
        checkOpinionChanges();
    }

    function closeOpinionSheet() {
        opinionOverlay.classList.remove('active');
        opinionSheet.classList.remove('active');
    }

    opinionTextarea.addEventListener('input', function() {
        charCounter.textContent = this.value.length + ' / 500';
        checkOpinionChanges();
    });

    function checkOpinionChanges() {
        const currentRating = parseInt(opiniaOcenaInput.value) || 5;
        if (originalOpinion !== '') {
            if (opinionTextarea.value === originalOpinion && currentRating === originalRating) {
                saveOpinionBtn.disabled = true;
            } else {
                saveOpinionBtn.disabled = false;
            }
        } else {
            if (opinionTextarea.value.trim() === '') {
                saveOpinionBtn.disabled = true;
            } else {
                saveOpinionBtn.disabled = false;
            }
        }
    }
</script>


<div class="profile-container">
<!-- <div class="debug-panel">
        <h3>🔍 Debug Info</h3>
        <div class="debug-item">
            <strong>Session Status:</strong> 
            <span class="debug-value"><?php echo $debug['session_active'] ? '✓' : '✗'; ?></span>
        </div>
        <div class="debug-item">
            <strong>User ID in Session:</strong> 
            <span class="debug-value"><?php echo $debug['user_id_set'] ? htmlspecialchars($debug['user_id']) : '✗ NOT SET'; ?></span>
        </div>
        <div class="debug-item">
            <strong>Session Imie:</strong> 
            <span class="debug-value"><?php echo $debug['imie'] ?: '✗ NOT SET'; ?></span>
        </div>
        <div class="debug-item">
            <strong>$conn exists:</strong> 
            <span class="debug-value"><?php echo $debug['conn_exists'] ? '✓' : '✗'; ?></span>
        </div>
        <div class="debug-item">
            <strong>$conn is valid:</strong> 
            <span class="debug-value"><?php echo $debug['conn_valid'] ? '✓ CONNECTED' : '✗ FAILED'; ?></span>
        </div>
        <div class="debug-item">
            <strong>User Found in DB:</strong> 
            <span class="debug-value"><?php echo $user ? '✓' : '✗'; ?></span>
        </div>
        <?php if ($error_msg): ?>
            <div class="debug-item">
                <strong>❌ ERROR:</strong> 
                <span class="debug-value"><?php echo htmlspecialchars($error_msg); ?></span>
            </div>
        <?php endif; ?>
    </div> -->

    <?php if ($user): ?>
        <div class="profile-header">
            <h1>👤 <?php echo htmlspecialchars($user['imie'] . ' ' . $user['nazwisko']); ?><?php echo ($user['role'] == 1) ? ' (Admin)' : ''; ?></h1>
            <p>Witaj w Twoim profilu EnerGym!</p>
            <?php if ($user['role'] == 1): ?>
                <p><a href="?page=admin" style="color: #ffeb3b; text-decoration: underline;">Przejdź do panelu admina</a></p>
            <?php endif; ?>
        </div>

        <div class="profile-grid">
            <div class="profile-card">
                <h3>📧 Dane Kontaktowe</h3>
                <p>
                    <span class="label">Adres E-mail:</span><br>
                    <span class="value"><?php echo htmlspecialchars($user['mail']); ?></span>
                </p>
                <p>
                    <span class="label">Imię:</span><br>
                    <span class="value"><?php echo htmlspecialchars($user['imie']); ?></span>
                </p>
                <p>
                    <span class="label">Nazwisko:</span><br>
                    <span class="value"><?php echo htmlspecialchars($user['nazwisko']); ?></span>
                </p>
            </div>

            <div class="profile-card">
                <h3>🏆 Status Konta</h3>
                <p>
                    <span class="label">ID Użytkownika:</span><br>
                    <span class="value"><?php echo htmlspecialchars($user['id']); ?></span>
                </p>
                <p>
                    <span class="label">Typ Konta:</span><br>
                    <span class="value"><?php echo ($user['role'] == 1) ? 'Administrator' : 'Użytkownik'; ?></span>
                </p>
                <p>
                    <span class="label">Status:</span><br>
                    <span class="value" style="color: #51cf66;">✓ Aktywne</span>
                </p>
            </div>

            <div class="profile-card">
                <h3>🏠 Adres zamieszkania</h3>
                <?php if (!empty($user['miasto']) || !empty($user['ulica']) || !empty($user['numer_domu']) || !empty($user['numer_lokalu']) || !empty($user['kod_pocztowy'])): ?>
                    <p><span class="label">Miasto:</span><br><span class="value"><?php echo htmlspecialchars($user['miasto'] ?? '-'); ?></span></p>
                    <p><span class="label">Ulica:</span><br><span class="value"><?php echo htmlspecialchars($user['ulica'] ?? '-'); ?></span></p>
                    <p><span class="label">Nr domu:</span><br><span class="value"><?php echo htmlspecialchars($user['numer_domu'] ?? '-'); ?></span></p>
                    <p><span class="label">Nr lokalu:</span><br><span class="value"><?php echo htmlspecialchars($user['numer_lokalu'] ?? '-'); ?></span></p>
                    <p><span class="label">Kod pocztowy:</span><br><span class="value"><?php echo htmlspecialchars($user['kod_pocztowy'] ?? '-'); ?></span></p>
                <?php else: ?>
                    <p class="value">Adres nie został jeszcze uzupełniony.</p>
                <?php endif; ?>
            </div>

            <div class="profile-card">
                <h3>🎟️ Karnet</h3>
                <?php if (!empty($user['karnet_typ']) && $user['karnet_typ'] !== 'Brak'): ?>
                    <p><span class="label">Typ karnetu:</span><br><span class="value"><?php echo htmlspecialchars($user['karnet_typ']); ?></span></p>
                    <p><span class="label">Data zakupu:</span><br><span class="value"><?php echo !empty($user['karnet_zakup']) ? date('d.m.Y', strtotime($user['karnet_zakup'])) : '-'; ?></span></p>
                    <p><span class="label">Data końca:</span><br><span class="value"><?php echo !empty($user['karnet_koniec']) ? date('d.m.Y', strtotime($user['karnet_koniec'])) : '-'; ?></span></p>
                    
                    <?php if (strpos($user['karnet_typ'], 'Odnawialny') !== false): ?>
                        <?php 
                            $kwota = ($user['karnet_typ'] === 'Karnet Standard Odnawialny') ? '94.99 PLN' : '174.99 PLN';
                            $zakup = new DateTime($user['karnet_zakup']);
                            $now = new DateTime();
                            $nextPayment = clone $zakup;
                            while ($nextPayment <= $now) {
                                $nextPayment->modify('+1 month');
                            }
                            $koniec = new DateTime($user['karnet_koniec']);
                            $isPaidOff = $nextPayment > $koniec;
                        ?>
                        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee;">
                            <p><span class="label">Następna płatność:</span><br>
                               <span class="value" style="color: #2d8f29; font-weight: bold;">
                                   <?php echo $isPaidOff ? 'Opłacono w całości' : $nextPayment->format('d.m.Y'); ?>
                               </span>
                            </p>
                            <?php if (!$isPaidOff): ?>
                                <p><span class="label">Kwota pobrania:</span><br>
                                   <span class="value" style="color: #2d8f29; font-weight: bold;"><?php echo $kwota; ?> / mies.</span>
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="value">Brak aktywnego karnetu.</p>
                    <p><a href="?page=karnety" style="color: #7a17cb; font-weight: bold; text-decoration: underline;">Sprawdź ofertę karnetów</a></p>
                <?php endif; ?>
            </div>

            <div class="profile-card">
                <h3>💪 Dane zdrowotne</h3>
                <?php if (!empty($user['waga']) || !empty($user['wzrost']) || !empty($user['bmi'])): ?>
                    <?php
                        $computedBmi = null;
                        if (!empty($user['waga']) && !empty($user['wzrost']) && is_numeric($user['waga']) && is_numeric($user['wzrost']) && (float)$user['wzrost'] > 0) {
                            $computedBmi = (float)$user['waga'] / (((float)$user['wzrost'] / 100) ** 2);
                        }
                        $bmiDisplay = $computedBmi !== null ? number_format($computedBmi, 1, '.', '') : (!empty($user['bmi']) ? number_format((float)$user['bmi'], 1, '.', '') : '-');
                    ?>
                    <p><span class="label">Waga:</span><br><span class="value"><?php echo htmlspecialchars($user['waga'] ?? '-'); ?> kg</span></p>
                    <p><span class="label">Wzrost:</span><br><span class="value"><?php echo htmlspecialchars($user['wzrost'] ?? '-'); ?> cm</span></p>
                    <p><span class="label">BMI:</span><br><span class="value"><?php echo htmlspecialchars($bmiDisplay); ?></span></p>
                <?php else: ?>
                    <p class="value">Wprowadź wagę i wzrost, aby obliczyć BMI.</p>
                <?php endif; ?>
            </div>

            <div class="profile-card">
                <h3>📝 Opinia</h3>
                <p class="value"><?php echo !empty($user['opinia']) ? nl2br(htmlspecialchars($user['opinia'])) : 'Brak opinii.'; ?></p>
                <?php if (empty($user['opinia'])): ?>
                    <button type="button" class="btn btn-primary" onclick="openOpinionSheet()" style="margin-top: 15px; width: 100%;">Dodaj opinię</button>
                <?php else: ?>
                    <button type="button" class="btn btn-secondary" onclick="openOpinionSheet()" style="margin-top: 15px; width: 100%;">Zobacz</button>
                <?php endif; ?>
            </div>

            <div class="profile-card karnety-section">
                <h3>✍️ Edytuj profil</h3>
                <?php if (!empty($profile_message)): ?>
                    <div class="<?php echo $profile_message_type === 'success' ? 'success-message' : 'error-message'; ?>">
                        <?php echo htmlspecialchars($profile_message); ?>
                    </div>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="profile-edit-grid">
                        <?php
                            $fieldMeta = [
                                'imie' => ['label' => 'Imię', 'type' => 'text'],
                                'nazwisko' => ['label' => 'Nazwisko', 'type' => 'text'],
                                'mail' => ['label' => 'E-mail', 'type' => 'email'],
                                'pesel' => ['label' => 'PESEL', 'type' => 'text'],
                                'numer_telefonu' => ['label' => 'Numer telefonu', 'type' => 'text'],
                                'miasto' => ['label' => 'Miasto', 'type' => 'text'],
                                'ulica' => ['label' => 'Ulica', 'type' => 'text'],
                                'numer_domu' => ['label' => 'Numer domu', 'type' => 'text'],
                                'numer_lokalu' => ['label' => 'Numer lokalu', 'type' => 'text'],
                                'kod_pocztowy' => ['label' => 'Kod pocztowy', 'type' => 'text'],
                                'opinia' => ['label' => 'Opinia', 'type' => 'textarea'],
                                'waga' => ['label' => 'Waga (kg)', 'type' => 'number', 'step' => '0.1'],
                                'wzrost' => ['label' => 'Wzrost (cm)', 'type' => 'number', 'step' => '0.1'],
                                'bmi' => ['label' => 'BMI', 'type' => 'number', 'step' => '0.1'],
                            ];
                            $blockedProfileFields = ['karnet_typ', 'karnet_zakup', 'karnet_koniec', 'opinia', 'ocena'];
                            foreach ($selectCols as $col):
                                if (in_array($col, ['id', 'role'], true) || in_array($col, $blockedProfileFields, true)) {
                                    continue;
                                }
                                $meta = $fieldMeta[$col] ?? ['label' => ucfirst(str_replace('_', ' ', $col)), 'type' => 'text'];
                                $value = $user[$col] ?? '';
                                if ($col === 'bmi' && $value !== '') {
                                    $value = number_format((float)$value, 1, '.', '');
                                }
                        ?>
                            <div class="form-group">
                                <label for="<?php echo htmlspecialchars($col); ?>"><?php echo htmlspecialchars($meta['label']); ?></label>
                                <?php if ($meta['type'] === 'textarea'): ?>
                                    <textarea id="<?php echo htmlspecialchars($col); ?>" name="<?php echo htmlspecialchars($col); ?>" rows="3"><?php echo htmlspecialchars($value); ?></textarea>
                                <?php else: ?>
                                    <input
                                        type="<?php echo htmlspecialchars($meta['type']); ?>"
                                        id="<?php echo htmlspecialchars($col); ?>"
                                        name="<?php echo htmlspecialchars($col); ?>"
                                        value="<?php echo htmlspecialchars($value); ?>"
                                        <?php echo isset($meta['step']) ? 'step="' . htmlspecialchars($meta['step']) . '"' : ''; ?>
                                    >
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" class="submit-btn" style="margin-top: 10px;">Zapisz dane profilu</button>
                </form>
            </div>

            <script>
                function calculateBmi(weight, height) {
                    if (!weight || !height || height <= 0) return '';
                    return Math.round((weight / ((height / 100) ** 2)) * 10) / 10;
                }

                const profileWeight = document.getElementById('waga');
                const profileHeight = document.getElementById('wzrost');
                const profileBmi = document.getElementById('bmi');

                if (profileWeight && profileHeight && profileBmi) {
                    const updateProfileBmi = () => {
                        const bmiValue = calculateBmi(parseFloat(profileWeight.value), parseFloat(profileHeight.value));
                        profileBmi.value = bmiValue !== '' ? bmiValue.toFixed(1) : '';
                    };
                    profileWeight.addEventListener('input', updateProfileBmi);
                    profileHeight.addEventListener('input', updateProfileBmi);
                    updateProfileBmi();
                }

                const phpMessages = document.querySelectorAll('.success-message, .error-message');
                phpMessages.forEach(msg => {
                    setTimeout(() => {
                        msg.style.display = 'none';
                    }, 3000);
                });

                const peselInput = document.getElementById('pesel');
                if (peselInput) {
                    const form = peselInput.closest('form');
                    form.addEventListener('submit', function(e) {
                        const peselVal = peselInput.value.trim();
                        if (peselVal !== '' && !/^\d{11}$/.test(peselVal)) {
                            e.preventDefault(); 
                            
                            let errorMsg = form.previousElementSibling;
                            if (!errorMsg || (!errorMsg.classList.contains('error-message') && !errorMsg.classList.contains('success-message'))) {
                                errorMsg = document.createElement('div');
                                form.parentNode.insertBefore(errorMsg, form);
                            }
                            
                            errorMsg.className = 'error-message';
                            errorMsg.textContent = 'Błędny PESEL. Musi składać się z dokładnie 11 cyfr.';
                            errorMsg.style.display = 'block';
                            
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                            
                            setTimeout(() => {
                                errorMsg.style.display = 'none';
                            }, 3000);
                        }
                    });
                }
            </script>


            <?php if (empty($user['karnet_typ']) || $user['karnet_typ'] === 'Brak'): ?>
                <div class="empty-message" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; margin-top: 30px;">
                    <div style="font-size: 16px; color: #333;">
                        📭 Nie masz jeszcze aktywnego karnetu.
                    </div>
                    <a href="?page=karnety" class="btn btn-primary">💳 Kup go tutaj!</a>
                </div>
            <?php endif; ?>

            <div class="action-buttons" style="margin-top: 30px;">
                <a href="?page=wylogowanie" class="btn btn-secondary" style="width: 100%; text-align: center; padding: 15px; box-sizing: border-box;">Wyloguj się</a>
            </div>

            <div class="action-buttons">
                <a href="?page=karnety" class="btn btn-primary">💳 Kup Karnet</a>
                <a href="?page=wylogowanie" class="btn btn-secondary">Wyloguj się</a>
            </div>
        </div>
    <?php else: ?>
        <div class="error-box">
            <h2>⚠️ Nie można załadować profilu</h2>
            <p><?php echo htmlspecialchars($error_msg); ?></p>
            <p><strong>Próbuj:</strong></p>
            <ul>
                <li><a href="?page=logowanie">Zaloguj się ponownie</a></li>
                <li><a href="?page=home">Powrót do strony głównej</a></li>
            </ul>
        </div>
    <?php endif; ?>
</div>
