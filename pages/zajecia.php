    <div class="logo-banner">
        <img src="pic/logo_gym1.jpg" alt="Logo siłowni" class="logo-img">
    </div>

    <div class="naglowek">
        <h1>Nasze Zajęcia</h1>
    </div>

    <div class="zajecia_section">
        <div class="zajecia_card" style="background-image: url('pic/klub1.jpg');">
            <h2>Yoga</h2>
            <p>Relaksujące zajęcia dla każdego. Wzmacniają ciało i redukują stres.</p>
            <button class="zajecia_button" onclick="openPopup('Yoga')">Zapisz się</button>
        </div>
        <div class="zajecia_card" style="background-image: url('pic/klub2.jpg');">
            <h2>CrossFit</h2>
            <p>Intensywne treningi siłowe i kondycyjne. Dla osób chcących wyzwania.</p>
            <button class="zajecia_button" onclick="openPopup('CrossFit')">Zapisz się</button>
        </div>
        <div class="zajecia_card" style="background-image: url('pic/klub3.jpg');">
            <h2>Zumba</h2>
            <p>Połączenie tańca i fitnessu. Spal kalorie i baw się świetnie!</p>
            <button class="zajecia_button" onclick="openPopup('Zumba')">Zapisz się</button>
        </div>
    </div>

    <div id="popup-modal" class="popup-modal">
        <div class="popup-content">
            <h2 id="popup-title">Zapisz się na zajęcia</h2>
            <button id="confirm-button" onclick="confirmPopup()">Potwierdź</button>
            <p id="popup-success" class="popup-success" style="display: none;">
                Aby zapisać się na zajęcia, skontaktuj się z EnerGym drogą telefoniczną (szczegóły w sekcji "kontakt").
            </p>
            <a id="contact-button" href="kontakt" style="display: none;">
                <button class="zajecia_button">Kontakt</button>
            </a>
        </div>
    </div>

    <script>
        function openPopup(className) {
            document.getElementById('popup-title').textContent = `Zapisz się na zajęcia: ${className}`;
            document.getElementById('popup-modal').style.display = 'flex';
            document.getElementById('confirm-button').style.display = 'inline-block';
            document.getElementById('popup-success').style.display = 'none';
            document.getElementById('contact-button').style.display = 'none';
        }

        function confirmPopup() {
            const successMessage = document.getElementById('popup-success');
            const contactButton = document.getElementById('contact-button');
            const confirmButton = document.getElementById('confirm-button');

            confirmButton.style.display = 'none';
            successMessage.style.display = 'block';
            contactButton.style.display = 'inline-block';

            setTimeout(() => {
                document.getElementById('popup-modal').style.display = 'none';
            }, 10000);
        }

        window.onclick = function(event) {
            const modal = document.getElementById('popup-modal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        };
    </script>
