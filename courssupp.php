<!DOCTYPE html>
<html lang="fr">
<?php
    // Include your database connection file
    include 'db_cnx.php';

    // Query to fetch data from Candidat, Code, Formule tables
    $stmt = $pdo->prepare("SELECT nom,prenom FROM formateur");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);


    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $msg = "";
        $error="";
        $email = $_POST["email"];
        $formateur  = $_POST['formateur'];
        $date = $_POST['date'];
        $heure = $_POST['heure'];

        if ( !$email ) {
            $msg = "inserer l'email !!";
        } else {
            try {
                $stmt = $pdo->prepare("SELECT * FROM candidat WHERE mail = :email ");
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->execute();
                $candidat = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($candidat) {
                    $candidatId=$candidat['id_candidat'];
                    echo $candidatId;
                    $stmt = $pdo->prepare("SELECT  cc.n_demande, t.ID,  v.immatriculation , cd.nb_heures
                    FROM candidat c
                    INNER JOIN c_conduit cc ON c.Id_candidat = cc.Id_candidat
                    INNER JOIN conduit cd ON cc.Id_conduit = cd.Id_conduit
                    INNER JOIN type_permis t ON cd.ID = t.ID
                    INNER JOIN vehicule v ON t.ID = v.ID  WHERE c.Id_candidat =  :id_candidat; ");
                    $stmt->bindParam(':id_candidat', $candidatId, PDO::PARAM_INT);
                    $stmt->execute();
                    $data = $stmt->fetch(PDO::FETCH_ASSOC);
                    $n_demande = $data['n_demande'];
                    $ID = $data['id'];
                    $immatriculation = $data['immatriculation'];
                    $nb_heures = $data['nb_heures'];
                    $currentDate = date("m/d/Y");

                    $currentTimestamp = strtotime($currentDate);
                    $dateTimestamp = strtotime($date);

                    // Calculate the minimum date allowed (current date + 4 days)
                    $minAllowedTimestamp = strtotime('+4 days', $currentTimestamp);

                    if ($dateTimestamp < $minAllowedTimestamp) {
                       
                        $msg="The selected date must be at least 4 days ahead of the current date.";
                    } 
                    $date_lim = date('Y-m-d', strtotime($date . ' - 3 days'));

                    $parts = explode(" ", $formateur);
                    $nom = $parts[0]; // "nom"
                    $prenom = $parts[1]; // "prenom"
                    //select the num_ss for the chossen  formateur
                    $stmt = $pdo->prepare("select num_ss from formateur where nom = :nom and prenom= :prenom; ");
                    $stmt->bindParam(':nom', $nom, PDO::PARAM_STR);
                    $stmt->bindParam(':prenom', $prenom, PDO::PARAM_STR);
                    $stmt->execute();
                    $num_ss = $stmt->fetchColumn();


                    // reservation d'heure de conduite initial
                    if ($nb_heures > 0) {
                        $stmt = $pdo->prepare("SELECT heure_reserve, date_plan, num_ss FROM reservation");
                        $stmt->execute();
                        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        $found = false;
                        foreach ($data as $row) {
                            $timerfromData = DateTime::createFromFormat('H:i:s',$row['heure_reserve'] );
                            $timefromInpute = DateTime::createFromFormat('H:i:s', $heure);
                            $datefromData = new DateTime($row['date_plan']);
                            $datefromInput = new DateTime($date);
                            if ( $timerfromData== $timefromInpute && $datefromData ==$datefromInput && $row['num_ss'] == $num_ss) {
                                $found = true;
                                break;
                            }
                        }
                        
                        if ($found) {
                            $error=  "cette heure est déja reserevée !!!! ";
                        } else {
                            $stmt = $pdo->prepare("INSERT INTO Reservation (Date_res, Statut, Date_valid, Date_lim, Date_plan, Id_cours_achete, N_demande, Immatriculation, Num_ss,heure_reserve)
                                                VALUES (:date_res, 'reserved', :date_valid, :date_lim, :date_plan, null, :n_demande, :immatriculation, :formateur,:heure)");
                            $stmt->bindParam(':date_res', $currentDate, PDO::PARAM_STR);
                            $stmt->bindParam(':date_valid', $date_valid, PDO::PARAM_STR);
                            $stmt->bindParam(':date_lim', $date_lim, PDO::PARAM_STR);
                            $stmt->bindParam(':date_plan', $date, PDO::PARAM_STR); 
                            $stmt->bindParam(':n_demande', $n_demande, PDO::PARAM_INT); 
                            $stmt->bindParam(':immatriculation', $immatriculation, PDO::PARAM_STR); 
                            $stmt->bindParam(':formateur', $num_ss, PDO::PARAM_INT); 
                            $stmt->bindParam(':heure', $heure, PDO::PARAM_STR);
                            $stmt->execute();

                            //-1h in the totale of heures
                            $stmt = $pdo->prepare("UPDATE Conduit c
                                                    SET nb_heures = :new_value
                                                    FROM C_conduit cc
                                                    JOIN Candidat ca ON cc.Id_candidat = ca.Id_candidat
                                                    WHERE c.Id_conduit = cc.Id_conduit
                                                    AND ca.Id_candidat = :id_candidat;");
                            $new_value = $nb_heures - 1;
                            $stmt->execute([':new_value' => $new_value, ':id_candidat' => $candidatId]);

                            $msg ="votre reservation est prise en compte";
                        }
                       
                        
                        
                        // reservation de cours supplimentaire
                    }else{
                        $stmt = $pdo->prepare("select heure_reserve , date_plan , num_ss from reservation; ");
                        $stmt->execute();
                        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        $found = false;
                        foreach ($data as $row) {
                            if ($row['num_ss'] == $num_ss && $row['heure_reserve'] == $heure && $row['date_plan'] == $date) {
                                $found = true;
                                break;
                            }
                        }
                        
                        if ($found) {
                            $error=  "cette heure est déja reserevée !!!! ";
                        } else {
                                // Check if the record exists
                                $stmt = $pdo->prepare("SELECT COUNT(*) FROM acheter_cours WHERE id_candidat = :id_candidat");
                                $stmt->bindParam(':id_candidat', $candidatId, PDO::PARAM_INT);
                                $stmt->execute();
                                $rowCount = $stmt->fetchColumn();

                                if ($rowCount == 0) {
                                    // If the record does not exist, insert a new record with nb_heures set to 1
                                    $stmt = $pdo->prepare("INSERT INTO acheter_cours (nombre_h_achete, id_candidat, id) VALUES (1, :id_candidat, :id)");
                                    $stmt->bindParam(':id_candidat', $candidatId, PDO::PARAM_INT);
                                    $stmt->bindParam(':id', $ID, PDO::PARAM_STR);
                                    $stmt->execute();
                                    $id_cours = $pdo->lastInsertId();
                                } else {
                                    $stmt = $pdo->prepare("SELECT id_cours_achete, nombre_h_achete FROM acheter_cours WHERE id_candidat = :id_candidat");
                                    $stmt->bindParam(':id_candidat', $candidatId, PDO::PARAM_INT);
                                    $stmt->execute();
                                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                    $id_cours = $row['id_cours_achete'];
                                    $current_nb_heures = $row['nombre_h_achete'];
                                    $new_nb_heures = $current_nb_heures + 1;

                                    $stmt = $pdo->prepare("UPDATE acheter_cours SET nombre_h_achete = :new_nb_heures WHERE id_candidat = :id_candidat");
                                    $stmt->bindParam(':new_nb_heures', $new_nb_heures, PDO::PARAM_INT);
                                    $stmt->bindParam(':id_candidat', $candidatId, PDO::PARAM_INT);
                                    $stmt->execute();
                                }
                                $stmt = $pdo->prepare("INSERT INTO Reservation (Date_res, Statut, Date_valid, Date_lim, Date_plan, Id_cours_achete, N_demande, Immatriculation, Num_ss,heure_reserve)
                                                        VALUES (:date_res, 'reserved', :date_valid, :date_lim, :date_plan, :id_cours, null, :immatriculation, :formateur,:heure)");
                                $stmt->bindParam(':date_res', $currentDate, PDO::PARAM_STR);
                                $stmt->bindParam(':date_valid', $date_valid, PDO::PARAM_STR);
                                $stmt->bindParam(':date_lim', $date_lim, PDO::PARAM_STR);
                                $stmt->bindParam(':date_plan', $date, PDO::PARAM_STR); 
                                $stmt->bindParam('::id_cours', $id_cours, PDO::PARAM_STR);
                                $stmt->bindParam(':immatriculation', $immatriculation, PDO::PARAM_STR); 
                                $stmt->bindParam(':formateur', $num_ss, PDO::PARAM_INT); 
                                $stmt->bindParam(':heure', $heure, PDO::PARAM_STR);
                                $stmt->execute();

                                $msg = "cette heure doit etre payer !!!!";
                                }
                     }
                }else{
                    $error="vous  n'etes pas autorisé à réserver pour ce candidat.";
                }

            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage();
            }
        }
    }
            // Close the PDO connection
            $pdo = null;
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cours Supplémentaires - Auto École</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/courssupp.css">
    <style>

        .msg{
            color:green;
            font-size: 18px;
            font-weight: 500
            margin:10px;
            padding: 5px;
        }
        .error{
            color:red;
            font-size: 18px;
            font-weight: 500
            margin:10px;
            padding: 5px;
        }
    
    </style>
    <script>
        function validateTime() {
            var inputTime = document.getElementById("heure").value;
            var hour = parseInt(inputTime.split(":")[0]);

            // Check if the input time is within the allowed ranges
            if ((hour >= 8 && hour < 12) || (hour >= 14 && hour < 18)) {
                return true; // Input time is valid
            } else {
                alert("Invalid time. Please select a time between 08:00-12:00 or 14:00-18:00.");
                return false; // Input time is invalid
            }
        }
</script>
</head>

<body>

    <div class="dashboard-container">
        
        <div class="main-content">
            
            <form action="courssupp.php" method="post" onsubmit="return validateTime()">
                
                <div class="form-section">
                    <h1>Réservation :</h1>
                    <h2>Informations personnelles du candidat :</h2>
                    
                    
                    <div class="form-group">
                        <label for="email">Email :</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                   
                </div>

                
                <!-- Cours supplémentaires souhaités -->
                <div class="form-section">
                    <h2>Choix de réservation :</h2>
                
                    <div class="form-group">
                        <label for="date_heure">Date(s) et heure(s) préférée(s) :</label>
                        <input type="date" id="date" name="date">
                        <input type="time" id="heure" name="heure">                 
                    </div>
                </div>

                <!-- Préférences supplémentaires -->
                <div class="form-section">
                    <h2>Préférences supplémentaires :</h2>
                        <div class="form-group">
                             <label for="formateur">Formateur préféré :</label>
                                <select name="formateur"> 
                                <option value="" disabled selected></option><!-- Ajout du nom pour récupérer la valeur sélectionnée -->
                                <?php foreach ($result as $row): ?>
                                 <option value="<?php echo $row['nom'] . ' ' . $row['prenom']; ?>">
                                    <?php echo $row['nom'] . ' ' . $row['prenom']; ?>
                                </option>
                                <?php endforeach; ?>
                                </select>
                        </div>

                        <?php
                        if ($pdo) {
                            $pdo = null;
                        } ?>
                    
                    
                </div>

                <!-- Informations complémentaires ou commentaires -->
                <div class="form-section">
                   
                <button type="submit" class="submit-btn"> Soumettre</button>
                <?php if(isset($msg) && !empty($msg)): ?>
                        <p id="successMessage" class="msg"><?php echo $msg; ?></p>
                        <script>
                            setTimeout(function() {
                                var successMessage = document.getElementById("successMessage");
                                if (successMessage) {
                                    successMessage.remove();
                                }
                            }, 5000); // 5 seconds in milliseconds
                        </script>
                    <?php endif; ?>

                    <?php if(isset($error) && !empty($error)): ?>
                        <p id="errorMessage" class="error"><?php echo $error; ?></p>
                        <script>
                            setTimeout(function() {
                                var errorMessage = document.getElementById("errorMessage");
                                if (errorMessage) {
                                    errorMessage.remove();
                                }
                            }, 5000); // 5 seconds in milliseconds
                        </script>
                    <?php endif; ?>
                </div>

                
            </form>
            
        </div>
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Tableau de Bord</h2>
            </div>
            <div class="sidebar-content">
                <button onclick="navigateTo('code.php')" ><i class="fas fa-key"></i> Code</button>
                <button onclick="navigateTo('conduite.php')"><i class="fas fa-car"></i> Conduite</button>
                <button onclick="navigateTo('courssupp.php')" class="click" ><i class="fas fa-book"></i> Réservation ( cours supp / heure conduite)</button>
                <button onclick="navigateTo('validation.php')"><i class="fas fa-check-circle"></i> Validation des Rendez-vous</button>
                <button onclick="navigateTo('candidat_list.php')"><i class="fas fa-users"></i> Liste des Candidats</button>
                <button onclick="navigateTo('index.php')"><i class="fas fa-arrow-left"></i> Accueil</button>
            </div>
        </div>
    </div>

    <script src="javascript/accueil.js"></script>
</body>

</html>
