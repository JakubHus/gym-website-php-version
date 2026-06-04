    <div class="bmi-container">
        <h1>Kalkulator BMI</h1>
        <form id="bmiForm">
            <label for="wzrost">Wzrost (cm):</label>
            <input type="number" id="wzrost" name="wzrost" placeholder="Podaj swój wzrost w cm" required>

            <label for="waga">Waga (kg):</label>
            <input type="number" id="waga" name="waga" placeholder="Podaj swoją wagę w kg" required>

            <button type="button" id="obliczBMI">Oblicz BMI</button>
        </form>
        <div id="wynikBMI" class="bmi-result"></div>
    </div>

    <div class="bmi-image-section">
        <div class="bmi-image-container">
            <img id="bmiImage" src="pic/bmi.png" alt="Tabela wskaźnika BMI dla różnych kategorii wagowych" />
        </div>
    </div>

    <script>
        document.getElementById('obliczBMI').addEventListener('click', function() {
            const wzrost = parseFloat(document.getElementById('wzrost').value) / 100;
            const waga = parseFloat(document.getElementById('waga').value);

            const wynikBMI = document.getElementById('wynikBMI');
            if (wzrost > 0 && waga > 0) {
                const bmi = (waga / (wzrost * wzrost)).toFixed(2);
                let kategoria = '';

                if (bmi < 18.5) {
                    kategoria = 'Niedowaga';
                } else if (bmi >= 18.5 && bmi < 24.9) {
                    kategoria = 'Waga prawidłowa';
                } else if (bmi >= 25 && bmi < 29.9) {
                    kategoria = 'Nadwaga';
                } else {
                    kategoria = 'Otyłość';
                }

                wynikBMI.innerText = `Twoje BMI: ${bmi} (${kategoria})`;
            } else {
                wynikBMI.innerText = 'Podaj prawidłowe wartości wzrostu i wagi.';
            }
        });
    </script>
