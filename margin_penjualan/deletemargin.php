<?php
include '../config/db.php';

if (isset($_GET['id'])) {
    $idmargin = $_GET['id'];
    $sql = "DELETE FROM margin_penjualan WHERE idmargin = $idmargin";

    if ($conn->query($sql) === TRUE) {
        header("Location: indexmargin.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    echo "ID margin tidak ada.";
}
?>
