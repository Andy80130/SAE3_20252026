<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudyGo - Recherche de Trajets</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css" />
    <link rel="stylesheet" href="../css/styleAccueil.css">
    <link rel="stylesheet" href="../css/styleRechercheTrajet.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
</head>
<body>

    <?php require ('../includes/header.php'); ?>

    <!-- Introduction Section -->
    <section>
        <h1 class="title">Rejoindre un trajet</h1>
        <div class="orangeBackground">
            <p>
                Rejoignez un trajet en un instant grâce à notre recherche
                intuitive. Trouvez un conducteur qui se rend dans la même
                direction, consultez les avis et réservez votre place.
                Voyager avec la communauté devient rapide, économique et
                pratique.
            </p>
            <img src="..\images\rechercheTrajetIntro.png" height="200px">
        </div>
    </section>

    <!-- Search Section -->
     <section>
        <h1 class="title">Renseignez votre recherche</h1>
        <form class="search" onsubmit="return false;">
            <div class="positions">
                <label style="display: flex; align-items: center; gap: 5px;">
                    <p>Départ :</p>
                    <input type="text" placeholder="ex : Amiens, Gare routière">
                </label>
                <label style="display: flex; align-items: center; gap: 5px;">
                    <p>Destination</p>
                    <input type="text" placeholder="ex : Amiens, IUT">
                </label>
            </div>
            <div class="horaires">
                <p>Horaires :</p>
                <label style="display: flex; align-items: center; gap: 5px;">
                    <p>Du :</p>
                    <input type="date">
                </label>
                <label style="display: flex; align-items: center; gap: 5px;">
                    <p>Au :</p>
                    <input type="date">
                </label>
                <p>Heure :</p>
                <label style="display: flex; align-items: center; gap: 5px;">
                    <p>Du :</p>
                    <input type="time" id="appt" name="appt">
                </label>
                <label style="display: flex; align-items: center; gap: 5px;">
                    <p>à :</p>
                    <input type="time" id="appt" name="appt">
                </label>
            </div>
        </form>
        <div class="search-btn-position">
            <button class="search-btn" onclick="return false;">
                🔍 Rechercher
            </button>
        </div>
     </section>

     <!-- Map -->
    <h1 class="title">Correspondances trouvées :</h1>
    <div class="map-container">
        <div id="map"></div>
    </div>

    <!-- Correspondances -->
    <section class="searchedJourney">
        <!-- Profil -->
        <div class="user">
            <div>
                <img class="user-icon" src="..\images\Profil_Picture.png" height="50px">
                <p class="name">Placeholder Name</p>
            </div>
            <!-- Infos trajet -->
            <div class="journeyInfo">
                <p>Départ : Gare du Nord</p>
                <p>Arrivée : IUT d'Amiens</p>
                <p>Le : Vendredi 17 Octobre</p>
                <p>Horaires : 7:30 à 7:50</p>
            </div>
            <p class="participants">Participants inscrits : 0</p>
        </div>
        <!-- Boutons -->
        <div class="buttons">
            <button class="search-btn" onclick="infosVoiture('voiture1')">
                Informations voiture
            </button>
            <button class=search-btn onclick="voirCarte([49.8942, 2.2957], [49.8892, 2.3057])">
                Voir sur la carte
            </button>
            <button class="search-btn">Envoyer une demande</button>
        </div>

        <!-- Infos voiture -->
        <div id="voiture1"></div>
    </section>

    <section class="searchedJourney">
        <!-- Profil -->
        <div class="user">
            <div>
                <img class="user-icon" src="..\images\Profil_Picture.png" height="50px">
                <p class="name">Placeholder Name</p>
            </div>
            <!-- Infos trajet -->
            <div class="journeyInfo">
                <p>Départ : Gare du Nord</p>
                <p>Arrivée : IUT d'Amiens</p>
                <p>Le : Vendredi 17 Octobre</p>
                <p>Horaires : 7:30 à 7:50</p>
            </div>
            <p class="participants">Participants inscrits : 0</p>
        </div>
        <!-- Boutons -->
        <div class="buttons">
            <button class="search-btn" onclick="infosVoiture('voiture2')">
                Informations voiture
            </button>
            <button class=search-btn onclick="voirCarte([49.8992, 2.2857], [49.8850, 2.2900])">
                Voir sur la carte
            </button>
            <button class="search-btn">Envoyer une demande</button>
        </div>

        <!-- Infos voiture -->
        <div id="voiture2"></div>
    </section>

    <!-- Rien -->
    <h1 class="orangeBackground">Vous ne trouvez pas ce que vous voulez ?</h1>
    <section class="orangeBackground">
        <p>Aucun trajet ne correspond pour le moment. Essayez d’ajuster vos
            filtres de recherche ou réessayez un peu plus tard.</p>
        <img src="..\images\rechercheTrajetFin.png" height="250px">
    </section>

    <!-- Footer -->
    <?php require ('../includes/footer.php'); ?>


    <script>
        // Initialize the map
        var map = L.map('map').setView([49.8942, 2.2957], 14);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Add markers
        var orangeIcon = L.divIcon({
            className: 'custom-icon',
            html: '<div style="background: #ff6600; width: 30px; height: 30px; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); border: 3px solid white;"></div>',
            iconSize: [30, 30],
            iconAnchor: [15, 30]
        });
        var blueIcon = L.divIcon({
            className: 'custom-icon',
            html: '<div style="background: blue; width: 30px; height: 30px; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); border: 3px solid white;"></div>',
            iconSize: [30, 30],
            iconAnchor: [15, 30]
        });
        var arrayMarkers=[];

        function infosVoiture(className) {
            if(document.getElementById(className).innerHTML == ""){
                document.getElementById(className).innerHTML = "<p>Modèle :</p> <p>Couleur :</p>"
                document.getElementById(className).style = "border-style: solid; border-radius: 15px; margin-top: 15px; margin-bottom: 15px; padding-top: 5px; padding-bottom: 5px; padding-left: 5px;";
            }
            else{
                document.getElementById(className).innerHTML = "";
                document.getElementById(className).style = "border: none;";
            }
        }
        function voirCarte(positionBgn, positionEnd){
            //Enlever chaque marker déjà présent
            for (i in arrayMarkers) {
                arrayMarkers[i].remove();
            }

            //Départ
            var begin = L.marker(positionBgn, {icon: orangeIcon});
            //Arrivée
            var end = L.marker(positionEnd, {icon: blueIcon});

            //Ajout des marqueurs sur la carte
            begin.addTo(map).bindPopup("Départ");
            end.addTo(map).bindPopup("Arrivée");

            arrayMarkers=[begin,end];
        }
    </script>
</body>