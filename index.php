<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord de l'Auto-École</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/accueil.css">
</head>

<body>

    <div class="dashboard-container">
        <div class="main-content">
            <h1>Auto École</h1>
            <p>Bienvenue à l'Auto-École.<br> Votre partenaire pour l'apprentissage<br> sûr et efficace de la conduite.</p>
        </div>
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Tableau de Bord</h2>
            </div>
            <div class="sidebar-content">
                <button onclick="navigateTo('code.php')"><i class="fas fa-key"></i> Code</button>
                <button onclick="navigateTo('conduite.php')"><i class="fas fa-car"></i> Conduite</button>
                <button onclick="navigateTo('courssupp.php')"><i class="fas fa-book"></i> Cours Supplémentaires</button>
                <button onclick="navigateTo('extra_courses_appointment.html')"><i class="fas fa-calendar-alt"></i> Rendez-vous Cours Supplémentaires</button>
                <button onclick="navigateTo('validation.php')"><i class="fas fa-check-circle"></i> Validation des Rendez-vous</button>
                <button onclick="navigateTo('candidat_list.php')"><i class="fas fa-users"></i> Liste des Candidats</button>
            </div>
        </div>
    </div>

    <script src="javascript/accueil.js"></script>
</body>

</html>
