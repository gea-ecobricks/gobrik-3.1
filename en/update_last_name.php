<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database credentials
$gobrik_servername = "localhost";
$gobrik_username = "ecobricks_brikchain_viewer";
$gobrik_password = "desperate-like-the-Dawn";
$gobrik_dbname = "ecobricks_gobrik_msql_db";

$buwana_servername = "localhost";
$buwana_username = "ecobricks_gobrik_app";
$buwana_password = "1EarthenAuth!";
$buwana_dbname = "ecobricks_earthenAuth_db";

// Establish connections to both databases
$buwana_conn = new mysqli($buwana_servername, $buwana_username, $buwana_password, $buwana_dbname);
$gobrik_conn = new mysqli($gobrik_servername, $gobrik_username, $gobrik_password, $gobrik_dbname);

// Check connections
if ($buwana_conn->connect_error) {
    die("Buwana Database connection failed: " . $buwana_conn->connect_error);
}
if ($gobrik_conn->connect_error) {
    die("GoBrik Database connection failed: " . $gobrik_conn->connect_error);
}

$buwana_conn->set_charset("utf8mb4");
$gobrik_conn->set_charset("utf8mb4");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['buwana_id'])) {
    $buwana_id = $_SESSION['buwana_id'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $update_buwana = isset($_POST['update_buwana']);

    if (!empty($first_name) && !empty($last_name)) {
        $full_name = $first_name . ' ' . $last_name;

        // Update GoBrik account
        $sql_update_gobrik = "UPDATE tb_ecobrickers SET first_name = ?, last_name = ?, full_name = ? WHERE buwana_id = ?";
        $stmt_gobrik = $gobrik_conn->prepare($sql_update_gobrik);
        if ($stmt_gobrik) {
            $stmt_gobrik->bind_param("sssi", $first_name, $last_name, $full_name, $buwana_id);
            $stmt_gobrik->execute();
            $stmt_gobrik->close();
        } else {
            echo "Error preparing statement for GoBrik: " . $gobrik_conn->error;
        }

        // Update Buwana account if checkbox is checked
        if ($update_buwana) {
            $sql_update_buwana = "UPDATE users_tb SET first_name = ?, last_name = ?, full_name = ? WHERE buwana_id = ?";
            $stmt_buwana = $buwana_conn->prepare($sql_update_buwana);
            if ($stmt_buwana) {
                $stmt_buwana->bind_param("sssi", $first_name, $last_name, $full_name, $buwana_id);
                $stmt_buwana->execute();
                $stmt_buwana->close();
            } else {
                echo "Error preparing statement for Buwana: " . $buwana_conn->error;
            }
        }

        echo "<script>
//             alert('Name updated successfully.');
            window.location.href = 'log.php';
        </script>";
    } else {
        echo "First name and last name are required.";
    }
} else {
    echo "Invalid request.";
}

$buwana_conn->close();
$gobrik_conn->close();
?>
