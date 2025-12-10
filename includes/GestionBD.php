<?php
	require('pdoSAE3.php');

    function GlobalSelect(string $table){
        global $db;
        if (!in_array($table, ['Users', 'Journeys', 'Notes','Reports','BlackList','Reservations'])) {
            throw new Exception("Table non autoris�e.");
        }
        $stmt = $db->prepare("SELECT * FROM $table");
        try{
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch (PDOException $e){
            throw new Exception("Erreur lors de la sélection");
        }
    }

    //Users
	function AddUser(string $lastName, string $firstName, string $mail, string $phoneNumber, string $hashed_password): bool {
        global $db;
		$stmt = $db->prepare("INSERT INTO Users(last_name,first_name,mail,phone_number,password) 
                                VALUES (:last_name,:first_name,:mail,:phone_number,:hashed_password)");
		try{
			$stmt->bindParam(':last_name', $lastName);
			$stmt->bindParam(':first_name', $firstName);
			$stmt->bindParam(':mail', $mail);
			$stmt->bindParam(':phone_number', $phoneNumber);
			$stmt->bindParam(':hashed_password', $hashed_password);

			return $stmt->execute();
		}
		catch (PDOException $e){
			throw new Exception("Veuillez utilisez votre mail UPJV");
		}
	}

	function UpdateVehicleInfo(int $userId, string $model, string $color): bool {
        global $db;
		$stmt = $db->prepare("UPDATE Users SET vehicle_model = :model, vehicle_color = :color WHERE user_id = :user_id");
		try {
        
			$stmt->bindParam(':model', $model);
			$stmt->bindParam(':color', $color);
			$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        
			return $stmt->execute();
        
		} 
		catch (PDOException $e) {
		echo "Erreur lors de l'update : " . $e->getMessage();
        return false;
		}
	}

	function deleteUser(int $userId): bool {
        global $db;
        try {
            $db->beginTransaction(); 

            //Suppression des R�servations
            $stmt_reservation = $db->prepare("DELETE FROM Reservation WHERE user_id = :user_id");
            $stmt_reservation->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt_reservation->execute();

            //Suppression des Notes
            $stmt_notes_author = $db->prepare("DELETE FROM Notes WHERE author_note = :user_id OR affected_user = :user_id;");
            $stmt_notes_author->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt_notes_author->execute();

            //Suppression des Signalements
            $stmt_reports_reporter = $db->prepare("DELETE FROM Reports WHERE reporter_id = :user_id OR user_reported = :user_id;");
            $stmt_reports_reporter->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt_reports_reporter->execute();

            //Suppression des Trajets
            $stmt_journeys = $db->prepare("DELETE FROM Journeys WHERE driver_id = :user_id");
            $stmt_journeys->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt_journeys->execute();

            //Suppression de l'utilisateur
            $sql_user = "DELETE FROM Users WHERE user_id = :user_id";
            $stmt_user = $db->prepare($sql_user);
            $stmt_user->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt_user->execute();
        
            $db->commit();
            return true;

        } 
        catch (PDOException $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            echo "Erreur critique lors de la suppression de l'utilisateur $userId : " . $e->getMessage();
            return false;
        }
    }

    function MailExist(string $mail): bool {
        global $db;
        $stmt = $db->prepare("SELECT COUNT(*) FROM Users WHERE mail = :mail");
        try{
            $stmt->bindParam(':mail', $mail);
            $stmt->execute();
            $count = $stmt->fetchColumn();
            return $count > 0;
        }
        catch (PDOException $e){
            throw new Exception("Vous avez déjà un compte");
        }
    }

function TrajetExist(int $user_id): bool {
    global $db;
    $stmt = $db->prepare("SELECT COUNT(*) FROM Journeys WHERE driver_id = :user_id");
    try{
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $count = $stmt->fetchColumn();
        return $count > 0;
    }
    catch (PDOException $e){
        throw new Exception("Vous ne pouvez pas changer de véhicule
                 en ayant créé un trajet avec celui-ci !");
    }
}

    function GetUserInfo(string $mail){
        global $db;
        $stmt = $db->prepare("SELECT * FROM Users WHERE mail = :mail");
        try{
            $stmt->bindParam(':mail', $mail);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        catch (PDOException $e){
            throw new Exception("Erreur lors de la récupération des données utilisateur");
        }
    }

    function GetUserInfoById(int $id){
        global $db;
        $stmt = $db->prepare("SELECT * FROM Users WHERE user_id = :id");
        try{
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        catch (PDOException $e){
            return null;
        }
    }

    //BlackList
    function AddMailBL(string $mail,string $reason,string $date): bool {
        global $db;
        $stmt = $db->prepare("INSERT INTO BlackList(mail,reason,ban_date) 
                                VALUES (:mail,:reason,:date)");
        try{
            $stmt->bindParam(':mail', $mail);
            $stmt->bindParam(':reason', $reason);
            $stmt->bindParam(':date', $date);
            return $stmt->execute();
        }
        catch (PDOException $e){
            echo "Erreur lors de l'insertion dans la blacklist : " . $e->getMessage();
            return false;
        }
    }

    function IsMailBL(string $mail): bool {
        global $db;
        $stmt = $db->prepare("SELECT COUNT(*) FROM BlackList WHERE mail = :mail");
        try{
            $stmt->bindParam(':mail', $mail);
            $stmt->execute();
            $count = $stmt->fetchColumn();
            return $count > 0;
        }
        catch (PDOException $e){
            throw new Exception("Votre compte est actuellement dans la liste noire,
             veuillez contacter le support pour d'éventuelles informations");
        }
    }

    function GetAllBlacklist() {
        global $db;
        try {
            $stmt = $db->prepare("SELECT * FROM BlackList ORDER BY ban_date DESC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    function RemoveFromBlacklist(string $mail): bool {
        global $db;
        try {
            $stmt = $db->prepare("DELETE FROM BlackList WHERE mail = :mail");
            $stmt->bindParam(':mail', $mail);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    //Journeys
    function AddJourney(string $start,string $arrival, string $date,int $nbPlace,int $driver_id): bool{
        global $db;
        $stmt = $db->prepare("INSERT INTO Journeys(start_adress,arrival_adress,start_date,number_place,driver_id) 
                                VALUES (:start_location,:arrival_location,:journey_date,:nb_place,:driver_id)");
        try{
            $stmt->bindParam(':start_location', $start);
            $stmt->bindParam(':arrival_location', $arrival);
            $stmt->bindParam(':journey_date', $date);
            $stmt->bindParam(':nb_place', $nbPlace, PDO::PARAM_INT);
            $stmt->bindParam(':driver_id', $driver_id, PDO::PARAM_INT);
            return $stmt->execute();
        }
        catch (PDOException $e){
            echo "Erreur lors de l'insertion du trajet : " . $e->getMessage();
            return false;
        }
    }

    function JourneyInfo(int $journey_id){
        global $db;
        $stmt = $db->prepare("SELECT * FROM Journeys WHERE journey_id = :journey_id");
        try{
            $stmt->bindParam(':journey_id', $journey_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        catch (PDOException $e){
            echo "Erreur lors de la s�lection du trajet : " . $e->getMessage();
            return null;
        }
    }

    function deleteJourney(int $journey_id){
        global $db;
        try{
            $db->beginTransaction();

            //Suppression des Reservations
            $stmt =$db->prepare("DELETE FROM Reservation WHERE journey_id = :journey_id");
            $stmt->bindParam(':journey_id', $journey_id, PDO::PARAM_INT);
            $stmt->execute();

            //Suppression du Trajet
            $stmt2 = $db->prepare("DELETE FROM Journeys WHERE journey_id = :journey_id");
            $stmt2->bindParam(':journey_id', $journey_id, PDO::PARAM_INT);
            $stmt2->execute();
            $db->commit();
            return true;
        }
        catch (PDOException $e){
            echo "Erreur lors de la suppression des r�servations du trajet : " . $e->getMessage();
            return false;
        }
    }

    function GetOrganizedJourneys(int $driver_id) {
        global $db;
        $stmt = $db->prepare("SELECT J.*, U.mail, U.first_name, U.last_name 
            FROM Journeys J
            JOIN Users U ON J.driver_id = U.user_id
            WHERE J.driver_id = :uid 
            ORDER BY J.start_date ASC");
        try {
            $stmt->execute([':uid' => $driver_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Erreur récupération trajets organisés : " . $e->getMessage();
            return [];
        }
    }

    function GetJourneyParticipants(int $journey_id) {
        global $db;
        $sql = "SELECT U.first_name, U.last_name , U.user_id
                FROM Reservation R 
                JOIN Users U ON R.user_id = U.user_id 
                WHERE R.journey_id = :jid";
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([':jid' => $journey_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Erreur récupération participants : " . $e->getMessage();
            return [];
        }
    }

    function GetReservedJourneysDetails(int $user_id) {
        global $db;
        // Jointure pour récupérer les infos du trajet ET le nom du conducteur
        $sql = "SELECT J.*, U.first_name, U.last_name, U.mail
                FROM Reservation R
                JOIN Journeys J ON R.journey_id = J.journey_id
                JOIN Users U ON J.driver_id = U.user_id
                WHERE R.user_id = :uid
                ORDER BY J.start_date ASC";
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([':uid' => $user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Erreur récupération trajets réservés : " . $e->getMessage();
            return [];
        }
    }

    /**
     * Recherche des trajets selon critères dynamiques
     * @param string|null $depart (optionnel) Ville de départ (recherche partielle)
     * @param string|null $destination (optionnel) Ville d'arrivée (recherche partielle)
     * @param string|null $date (optionnel) Date du trajet (format YYYY-MM-DD)
     */

function SearchJourneys(?string $depart, ?string $arrivee, ?string $datetime, ?int $userId = null) {
    global $db;

    // Valeurs par défaut
    $depart  = $depart  ?: '';
    $arrivee = $arrivee ?: '';

    // Début de la requête SQL
    $sql = "SELECT 
                J.journey_id,
                CONCAT(U.first_name, ' ', U.last_name) AS driver_name,
                J.start_adress AS depart,
                J.arrival_adress AS destination,
                J.start_date,
                J.number_place,
                U.vehicle_model,
                U.vehicle_color
            FROM Journeys J
            JOIN Users U ON J.driver_id = U.user_id
            WHERE J.start_adress LIKE :depart
              AND J.arrival_adress LIKE :arrivee
              AND J.start_date >= :datetime";

    // --- NOUVEAU CODE : FILTRES UTILISATEUR ---
    // Si un utilisateur est connecté, on applique les filtres
    if ($userId !== null) {
        // 1. Ne pas afficher les trajets où JE suis le conducteur
        $sql .= " AND J.driver_id != :uid";

        // 2. Ne pas afficher les trajets que j'ai DÉJÀ réservés
        // On vérifie que l'ID du trajet n'est pas dans la table Reservation pour cet user
        $sql .= " AND J.journey_id NOT IN (
                    SELECT journey_id FROM Reservation WHERE user_id = :uid
                  )";
    }
    // ------------------------------------------

    $sql .= " ORDER BY J.start_date ASC";

    try {
        $stmt = $db->prepare($sql);
        
        // Préparation des paramètres de base
        $params = [
            ':depart' => "%$depart%",
            ':arrivee' => "%$arrivee%",
            ':datetime' => $datetime
        ];

        // Ajout du paramètre :uid si nécessaire
        if ($userId !== null) {
            $params[':uid'] = $userId;
        }

        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Erreur lors de la recherche : " . $e->getMessage();
        return [];
    }
}


    //Reservation
    function AddReservation(int $user_id,int $journey_id):bool{
        global $db;
        $stmt = $db->prepare("INSERT INTO Reservation(user_id,journey_id) 
                                VALUES (:user_id,:journey_id)");
        try{
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':journey_id', $journey_id, PDO::PARAM_INT);
            return $stmt->execute();
        }
        catch (PDOException $e){
            throw new Exception("Erreur lors de l'insertion de la réservation");
        }
    }

    function cancelReservation(int $user_id,int $journey_id):bool{
        global $db;
        $stmt= $db->prepare("DELETE FROM Reservation WHERE user_id = :user_id AND journey_id = :journey_id");
        try{
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':journey_id', $journey_id, PDO::PARAM_INT);
            return $stmt->execute();
        }
        catch (PDOException $e){
            throw new Exception("Erreur lors de l'annulation de la réservation");
        }
    }

    function UserReservations(int $user_id){
        global $db;
        $stmt = $db->prepare("SELECT * FROM Reservation WHERE user_id = :user_id");
        try{
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch (PDOException $e){
            throw new Exception("Erreur lors de la sélection des réservations");
        }
    }

    function RemainingSeats(int $journey_id): int {
        global $db;
        try {
            $stmt_total = $db->prepare("SELECT number_place FROM Journeys WHERE journey_id = :journey_id");
            $stmt_total->bindParam(':journey_id', $journey_id, PDO::PARAM_INT);
            $stmt_total->execute();
            $total_places = $stmt_total->fetchColumn();

            $stmt_reserved = $db->prepare("SELECT COUNT(*) FROM Reservation WHERE journey_id = :journey_id");
            $stmt_reserved->bindParam(':journey_id', $journey_id, PDO::PARAM_INT);
            $stmt_reserved->execute();
            $reserved_places = $stmt_reserved->fetchColumn();

            return max(0, $total_places - $reserved_places);
        } 
        catch (PDOException $e) {
            throw new Exception("Erreur lors du calcul des places restantes");
        }
    }

    //Notes
    function AddNote(float $note,string $comment,int $author_note,int $affected_user): bool {
        global $db;
        $stmt = $db->prepare("INSERT INTO Notes(note,note_description,author_note,affected_user) 
                                VALUES (:note,:comment,:author_note,:affected_user)");
        try{
            $stmt->bindParam(':note', $note);
            $stmt->bindParam(':comment', $comment);
            $stmt->bindParam(':author_note', $author_note, PDO::PARAM_INT);
            $stmt->bindParam(':affected_user', $affected_user, PDO::PARAM_INT);
            return $stmt->execute();
        }
        catch (PDOException $e){
            throw new Exception("Erreur lors de l'insertion de la note");
        }
    }

    function AverageUserNote(int $user_id): float {
        global $db;
        $stmt = $db->prepare("SELECT AVG(note) as average_note FROM Notes WHERE affected_user = :user_id");
        try{
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['average_note'] !== null ? (float)$result['average_note'] : 0.0;
        }
        catch (PDOException $e){
            throw new Exception("Erreur lors du calcul de la note moyenne");
        }
    }

    function UserNotes(int $user_id){
        global $db;
        $stmt = $db->prepare("SELECT * FROM Notes WHERE affected_user = :user_id");
        try{
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch (PDOException $e){
            throw new Exception("Erreur lors de la sélection des notes");
        }
    }

    function DeleteUserNotes(int $userId): bool {
        global $db;
        try {
            $stmt = $db->prepare("DELETE FROM Notes WHERE author_note = :uid");
            $stmt->bindParam(':uid', $userId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    function DeleteTargetedNote(int $authorId, int $affectedUserId): bool {
        global $db;
        try {
            $stmt = $db->prepare("DELETE FROM Notes WHERE author_note = :author AND affected_user = :affected");
            $stmt->bindParam(':author', $authorId, PDO::PARAM_INT);
            $stmt->bindParam(':affected', $affectedUserId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la suppression des notes");
        }
    }

    //Reports
    function AddReport(string $reason,int $reported_user,int $reporter):bool{
        global $db;
        $stmt = $db->prepare("INSERT INTO Reports(report_cause,user_reported,reporter_id) 
                                VALUES (:reason,:reported_user,:reporter)");
        try{
            $stmt->bindParam(':reason', $reason);
            $stmt->bindParam(':reported_user', $reported_user, PDO::PARAM_INT);
            $stmt->bindParam(':reporter', $reporter, PDO::PARAM_INT);
            return $stmt->execute();
        }
        catch (PDOException $e){
            echo "Erreur lors de l'insertion du report : " . $e->getMessage();
            return false;
        }
    }

    function UserReports(int $user_id){
        global $db;
        $stmt = $db->prepare("SELECT * FROM Reports WHERE user_reported = :user_id");
        try{
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch (PDOException $e){
            echo "Erreur lors de la s�lection des reports : " . $e->getMessage();
            return [];
        }
    }
    
    function PendingReport(): array {
        global $db;
        $stmt = $db->prepare("SELECT * FROM Reports WHERE status = 0");
        try{
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch (PDOException $e){
            echo "Erreur lors de la s�lection des reports en attente : " . $e->getMessage();
            return [];
        }
    }

    function GetAllReportsWithDetails() {
        global $db;
        try {
            $stmt = $db->prepare("SELECT Rep.reporting_id, Rep.report_cause, Rep.status, Rep.user_reported, Rep.reporter_id,
                       U.first_name AS reported_firstname, U.last_name AS reported_lastname, U.mail AS reported_mail,
                       R.first_name AS reporter_firstname, R.last_name AS reporter_lastname
                FROM Reports Rep
                JOIN Users U ON Rep.user_reported = U.user_id
                JOIN Users R ON Rep.reporter_id = R.user_id
                ORDER BY Rep.user_reported ASC, Rep.reporting_id DESC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Erreur récupération signalements : " . $e->getMessage();
            return [];
        }
    }

    function UpdateReportStatus(int $reportId, int $newStatus): bool {
        global $db;
        try {
            $stmt = $db->prepare("UPDATE Reports SET status = :status WHERE reporting_id = :id");
            $stmt->bindParam(':status', $newStatus, PDO::PARAM_INT);
            $stmt->bindParam(':id', $reportId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
?>