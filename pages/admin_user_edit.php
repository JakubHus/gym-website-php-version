<?php
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 1) {
    header("Location: ?page=home");
    exit();
}

$edit_id = (int)($_GET['id'] ?? 0);
if ($edit_id <= 0) {
    header("Location: ?page=admin");
    exit();
}

$message = '';
$user = null;
$values = [];

function is_valid_pesel($pesel) {
    if (!preg_match('/^[0-9]{11}$/', $pesel)) {
        return false;
    }
    $digits = str_split($pesel);
    $weights = [1, 3, 7, 9, 1, 3, 7, 9, 1, 3];
    $sum = 0;
    foreach ($weights as $index => $weight) {
        $sum += $weight * (int)$digits[$index];
    }
    $control = (10 - ($sum % 10)) % 10;
    return $control === (int)$digits[10];
}

$result = mysqli_query($conn, "SELECT * FROM users WHERE id = $edit_id");
if ($result && mysqli_num_rows($result) === 1) {
    $user = mysqli_fetch_assoc($result);
    foreach ($user as $key => $val) {
        $values[$key] = $val;
    }
} else {
    header("Location: ?page=admin");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updatedFields = ['imie','nazwisko','mail','role','pesel','numer_telefonu','miasto','ulica','numer_domu','numer_lokalu','kod_pocztowy','opinia','karnet_typ','karnet_zakup','karnet_koniec','waga','wzrost','bmi'];
    foreach ($updatedFields as $field) {
        $values[$field] = trim($_POST[$field] ?? '');
    }

    if ($values['imie'] === '' || $values['nazwisko'] === '' || $values['mail'] === '' || !in_array($values['role'], ['0', '1'], true)) {
        $message = 'Proszę wypełnić obowiązkowe pola: imię, nazwisko, e-mail i rola.';
    } elseif ($values['pesel'] !== '' && !is_valid_pesel($values['pesel'])) {
        $message = 'PESEL jest nieprawidłowy. Sprawdź sumę kontrolną i długość 11 cyfr.';
    } else {
        if ($values['waga'] !== '' && $values['wzrost'] !== '' && is_numeric($values['waga']) && is_numeric($values['wzrost']) && (float)$values['wzrost'] > 0) {
            $values['bmi'] = round((float)$values['waga'] / (((float)$values['wzrost'] / 100) ** 2), 1);
        } else {
            $values['bmi'] = '';
        }

        $parts = [];
        $parts[] = "imie='" . mysqli_real_escape_string($conn, $values['imie']) . "'";
        $parts[] = "nazwisko='" . mysqli_real_escape_string($conn, $values['nazwisko']) . "'";
        $parts[] = "mail='" . mysqli_real_escape_string($conn, $values['mail']) . "'";
        $parts[] = "role=" . ((int)$values['role']);
        $parts[] = "pesel=" . ($values['pesel'] !== '' ? "'" . mysqli_real_escape_string($conn, $values['pesel']) . "'" : 'NULL');
        $parts[] = "numer_telefonu=" . ($values['numer_telefonu'] !== '' ? "'" . mysqli_real_escape_string($conn, $values['numer_telefonu']) . "'" : 'NULL');
        $parts[] = "miasto=" . ($values['miasto'] !== '' ? "'" . mysqli_real_escape_string($conn, $values['miasto']) . "'" : 'NULL');
        $parts[] = "ulica=" . ($values['ulica'] !== '' ? "'" . mysqli_real_escape_string($conn, $values['ulica']) . "'" : 'NULL');
        $parts[] = "numer_domu=" . ($values['numer_domu'] !== '' ? "'" . mysqli_real_escape_string($conn, $values['numer_domu']) . "'" : 'NULL');
        $parts[] = "numer_lokalu=" . ($values['numer_lokalu'] !== '' ? "'" . mysqli_real_escape_string($conn, $values['numer_lokalu']) . "'" : 'NULL');
        $parts[] = "kod_pocztowy=" . ($values['kod_pocztowy'] !== '' ? "'" . mysqli_real_escape_string($conn, $values['kod_pocztowy']) . "'" : 'NULL');
        $parts[] = "opinia=" . ($values['opinia'] !== '' ? "'" . mysqli_real_escape_string($conn, $values['opinia']) . "'" : 'NULL');
        $parts[] = "karnet_typ=" . ($values['karnet_typ'] !== '' ? "'" . mysqli_real_escape_string($conn, $values['karnet_typ']) . "'" : 'NULL');
        $parts[] = "karnet_zakup=" . ($values['karnet_zakup'] !== '' ? "'" . mysqli_real_escape_string($conn, $values['karnet_zakup']) . "'" : 'NULL');
        $parts[] = "karnet_koniec=" . ($values['karnet_koniec'] !== '' ? "'" . mysqli_real_escape_string($conn, $values['karnet_koniec']) . "'" : 'NULL');
        $parts[] = "waga=" . ($values['waga'] !== '' ? "'" . mysqli_real_escape_string($conn, $values['waga']) . "'" : 'NULL');
        $parts[] = "wzrost=" . ($values['wzrost'] !== '' ? "'" . mysqli_real_escape_string($conn, $values['wzrost']) . "'" : 'NULL');
        $parts[] = "bmi=" . ($values['bmi'] !== '' ? "'" . mysqli_real_escape_string($conn, $values['bmi']) . "'" : 'NULL');

        if (!empty($_POST['haslo'])) {
            $parts[] = "haslo='" . password_hash($_POST['haslo'], PASSWORD_DEFAULT) . "'";
        }

        $sql = "UPDATE users SET " . implode(', ', $parts) . " WHERE id = $edit_id";
        if (mysqli_query($conn, $sql)) {
            header("Location: ?page=admin&msg=user_updated");
            exit();
        } else {
            $message = 'Wystąpił problem z bazą danych: ' . mysqli_error($conn);
        }
    }
}
?>

