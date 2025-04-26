<?php
include 'configuration.php'; // Include the database configuration file

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";
$user = null;

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["contact"]) && is_numeric($_POST["contact"])) {
        $contact = $_POST["contact"];
        
        // Get user information
        $stmt = $conn->prepare("SELECT * FROM utilisateurs WHERE contact = ?");
        $stmt->bind_param("i", $contact);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $stmt->close();
            
            // If confirming seat
            if (isset($_POST["confirm_seat"]) && $_POST["confirm_seat"] == "yes") {
                $update_stmt = $conn->prepare("UPDATE utilisateurs SET seat_confirmed = 'oui' WHERE contact = ?");
                $update_stmt->bind_param("i", $contact);
                
                if ($update_stmt->execute()) {
                    $message = "<div class='success'>Siège confirmé avec succès!</div>";
                    $user["seat_confirmed"] = "oui";
                } else {
                    $message = "<div class='error'>Erreur lors de la confirmation du siège.</div>";
                }
                
                $update_stmt->close();
            }
        } else {
            $message = "<div class='error'>Utilisateur non trouvé.</div>";
            $stmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification des QR Codes - Xplosion Francophone</title>
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
        .container {
            background-color: #f9f9f9;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        h2 {
            color: #2b5797;
            margin-bottom: 20px;
            text-align: center;
        }
        .form-container {
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], input[type="number"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #c01857;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }
        button:hover {
            background-color: #a01548;
        }
        .success {
            color: green;
            padding: 10px;
            background-color: #e6ffe6;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .error {
            color: red;
            padding: 10px;
            background-color: #ffeeee;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .info-container {
            text-align: left;
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
        .seat-confirmed {
            background-color: #4CAF50;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
            display: inline-block;
            margin-top: 10px;
        }
        .seat-not-confirmed {
            background-color: #f44336;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
            display: inline-block;
            margin-top: 10px;
        }
        .scanner-container {
            text-align: center;
            margin-bottom: 20px;
        }
        #qr-reader {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
        }
    </style>
    <script src="https://unpkg.com/html5-qrcode"></script>
</head>
<body>
    <!-- Header Image -->
    <img src="image.png" alt="Xplosion Francophone" class="header-image">
    
    <div class="container">
        <h2>Vérification</h2>
        
        <?php echo $message; ?>
        
        <div class="scanner-container">
            <div id="qr-reader"></div>
            <div id="qr-reader-results"></div>
        </div>
        
        <div class="form-container">
            <h3>Ou entrez le contact manuellement:</h3>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-group">
                    <label for="contact">Contact de l'utilisateur:</label>
                    <input type="number" id="contact" name="contact" required>
                </div>
                <button type="submit">Vérifier</button>
            </form>
        </div>
        
        <?php if ($user): ?>
        <div class="info-container">
            <h3>Informations de l'utilisateur:</h3>
            
            <?php if ($user["seat_confirmed"] == "oui"): ?>
                <div class="seat-confirmed">Siège Confirmé</div>
            <?php else: ?>
                <div class="seat-not-confirmed" style="margin-bottom: 10px;">Siège Non Confirmé</div>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" style="margin-bottom: 10px;">
                    <input type="hidden" name="contact"  value="<?php echo $user["contact"]; ?>">
                    <input type="hidden" name="confirm_seat" value="yes">
                    <button type="submit">Confirmer le Siège</button>
                </form>
            <?php endif; ?>
            
            <div class="info-row">
                <div class="info-label">ID:</div>
                <div class="info-value"><?php echo $user["id"]; ?></div>
            </div>
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
        </div>
        <?php endif; ?>
    </div>
    
    <script>
        function onScanSuccess(decodedText, decodedResult) {
            // Extract the ID from the decoded text
            // Looking for pattern like "ID: 123"
            // const idMatch = decodedText.match(/ID: (\d+)/);
            const idMatch = decodedText;

            console.log(`Decoded text: ${decodedText}`);
            console.log(`Decoded result: ${decodedResult}`);
            console.log(`ID match: ${idMatch}`);
            
            if (idMatch) {
                // const userId = idMatch[1]; // Extract the ID from the match 
                const userId = idMatch; // Extract the ID from the match 
                document.getElementById('contact').value = userId;
                document.querySelector('form').submit();
            } else {
                document.getElementById('qr-reader-results').innerHTML = 
                    '<div class="error">QR code invalide. Veuillez scanner un QR code valide.</div>';
            }
        }

        // Initialize QR scanner
        function initQRScanner() {
            const html5QrCode = new Html5Qrcode("qr-reader");
            const config = { fps: 10, qrbox: { width: 250, height: 250 } };
            
            // Start scanning
            html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess)
                .catch(err => {
                    document.getElementById('qr-reader').innerHTML = 
                        '<div class="error">Erreur d\'accès à la caméra: ' + err + '</div>';
                });
        }

        // Initialize scanner when page loads
        window.onload = initQRScanner;
    </script>
</body>
</html>