<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>StudyGo - Mes Trajets</title>
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    />

    <link rel="stylesheet" href="../css/styleReservation.css" />
  </head>
  <body>
  <?php require("../includes/header.php")?>

    <main>
      <h1 class="page-title">Mes trajets et reservations</h1>

      <div class="tabs-primary">
        <button class="tab active">Trajets à venir</button>
        <button class="tab">Demandes en attente</button>
        <button class="tab">Demande reçues</button>
      </div>

      <div class="tabs-secondary">
        <button class="tab active">Mes trajets organisés</button>
        <button class="tab">Mes trajets réservés</button>
      </div>

      <section class="trip-list">
        <h2 class="section-title">Nombre de trajets : 2</h2>

        <article class="card">
          <div class="card-header">
            <div class="organizer">
              <img
                src="https://cyberschool.univ-rennes.fr/app/uploads/2024/05/etudiant-double-diplomation-cyberschool-1024x699.jpg"
                alt="Alexandre"
                class="avatar"
              />
              <div class="organizer-info">
                <h3>Alexandre Trubin</h3>
                <span class="participants-count"
                  >Participants inscrits : 1</span
                >
              </div>
            </div>
          </div>
          <div class="trip-details">
            <p><strong>Départ :</strong> IUT d'Amiens</p>
            <p><strong>Arrivée :</strong> Rue 3 Cailloux</p>
            <p><strong>Date :</strong> 18:10 à 18:30, Vendredi 17 Octobre</p>
          </div>
          <div class="card-actions">
            <button class="btn btn-outline">Annuler mon trajet</button>
            <button class="btn btn-outline">Info voiture</button>
            <button class="btn btn-outline">Voir les participants</button>
          </div>
        </article>

        <article class="card">
          <div class="card-header">
            <div class="organizer">
              <img
                src="https://cyberschool.univ-rennes.fr/app/uploads/2024/05/etudiant-double-diplomation-cyberschool-1024x699.jpg"
                alt="Alexandre"
                class="avatar"
              />
              <div class="organizer-info">
                <h3>Alexandre Trubin</h3>
                <span class="participants-count"
                  >Participants inscrits : 3</span
                >
              </div>
            </div>
          </div>
          <div class="trip-details">
            <p><strong>Départ :</strong> IUT d'Amiens</p>
            <p><strong>Arrivée :</strong> Rue 3 Cailloux</p>
            <p><strong>Date :</strong> 18:10 à 18:30, Lundi 20 Octobre</p>
          </div>
          <div class="card-actions">
            <button class="btn btn-outline">Annuler mon trajet</button>
            <button class="btn btn-outline">Info voiture</button>
            <button class="btn btn-filled">Voir les participants</button>
          </div>

          <div class="participants-list">
            <div class="participant">
              <img
                src="https://i.pravatar.cc/150?u=b"
                alt="Florian"
                class="avatar-small"
              />
              <span>Florian Diego</span>
            </div>
            <div class="participant">
              <img
                src="https://i.pravatar.cc/150?u=c"
                alt="Maxime"
                class="avatar-small"
              />
              <span>Julie Cahier</span>
            </div>
            <div class="participant">
              <img
                src="https://i.pravatar.cc/150?u=d"
                alt="Charlotte"
                class="avatar-small"
              />
              <span>Maxime Bigard</span>
            </div>
          </div>
        </article>
      </section>

      <div class="illustration-container">
        <img
          src="https://cdni.iconscout.com/illustration/premium/thumb/carpooling-service-app-illustration-download-in-svg-png-gif-file-formats--online-booking-sharing-share-ride-taxi-pack-vehicle-illustrations-4609653.png?f=webp"
          alt="Illustration Trajet"
        />
      </div>
    </main>

    <?php require("../includes/footer.php")?>
  </body>
</html>
