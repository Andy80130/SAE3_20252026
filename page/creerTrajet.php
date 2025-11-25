<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudyGo - Covoiturage étudiant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css" />
    <link rel="stylesheet" href="../css/styleCreerTrajet.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
</head>

<body>
    <!-- Header -->
    <?php require ('../includes/header.php'); ?>

    <h1 class="TitreCreer" style="text-align:center; margin:30px auto; width:100%;">
        Creer un trajet
    </h1>

    <!-- Hero Section -->
    <section class="hero">

        <div class="title">Itinéraire</div>

        <div class="card">
            <form class="itinerary" method="post" action="">
                <div class="field">
                    <div class="labelOrange">Départ</div>
                    <select name="depart">
                        <?php global $locations, $depart;
                        foreach ($locations as $key => $label): ?>
                        <option value="<?= htmlspecialchars($key) ?>"
                        <?= $depart === $key ? 'selected' : '' ?>>
                        <?= htmlspecialchars($label) ?>
                    </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="swap">
                    <button type="submit" name="action" value="swap" title="Inverser"> ⇄ </button>
                </div>

                <div class="field">
                    <div class="labelOrange">Destination</div>
                    <select name="destination">
                        <?php global $locations, $destination;
                        foreach ($locations as $key => $label): ?>
                        <option value="<?= htmlspecialchars($key) ?>"
                        <?= $destination === $key ? 'selected' : '' ?>>
                        <?= htmlspecialchars($label) ?>
                    </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>
    </section>

    <div class="map-container">
        <div id="map"></div>
    </div>
    <script>
        // Initialize the map
        var map = L.map('map').setView([49.8942, 2.2957], 14);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

    </script>

    <section class="hero">
        <div class="title">Information sur le trajet</div>

        <div class="card">

            <form class="trip-form">

                <div class="form-group">
                    <label for="date" class="form-label">
                        <span class="icon"></span> Date du trajet
                    </label>
                    <input type="date" id="date" name="date" class="form-input">
                </div>


                <div class="form-group time-range">
                    <label class="form-label">
                        <span class="icon"></span> Départ : de
                    </label>
                    <input type="time" name="start" class="form-input time-input">
                    <span class="time-separator">à</span>
                    <input type="time" name="end" class="form-input time-input">
                </div>


                <div class="form-group">
                    <label for="places" class="form-label">
                        <span class="icon"></span> Nombre de places dispo.
                    </label>
                    <select id="places" name="places" class="form-input">
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4+</option>
                    </select>
                </div>
            </form>
        </div>

    </section>




    <div class="infosCertif">
        <div class="petittitle">Je certifie que les informations ci-dessus sont exactes ‎ ‎ → </div>
        <label class="custom-checkbox">
            <input type="checkbox">
            <span class="checkmark"></span>
        </label>
    </div>
    <div class="actions">
        <button class="secondary" type="reset">Réinitialiser</button>
    </div>




    <section class="hero">
        <button class="primary"  style="text-align:center; margin:30px auto; width:50%;" 
                type="submit" name="action" value="search">Creer le trajet</button>
    </section>


    <div class="full-image">
    <img src="https://img.freepik.com/vecteurs-libre/illustration-concept-abstrait-stylo-numerique_335657-2281.jpg">
    </div>

     <?php require("../includes/footer.php")?>

</body>
</html>



