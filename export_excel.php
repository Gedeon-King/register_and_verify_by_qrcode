<?php
include 'configuration.php'; // Inclure la configuration de la base de données

// Connexion à la base de données
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérification de la connexion
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Préparer le téléchargement du fichier
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=inscriptions_" . date('Y-m-d') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// Commencer le tableau HTML
echo "<table border='1'>";
echo "<tr>
    <th>ID</th>
    <th>Nom</th>
    <th>Prenom</th>
    <th>Contact</th>
    <th>Quartier</th>
    <th>Membre d'eglise</th>
    <th>Nom de l'eglise</th>
    <th>Experience</th>
    <th>Siege Confirme</th>
    <th>Date d'inscription</th>
</tr>";

// Récupérer les données
$sql = "SELECT * FROM utilisateurs ORDER BY date_inscription DESC";
$result = $conn->query($sql);

// Remplir le tableau
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['nom']) . "</td>";
        echo "<td>" . htmlspecialchars($row['prenom']) . "</td>";
        echo "<td>" . htmlspecialchars($row['contact']) . "</td>";
        echo "<td>" . htmlspecialchars($row['quartier']) . "</td>";
        echo "<td>" . htmlspecialchars($row['eglise_membre']) . "</td>";
        echo "<td>" . htmlspecialchars($row['nom_eglise']) . "</td>";
        echo "<td>" . htmlspecialchars($row['experience']) . "</td>";
        echo "<td>" . (isset($row['seat_confirmed']) && $row['seat_confirmed'] == "oui" ? "Oui" : "Non") . "</td>";
        echo "<td>" . $row['date_inscription'] . "</td>";
        echo "</tr>";
    }
}

// Fin du tableau
echo "</table>";

// Fermer la connexion
$conn->close();
?>