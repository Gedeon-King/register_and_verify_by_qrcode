<?php
include 'configuration.php'; // Include the database configuration file

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Delete user if requested
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM utilisateurs WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: tables.php?message=deleted");
    exit();
}

// Toggle seat confirmation if requested
if (isset($_GET['toggle_seat']) && is_numeric($_GET['toggle_seat'])) {
    $id = $_GET['toggle_seat'];

    // Get current seat status
    $check_stmt = $conn->prepare("SELECT seat_confirmed FROM utilisateurs WHERE id = ?");
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $current_status = $row['seat_confirmed'] ?? 'non';
        $new_status = ($current_status == 'oui') ? 'non' : 'oui';

        // Update seat status
        $update_stmt = $conn->prepare("UPDATE utilisateurs SET seat_confirmed = ? WHERE id = ?");
        $update_stmt->bind_param("si", $new_status, $id);
        $update_stmt->execute();
        $update_stmt->close();
    }

    $check_stmt->close();
    header("Location: tables.php?message=seat_updated");
    exit();
}

// Query to get all registrations
$sql = "SELECT * FROM utilisateurs ORDER BY date_inscription DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration des inscriptions</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }

        h1 {
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .success {
            color: green;
            padding: 10px;
            background-color: #e6ffe6;
            border-radius: 4px;
            margin-bottom: 15px;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .delete-btn {
            color: white;
            background-color: #f44336;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 4px;
        }

        .delete-btn:hover {
            background-color: #d32f2f;
        }

        .toggle-btn {
            color: white;
            background-color: #4CAF50;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 4px;
        }

        .toggle-btn:hover {
            background-color: #45a049;
        }

        .seat-confirmed {
            color: green;
            font-weight: bold;
        }

        .seat-not-confirmed {
            color: red;
            font-weight: bold;
        }

        .filter-container {
            margin-bottom: 20px;
        }

        .export-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 4px;
            float: right;
        }
    </style>
</head>

<body>
    
    <h1>Liste des inscriptions</h1>

    <?php
    if (isset($_GET['message'])) {
        if ($_GET['message'] == "deleted") {
            echo "<div class='success'>L'utilisateur a été supprimé avec succès.</div>";
        } elseif ($_GET['message'] == "seat_updated") {
            echo "<div class='success'>Le statut du siège a été mis à jour avec succès.</div>";
        }
    }
    ?>

    <div class="filter-container">
        <a href="verify" class="export-btn" style="margin-right: 10px;">Scanner QR Code</a>
        <a href="index" class="export-btn" style="margin-right: 10px;">Inscription</a>
        <a href="export_excel" class="export-btn" style="margin-right: 10px;">Télécharger Excel</a>
    </div>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Contact</th>
                    <th>Quartier</th>
                    <th>Membre d'église</th>
                    <th>Nom de l'église</th>
                    <th>Expérience</th>
                    <th>Siège Confirmé</th>
                    <th>Date d'inscription</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row["id"]; ?></td>
                        <td><?php echo htmlspecialchars($row["nom"]); ?></td>
                        <td><?php echo htmlspecialchars($row["prenom"]); ?></td>
                        <td><?php echo htmlspecialchars($row["contact"]); ?></td>
                        <td><?php echo htmlspecialchars($row["quartier"]); ?></td>
                        <td><?php echo $row["eglise_membre"]; ?></td>
                        <td><?php echo htmlspecialchars($row["nom_eglise"]); ?></td>
                        <td><?php echo $row["experience"]; ?></td>
                        <td class="<?php echo isset($row["seat_confirmed"]) && $row["seat_confirmed"] == "oui" ? 'seat-confirmed' : 'seat-not-confirmed'; ?>">
                            <?php echo isset($row["seat_confirmed"]) ? ($row["seat_confirmed"] == "oui" ? "Oui" : "Non") : "Non"; ?>
                        </td>
                        <td><?php echo $row["date_inscription"]; ?></td>
                        <td class="actions">
                            <a href="tables.php?toggle_seat=<?php echo $row["id"]; ?>" class="toggle-btn">
                                <?php echo isset($row["seat_confirmed"]) && $row["seat_confirmed"] == "oui" ? "Annuler siège" : "Confirmer siège"; ?>
                            </a>
                            <a href="tables.php?delete=<?php echo $row["id"]; ?>" class="delete-btn" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur?')">Supprimer</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Aucune inscription trouvée.</p>
    <?php endif; ?>

</body>

</html>

<?php
// Close the database connection
$conn->close();
?>