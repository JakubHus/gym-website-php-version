<?php
    require_once __DIR__ . '/init.php'; // sesja + ciasteczka (przed wyslaniem HTML)

    $permit = [
        'home', 'karnety', 'onas', 'kluby', 'bmi',
        'zajecia', 'kontakt', 'regulamin', 'transakcja'
    ];

    $page = $_GET['page'] ?? 'home';
    if( !in_array($page, $permit, true) ) 
    {
        $page = 'home';
    }

    $meta = [
        'home' => [
            'title' => 'energym.com',
            'css'   => 'silownia1.css',
            'desc'  => 'EnerGym to sieć nowoczesnych siłowni w Krakowie. Oferujemy sprzęt najwyższej jakości, zajęcia grupowe, karnety dopasowane do Twoich potrzeb. Sprawdź naszą ofertę!',
            'head'  => '<script src="js/silownia.js" defer></script>',
        ],
        'karnety' => [
            'title' => 'Karnety w EnerGym',
            'css'   => 'karnety.css',
            'desc'  => 'Poznaj ofertę karnetów siłowni EnerGym: Basic, Standard, Premium. Dopasuj karnet do swoich potrzeb i korzystaj z naszych klubów fitness. Sprawdź ceny!',
        ],
        'onas' => [
            'title' => 'O EnerGym',
            'css'   => 'onas.css',
            'desc'  => 'Poznaj historię EnerGym - nowoczesnych siłowni w Krakowie. Odkryj naszą misję, wartości i dołącz do społeczności fitness. Sprawdź więcej!',
        ],
        'kluby' => [
            'title' => 'Nasze Kluby',
            'css'   => 'kluby.css',
            'desc'  => 'Poznaj nasze kluby fitness w Krakowie: SouthClub, EastClub i NorthClub. Oferujemy nowoczesny sprzęt, sale fitness, sauny oraz zajęcia grupowe. Sprawdź lokalizacje!',
            'head'  =>
                '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>' .
                '<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>' .
                '<script src="js/silownia.js" defer></script>',
        ],
        'bmi' => [
            'title' => 'Kalkulator BMI',
            'css'   => 'bmi.css',
            'desc'  => 'Skorzystaj z naszego kalkulatora BMI, aby łatwo obliczyć wskaźnik masy ciała. Dowiedz się, czy Twoja waga jest prawidłowa i poznaj kategorie BMI.',
        ],
        'zajecia' => [
            'title' => 'Zajęcia w EnerGym',
            'css'   => 'zajecia.css',
            'desc'  => 'Odkryj zajęcia fitness w EnerGym: Yoga, CrossFit, Zumba i inne. Spal kalorie, wzmocnij ciało i baw się świetnie! Zapraszamy do zapisów.',
        ],
        'kontakt' => [
            'title' => 'Kontakt EnerGym',
            'css'   => 'kontakt.css',
            'desc'  => 'Skontaktuj się z EnerGym! Oferujemy wsparcie w sprawach związanych z karnetami, zajęciami fitness, oraz pytaniami dotyczącymi siłowni. Sprawdź FAQ!',
        ],
        'regulamin' => [
            'title' => 'Regulamin EnerGym',
            'css'   => 'regulamin.css',
            'desc'  => 'Sprawdź regulamin siłowni EnerGym w Krakowie. Zasady korzystania z usług fitness, karnetów oraz bezpieczeństwa. Zapraszamy do naszych klubów fitness.',
        ],
        'transakcja' => [
            'title' => 'Panel płatności',
            'css'   => 'transakcja.css',
            'desc'  => 'Kup karnet na siłownię EnerGym online. Bezpieczne płatności BLIK i inne opcje. Wypełnij formularz i zacznij swoją przygodę z fitness już dziś!',
        ],
    ];

    $m = $meta[$page] ?? $meta['home'];
    $pageTitle       = $m['title'];
    $pageCss         = $m['css'];
    $pageDescription = $m['desc'];
    $pageHead        = $m['head'] ?? '';

    include __DIR__ . '/header.php';

    $filePath = __DIR__ . '/pages/' . $page . '.php';
    if( file_exists($filePath) ) 
    {
        include $filePath;
    } 
    else 
    {
        echo '<h1>Blad 404</h1><p>Strona o podanym adresie nie istnieje.</p>';
    }

    include __DIR__ . '/footer.php';
?>
