    <div class="logo-banner">
        <img src="pic/logo_gym1.jpg" alt="Logo siłowni" class="logo-img">
    </div>

    <div class="naglowek">
        <h1>Poznaj naszą ofertę!</h1>
    </div>

    <div class="karnety_section">
        <div class="karnet_card" style="background-image: url('pic/basic.webp'); background-size: cover; background-position: center;">
            <h2 style="font-size: 3rem; color: white;">Karnet Basic</h2>
            <p style="font-size: 1.5rem; color: white;">Idealny dla osób rozpoczynających swoją przygodę z siłownią</p>
            <p class="karnet_price">79,99 PLN / mies.</p>
            <button onclick="openModal('basicModal')" class="karnet_button">Sprawdź</button>
        </div>
        <div class="karnet_card" style="background-image: url('pic/standard.webp'); background-size: cover; background-position: center;">
            <h2 style="font-size: 3rem; color: white;">Karnet Standard</h2>
            <p style="font-size: 1.5rem; color: white;">Dopasowany do potrzeb osób zaawansowanych w treningu siłowym</p>
            <p class="karnet_price">119,99 PLN / mies.</p>
            <button onclick="openModal('standardModal')" class="karnet_button">Sprawdź</button>
        </div>
        <div class="karnet_card" style="background-image: url('pic/premium.webp'); background-size: cover; background-position: center;">
            <h2 style="font-size: 3rem; color: white;">Karnet Premium</h2>
            <p style="font-size: 1.5rem; color: white;">Spełni oczekiwania nawet najbardziej wymagających klientów</p>
            <p class="karnet_price">199,99 PLN / mies.</p>
            <button onclick="openModal('premiumModal')" class="karnet_button">Sprawdź</button>
        </div>
    </div>

    <div id="basicModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('basicModal')">&times;</span>
            <h2>Karnet Basic</h2>
            <ul>
                <li>Możliwość wejścia do wybranego klubu do godz. 16:00 przez 7 dni w tygodniu.</li>
                <li>Darmowy trening z trenerem personalnym jednorazowo w miesiącu.</li>
                <li>Wybór jednego dowolnego klubu w ramach jednego karnetu.</li>
            </ul>
            <button class="buy-button" onclick="location.href='transakcja'">Dokonaj zakupu</button>
        </div>
    </div>

    <div id="standardModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('standardModal')">&times;</span>
            <h2>Karnet Standard</h2>
            <ul>
                <li>Możliwość wejścia do jednego klubu przez 7 dni w tyg. o dowolnej porze w godzinach otwarcia klubu.</li>
                <li>Dostęp do zapisu na jedne wybrane zajęcia grupowe w ramach jednego karnetu.</li>
                <li>Możliwość otrzymania darmowych planów treningowych od naszych ekspertów.</li>
            </ul>
            <button class="buy-button" onclick="location.href='transakcja'">Dokonaj zakupu</button>
        </div>
    </div>

    <div id="premiumModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('premiumModal')">&times;</span>
            <h2>Karnet Premium</h2>
            <ul>
                <li>Możliwość wejścia do dowolnego klubu przez 7 dni w tyg. o dowolnej porze w godzinach otwarcia klubu.</li>
                <li>Dostęp do zapisu na wszystkie zajęcia grupowe w ramach jednego karnetu.</li>
                <li>Jedne darmowe konsultacje z dietetykiem raz w obrębie okresu rozliczeniowego karnetu.</li>
            </ul>
            <button class="buy-button" onclick="location.href='transakcja'">Dokonaj zakupu</button>
        </div>
    </div>

    <script>
        function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('show');
        }
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('show');
        }
    }

    window.onclick = function(event) {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            if (event.target === modal) {
                modal.classList.remove('show');
            }
        });
    };
    </script>
