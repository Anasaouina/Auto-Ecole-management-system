<!DOCTYPE html>
<html lang="fr">
<!-- <?php
    include 'db_cnx.php';
    $stmt = $pdo->prepare("SELECT * FROM candidat");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?> -->
<?php
// Include your database connection file
include 'db_cnx.php';

// Query to fetch data from Candidat, Code, Formule tables
$stmt = $pdo->prepare("SELECT 
c.Id_candidat, 
c.Nom, 
c.Prenom, 
c.Mail, 
c.adresse, 
c.date_naissance, 
t.libelle AS type_permis, 
co.Id_code, 
f.Libelle AS code, 
f.libelle_con AS conduit,
CASE 
    WHEN a.nombre_h_achete IS NULL THEN 0 
    ELSE CAST(a.nombre_h_achete AS INTEGER)
END AS nombre_h_achete
FROM 
candidat c
INNER JOIN 
c_conduit cc ON c.Id_candidat = cc.Id_candidat
INNER JOIN 
conduit cd ON cc.Id_conduit = cd.Id_conduit
INNER JOIN 
type_permis t ON cd.ID = t.ID
INNER JOIN 
code co ON cd.Id_formule = co.Id_formule
INNER JOIN 
formule f ON co.Id_formule = f.Id_formule
LEFT JOIN 
acheter_cours a ON c.Id_candidat = a.Id_candidat;
");
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Close the PDO connection
$pdo = null;
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Candidats</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/candidat_list.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <style>
        .fa-trash{
            color: red;
            margin: 5px;
            
        }
        .fa-pen{
            color: green;
            margin: 5px;
        }
        .fa-pen,.fa-trash{
            cursor: pointer;

        }
        .editForm{
            display: none;
        }
    </style>
</head>

<body>
    

    <div class="dashboard-container">
        
        <div class="main-content">
            <div class="editForm">
                <h1>modifier candidat </h1>
                <form id="editForm" >
                    <input type="hidden" id="userId" name="userId">
                    <label for="nom">Nom : </label>
                    <input type="text" id="nom" name="nom">
                    <label for="prenom">prenom : </label>
                    <input type="text" id="prenom" name="prenom">
                    <label for="email">Email : </label>
                    <input type="email" id="email" name="email">
                    <label for="adresse">Adresse: </label>
                    <input type="text" id="adresse" name="adresse">
                    <label for="tele">Telephone: </label>
                    <input type="text" id="tele" name="tele">
                    <button type="submit">Save Changes</button>
                </form>

            </div>
            <h1>Liste des Candidats</h1>
            
          
            <table id="candidatesTable">
    <thead>
        <tr>
            <th>ID Candidat</th>
            <th>Nom</th>
            <th>Mail</th>
            <th>Prénom</th>
            <th>Adresse</th>
            <th>Code</th>
            <th>type de permis </th>
            <th>Conduit</th>
            <th>Cours Supplémentaire</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php
        // Output table rows using the fetched data
        foreach ($result as $row) {
            //echo "<tr>";
            echo "<tr data-id='{$row['id_candidat']}'>";
            echo "<td>{$row['id_candidat']}</td>";
            echo "<td>{$row['nom']}</td>";
            echo "<td>{$row['mail']}</td>";
            echo "<td>{$row['prenom']}</td>";
            echo "<td>{$row['adresse']}</td>";
            echo "<td>{$row['code']}</td>";
            echo "<td>{$row['type_permis']}</td>";
            echo "<td>{$row['conduit']}</td>";
            echo "<td>{$row['nombre_h_achete']}</td>";
            echo '<td> <i class="fa-solid fa-trash"></i> <i class="fa-solid fa-pen"></i></td>';
            echo "</tr>";
        }
        ?>
    </tbody>
</table>

        </div>


       
        
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Tableau de Bord</h2>
            </div>
            <div class="sidebar-content">
                <button onclick="navigateTo('code.php')" ><i class="fas fa-key"></i> Code</button>
                <button onclick="navigateTo('conduite.php')"><i class="fas fa-car"></i> Conduite</button>
                <button onclick="navigateTo('courssupp.php')"><i class="fas fa-book"></i> Réservation ( cours supp / heure conduite)</button>
                <button onclick="navigateTo('validation.html')"><i class="fas fa-check-circle"></i> Validation des Rendez-vous</button>
                <button onclick="navigateTo('candidat_list.php')"class="click"><i class="fas fa-users"></i> Liste des Candidats</button>
                <button onclick="navigateTo('index.php')"><i class="fas fa-arrow-left"></i> Accueil</button>
            </div>
        </div>
    </div>

    <script src="javascript/accueil.js"></script>
    <script>


        //function to remove user  from the table
        $(document).ready(function() {
            $('.fa-trash').on('click', function() {
                console.log("youc clique the trach icon");
                var userId = $(this).closest('tr').data('id'); // Get the user's ID from the closest <tr> element
                console.log(userId);
                if (confirm("Are you sure you want to delete this user?")) {
                    $.ajax({
                        url: './php/delete_user.php', // PHP script to handle the deletion
                        method: 'POST',
                        data: { userId: userId },
                        success: function(response) {
                            // Handle success (e.g., remove the row from the table)
                            $(this).closest('tr').remove();
                            alert('User deleted successfully!');
                            location.reload();
                        },
                        error: function(xhr, status, error) {
                            // Handle error
                            console.error(xhr.responseText);
                            alert('Error deleting user. Please try again.');
                        }
                    });
                }
            });
        });
            //function for editing users 
            $(document).ready(function() {
                // Edit button click event listener
                $('.fa-pen').click(function() {
                    var userId = $(this).closest('tr').data('id');
                    
                    // AJAX request to fetch candidate data
                    $.ajax({
                        url: 'php/update_user.php',
                        type: 'GET',
                        data: { id: userId },
                        dataType: 'json',
                        success: function(response) {
                            console.log("nom :", response.nom)
                            // Populate form with candidate data for editing
                            $('#editForm #userId').val(response.id_candidat);
                            $('#editForm #nom').val(response.nom);
                            $('#editForm #prenom').val(response.prenom);
                            $('#editForm #email').val(response.mail);
                            $('#editForm #adresse').val(response.adresse);
                            $('#editForm #tele').val(response.no_tele);
                            // Populate other form fields as needed
                            $('.editForm').show();
                        },
                        error: function(xhr, status, error) {
                            console.error(error);
                        }
                    });
                });
            });
        
            // Submit button click event listener
            $('#editForm').submit(function(event) {
                event.preventDefault(); // Prevent default form submission

                // AJAX request to update candidate data
                $.ajax({
                    url: 'php/update_user.php',
                    type: 'POST',
                    data: $(this).serialize(), // Serialize form data
                    success: function(response) {
                        // Handle success (e.g., display success message)
                        alert('Candidate data updated successfully!');
                        window.location.href = 'candidat_list.php'; // Redirect to candidat_list.php
                    },
                    error: function(xhr, status, error) {
                        // Handle error
                        console.error(error);
                    }
                });
            });
        

    </script>
</body>

</html>
