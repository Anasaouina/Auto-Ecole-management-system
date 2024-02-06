
<!DOCTYPE html>
<html lang="fr">



<?php
        include 'db_cnx.php';

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $msg = "";
            $error="";
            $nom = $_POST["nom"];
            $prenom = $_POST["prenom"];
            $email = $_POST["email"];
            @$tele = $_POST["tele"];
            $adresse = $_POST["adresse"];
            $permis =$_POST["type_permis"];
            $conduit = $_POST["formule"];
            $nb_heures=0;
            $ID=0;
            switch ($permis) {
                case 'Permis A':
                    $nb_heures=24;
                    $ID=1;
                    break;

                case 'Permis B':
                    $nb_heures=20;
                    $ID=2;
                    break;
                
                case 'Permis C':
                    $nb_heures=70;
                    $ID=3;
                    break;
                default:
                    $nb_heures=20;
                    $ID=1;
                    break;
            }

            if (!$nom || !$prenom || !$email || !$tele || !$adresse) {
                $msg = "Tous les champs doivent être remplis !!";
            } else {
                try {
                    // Check if the email and telephone already exist in the database
                    $stmt = $pdo->prepare("SELECT * FROM candidat WHERE mail = :email AND no_tele = :tele");
                    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                    $stmt->bindParam(':tele', $tele, PDO::PARAM_STR);
                    $stmt->execute();
                    $candidat = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($candidat) {
                        $candidatId = $candidat['id_candidat'];
                        $birth = $candidat['date_naissance'];
                        $currentDate = date("m/d/Y"); 

                        function calculateAge($dateOfBirth) {
                            $today = new DateTime();
                            $birthDate = new DateTime($dateOfBirth);
                            $age = $today->diff($birthDate)->y;
                            
                            return $age;
                        }

                        $age = calculateAge($birth);
                        
                        if(($age <18 &&  $conduit ==="Conduite accompagnée B") || ($age > 21 && $conduit ==="Poids Lourd") || ($age >=18 &&  ($conduit ==="Conduite normale B") || ($conduit ==="Conduite Moto") ) ){
                            // Retrieve the code ID for the candidat
                            $sql = "SELECT id_code FROM achete WHERE id_candidat = ?";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute([$candidatId]);
                            $codeId = $stmt->fetchColumn(); 
                            

                            $sql = "SELECT id_formule FROM code WHERE id_code = ?";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute([$codeId]);
                            $formuleId = $stmt->fetchColumn(); 

                            $sql = "UPDATE formule SET libelle_con = :value WHERE id_formule = :condition";
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindParam(':value', $conduit, PDO::PARAM_STR); 
                            $stmt->bindParam(':condition', $formuleId, PDO::PARAM_STR); 
                            $stmt->execute();
                            
                            $sql = "INSERT INTO conduit (nb_heures, id, id_formule) VALUES (?, ?, ?)";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute([$nb_heures, $ID, $formuleId]);
                            $conduitId = $pdo->lastInsertId();

                            $currentDate = date("m/d/Y");
                            $sql = "INSERT INTO c_conduit (date_achat, id_conduit, id_candidat) VALUES (?, ?, ?)";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute([$currentDate, $conduitId, $candidatId]);
                            $msg=  "Conduite ajoutée avec succès !";
                        }else {
                            $error= "Cette formule n'est pas adaptée à votre âge";
                        }
                        
                        

                    } else {
                        
                        header("Location: code.php");
                        die();
                    }

                    
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
    <title>Formulaire de Conduite - Auto École</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/conduite.css">
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

        function updateConduiteOptions() {
            
            var selectedTypePermis = document.getElementById("type_permis").value;
            var conduiteDropdown = document.getElementById("formule");

            conduiteDropdown.innerHTML = "";

            if (selectedTypePermis === "Permis A") {
                addOption(conduiteDropdown, "Conduite Moto", "Conduite Moto");
            } else if (selectedTypePermis === "Permis B") {
                addOption(conduiteDropdown, "Conduite accompagnée B", "Conduite accompagnée B");
                addOption(conduiteDropdown, "Conduite normale B", "Conduite normale B");
            } else if (selectedTypePermis === "Permis C") {
                addOption(conduiteDropdown, "Poids Lourd", "Poids Lourd");
            }
        }

        function addOption(select, text, value) {
            var option = document.createElement("option");
            option.text = text;
            option.value = value;
            select.add(option);
        }
        function updateConduiteOptions() {
            var selectedTypePermis = document.getElementById("type_permis").value;
            var conduiteDropdown = document.getElementById("formule");


            conduiteDropdown.innerHTML = "";

            switch (selectedTypePermis) {
                case "Permis A":
                    addOption(conduiteDropdown, "Conduite Moto", "Conduite Moto");
                    break;
                case "Permis B":
                    addOption(conduiteDropdown, "Conduite accompagnée B", "Conduite accompagnée B");
                    addOption(conduiteDropdown, "Conduite normale B", "Conduite normale B");
                    break;
                case "Permis C":
                    addOption(conduiteDropdown, "Poids Lourd", "Poids Lourd");
                    break;
                default:
                   
                    break;
            }
        }

        function addOption(select, text, value) {
            var option = document.createElement("option");
            option.text = text;
            option.value = value;
            select.add(option);
        }

        // Call the function to set default options when the page loads
        window.onload = function () {
            updateConduiteOptions();
        };
    </script>
</head>

<body>

    <div class="dashboard-container">
        <div class="main-content">
            
            <form action="conduite.php" method="post">
               
                
                <div class="form-section">
                    <h1>Formulaire de Conduite</h1>
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
                        <label for="tele">Numéro de téléphone :</label>
                        <input type="text" id="tele" name="tele" required>
                    </div>
                    <div class="form-group">
                        <label for="adresse">Adresse :</label>
                        <textarea id="adresse" name="adresse" rows="3" required></textarea>
                    </div>
                </div>

                <!-- Type de conduite souhaité -->
                <div class="form-section">
                    <h2>Type de conduite souhaité :</h2>
                    <div class="form-group">
                        <label for="type_permis">Sélection du type de permis :</label>
                        <select id="type_permis" name="type_permis" onchange="updateConduiteOptions()" required>
                            <option value="Permis A">Permis A</option>
                            <option value="Permis B">Permis B</option>
                            <option value="Permis C">Permis C</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="formule">Formule souhaitée :</label>
                        <select id="formule" name="formule" required>
                            <!-- les options seleon le choix du permis -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="duree">Date de fin :</label>
                        <input type="date" id="duree" name="duree" required>
                    </div>
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
                <button onclick="navigateTo('conduite.php')"class="click"><i class="fas fa-car"></i> Conduite</button>
                <button onclick="navigateTo('courssupp.php')"><i class="fas fa-book"></i> Réservation ( cours supp / heure conduite)</button>
                <button onclick="navigateTo('validation.html')"><i class="fas fa-check-circle"></i> Validation des Rendez-vous</button>
                <button onclick="navigateTo('candidat_list.php')"><i class="fas fa-users"></i> Liste des Candidats</button>
                <button onclick="navigateTo('index.php')"><i class="fas fa-arrow-left"></i> Accueil</button>
            </div>
        </div>
    </div>

    <script src="javascript/accueil.js"></script>

</body>

</html>
