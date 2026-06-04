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
$optionalCols = ['pesel','numer_telefonu','miasto','ulica','numer_domu','numer_lokalu','kod_pocztowy','opinia','karnet_typ','karnet_zakup','karnet_koniec','waga','wzrost','bmi'];

// Obsługa zapisu profilu
if (isset($conn) && $conn && $_SERVER['REQUEST_METHOD'] === 'POST') {
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
        $profile_message = "PESEL jest nieprawidłowy. Sprawdź sumę kontrolną i długość 11 cyfr.";
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

        $updates = [];
        foreach ($updatedCols as $col) {
            if ($col === 'role' || $col === 'id') {
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
        width: 50%;
        min-width: 320px;
        box-sizing: border-box;
        margin: 40px auto;
        padding: 0 20px;
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
                <?php if (!empty($user['karnet_typ']) || !empty($user['karnet_zakup']) || !empty($user['karnet_koniec'])): ?>
                    <p><span class="label">Typ karnetu:</span><br><span class="value"><?php echo htmlspecialchars($user['karnet_typ'] ?? '-'); ?></span></p>
                    <p><span class="label">Data zakupu:</span><br><span class="value"><?php echo !empty($user['karnet_zakup']) ? date('d.m.Y', strtotime($user['karnet_zakup'])) : '-'; ?></span></p>
                    <p><span class="label">Data końca:</span><br><span class="value"><?php echo !empty($user['karnet_koniec']) ? date('d.m.Y', strtotime($user['karnet_koniec'])) : '-'; ?></span></p>
                <?php else: ?>
                    <p class="value">Brak karnetu.</p>
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
                            $blockedProfileFields = ['karnet_typ', 'karnet_zakup', 'karnet_koniec'];
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
            </script>

            <div class="profile-card karnety-section">
                <h3>🎟️ Twoje Karnety</h3>
                <?php if (count($karnety) > 0): ?>
                    <div class="karnety-list">
                        <?php foreach ($karnety as $karnet): ?>
                            <div class="karnet-item">
                                <h4><?php echo htmlspecialchars($karnet['typ_karnetu'] ?? 'Karnet'); ?></h4>
                                <p><strong>Data ważności do:</strong> <?php echo date('d.m.Y', strtotime($karnet['data_konca'] ?? '')); ?></p>
                                <p><strong>Dni pozostałe:</strong> <span style="color: #7a17cb; font-weight: bold;">
                                    <?php echo ceil((strtotime($karnet['data_konca'] ?? '') - time()) / (60*60*24)); ?>
                                </span></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-message">
                        📭 Nie masz aktywnych karnetów. <a href="?page=karnety" style="color: #7a17cb; font-weight: bold; text-decoration: none;">Kup karnet →</a>
                    </div>
                <?php endif; ?>
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
