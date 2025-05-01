<?php
include 'configuration.php'; // Include the database configuration file

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_GET['id'];

// Get user information
$stmt = $conn->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    header("Location: index.php");
    exit();
}

$user = $result->fetch_assoc();

// Update seat confirmation if requested
if (isset($_GET['confirm_seat']) && $_GET['confirm_seat'] == 'true') {
    $update_stmt = $conn->prepare("UPDATE utilisateurs SET seat_confirmed = 'oui' WHERE id = ?");
    $update_stmt->bind_param("i", $user_id);
    $update_stmt->execute();
    $update_stmt->close();
    $user['seat_confirmed'] = 'oui';
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription Réussie - Xplosion Francophone</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .header-image {
            width: 100%;
            max-width: 800px;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .success-container {
            background-color: #f9f9f9;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h2 {
            color: #2b5797;
            margin-bottom: 20px;
        }

        .success-message {
            color: #c01857;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .qr-container {
            margin: 20px auto;
            text-align: center;
        }

        .info-container {
            text-align: left;
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
            border-radius: 8px;
        }

        .info-row {
            display: flex;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 8px;
        }

        .info-label {
            font-weight: bold;
            width: 40%;
        }

        .info-value {
            width: 60%;
        }

        .print-button,
        .action-button {
            background-color: #c01857;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            margin-top: 20px;
            margin-right: 10px;
            text-decoration: none;
            display: inline-block;
        }

        .print-button:hover,
        .action-button:hover {
            background-color: #a01548;
        }

        #qrcode {
            margin: 0 auto;
            display: inline-block;
        }

        @media print {

            .print-button,
            .action-button {
                display: none;
            }
        }
    </style>
</head>

<body>
    <!-- Header Image -->
    <img src="image.png" alt="Xplosion Francophone" class="header-image">

    <div class="success-container">
        <div>
            <button class="print-button" onclick="window.print()">Imprimer</button>
        </div>

        <h2>Inscription Réussie</h2>
        <div class="success-message">Votre inscription a été enregistrée avec succès!</div>


        <div class="info-container">
            <h3>Vos informations:</h3>
            <div class="info-row">
                <div class="info-label">Nom:</div>
                <div class="info-value"><?php echo htmlspecialchars($user["nom"]); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Prénom:</div>
                <div class="info-value"><?php echo htmlspecialchars($user["prenom"]); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Contact:</div>
                <div class="info-value"><?php echo htmlspecialchars($user["contact"]); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Quartier:</div>
                <div class="info-value"><?php echo htmlspecialchars($user["quartier"]); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Membre d'Église:</div>
                <div class="info-value"><?php echo htmlspecialchars($user["eglise_membre"]); ?></div>
            </div>
            <?php if ($user["eglise_membre"] == "oui"): ?>
                <div class="info-row">
                    <div class="info-label">Nom de l'Église:</div>
                    <div class="info-value"><?php echo htmlspecialchars($user["nom_eglise"]); ?></div>
                </div>
            <?php endif; ?>
            <div class="info-row">
                <div class="info-label">Expérience:</div>
                <div class="info-value"><?php echo htmlspecialchars($user["experience"]); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Date d'inscription:</div>
                <div class="info-value"><?php echo htmlspecialchars($user["date_inscription"]); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Siège Confirmé:</div>
                <div class="info-value"><?php echo isset($user["seat_confirmed"]) ? ($user["seat_confirmed"] == "oui" ? "Oui" : "Non") : "Non"; ?></div>
            </div>
        </div>

        <div class="qr-container">
            <!-- QR Code will be generated here -->
            <div id="qrcode"></div>

            <script src="https://cdn.jsdelivr.net/npm/qrcodejs/qrcode.min.js"></script>
            <!-- <script src="qrcode.js"></script> -->
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const qrCodeDiv = document.getElementById('qrcode');
                    const userId = "<?php echo htmlspecialchars($user['contact']); ?>"; // récupère ID PHP

                    qrCodeDiv.innerHTML = ""; // Clear any previous QR Code
                    if (userId.trim() !== "") {
                        new QRCode(qrCodeDiv, {
                            text: userId,
                            width: 256,
                            height: 256,
                            colorDark: "#000000",
                            colorLight: "#ffffff",
                            correctLevel: QRCode.CorrectLevel.H
                        });
                    } else {
                        alert("Aucun ID disponible pour générer le QR code !");
                    }
                });
            </script>

            <p>Scannez ce code pour vérifier votre ID</p>
        </div>
    </div>
</body>

</html>