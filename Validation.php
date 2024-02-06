<!DOCTYPE html>
<html lang="fr">


  <?php 
      include 'db_cnx.php';


      use PHPMailer\PHPMailer\PHPMailer;
      use PHPMailer\PHPMailer\SMTP;
      use PHPMailer\PHPMailer\Exception;
      
      require 'phpmailer/src/Exception.php';
      require 'phpmailer/src/PHPMailer.php';
      require 'phpmailer/src/SMTP.php';

      session_start();
      $stmt = $pdo->prepare("SELECT c.Nom AS Nom_candidat, f.Nom AS Nom_formateur, r.num_res, r.Date_plan AS date_reservee, r.heure_reserve AS heure_reservee, r.Statut AS Statut_reservation
                                  FROM Reservation r
                                  INNER JOIN C_conduit cc ON r.N_demande = cc.N_demande
                                  INNER JOIN Candidat c ON cc.Id_candidat = c.Id_candidat
                                  INNER JOIN Formateur f ON r.Num_ss = f.Num_ss;");
          
      $stmt->execute();
      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        


      if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // Sanitize and store the received data
        $reservationId = $_POST['id'];
        $status = $_POST['status'];
        $currentDate = date("m/d/Y");
        echo " statut"." ".$status;
      // Update the status of the reservation in the database
      $randomFormateurNumSs = null;
          if (!isset($_SESSION['refusetime'])) {
            $_SESSION['refusetime'] = 0;
            }
        if ($status == 'refusee') {
            try {
                  $_SESSION['refusetime']++; // Increment the refusal count

                  // Check if the reservation has been refused twice
                  if ($_SESSION['refusetime'] >= 2) {
                      $status = 'impossible';
                      $_SESSION['refusetime'] = 0;

                      $stmt = $pdo->prepare("SELECT c.Mail AS email_candidat, f.Nom AS nom_formateur, r.statut, r.Date_plan AS date_planification FROM Reservation r
                                  INNER JOIN C_conduit cc ON r.N_demande = cc.N_demande
                                  INNER JOIN Candidat c ON cc.Id_candidat = c.Id_candidat
                                  INNER JOIN Formateur f ON r.Num_ss = f.Num_ss
                                  WHERE r.Num_res = :reservationId");
                      $stmt->bindParam(':reservationId', $reservationId, PDO::PARAM_INT);
                      $stmt->execute();
                      $result = $stmt->fetch(PDO::FETCH_ASSOC);

                      $emailCandidat = $result['email_candidat'];
                      $nomFormateur = $result['nom_formateur'];
                      $datePlanification = $result['date_planification'];
                      $sujet = "Reservation impossible";
                      $message = "Cher Candidat,\n\nVotre réservation pour une heure de conduite avec le formateur $nomFormateur le $datePlanification ne peut pas être mise en place.\n\nCordialement,\nVotre auto-école";
                      
                      $mail = new PHPMailer();

                      try {
                          // Server settings
                          $mail->isSMTP();
                          $mail->Host = 'smtp.gmail.com';  // SMTP server address
                          $mail->SMTPAuth = true;
                          $mail->Username = 'amjad.ae.97@gmail.com'; // SMTP username
                          $mail->Password = 'ocewmuqcybvpdzkr'; // SMTP password
                          $mail->Port = 587; // SMTP port (usually 587 for TLS encryption)

                          // Recipients
                          $mail->setFrom('amjad.ae.97@gmail.com', 'admin'); // Sender's email and name
                          $mail->addAddress($emailCandidat); // Recipient's email

                          // Content
                          $mail->isHTML(true); // Set email format to HTML
                          $mail->Subject = $sujet;
                          $mail->Body = $message;

                          // Send email
                          $mail->send();
                          echo 'Email sent successfully.';
                      } catch (Exception $e) {
                          echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                      }


                  }
                // Get a random formateur from the database
                $stmt = $pdo->prepare("SELECT Num_ss, Nom, Prenom FROM Formateur ORDER BY RANDOM() LIMIT 1");
                $stmt->execute();
                $randomFormateur = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($refusetime==2) {
                  $status='impssible';
                }
                // Check if a random formateur is found
                if ($randomFormateur) {
                    // Use the $randomFormateur array as needed
                    $randomFormateurNumSs = $randomFormateur['num_ss'];
                } else {
                    // No random formateur found
                    echo "No formateur found in the database.";
                }

            } catch (PDOException $e) {
                // Respond with error message
                echo "Error: " . $e->getMessage();
            }
        

        try {
            // Update reservation status and random formateur if status is 'refusee'
            $stmt = $pdo->prepare("UPDATE Reservation SET Statut = :status , date_valid = :currentDate, num_ss= :randfor WHERE Num_res = :reservationId");
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            $stmt->bindParam(':currentDate', $currentDate, PDO::PARAM_STR);
            $stmt->bindParam(':randfor', $randomFormateurNumSs, PDO::PARAM_INT);
            $stmt->bindParam(':reservationId', $reservationId, PDO::PARAM_INT);
            $stmt->execute();

            // Respond with success message
            echo "Status updated successfully to $status";
        } catch (PDOException $e) {
            // Respond with error message
            echo "Error: " . $e->getMessage();
        }
      }else {
          try {
            
            $stmt = $pdo->prepare("UPDATE Reservation SET Statut = :status , date_valid = :currentDate WHERE Num_res = :reservationId");
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            $stmt->bindParam(':currentDate', $currentDate, PDO::PARAM_STR);
            $stmt->bindParam(':reservationId', $reservationId, PDO::PARAM_INT);
            $stmt->execute();

            // Respond with success message
            echo "Status updated successfully to $status";
          } catch (PDOException $e) {
            // Respond with error message
            echo "Error: " . $e->getMessage();
        }
      }
      
    } 

  ?>

  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Liste des Candidats</title>
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
    />
    <link rel="stylesheet" href="css/candidat_list.css" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <style>
      .fa-xmark {
        color: red;
        font-size:30px;
        margin: 5px;
        
        margin-right: 15px;
      }
      .fa-check {
        color: green;
        font-size:30px;
        margin-left: 15px;
        margin: 5px;
      }
      .fa-check,
      .fa-xmark {
        cursor: pointer;
      }
      .editForm {
        display: none;
      }
    </style>
  </head>

  <body>
    <div class="dashboard-container">
      <div class="main-content">
        <h1>Liste des réservation</h1>

        <table id="candidatesTable">
          <thead>
            <tr>
             <!-- <th>ID Candidat</th>  -->
              <th>Candidat</th>
              <th>Formateur</th>
              <th>Date réservée</th>
              <th>heure réservée</th>
              <th>Statut_reservation</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
          <?php
             // Output table rows using the fetched data
            foreach ($result as $row) {
                //echo "<tr>";
                echo "<tr data-id='{$row['num_res']}'>";
                // echo "<td>{$row['num_res']}</td>";
                echo "<td>{$row['nom_candidat']}</td>";
                echo "<td>{$row['nom_formateur']}</td>";
                echo "<td>{$row['date_reservee']}</td>";
                echo "<td>{$row['heure_reservee']}</td>";
                echo "<td>{$row['statut_reservation']}</td>";
                echo '<td> <i class="fa-solid fa-xmark"></i> <i class="fa-solid fa-check"></i></td>';
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
          <button onclick="navigateTo('code.php')">
            <i class="fas fa-key"></i> Code
          </button>
          <button onclick="navigateTo('conduite.php')">
            <i class="fas fa-car"></i> Conduite
          </button>
          <button onclick="navigateTo('courssupp.php')">
            <i class="fas fa-book"></i> Réservation ( cours supp / heure
            conduite)
          </button>
          <button onclick="navigateTo('validation.php')" class="click">
            <i class="fas fa-check-circle"></i> Validation des Rendez-vous
          </button>
          <button onclick="navigateTo('candidat_list.php')">
            <i class="fas fa-users"></i> Liste des Candidats
          </button>
          <button onclick="navigateTo('index.php')">
            <i class="fas fa-arrow-left"></i> Accueil
          </button>
        </div>
      </div>
    </div>

    <script src="javascript/accueil.js"></script>

    <script>
        $(document).ready(function () {
            // Handle click event for accept icon
            $(document).on('click', '.fa-check', function () {
                var row = $(this).closest('tr');
                var reservationId = $(this).closest('tr').data('id');

                // Make AJAX request to update status to 'accepte'
                $.ajax({
                    url: 'Validation.php',
                    type: 'POST',
                    data: {
                        id: reservationId,
                        status: 'accepte'
                    },  
                    success: function (response) {
                        // Handle success response
                        console.log('Status updated to accepte');
                        location.reload();
                    },
                    error: function (xhr, status, error) {
                        // Handle error
                        console.error(xhr.responseText);
                    }
                });
            });

            // Handle click event for reject icon
            $(document).on('click', '.fa-xmark', function () {
                var row = $(this).closest('tr');
                var reservationId = $(this).closest('tr').data('id');

                // Make AJAX request to update status to 'refusee'
                $.ajax({
                    url: 'Validation.php',
                    type: 'POST',
                    data: {
                        id: reservationId,
                        status: 'refusee'
                    },
                    success: function (response) {
                        // Handle success response
                        console.log('Status updated to refusee');
                        location.reload();
                    },
                    error: function (xhr, status, error) {
                        // Handle error
                        console.error(xhr.responseText);
                    }
                });
            });
        });
    </script>
  </body>
</html>
