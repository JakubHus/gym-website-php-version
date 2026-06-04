<?php
// Rejestracja — tylko imię, nazwisko, e-mail i hasło
$message = "";

// Obsługa wysłania formularza
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Required fields
    $imie = trim($_POST['imie'] ?? '');
    $nazwisko = trim($_POST['nazwisko'] ?? '');
    $mail = trim($_POST['mail'] ?? '');
    $haslo = $_POST['haslo'] ?? '';

    if (empty($imie) || empty($nazwisko) || empty($mail) || empty($haslo)) {
        $message = "Imię, nazwisko, e-mail i hasło są wymagane!";
    } else {
        $hashed_password = password_hash($haslo, PASSWORD_DEFAULT);

        // Escape
        $imie_safe = mysqli_real_escape_string($conn, $imie);
        $nazwisko_safe = mysqli_real_escape_string($conn, $nazwisko);
        $mail_safe = mysqli_real_escape_string($conn, $mail);

        $sql = "INSERT INTO users (imie, nazwisko, mail, haslo) VALUES ('$imie_safe', '$nazwisko_safe', '$mail_safe', '$hashed_password')";

        if (mysqli_query($conn, $sql)) {
            header("Location: ?page=logowanie&msg=registered");
            exit();
        } else {
            $errno = mysqli_errno($conn);
            if ($errno === 1062) {
                $message = "Konto z takim adresem e-mail już istnieje!";
            } else {
                $message = "Wystąpił błąd bazy danych. Spróbuj ponownie.";
            }
        }
    }
}
?>

<style>
    .registration-container {
        width: 33%;
        min-width: 320px;
        box-sizing: border-box;
        margin: 60px auto;
        padding: 40px;
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border-radius: 15px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        border: 3px solid #7a17cb;
    }

    .registration-container h1 {
        text-align: center;
        color: #1a1a1a;
        font-size: 32px;
        margin-bottom: 10px;
        font-weight: bold;
    }

    .registration-subtitle {
        text-align: center;
        color: #666;
        font-size: 14px;
        margin-bottom: 30px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #333;
        font-weight: bold;
        font-size: 14px;
    }

    .form-group input {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #ddd;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.3s ease;
        box-sizing: border-box;
        font-family: 'Franklin Gothic Medium', Arial, sans-serif;
    }

    .form-group input:focus {
        outline: none;
        border-color: #7a17cb;
        background-color: #f5f0ff;
        box-shadow: 0 0 10px rgba(122, 23, 203, 0.2);
    }

    .submit-btn {
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
        margin-top: 10px;
    }

    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(122, 23, 203, 0.4);
    }

    .submit-btn:active {
        transform: translateY(0);
    }

    .error-message {
        background-color: #ff6b6b;
        color: white;
        padding: 14px;
        margin-bottom: 20px;
        border-radius: 8px;
        border-left: 4px solid #d63031;
        font-weight: 500;
    }

    .success-message {
        background-color: #51cf66;
        color: white;
        padding: 14px;
        margin-bottom: 20px;
        border-radius: 8px;
        border-left: 4px solid #2f9e44;
        font-weight: 500;
    }

    .login-link-section {
        text-align: center;
        margin-top: 25px;
        padding-top: 20px;
        border-top: 2px solid #eee;
    }

    .login-link-section p {
        color: #666;
        font-size: 14px;
        margin: 0;
    }

    .login-link-section a {
        color: #7a17cb;
        text-decoration: none;
        font-weight: bold;
        transition: color 0.3s ease;
    }

    .login-link-section a:hover {
        color: #b45ed6;
        text-decoration: underline;
    }
</style>

<div class="registration-container">
    <h1>🔐 Rejestracja</h1>
    <p class="registration-subtitle">Dołącz do społeczności EnerGym i zacznij swoją przygodę!</p>

    <?php if (!empty($message)): ?>
        <?php if (strpos($message, 'wymaga') !== false || strpos($message, 'błąd') !== false || strpos($message, 'już') !== false): ?>
            <div class="error-message"><?php echo $message; ?></div>
        <?php else: ?>
            <div class="success-message"><?php echo $message; ?></div>
        <?php endif; ?>
    <?php endif; ?>

    <form method="POST" action="">
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:16px;">
            <div class="form-group">
                <label for="imie">✨ Imię</label>
                <input type="text" id="imie" name="imie" placeholder="Wpisz swoje imię" required>
            </div>

            <div class="form-group">
                <label for="nazwisko">✨ Nazwisko</label>
                <input type="text" id="nazwisko" name="nazwisko" placeholder="Wpisz swoje nazwisko" required>
            </div>

            <div class="form-group">
                <label for="mail">📧 Adres E-mail</label>
                <input type="email" id="mail" name="mail" placeholder="Wpisz swój e-mail" required>
            </div>

            <div class="form-group">
                <label for="haslo">🔑 Hasło</label>
                <input type="password" id="haslo" name="haslo" placeholder="Wpisz bezpieczne hasło" required>
            </div>
        </div>

        <div style="margin-top:16px">
            <button type="submit" class="submit-btn">Załóż konto 💪</button>
        </div>
    </form>
    
    <div class="login-link-section">
        <p>Masz już konto? <a href="?page=logowanie">Zaloguj się tutaj!</a></p>
    </div>
</div>