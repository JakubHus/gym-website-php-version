<?php
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 1) {
    header("Location: ?page=home");
    exit();
}

$msg_html = '';
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'user_added':
            $msg_html = '<div class="admin-alert success">✅ Użytkownik został pomyślnie dodany.</div>';
            break;
        case 'user_updated':
            $msg_html = '<div class="admin-alert success">✅ Dane użytkownika zostały zaktualizowane.</div>';
            break;
        case 'user_deleted':
            $msg_html = '<div class="admin-alert success">✅ Użytkownik został usunięty.</div>';
            break;
        case 'trainer_added':
            $msg_html = '<div class="admin-alert success">✅ Trener został dodany.</div>';
            break;
        case 'trainer_updated':
            $msg_html = '<div class="admin-alert success">✅ Dane trenera zostały zaktualizowane.</div>';
            break;
        case 'trainer_deleted':
            $msg_html = '<div class="admin-alert success">✅ Trener został usunięty.</div>';
            break;
        case 'db_error':
            $msg_html = '<div class="admin-alert error">⛔ Wystąpił błąd bazy danych.</div>';
            break;
        case 'delete_self_error':
            $msg_html = '<div class="admin-alert error">⛔ Nie możesz usunąć własnego konta administratora!</div>';
            break;
    }
}

$userResult = mysqli_query($conn, "SELECT id, imie, nazwisko, mail, role FROM users ORDER BY nazwisko ASC, imie ASC");
$trainerResult = mysqli_query($conn, "SHOW TABLES LIKE 'trenerzy'");
$trainers = [];
$trainerTableExists = $trainerResult && mysqli_num_rows($trainerResult) > 0;
if ($trainerTableExists) {
    $trainerQuery = mysqli_query($conn, "SELECT id, imie, nazwisko, specjalizacja, numer_telefonu, cena FROM trenerzy ORDER BY nazwisko ASC, imie ASC");
    if ($trainerQuery) {
        while ($row = mysqli_fetch_assoc($trainerQuery)) {
            $trainers[] = $row;
        }
    }
}
?>

<style>
    .admin-page { max-width: 1100px; margin: 40px auto; padding: 0 20px; }
    .admin-alert { padding: 15px 20px; border-radius: 10px; margin-bottom: 20px; font-weight: bold; }
    .admin-alert.success { background: #e6ffed; color: #1d6b2e; border: 1px solid #95d7a4; }
    .admin-alert.error { background: #ffe7e7; color: #8b2323; border: 1px solid #f0a2a2; }
    .admin-actions { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 25px; }
    .admin-actions a { padding: 12px 18px; border-radius: 10px; text-decoration: none; color: white; font-weight: bold; }
    .btn-add-user { background: #2d8f29; }
    .btn-add-trainer { background: #4a5ed6; }
    .admin-section { margin-bottom: 40px; }
    .admin-section h2 { margin-bottom: 15px; color: #333; font-size: 26px; }
    .admin-table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 16px; }
    .admin-table th,
    .admin-table td { padding: 18px 15px; border: 1px solid #ddd; }
    .admin-table th { background: #f7f7f7; text-align: left; font-size: 17px; }
    .admin-table tbody tr:hover { background: #fafafa; }
    .admin-table a { color: #2d60d6; text-decoration: none; }
    .admin-table a.delete-link { color: #d32f2f; }
    .admin-section .note { color: #666; margin-top: 8px; }
</style>

<div class="admin-page">
    <?php echo $msg_html; ?>

    <div class="admin-actions">
        <a href="?page=admin_user_add" class="btn-add-user">+ Dodaj użytkownika</a>
        <a href="?page=admin_trainer_add" class="btn-add-trainer">+ Dodaj trenera</a>
    </div>

    <div class="admin-section">
        <h2>Lista użytkowników</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Imię i nazwisko</th>
                    <th>E-mail</th>
                    <th>Rola</th>
                    <th style="text-align:center;">Akcje</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($userResult && mysqli_num_rows($userResult) > 0): ?>
                    <?php while ($userRow = mysqli_fetch_assoc($userResult)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($userRow['id']); ?></td>
                            <td><?php echo htmlspecialchars($userRow['nazwisko'] . ' ' . $userRow['imie']); ?></td>
                            <td><?php echo htmlspecialchars($userRow['mail']); ?></td>
                            <td><?php echo ($userRow['role'] == 1) ? '<strong>Admin</strong>' : 'Użytkownik'; ?></td>
                            <td style="text-align:center;">
                                <a href="?page=admin_user_edit&id=<?php echo htmlspecialchars($userRow['id']); ?>">Edytuj</a>
                                |
                                <a href="?page=admin_user_delete&id=<?php echo htmlspecialchars($userRow['id']); ?>" class="delete-link" onclick="return confirm('Czy na pewno chcesz usunąć tego użytkownika?');">Usuń</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align:center;">Brak użytkowników w systemie.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="admin-section">
        <h2>Lista trenerów</h2>
        <?php if ($trainerTableExists): ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Imię i nazwisko</th>
                        <th>Specjalizacja</th>
                        <th>Telefon</th>
                        <th>Cena</th>
                        <th style="text-align:center;">Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($trainers)): ?>
                        <?php foreach ($trainers as $trainer): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($trainer['id']); ?></td>
                                <td><?php echo htmlspecialchars($trainer['nazwisko'] . ' ' . $trainer['imie']); ?></td>
                                <td><?php echo htmlspecialchars($trainer['specjalizacja']); ?></td>
                                <td><?php echo htmlspecialchars($trainer['numer_telefonu'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($trainer['cena']); ?> zł</td>
                                <td style="text-align:center;">
                                    <a href="?page=admin_trainer_edit&id=<?php echo htmlspecialchars($trainer['id']); ?>">Edytuj</a>
                                    |
                                    <a href="?page=admin_trainer_delete&id=<?php echo htmlspecialchars($trainer['id']); ?>" class="delete-link" onclick="return confirm('Czy na pewno chcesz usunąć tego trenera?');">Usuń</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align:center;">Brak trenerów w bazie danych.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="note">Brak tabeli <strong>trenerzy</strong> w bazie danych. Zarządzanie trenerami jest niedostępne.</p>
        <?php endif; ?>
    </div>
</div>
