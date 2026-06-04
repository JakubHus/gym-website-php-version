<?php
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 1) {
    header("Location: ?page=home");
    exit();
}

$message = '';
$values = [
    'imie' => '',
    'nazwisko' => '',
    'mail' => '',
    'haslo' => '',
    'role' => 0,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($values as $key => $_) {
        if (isset($_POST[$key])) {
            $values[$key] = trim($_POST[$key]);
        }
    }

    if ($values['imie'] === '' || $values['nazwisko'] === '' || $values['mail'] === '' || $values['haslo'] === '' || !in_array($values['role'], ['0', '1'], true)) {
        $message = 'Proszę wypełnić obowiązkowe pola: imię, nazwisko, e-mail, hasło i rola.';
    } else {
        $imie_safe = mysqli_real_escape_string($conn, $values['imie']);
        $nazwisko_safe = mysqli_real_escape_string($conn, $values['nazwisko']);
        $mail_safe = mysqli_real_escape_string($conn, $values['mail']);
        $haslo_safe = password_hash($values['haslo'], PASSWORD_DEFAULT);
        $role_safe = (int)$values['role'];

        $sql = "INSERT INTO users (imie,nazwisko,mail,haslo,role) VALUES ('{$imie_safe}','{$nazwisko_safe}','{$mail_safe}','{$haslo_safe}',{$role_safe})";

        if (mysqli_query($conn, $sql)) {
            header("Location: ?page=admin&msg=user_added");
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
    <h2>Dodaj nowego użytkownika</h2>
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
                <label>Hasło</label>
                <input type="password" name="haslo" value="" required>
            </div>
            <div>
                <label>Rola</label>
                <select name="role">
                    <option value="0" <?php echo $values['role'] == 0 ? 'selected' : ''; ?>>Użytkownik</option>
                    <option value="1" <?php echo $values['role'] == 1 ? 'selected' : ''; ?>>Administrator</option>
                </select>
            </div>
        </div>

        <div style="margin-top: 20px; display: flex; gap: 14px; flex-wrap: wrap;">
            <button type="submit">Utwórz użytkownika</button>
            <a class="cancel-link" href="?page=admin">Anuluj</a>
        </div>
    </form>
</div>