<style>
    .admin-form { max-width: 750px; margin: 40px auto; padding: 20px; background: #fff; border: 1px solid #ddd; border-radius: 15px; }
    .admin-form h2 { margin-bottom: 20px; }
    .admin-form .row { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 18px; }
    .admin-form label { display: block; margin-bottom: 6px; font-weight: bold; color: #333; }
    .admin-form input, .admin-form select, .admin-form textarea { width: 100%; padding: 10px 12px; border: 1px solid #ccc; border-radius: 10px; font-size: 15px; box-sizing: border-box; }
    .admin-form textarea { min-height: 100px; resize: vertical; }
    .admin-form .actions { margin-top: 20px; display: flex; gap: 12px; flex-wrap: wrap; }
    .admin-form button { padding: 12px 20px; border: none; border-radius: 10px; cursor: pointer; background: #2d8f29; color: white; font-weight: bold; }
    .admin-form .cancel-link { display: inline-block; padding: 12px 20px; background: #eee; color: #333; text-decoration: none; border-radius: 10px; }
    .admin-form .message { margin-bottom: 20px; padding: 12px 15px; border-radius: 10px; }
    .admin-form .message.error { background: #ffe7e7; color: #8b2323; border: 1px solid #f4c2c2; }
</style>

<div class="admin-form">
    <h2>Edytuj użytkownika: <?php echo htmlspecialchars($values['imie'] . ' ' . $values['nazwisko']); ?></h2>
    <?php if ($message !== ''): ?>
        <div class="message error"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <form method="POST" action="">
        <div class="row">
            <div>
                <label>Imię</label>
                <input type="text" name="imie" value="<?php echo htmlspecialchars($values['imie']); ?>" required>
            </div>
            <div>
                <label>Nazwisko</label>
                <input type="text" name="nazwisko" value="<?php echo htmlspecialchars($values['nazwisko']); ?>" required>
            </div>
            <div>
                <label>E-mail</label>
                <input type="email" name="mail" value="<?php echo htmlspecialchars($values['mail']); ?>" required>
            </div>
            <div>
                <label>Hasło (zostaw puste, jeśli bez zmian)</label>
                <input type="password" name="haslo" value="">
            </div>
            <div>
                <label>Rola</label>
                <select name="role">
                    <option value="0" <?php echo $values['role'] == 0 ? 'selected' : ''; ?>>Użytkownik</option>
                    <option value="1" <?php echo $values['role'] == 1 ? 'selected' : ''; ?>>Administrator</option>
                </select>
            </div>
            <div>
                <label>PESEL</label>
                <input type="text" name="pesel" value="<?php echo htmlspecialchars($values['pesel']); ?>">
            </div>
            <div>
                <label>Numer telefonu</label>
                <input type="text" name="numer_telefonu" value="<?php echo htmlspecialchars($values['numer_telefonu']); ?>">
            </div>
            <div>
                <label>Miasto</label>
                <input type="text" name="miasto" value="<?php echo htmlspecialchars($values['miasto']); ?>">
            </div>
            <div>
                <label>Ulica</label>
                <input type="text" name="ulica" value="<?php echo htmlspecialchars($values['ulica']); ?>">
            </div>
            <div>
                <label>Numer domu</label>
                <input type="text" name="numer_domu" value="<?php echo htmlspecialchars($values['numer_domu']); ?>">
            </div>
            <div>
                <label>Numer lokalu</label>
                <input type="text" name="numer_lokalu" value="<?php echo htmlspecialchars($values['numer_lokalu']); ?>">
            </div>
            <div>
                <label>Kod pocztowy</label>
                <input type="text" name="kod_pocztowy" value="<?php echo htmlspecialchars($values['kod_pocztowy']); ?>">
            </div>
            <div>
                <label>Typ karnetu</label>
                <input type="text" name="karnet_typ" value="<?php echo htmlspecialchars($values['karnet_typ']); ?>">
            </div>
            <div>
                <label>Data zakupu karnetu</label>
                <input type="date" name="karnet_zakup" value="<?php echo htmlspecialchars($values['karnet_zakup']); ?>">
            </div>
            <div>
                <label>Data końca karnetu</label>
                <input type="date" name="karnet_koniec" value="<?php echo htmlspecialchars($values['karnet_koniec']); ?>">
            </div>
            <div>
                <label>Waga (kg)</label>
                <input id="waga" type="number" step="0.1" name="waga" value="<?php echo htmlspecialchars($values['waga']); ?>">
            </div>
            <div>
                <label>Wzrost (cm)</label>
                <input id="wzrost" type="number" step="0.1" name="wzrost" value="<?php echo htmlspecialchars($values['wzrost']); ?>">
            </div>
            <div>
                <label>BMI</label>
                <input id="bmi" type="number" step="0.1" name="bmi" value="<?php echo htmlspecialchars($values['bmi']); ?>" readonly>
            </div>
            <div style="grid-column: 1 / -1;">
                <label>Opinia</label>
                <textarea name="opinia"><?php echo htmlspecialchars($values['opinia']); ?></textarea>
            </div>
        </div>

        <div style="margin-top: 20px; display: flex; gap: 14px; flex-wrap: wrap;">
            <button type="submit">Zapisz zmiany</button>
            <a class="cancel-link" href="?page=admin">Anuluj</a>
        </div>
    </form>
</div>

<script>
    function calculateBmi(weight, height) {
        if (!weight || !height || height <= 0) return '';
        return Math.round((weight / ((height / 100) ** 2)) * 10) / 10;
    }

    const editWeight = document.getElementById('waga');
    const editHeight = document.getElementById('wzrost');
    const editBmi = document.getElementById('bmi');

    if (editWeight && editHeight && editBmi) {
        const updateEditBmi = () => {
            const bmiValue = calculateBmi(parseFloat(editWeight.value), parseFloat(editHeight.value));
            editBmi.value = bmiValue !== '' ? bmiValue.toFixed(1) : '';
        };
        editWeight.addEventListener('input', updateEditBmi);
        editHeight.addEventListener('input', updateEditBmi);
        updateEditBmi();
    }
</script>
