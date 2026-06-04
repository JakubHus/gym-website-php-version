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
$values = ['imie' => '', 'nazwisko' => '', 'specjalizacja' => '', 'cena' => '', 'numer_telefonu' => ''];

$result = mysqli_query($conn, "SELECT * FROM trenerzy WHERE id = $edit_id");
if ($result && mysqli_num_rows($result) === 1) {
    $row = mysqli_fetch_assoc($result);
    $values = array_merge($values, $row);
    $values['numer_telefonu'] = $row['numer_telefonu'] ?? '';
} else {
    header("Location: ?page=admin");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($values as $key => $_) {
        $values[$key] = trim($_POST[$key] ?? '');
    }

    if ($values['imie'] === '' || $values['nazwisko'] === '' || $values['specjalizacja'] === '' || $values['cena'] === '' || $values['numer_telefonu'] === '') {
        $message = 'Wszystkie pola są wymagane!';
    } elseif (!is_numeric($values['cena'])) {
        $message = 'Cena musi być liczbą.';
    } else {
        $imie_safe = mysqli_real_escape_string($conn, $values['imie']);
        $nazwisko_safe = mysqli_real_escape_string($conn, $values['nazwisko']);
        $spec_safe = mysqli_real_escape_string($conn, $values['specjalizacja']);
        $telefon_safe = mysqli_real_escape_string($conn, $values['numer_telefonu']);
        $cena_safe = number_format((float)$values['cena'], 2, '.', '');

        $sql = "UPDATE trenerzy SET imie='{$imie_safe}', nazwisko='{$nazwisko_safe}', specjalizacja='{$spec_safe}', numer_telefonu='{$telefon_safe}', cena='{$cena_safe}' WHERE id = $edit_id";
        if (mysqli_query($conn, $sql)) {
            header('Location: ?page=admin&msg=trainer_updated');
            exit();
        } else {
            $message = 'Wystąpił problem z bazą danych: ' . mysqli_error($conn);
        }
    }
}
?>

<style>
    .admin-form { max-width: 650px; margin: 40px auto; padding: 20px; background: #fff; border: 1px solid #ddd; border-radius: 15px; }
    .admin-form h2 { margin-bottom: 20px; }
    .admin-form label { display: block; margin-bottom: 6px; font-weight: bold; }
    .admin-form input { width: 100%; padding: 10px 12px; border: 1px solid #ccc; border-radius: 10px; margin-bottom: 15px; box-sizing: border-box; }
    .admin-form .actions { display: flex; gap: 12px; flex-wrap: wrap; }
    .admin-form button { padding: 12px 20px; background: #4a5ed6; color: white; border: none; border-radius: 10px; cursor: pointer; font-weight: bold; }
    .admin-form .cancel-link { padding: 12px 20px; background: #eee; color: #333; text-decoration: none; border-radius: 10px; }
    .admin-form .message { margin-bottom: 20px; padding: 12px 15px; border-radius: 10px; background: #ffe7e7; color: #8b2323; border: 1px solid #f4c2c2; }
</style>

<div class="admin-form">
    <h2>Edytuj trenera: <?php echo htmlspecialchars($values['imie'] . ' ' . $values['nazwisko']); ?></h2>
    <?php if ($message !== ''): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <form method="POST" action="">
        <label>Imię</label>
        <input type="text" name="imie" value="<?php echo htmlspecialchars($values['imie']); ?>" required>

        <label>Nazwisko</label>
        <input type="text" name="nazwisko" value="<?php echo htmlspecialchars($values['nazwisko']); ?>" required>

        <label>Specjalizacja</label>
        <input type="text" name="specjalizacja" value="<?php echo htmlspecialchars($values['specjalizacja']); ?>" required>

        <label>Numer telefonu</label>
        <input type="text" name="numer_telefonu" value="<?php echo htmlspecialchars($values['numer_telefonu']); ?>" required>

        <label>Cena</label>
        <input type="number" step="5" min="0" name="cena" value="<?php echo htmlspecialchars($values['cena']); ?>" required>

        <div class="actions">
            <button type="submit">Zapisz zmiany</button>
            <a class="cancel-link" href="?page=admin">Anuluj</a>
        </div>
    </form>
</div>
