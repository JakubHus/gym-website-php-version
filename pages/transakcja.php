    <div class="logo-banner">
        <img src="pic/logo_gym1.jpg" alt="Logo siłowni" class="logo-img">
    </div>

    <div class="form-container">
        <form id="transaction-form">
            <h2>Wprowadź swoje dane:</h2>
            <div class="form-row">
                <div class="form-group">
                    <label for="email">Podaj e-mail</label>
                    <input type="email" id="email" name="email" required placeholder="e-mail">
                </div>
                <div class="form-group">
                    <label for="name">Podaj imię</label>
                    <input type="text" id="name" name="name" required placeholder="imię">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="surname">Podaj nazwisko</label>
                    <input type="text" id="surname" name="surname" required placeholder="nazwisko">
                </div>
                <div class="form-group">
                    <label for="address">Podaj swój adres zamieszkania</label>
                    <input type="text" id="address" name="address" required placeholder="adres">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="postalcode">Podaj kod pocztowy</label>
                    <input type="text" id="postalcode" name="postalcode" required placeholder="kod pocztowy">
                </div>
                <div class="form-group">
                    <label for="phone">Podaj numer telefonu</label>
                    <input type="tel" id="phone" name="phone" required placeholder="numer telefonu">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label id="blik-label" for="blik">Wprowadź kod BLIK z aplikacji bankowej</label>
                    <input type="text" id="blik" name="blik" required placeholder="Blik">
                </div>
            </div>

            <button type="button" id="submit-btn" class="submit-btn">Zatwierdź transakcję</button>
        </form>
    </div>
    
    <div id="processing-message">Przetwarzanie transakcji. Proszę czekać...</div>
    <div id="success-message">Transakcja przebiegła pomyślnie. Karnet zostanie przesłany na Twój e-mail. Dziękujemy za zakup.</div>

    <script>
        document.getElementById('submit-btn').addEventListener('click', function() {
            document.getElementById('processing-message').style.display = 'block';
            
            document.getElementById('transaction-form').style.display = 'none';
            
            setTimeout(function() {
                document.getElementById('processing-message').style.display = 'none';
                document.getElementById('success-message').style.display = 'block';
            }, 5000);

            setTimeout(function() {
                window.location.href = 'home';
            }, 10000);
        });
    </script>
