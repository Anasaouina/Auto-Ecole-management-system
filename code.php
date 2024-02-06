<!DOCTYPE html>
<html lang="fr">
    
<?php
include 'db_cnx.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $msg = "";
    $nom = $_POST["nom"];
    $prenom = $_POST["prenom"];
    $email = $_POST["email"];
    $tele = $_POST["telephone"];
    $adresse = $_POST["adresse"];
    $birth=$_POST["nissance"];
    $start = $_POST["date_debut"];
    $end = $_POST["date_fin"] !== "" ? $_POST["date_fin"] : null;
    $formule = $_POST["formule_code"];

    if ($_POST["formule_code"] === "Code_illimite") {
        $prix = 2000;
        $code = 1;
    } else {
        $prix = 3500;
        $code = 0;
    }

    if (!$nom || !$prenom || !$email || !$tele || !$adresse) {
        $msg = "Tous les champs doivent être remplis !!";
    } else {
        try {
            $sql = "INSERT INTO candidat (nom, mail, prenom, adresse, no_tele,date_naissance) VALUES (?, ?, ?, ?, ?,?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nom, $email, $prenom, $adresse, $tele,$birth]);
            $candidatId = $pdo->lastInsertId();

            $sql = "INSERT INTO formule (libelle, prix) VALUES (?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$formule, $prix]);
            $formuleId = $pdo->lastInsertId();

            $sql = "INSERT INTO code (formule_illimite, id_formule) VALUES (?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$code, $formuleId]);
            $codeId = $pdo->lastInsertId();

            $sql = "INSERT INTO achete (id_candidat, id_code, date_debut, date_fin) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$candidatId, $codeId, $start, $end]);

            $msg="Les informations sont enregistrées avec succès!";
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }

        // Close the PDO connection
        $pdo = null;
    }
}
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulaire d'Inscription au Cours de Code</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/code.css">
    <style>
        .msg{
            color:green;
            font-size: 18px;
            font-weight: 500
            margin:10px;
            padding: 5px;
        }
    </style>
</head>

<body>

    <div class="dashboard-container">
        
        <div class="main-content">
            <h1>Formulaire d'Inscription au Cours de Code</h1>
            <form action="code.php" method="post">
                <div class="form-section">
                    <h2>Informations personnelles du candidat :</h2>
                    <div class="form-group">
                        <label for="nom">Nom :</label>
                        <input type="text" id="nom" name="nom" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="prenom">Prénom :</label>
                        <input type="text" id="prenom" name="prenom" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email :</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="telephone">Numéro de téléphone :</label>
                        <input type="tel" id="telephone" name="telephone" required>
                    </div>
                    <div class="form-group">
                        <label for="nissance">date de naissance :</label>
                        <input type="date" id="nissance" name="nissance" required>
                    </div>
                    <div class="form-group">
                        <label for="adresse">Adresse physique :</label>
                        <input type="text" id="adresse" name="adresse" required>
                    </div>
                </div>
                <div class="form-section">
                    <h2>Choix de la formule de code :</h2>
                    <div class="form-group">
                        <label for="formule_code">Choix de la formule :</label>
                        <select id="formule_code" name="formule_code" onchange="toggleDateFields()" required>
                            <option value="Code_illimite" disabled selected> </option>
                            <option value="Code_illimite">Formule illimitée</option>
                            <option value="Code">Formule simple (max 6 mois)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="date_debut">Date de début :</label>
                        <input type="date" id="date_debut" name="date_debut" onchange="setMaxEndDate()" required>
                    </div>
                    <div class="form-group formule_code_group" id="date_fin_group" style="display: none;">
                        <label for="date_fin">Date de fin :</label>
                        <input type="date" id="date_fin" name="date_fin" >
                    </div>
                    
                    <button type="submit">Inscrire</button>

                    <?php if(isset($msg) && !empty($msg)): ?>
                    <p id="message" class="msg" ><?php echo $msg; ?></p>
                    <script>
                        
                        setTimeout(function() {
                            var messageElement = document.getElementById("message");
                            if (messageElement) {
                                messageElement.remove();
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
                <button onclick="navigateTo('code.php')" class="click"><i class="fas fa-key"></i> Code</button>
                <button onclick="navigateTo('conduite.php')"><i class="fas fa-car"></i> Conduite</button>
                <button onclick="navigateTo('courssupp.php')"><i class="fas fa-book"></i>Réservation ( cours supp / heure conduite)</button>
                <button onclick="navigateTo('validation.html')"><i class="fas fa-check-circle"></i> Validation des Rendez-vous</button>
                <button onclick="navigateTo('candidat_list.php')"><i class="fas fa-users"></i> Liste des Candidats</button>
                <button onclick="navigateTo('index.php')"><i class="fas fa-arrow-left"></i> Accueil</button>
            </div>
        </div>
    </div>

    <script src="javascript/accueil.js"></script>
</body>

</html>
