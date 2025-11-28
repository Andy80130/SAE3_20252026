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
            echo "Erreur lors de la s�lection : " . $e->getMessage();
            return [];
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
			echo "Veuillez utilisez votre mail UPJV";
			return false;
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
            echo "Vous avez déjà un compte";
            return false;
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
            echo "Erreur lors de la selection de l'utilisateur : " . $e->getMessage();
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
            echo "Erreur lors de la v�rification dans la blacklist : " . $e->getMessage();
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

            //Suppression des R�servations
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
        $stmt = $db->prepare("SELECT * FROM Journeys WHERE driver_id = :uid ORDER BY start_date ASC");
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
        $sql = "SELECT U.first_name, U.last_name 
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
        $sql = "SELECT J.*, U.first_name, U.last_name 
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
            echo "Erreur lors de l'insertion de la r�servation : " . $e->getMessage();
            return false;
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
            echo "Erreur lors de l'annulation de la r�servation : " . $e->getMessage();
            return false;
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
            echo "Erreur lors de la s�lection des r�servations : " . $e->getMessage();
            return [];
        }
    }

    function RemainingSeats(int $journey_id): int {
        global $db;
        try {
            // R�cup�rer le nombre total de places pour le trajet
            $stmt_total = $db->prepare("SELECT number_place FROM Journeys WHERE journey_id = :journey_id");
            $stmt_total->bindParam(':journey_id', $journey_id, PDO::PARAM_INT);
            $stmt_total->execute();
            $total_places = $stmt_total->fetchColumn();

            // R�cup�rer le nombre de r�servations pour le trajet
            $stmt_reserved = $db->prepare("SELECT COUNT(*) FROM Reservation WHERE journey_id = :journey_id");
            $stmt_reserved->bindParam(':journey_id', $journey_id, PDO::PARAM_INT);
            $stmt_reserved->execute();
            $reserved_places = $stmt_reserved->fetchColumn();

            // Calculer les places restantes
            return max(0, $total_places - $reserved_places);
        } 
        catch (PDOException $e) {
            echo "Erreur lors du calcul des places restantes : " . $e->getMessage();
            return 0;
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
            echo "Erreur lors de l'insertion de la note : " . $e->getMessage();
            return false;
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
            echo "Erreur lors du calcul de la note moyenne : " . $e->getMessage();
            return 0.0;
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
            echo "Erreur lors de la s�lection des notes : " . $e->getMessage();
            return [];
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
?>