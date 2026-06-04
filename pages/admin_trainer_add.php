<?php
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 1) {
    header("Location: ?page=home");
    exit();
}

$message = '';
$values = ['imie' => '', 'nazwisko' => '', 'specjalizacja' => '', 'cena' => '', 'numer_telefonu' => ''];

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

        $sql = "INSERT INTO trenerzy (imie, nazwisko, specjalizacja, numer_telefonu, cena) VALUES ('{$imie_safe}', '{$nazwisko_safe}', '{$spec_safe}', '{$telefon_safe}', '{$cena_safe}')";
        if (mysqli_query($conn, $sql)) {
            header('Location: ?page=admin&msg=trainer_added');
            exit();
        } else {
            $message = 'Wystąpił problem z bazą danych: ' . mysqli_error($conn);
        }
    }
}
?>

<style>
    .admin-form { width: 33%; min-width: 320px; margin: 40px auto; padding: 20px; background: #fff; border: 1px solid #ddd; border-radius: 15px; box-sizing: border-box; }
    .admin-form h2 { margin-bottom: 20px; }
    .admin-form label { display: block; margin-bottom: 6px; font-weight: bold; }
    .admin-form input { width: 100%; padding: 10px 12px; border: 1px solid #ccc; border-radius: 10px; margin-bottom: 15px; box-sizing: border-box; }
    .admin-form .actions { display: flex; gap: 12px; flex-wrap: wrap; }
    .admin-form button { padding: 12px 20px; background: #4a5ed6; color: white; border: none; border-radius: 10px; cursor: pointer; font-weight: bold; }
    .admin-form .cancel-link { padding: 12px 20px; background: #eee; color: #333; text-decoration: none; border-radius: 10px; }
    .admin-form .message { margin-bottom: 20px; padding: 12px 15px; border-radius: 10px; background: #ffe7e7; color: #8b2323; border: 1px solid #f4c2c2; }
</style>

<div class="admin-form">
    <h2>Dodaj nowego trenera</h2>
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
            <button type="submit">Dodaj trenera</button>
            <a class="cancel-link" href="?page=admin">Anuluj</a>
        </div>
    </form>
</div>
