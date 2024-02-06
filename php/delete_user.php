<?php

include '../db_cnx.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the user ID from the AJAX request
    $userId = $_POST['userId'];
    echo $userId;

    $stmt = $pdo->prepare("select n_demande from c_conduit where id_candidat= :userId");
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $n_demande = $stmt->fetchColumn();

    $stmt = $pdo->prepare("select id_code from achete where id_candidat= :userId");
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $id_code = $stmt->fetchColumn();

    $stmt = $pdo->prepare("select id_formule from code where id_code= :id_code");
    $stmt->bindParam(':id_code', $id_code, PDO::PARAM_INT);
    $stmt->execute();
    $id_formule= $stmt->fetchColumn();

    $stmt = $pdo->prepare("DELETE FROM reservation WHERE n_demande = :n_demande");
    $stmt->bindParam(':n_demande', $n_demande, PDO::PARAM_INT);
    $stmt->execute();

    

    

    $stmt = $pdo->prepare("DELETE FROM c_conduit WHERE id_candidat = :userId");
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $stmt = $pdo->prepare("DELETE FROM achete WHERE id_candidat = :userId");
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();
    // Delete the user from the database
    $stmt = $pdo->prepare("DELETE FROM candidat WHERE id_candidat = :userId");
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();

    $stmt = $pdo->prepare("DELETE FROM code WHERE id_formule = :id_formule");
    $stmt->bindParam(':id_formule', $id_formule, PDO::PARAM_INT);
    $stmt->execute();

    $stmt = $pdo->prepare("DELETE FROM conduit WHERE id_formule = :id_formule");
    $stmt->bindParam(':id_formule', $id_formule, PDO::PARAM_INT);
    $stmt->execute();
    
    $stmt = $pdo->prepare("DELETE FROM formule WHERE id_formule = :id_formule");
    $stmt->bindParam(':id_formule', $id_formule, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->execute()) {
        echo "seccsefly ";
    } else {
        // Error occurred, log or output the error message
        echo "Error: " . $stmt->errorInfo()[2];
    }
    // Close the PDO connection
    $pdo = null;
}
?>
