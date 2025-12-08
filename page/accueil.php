<?php
session_start();
require("../includes/GestionBD.php");

// --- 1. SÉCURITÉ & DONNÉES UTILISATEUR ---
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit();
}
$userId = $_SESSION['user_id'];
$userInfo = GetUserInfo($_SESSION['mail']);
$prenom = htmlspecialchars($userInfo['first_name']);

// --- 2. LOGIQUE DASHBOARD PERSONNEL ---
$trajets_organises = GetOrganizedJourneys($userId);
$trajets_reserves = GetReservedJourneysDetails($userId);
$tous_les_trajets = array_merge($trajets_organises, $trajets_reserves);
$now = time();

$nbTrajetsAVenir = 0;
$nbReservationEnAttente = 0; 
$nbDemandesEnAttente = 0;

foreach ($tous_les_trajets as $t) {
    if (strtotime($t['start_date']) > $now) {
        $nbTrajetsAVenir++;
    }
}

// --- 3. SIMULATION DES "TRAJETS PROCHES" ---
$trajetsProches = [
    ['nom' => 'Thomas', 'depart' => 'Gare du Nord', 'arrivee' => "IUT d'Amiens", 'heure' => '07:30', 'avatar' => '../images/Profil_Picture.png'],
    ['nom' => 'Sarah', 'depart' => 'Centre Ville', 'arrivee' => "UPJV Pôle Sud", 'heure' => '08:15', 'avatar' => '../images/Profil_Picture.png'],
    ['nom' => 'Lucas', 'depart' => 'Gare Routière', 'arrivee' => "St Leu", 'heure' => '08:45', 'avatar' => '../images/Profil_Picture.png']
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - StudyGo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <link rel="stylesheet" href="../css/styleAccueil.css">
</head>
<body>

    <?php require("../includes/header.php") ?>

    <main>
        <section class="hero-banner">
            <div class="hero-text">
                <h1>Bienvenue sur StudyGo, <?= $prenom ?> !</h1>
                <p>Le covoiturage étudiant, simple et malin !</p>
            </div>
            <div class="hero-image-container">
                <img src="../images/TrajetVoiture.jpg" alt="Voyage en voiture" class="hero-img">
            </div>
        </section>

        <section class="search-section-container">
            <div id="map-hero"></div>
            
            <div class="search-bar-wrapper">
                <form action="RechercheTrajet.php" method="GET" class="main-search-form">
                    <div class="search-input-group">
                        <i class="fa-solid fa-location-dot icon-orange"></i>
                        <input type="text" name="depart" placeholder="Départ">
                    </div>
                    <div class="search-input-group middle">
                        <i class="fa-solid fa-location-dot icon-orange"></i>
                        <input type="text" name="destination" placeholder="Destination">
                    </div>
                    <div class="search-input-group date-group">
                        <i class="fa-regular fa-calendar icon-orange"></i>
                        <input type="date" name="date" value="<?= date('Y-m-d') ?>">
                    </div>
                    <button type="submit" class="btn-search-main">
                        <i class="fa-solid fa-magnifying-glass"></i> Rechercher
                    </button>
                </form>
            </div>
        </section>


        <section class="orange-section trajets-proches">
            <h2 class="section-title-white">Trajets proche de vous :</h2>
            <div class="trajets-list">
                <?php foreach($trajetsProches as $tp): ?>
                    <a href="RechercheTrajet.php" class="trajet-card-link">
                        <div class="trajet-card">
                            <img src="<?= $tp['avatar'] ?>" alt="Avatar" class="avatar-mini">
                            <div class="trajet-infos-line">
                                <span class="trajet-route"><?= $tp['depart'] ?> / <?= $tp['arrivee'] ?></span>
                                <span class="trajet-time"><?= $tp['heure'] ?>, Auj.</span>
                            </div>
                            <i class="fa-solid fa-chevron-right arrow-icon"></i>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
            <button class="btn-see-more">Voir plus <i class="fa-solid fa-chevron-down"></i></button>
        </section>


        <section class="orange-section trajets-populaires">
            <div class="pop-content">
                <h2 class="section-title-white left-align">Trajets Populaires</h2>
                <p class="white-text left-align">Les trajets les plus empruntés par les étudiants</p>
                
                <div class="pop-links">
                    <a href="RechercheTrajet.php?depart=Amiens,+Gare+routière&destination=IUT+d'Amiens" class="pop-link">
                        Amiens, Gare routière <i class="fa-solid fa-arrow-right-long"></i> IUT d'Amiens
                        <i class="fa-solid fa-chevron-right chevron-end"></i>
                    </a>
                    <a href="RechercheTrajet.php?depart=IUT+d'Amiens&destination=Amiens,+Gare+routière" class="pop-link">
                        IUT d'Amiens <i class="fa-solid fa-arrow-right-long"></i> Amiens, Gare routière
                        <i class="fa-solid fa-chevron-right chevron-end"></i>
                    </a>
                </div>
            </div>
        </section>


        <section class="dashboard-perso-section">
            <div class="dashboard-container">
                <div class="illustration-col">
                    <img src="../images/EtudiantsEnVoiture.jpg" alt="Illustration Dashboard" class="dash-img">
                </div>
                <div class="content-col">
                    <h2 class="title-orange">Ne perdez plus le fil !</h2>
                    <p class="text-gray">Accédez facilement à vos réservations et retrouvez tous vos trajets passés ou à venir en un seul endroit.</p>
                    
                    <div class="stats-bloc-orange">
                        <div class="stat-line">
                            <strong>Vous avez <?= $nbTrajetsAVenir ?> trajets à venir</strong>
                        </div>
                        <div class="stat-line middle">
                            <strong>Vous avez <?= $nbReservationEnAttente ?> réservation en attente</strong>
                        </div>
                        <div class="stat-line">
                            <strong>Vous avez <?= $nbDemandesEnAttente ?> demandes en attente</strong>
                        </div>
                        
                        <a href="reservation.php" class="btn-voir-trajets">Voir mes trajets</a>
                    </div>
                </div>
            </div>
        </section>


        <section class="orange-section about-preview">
            <div class="about-container-flex">
                <div class="about-img-col">
                    <img src="../images/GroupeEtudiant.jpg" alt="Groupe StudyGo" class="about-img">
                </div>
                <div class="about-text-col">
                    <h2 class="section-title-white">A propos de notre groupe</h2>
                    <p class="white-text justified">
                        Notre application web de covoiturage a été créée par des étudiants, pour des étudiants. Nous avons voulu proposer une solution simple, économique et écologique pour faciliter les déplacements entre le campus, le logement et les lieux de loisirs.
                    </p>
                    <a href="Apropos.php" class="btn-savoir-plus">En savoir plus</a>
                </div>
            </div>
        </section>

    </main>

    <?php require("../includes/footer.php") ?>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var map = L.map('map-hero', { zoomControl: false, scrollWheelZoom: false, dragging: true }).setView([49.8942, 2.2957], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap',
                opacity: 0.8 
            }).addTo(map);
            
            L.marker([49.8942, 2.2957]).addTo(map); 
            L.marker([49.8800, 2.3000]).addTo(map); 
        });
    </script>

</body>
</html>