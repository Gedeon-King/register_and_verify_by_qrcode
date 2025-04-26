<?php
// include 'configuration.php'; // Include the database configuration file
include 'configuration.php'; // Include the database configuration file

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables for form values (to maintain form data on submission errors)
$nom = $prenom = $contact = $quartier = $eglise_membre = $nom_eglise = $experience = "";
$errors = []; // Array to store errors

// Process form submission
// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate name
    if (empty($_POST["nom"])) {
        $errors[] = "Le nom est requis";
    } else {
        $nom = test_input($_POST["nom"]);
    }

    // Validate first name
    if (empty($_POST["prenom"])) {
        $errors[] = "Le prénom est requis";
    } else {
        $prenom = test_input($_POST["prenom"]);
    }

    // Validate contact
    if (empty($_POST["contact"])) {
        $errors[] = "Le contact est requis";
    } else {
        $contact = test_input($_POST["contact"]);

        // Check if contact already exists in the database
        $stmt_check_contact = $conn->prepare("SELECT * FROM utilisateurs WHERE contact = ?");
        $stmt_check_contact->bind_param("s", $contact);
        $stmt_check_contact->execute();
        $result = $stmt_check_contact->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = "Le contact existe déjà dans la base de données.";
        }
        $stmt_check_contact->close();
    }

    // Validate quartier
    if (empty($_POST["quartier"])) {
        $errors[] = "Le quartier est requis";
    } else {
        $quartier = test_input($_POST["quartier"]);
    }

    // Validate church membership
    if (!isset($_POST["eglise_membre"])) {
        $errors[] = "Veuillez indiquer si vous appartenez à une église";
    } else {
        $eglise_membre = test_input($_POST["eglise_membre"]);

        // If user belongs to a church, validate church name
        if ($eglise_membre == "oui" && empty($_POST["nom_eglise"])) {
            $errors[] = "Le nom de l'église est requis";
        } else {
            $nom_eglise = test_input($_POST["nom_eglise"]);
            if ($eglise_membre == "non") {
                $nom_eglise = "";  // Clear church name if "non" is selected
            }
        }
    }

    // Validate experience
    if (empty($_POST["experience"])) {
        $errors[] = "Veuillez indiquer votre expérience";
    } else {
        $experience = test_input($_POST["experience"]);
    }

    // If no errors, save to database and redirect
    if (empty($errors)) {
        // Prepare SQL statement to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO utilisateurs (nom, prenom, contact, quartier, eglise_membre, nom_eglise, experience) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $nom, $prenom, $contact, $quartier, $eglise_membre, $nom_eglise, $experience);

        // Execute the statement
        if ($stmt->execute()) {
            // Get the ID of the inserted record
            $last_id = $conn->insert_id;

            // Close statement
            $stmt->close();

            // Close the database connection
            $conn->close();

            // Redirect to success page with the user ID
            header("Location: success.php?id=" . $last_id);
            exit();
        } else {
            $errors[] = "Erreur lors de l'enregistrement: " . $stmt->error;
            $stmt->close();
        }
    }
}


// Function to sanitize form data
function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulaire d'inscription - Xplosion Francophone</title>
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

        .form-container {
            background-color: #f9f9f9;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .church-name {
            display: none;
        }

        .error {
            color: red;
            margin-bottom: 15px;
            padding: 10px;
            background-color: #ffeeee;
            border-radius: 4px;
        }

        button {
            background-color: #c01857;
            /* Using the pink color from the image */
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

        h2 {
            color: #2b5797;
            /* Using the blue color from the image */
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <!-- Header Image -->
    <img src="image.png" alt="Xplosion Francophone" class="header-image">

    <div class="form-container">
        <h2>Formulaire d'inscription</h2>

        <?php
        // Display errors if any
        if (!empty($errors)) {
            echo "<div class='error'>";
            foreach ($errors as $error) {
                echo $error . "<br>";
            }
            echo "</div>";
        }
        ?>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="nom">Nom:</label>
                <input type="text" id="nom" name="nom" onkeyup="this.value = this.value.toUpperCase();" value="<?php echo $nom; ?>">
            </div>

            <div class="form-group">
                <label for="prenom">Prénom:</label>
                <input type="text" id="prenom" name="prenom" onkeyup="this.value = this.value.toUpperCase();" value="<?php echo $prenom; ?>">
            </div>

            <div class="form-group">
                <label for="contact">Contact:</label>
                <input type="text" id="contact" name="contact" value="<?php echo $contact; ?>">
            </div>

            <div class="form-group">
                <label for="quartier">Quartier:</label>
                <input type="text" id="quartier" name="quartier" onkeyup="this.value = this.value.toUpperCase();" value="<?php echo $quartier; ?>">
            </div>

            <div class="form-group">
                <label>Appartiens-tu à une église?</label>
                <div style="margin-top: 5px;">
                    <input type="radio" id="eglise_oui" name="eglise_membre" value="oui" <?php if ($eglise_membre == "oui") echo "checked"; ?> onclick="toggleChurchName(true)">
                    <label for="eglise_oui" style="display: inline-block; margin-right: 20px;">Oui</label>

                    <input type="radio" id="eglise_non" name="eglise_membre" value="non" <?php if ($eglise_membre == "non") echo "checked"; ?> onclick="toggleChurchName(false)">
                    <label for="eglise_non" style="display: inline-block;">Non</label>
                </div>
            </div>

            <div class="form-group church-name" id="church_name_div">
                <label for="nom_eglise">Nom de l'église:</label>
                <input type="text" id="nom_eglise" name="nom_eglise" onkeyup="this.value = this.value.toUpperCase();" value="<?php echo $nom_eglise; ?>">
            </div>

            <div class="form-group">
                <label for="experience">As-tu expérimenté la 1ère ou 2ème:</label>
                <select id="experience" name="experience">
                    <option value="" <?php if ($experience == "") echo "selected"; ?>>Sélectionner</option>
                    <option value="1ere" <?php if ($experience == "1ere") echo "selected"; ?>>1ère</option>
                    <option value="2eme" <?php if ($experience == "2eme") echo "selected"; ?>>2ème</option>
                    <option value="les deux" <?php if ($experience == "les deux") echo "selected"; ?>>Les deux</option>
                    <option value="aucune" <?php if ($experience == "aucune") echo "selected"; ?>>Aucune</option>
                </select>
            </div>

            <button type="submit">S'inscrire</button>
        </form>
    </div>

    <script>
        // Function to show/hide church name field
        function toggleChurchName(show) {
            const churchNameDiv = document.getElementById('church_name_div');
            churchNameDiv.style.display = show ? 'block' : 'none';

            // If hiding, clear the input
            if (!show) {
                document.getElementById('nom_eglise').value = '';
            }
        }

        // Initialize the church name visibility based on initial selection
        window.onload = function() {
            const egliseOui = document.getElementById('eglise_oui');
            if (egliseOui.checked) {
                toggleChurchName(true);
            } else {
                const egliseNon = document.getElementById('eglise_non');
                if (egliseNon.checked) {
                    toggleChurchName(false);
                }
            }
        };
    </script>
</body>

</html>