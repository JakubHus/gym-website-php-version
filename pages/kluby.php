    <div class="logo-banner">
        <img src="pic/logo_gym1.jpg" alt="Logo siłowni" class="logo-img">
    </div>

    <div class="naglowek">
        <h1>Nasze Kluby</h1>
    </div>

    <div class="kluby_section">
        <div class="klub_card" style="background-image: url('pic/club1.webp'); opacity: 0.8;">
            <h2>SouthClub</h2>
            <p>Położony na południu Krakowa, oferuje nowoczesny sprzęt i dużo sal fitness.</p>
            <button class="klub_button" onclick="openPopup('popupSouth')">Zobacz więcej</button>
        </div>
        <div class="klub_card" style="background-image: url('pic/club2.webp'); opacity: 0.8;">
            <h2>EastClub</h2>
            <p>Doskonale wyposażony z przestronnymi salami do zajęć grupowych oraz salą do gry w squash.</p>
            <button class="klub_button" onclick="openPopup('popupEast')">Zobacz więcej</button>
        </div>
        <div class="klub_card" style="background-image: url('pic/club3.webp'); opacity: 0.8;">
            <h2>NorthClub</h2>
            <p>Oprócz samej siłowni oferuje dostęp do boiska koszykarskiego, salę MMA oraz dwie sauny.</p>
            <button class="klub_button" onclick="openPopup('popupNorth')">Zobacz więcej</button>
        </div>
    </div>

    <div id="popupSouth" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closePopup('popupSouth')">&times;</span>
            <h2>SouthClub</h2>
            <p>SouthClub to najnowocześniejsza siłownia w Krakowie. Oferujemy:</p>
            <ul>
                <li>Nowoczesny sprzęt do ćwiczeń.</li>
                <li>Dużo sal fitness.</li>
                <li>Dogodna lokalizacja na południu Krakowa.</li>
            </ul>
        </div>
    </div>

    <div id="popupEast" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closePopup('popupEast')">&times;</span>
            <h2>EastClub</h2>
            <p>EastClub wyróżnia się doskonałym wyposażeniem:</p>
            <ul>
                <li>Przestronne sale do zajęć grupowych.</li>
                <li>Nowoczesna sala do gry w squash.</li>
                <li>Profesjonalna obsługa i wsparcie trenerów.</li>
            </ul>
        </div>
    </div>

    <div id="popupNorth" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closePopup('popupNorth')">&times;</span>
            <h2>NorthClub</h2>
            <p>NorthClub to idealne miejsce dla aktywnych:</p>
            <ul>
                <li>Boisko koszykarskie.</li>
                <li>Profesjonalna sala MMA.</li>
                <li>Dwie nowoczesne sauny.</li>
            </ul>
        </div>
    </div>

    <div id="map" style="height: 500px; margin: 20px 0;"></div>

    <script>
        function openPopup(id) {
            const modal = document.getElementById(id);
            modal.classList.add('show');
        }

        function closePopup(id) {
            const modal = document.getElementById(id);
            modal.classList.remove('show');
        }

        var map = L.map('map').setView([50.058, 19.935], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        var marker1 = L.marker([50.041417, 19.922111]).addTo(map);
        marker1.bindPopup("<b>SouthClub</b><br>Położony na południu Krakowa, oferuje nowoczesny sprzęt i dużo sal fitness.");

        var marker2 = L.marker([50.058151, 19.957071]).addTo(map);
        marker2.bindPopup("<b>EastClub</b><br>Doskonale wyposażony z przestronnymi salami do zajęć grupowych oraz salą do gry w squash.");

        var marker3 = L.marker([50.080424, 19.919762]).addTo(map);
        marker3.bindPopup("<b>NorthClub</b><br>Oprócz samej siłowni oferuje dostęp do boiska koszykarskiego, salę MMA oraz dwie sauny.");

        var bounds = L.featureGroup([marker1, marker2, marker3]).getBounds();
        map.fitBounds(bounds);
    </script>
