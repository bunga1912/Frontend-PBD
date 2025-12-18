<?php
include '../config/db.php';

if (isset($_GET['id'])) {
    $idvendor = $_GET['id'];
    $sql = "DELETE FROM vendor WHERE idvendor = $idvendor";

    if ($conn->query($sql) === TRUE) {
        header("Location: indexvendor.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    echo "ID vendor tidak ada.";
}
?>
