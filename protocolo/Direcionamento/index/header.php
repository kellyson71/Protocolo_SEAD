<?php
session_start();
require_once '../env/config.php';

// Define admin users
$adminUsers = ["secretaria", "k", "protocolo_geral", "Secretaria", "Protocolo_Geral"];

// Create connection
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch departments from "usuarios" table
$departments = [];
$sql = "SELECT DISTINCT username FROM usuarios";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $departments[] = $row["username"];
    }
} else {
    die("No departments found.");
}

$conn->close();

// Check if 'dep' exists in $_GET before accessing it
$dep = isset($_GET["dep"]) ? $_GET["dep"] : '';

// Session validation
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || ($_SESSION["dep"] !== $dep && $_SESSION["dep"] !== "k")) {
    header("Location: ../");
    exit();
}

// User simulation
if ($_SESSION["dep"] === "k" && isset($_POST["simulate_user"])) {
    $_SESSION["dep"] = $_POST["simulate_user"];
    header("Location: index.php?dep=" . $_POST["simulate_user"]);
    exit();
}

// For admin users, get the selected department filter
if (in_array($_SESSION["dep"], $adminUsers)) {
    $filterDep = isset($_GET['filterDep']) ? $_GET['filterDep'] : '';
} else {
    $filterDep = $_SESSION["dep"];
}

// Verificar se o tutorial já foi exibido na sessão
if (!isset($_SESSION['tutorial_exibido'])) {
    $_SESSION['tutorial_exibido'] = true;
    $exibirTutorial = true;
} else {
    $exibirTutorial = false;
}
    $exibirTutorial = true;

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="styles.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <title>Protocolos</title>
</head>

<body>
    <?php include 'navbar.php'; ?>
</body>

</html>