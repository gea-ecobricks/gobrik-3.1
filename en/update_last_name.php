<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

    // Include database connections
    require_once '../gobrikconn_env.php';
    require_once '../buwanaconn_env.php';

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
