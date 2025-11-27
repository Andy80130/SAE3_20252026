<?php
// 1. Bouton "Trajets Organisés"
$trajets_organises = [
    [
        'nom' => 'Alexandre Trubin',
        'avatar' => 'https://cyberschool.univ-rennes.fr/app/uploads/2024/05/etudiant-double-diplomation-cyberschool-1024x699.jpg',
        'nb' => 1,
        'depart' => "IUT d'Amiens",
        'arrivee' => "Rue 3 Cailloux",
        'date' => "18:10 à 18:30, Vendredi 17 Octobre",
        'participants' => [] // Vide
    ],
    [
        'nom' => 'Alexandre Trubin',
        'avatar' => 'https://cyberschool.univ-rennes.fr/app/uploads/2024/05/etudiant-double-diplomation-cyberschool-1024x699.jpg',
        'nb' => 3,
        'depart' => "IUT d'Amiens",
        'arrivee' => "Rue 3 Cailloux",
        'date' => "18:10 à 18:30, Lundi 20 Octobre",
        'participants' => ['Florian Diego', 'Maxime Cahier', 'Charlotte Bigard'] // Liste remplie
    ]
];

// 2. Bouton "Trajets Réservés"
$trajets_reserves = [
    [
        'nom' => 'Rodrigue Malibo', 
        'avatar' => 'https://i.pravatar.cc/150?u=rodrigue', // Image unique
        'nb' => 1, 
        'depart' => "IUT d'Amiens", 
        'arrivee' => "24 Rue Paul Langevin", 
        'date' => "14:15, Ven 14 Nov"
    ],
    [
        'nom' => 'Jamy Dufeuille', 
        'avatar' => 'https://i.pravatar.cc/150?u=jamy', // Image unique
        'nb' => 2, 
        'depart' => "IUT d'Amiens", 
        'arrivee' => "24 Rue Paul Langevin", 
        'date' => "18:10, Jeu 13 Nov"
    ]
];

// 3. Demandes en Attente (IMAGES AJOUTÉES ICI)
$demandes_attente = [
    [
        'nom' => 'Sophian Kalaris', 
        'avatar' => 'https://storage.letudiant.fr/mediatheque/letudiant/8/3/2971583-tests-et-certifications-pour-etudier-en-france-en-tant-que-marocaine-632x421.jpeg',
        'date' => '7:30, Ven 17 Oct'
    ],
    [
        'nom' => 'Brunot Marcie', 
        'avatar' => 'https://www.leparisien.fr/resizer/MVt_dTt7_E4tIUmyWfhYx3kEcnk=/932x582/cloudfront-eu-central-1.images.arcpublishing.com/leparisien/7LQOT63RHRAJZFDUEJAUWDRED4.jpg',
        'date' => '7:20, Ven 17 Oct'
    ],
    [
        'nom' => 'Frédérique Carlotin', 
        'avatar' => 'https://lh5.googleusercontent.com/InCRmf2nKCjSHVPh7rwtf-y_KK_PHk90CWUenmaGIhu9T7F8gUEOEc-R9rJn5hMgsvCeMBIn4RSXzLWdHRjYiqYzqPMbIlmKsmtNvh9IEhYRyKeqeIkPpvUKe0R5hc30MzqeThRmt52VXUaOno0BORg',
        'date' => '18:10, Mar 4 Nov'
    ]
];

// D. Demandes Reçues (IMAGES AJOUTÉES ICI)
$demandes_recues = [
    [
        'nom' => 'Mathis Arnaud', 
        'avatar' => 'https://i.pravatar.cc/150?u=mathis', // <--- Image différente pour Mathis
        'date' => '18:10, Ven 17 Oct'
    ],
    [
        'nom' => 'Corentin Delasalle', 
        'avatar' => 'https://www.gerinter.fr/wp-content/uploads/2023/11/etudiant_interim_gerinter_0.png', // <--- Image différente pour Corentin
        'date' => '18:10, Ven 17 Oct'
    ]
];

