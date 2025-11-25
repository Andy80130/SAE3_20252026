<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudyGo - Profil</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css" />
    <link rel="stylesheet" href="../css/styleAccueil.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
</head>
<body>
    <?php require("../includes/header.php") ?>

    <h1 class="Titre">Profil</h1>
    <div class="profile-container">

    <!-- Partie haute : photo + nom + icône notification -->
    <div class="profile-header">
        <img src="../images/Icone_Profil.png" alt="Photo de profil" class="profile-photo">

        <div class="profile-name">
            Alexandre Trufin
        </div>
        <div class="profile-alert">
            <button class="alert-btn" aria-label="Information">❗ Signaler</button>
        </div>
    </div>
    <section class="vehicle-info">
    <h2>Informations sur le véhicule</h2>

    <form method="post" action="saveVehicle.php">

        <div class="input-group">
            <label for="annee">Modèle</label>
            <input type="number" id="annee" name="annee" value="">
        </div>

        <div class="input-group">
            <label for="annee">Couleur</label>
            <input type="number" id="annee" name="annee" value="">
        </div>

        <button type="submit" class="save-btn">Enregistrer</button>
    </form>
</section>

</div>

    <?php require("../includes/footer.php") ?>

</body>
</html>