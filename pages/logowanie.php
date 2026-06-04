<?php
// Zabezpieczenie przed zalogowanymi (nie logujemy się dwa razy)
if (isset($_SESSION['user_id'])) {
    header("Location: ?page=home");
    exit();
}

$message = "";
$messageType = "";

// Odbieranie komunikatu o udanej rejestracji
if (isset($_GET['msg']) && $_GET['msg'] === 'registered') {
    $message = "Konto zostało założone! Możesz się teraz zalogować.";
    $messageType = "success";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $mail = trim($_POST['mail'] ?? '');
    $haslo = $_POST['haslo'] ?? '';

    if (empty($mail) || empty($haslo)) {
        $message = "Wpisz e-mail i hasło.";
        $messageType = "error";
    } else {
        // Zabezpieczenie e-maila
        $mail_safe = mysqli_real_escape_string($conn, $mail);
        
        // Szukamy użytkownika po e-mailu
        $sql = "SELECT id, imie, haslo, role FROM users WHERE mail = '$mail_safe'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) === 1) {
            $row = mysqli_fetch_assoc($result);
            
            // Weryfikacja hasła
            if (password_verify($haslo, $row['haslo'])) {
                
                // SUKCES! Zapisujemy dane w sesji
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['imie'] = $row['imie'];
                $_SESSION['role'] = (int)$row['role'];

                // Przekierowanie na stronę główną po udanym zalogowaniu
                header("Location: ?page=home");
                exit();
            } else {
                $message = "Błędne hasło!";
                $messageType = "error";
            }
        } else {
            $message = "Nie znaleziono konta z takim adresem e-mail.";
            $messageType = "error";
        }
    }
}
?>

<style>
    .login-container {
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

    .login-container h1 {
        text-align: center;
        color: #1a1a1a;
        font-size: 32px;
        margin-bottom: 10px;
        font-weight: bold;
    }

    .login-subtitle {
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

    .register-link-section {
        text-align: center;
        margin-top: 25px;
        padding-top: 20px;
        border-top: 2px solid #eee;
    }

    .register-link-section p {
        color: #666;
        font-size: 14px;
        margin: 0;
    }

    .register-link-section a {
        color: #7a17cb;
        text-decoration: none;
        font-weight: bold;
        transition: color 0.3s ease;
    }

    .register-link-section a:hover {
        color: #b45ed6;
        text-decoration: underline;
    }
</style>

<div class="login-container">
    <h1>🔓 Logowanie</h1>
    <p class="login-subtitle">Zaloguj się do swojego konta EnerGym</p>

    <?php if (!empty($message)): ?>
        <?php if ($messageType === 'error'): ?>
            <div class="error-message"><?php echo $message; ?></div>
        <?php elseif ($messageType === 'success'): ?>
            <div class="success-message"><?php echo $message; ?></div>
        <?php endif; ?>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="mail">📧 Adres E-mail</label>
            <input type="email" id="mail" name="mail" placeholder="Wpisz swój e-mail" required>
        </div>

        <div class="form-group">
            <label for="haslo">🔑 Hasło</label>
            <input type="password" id="haslo" name="haslo" placeholder="Wpisz swoje hasło" required>
        </div>
        
        <button type="submit" class="submit-btn">Zaloguj się 💪</button>
    </form>

    <div class="register-link-section">
        <p>Nie masz jeszcze konta? <a href="?page=rejestracja">Zarejestruj się tutaj!</a></p>
    </div>
</div>