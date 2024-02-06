<?php
    include '../db_cnx.php';

    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        // Check if the 'id' parameter is set in the GET request
        
            $userId = $_GET['id'];
            
            $stmt = $pdo->prepare("SELECT * FROM candidat WHERE Id_candidat = ?");
            $stmt->execute([$userId]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);

            // Return user data as JSON response
            echo json_encode($userData);
       
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $userId = $_POST['userId'];
        $nom = $_POST['nom']; // Assuming these are the form field names
        $mail = $_POST['email']; // Assuming these are the form field names
        $prenom = $_POST['prenom']; // Assuming these are the form field names
        $adresse = $_POST['adresse']; // Assuming these are the form field names
        $tel = $_POST['tele'];
        // Update candidate data in the database
     
        $stmt = $pdo->prepare("UPDATE candidat SET nom=:nom , mail =:mail,prenom =:prenom, adresse=:adresse, no_tele=:no_tele WHERE id_candidat = :id_candidat");
        $stmt->bindParam(':nom', $nom, PDO::PARAM_STR);
        $stmt->bindParam(':mail', $mail, PDO::PARAM_STR);
        $stmt->bindParam(':prenom', $prenom, PDO::PARAM_STR);
        $stmt->bindParam(':adresse', $adresse, PDO::PARAM_STR);
        $stmt->bindParam(':id_candidat', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':no_tele', $tel, PDO::PARAM_STR);
        $stmt->execute();
        header("Location: ../candidat_list.php");
         exit();
    }
    

        $pdo = null;
?>