function afficherCarte($data, $typeBouton = 'standard') {
    // Si l'avatar est défini dans le tableau, on l'utilise. Sinon, image par défaut.
    $avatar = $data['avatar'] ?? 'https://i.pravatar.cc/150'; 
    $depart = $data['depart'] ?? "Gare du Nord"; 
    $arrivee = $data['arrivee'] ?? "IUT d'Amiens";
    
    echo '<article class="card">';
    echo '  <div class="card-header">';
    echo '    <div class="organizer">';
    echo '      <img src="'.$avatar.'" class="avatar" alt="Photo de '.$data['nom'].'" />';
    echo '      <div class="organizer-info">';
    echo '        <h3>'.$data['nom'].'</h3>';
    if(isset($data['nb'])) echo '<span class="participants-count">Inscrits : '.$data['nb'].'</span>';
    echo '      </div>';
    echo '    </div>';
    echo '  </div>';
    
    echo '  <div class="trip-details">';
    echo '    <p><strong>Départ :</strong> '.$depart.'</p>';
    echo '    <p><strong>Arrivée :</strong> '.$arrivee.'</p>';
    echo '    <p><strong>Date :</strong> '.$data['date'].'</p>';
    echo '  </div>';

    echo '  <div class="card-actions">';
    if ($typeBouton == 'validation') {
        echo '    <button class="btn btn-accept">Accepter</button>';
        echo '    <button class="btn btn-refuse">Refuser</button>';
    } else {
        echo '    <button class="btn btn-outline">Annuler</button>';
        echo '    <button class="btn btn-outline">Infos</button>';
        
        // Bouton participant (plein si la liste existe, sinon vide)
        $style = !empty($data['participants']) ? 'btn-filled' : 'btn-outline';
        echo '    <button class="btn '.$style.' toggle-btn">Participants</button>';
    }
    echo '  </div>';
    
    // Affichage liste participants si elle existe
    if (!empty($data['participants'])) {
        echo '<div class="participants-list" style="display:none;">';
        foreach($data['participants'] as $p) {
            echo '<div class="participant"><i class="fas fa-user-circle"></i> <span>'.$p.'</span></div>';
        }
        echo '</div>';
    }
    echo '</article>';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudyGo - Mes Trajets</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <link rel="stylesheet" href="../css/styleReservation.css" />
    <style>
        .section-contenu { display: none; } /* Tout caché par défaut */
        .section-contenu.active { display: block; } /* Sauf si active */
        .sous-menu { display: none; }
        .sous-menu.visible { display: flex; }
    </style>
</head>
<body>
    <?php require("../includes/header.php") ?>

    <main>
        <h1 class="page-title">Mes trajets et reservations</h1>

        <div class="tabs-primary">
            <button class="tab active" onclick="changerOnglet('avenir', this)">Trajets à venir</button>
            <button class="tab" onclick="changerOnglet('attente', this)">Demandes en attente</button>
            <button class="tab" onclick="changerOnglet('recues', this)">Demande reçues</button>
        </div>

        <div id="sous-menu-container" class="tabs-secondary sous-menu visible">
            <button class="tab active" onclick="changerSousOnglet('organises', this)">Mes trajets organisés</button>
            <button class="tab" onclick="changerSousOnglet('reserves', this)">Mes trajets réservés</button>
        </div>

        <div id="bloc-organises" class="section-contenu active">
            <h2 class="section-title">Nombre de trajets : <?php echo count($trajets_organises); ?></h2>
            <?php foreach($trajets_organises as $trajet) { afficherCarte($trajet); } ?>
             
             <div class="illustration-container">
                 <img src="https://cdni.iconscout.com/illustration/premium/thumb/carpooling-service-app-illustration-download-in-svg-png-gif-file-formats--online-booking-sharing-share-ride-taxi-pack-vehicle-illustrations-4609653.png?f=webp" alt="Illustration Trajet" />
             </div>
        </div>

        <div id="bloc-reserves" class="section-contenu">
            <h2 class="section-title">Nombre de trajets : <?php echo count($trajets_reserves); ?></h2>
            <?php foreach($trajets_reserves as $trajet) { afficherCarte($trajet); } ?>
            </div>

        <div id="bloc-attente" class="section-contenu">
            <h2 class="section-title">Nombre de demandes en attente : <?php echo count($demandes_attente); ?></h2>
            <?php foreach($demandes_attente as $demande) { afficherCarte($demande); } ?>
             
             <div class="illustration-container">
                <img src="https://cdni.iconscout.com/illustration/premium/thumb/checklist-illustration-download-in-svg-png-gif-file-formats--task-list-clipboard-business-miscellaneous-pack-illustrations-3025686.png" alt="Illustration Attente" style="max-width:200px"/>
            </div>
        </div>

        <div id="bloc-recues" class="section-contenu">
            <h2 class="section-title">Nombre de demandes reçues : <?php echo count($demandes_recues); ?></h2>
            <?php 
                // On précise 'validation' pour avoir les boutons vert/rouge
                foreach($demandes_recues as $demande) { afficherCarte($demande, 'validation'); } 
            ?>
            
            <div class="illustration-container">
                <img src="https://cdni.iconscout.com/illustration/premium/thumb/email-marketing-illustration-download-in-svg-png-gif-file-formats--mail-message-envelope-newsletter-business-pack-illustrations-3698042.png" alt="Illustration Reçues" style="max-width:200px" />
            </div>
        </div>

    </main>

    <?php require("../includes/footer.php") ?>

    <script>

        function changerOnglet(choix, boutonClique) {
            // 1. Gestion de la couleur des boutons
            document.querySelectorAll('.tabs-primary .tab').forEach(btn => btn.classList.remove('active'));
            boutonClique.classList.add('active');

            // 2. Cacher tout le contenu principal
            document.querySelectorAll('.section-contenu').forEach(div => div.classList.remove('active'));

            // 3. Gestion Spéciale "Trajets à venir"
            const sousMenu = document.getElementById('sous-menu-container');
            
            if(choix === 'avenir') {
                sousMenu.classList.add('visible'); // Montrer le sous-menu
                // On active le premier sous-onglet par défaut (Organisés)
                document.getElementById('bloc-organises').classList.add('active');
                // Réinitialiser visuellement le sous-menu sur le 1er bouton
                document.querySelectorAll('.tabs-secondary .tab').forEach(b => b.classList.remove('active'));
                document.querySelector('.tabs-secondary .tab:first-child').classList.add('active');
            } 
            else if (choix === 'attente') {
                sousMenu.classList.remove('visible'); // Cacher sous-menu
                document.getElementById('bloc-attente').classList.add('active');
            }
            else if (choix === 'recues') {
                sousMenu.classList.remove('visible'); // Cacher sous-menu
                document.getElementById('bloc-recues').classList.add('active');
            }
        }

        function changerSousOnglet(choix, boutonClique) {
            // Gestion des boutons du sous-menu
            document.querySelectorAll('.tabs-secondary .tab').forEach(btn => btn.classList.remove('active'));
            boutonClique.classList.add('active');

            // Cacher les deux sous-sections
            document.getElementById('bloc-organises').classList.remove('active');
            document.getElementById('bloc-reserves').classList.remove('active');

            // Afficher la bonne
            document.getElementById('bloc-' + choix).classList.add('active');
        }

        // Script pour ouvrir/fermer la liste des participants
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.toggle-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    // Trouver la liste DANS la même carte
                    const liste = this.closest('.card').querySelector('.participants-list');
                    if(liste) {
                        // Basculer l'affichage
                        if (liste.style.display === 'none' || liste.style.display === '') {
                            liste.style.display = 'block';
                            this.classList.remove('btn-outline');
                            this.classList.add('btn-filled');
                        } else {
                            liste.style.display = 'none';
                            this.classList.add('btn-outline');
                            this.classList.remove('btn-filled');
                        }
                    } else {
                        // Optionnel : Message si pas de participants
                        // alert('Aucun participant à afficher.');
                    }
                });
            });
        });
    </script>
</body>
</html>