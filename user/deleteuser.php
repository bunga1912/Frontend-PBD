<?php
include '../config/db.php';

if (isset($_GET['id'])) {
    $iduser = $_GET['id'];
    $sql = "DELETE FROM user WHERE iduser = $iduser";

    if ($conn->query($sql) === TRUE) {
        header("Location: indexuser.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    echo "ID user tidak ada.";
}
?>
