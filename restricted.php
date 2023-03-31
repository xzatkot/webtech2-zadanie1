<?php
session_start();

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

require_once('config.php');
require_once('partials/navigation.php');

$db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
    $email = $_SESSION['email'];
    $id = $_SESSION['id'];
    $fullname = $_SESSION['fullname'];
    $name = $_SESSION['name'];
    $surname = $_SESSION['surname'];
    echo '<div class="container-md">';
    echo '<h3>Vitaj ' . $fullname . '</h3>';
    echo '<p><strong>Si prihlásený pod emailom:</strong> ' . $email . '</p>';
    echo '<p><strong>Tvoj identifikátor je:</strong> ' . $id . '</p>';

} elseif ((isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true)) {
    $email = $_SESSION['email'];
    $login = $_SESSION['login'];
    $fullname = $_SESSION['fullname'];
    $created_at = $_SESSION['created_at'];
    echo '<div class="container-md">';
    echo '<h3>Vitaj ' . $_SESSION['fullname'] . ' </h3>';
    echo '<p><strong>Si prihlásený pod emailom:</strong> ' . $_SESSION['email'] . '</p>';
    echo '<p><strong>Tvoj identifikátor (login) je:</strong> ' . $_SESSION['login'] . '</p>';
    echo '<p><strong>Dátum registrácie/vytvorenia konta:</strong> ' . $_SESSION['created_at'] . '</p>';
} else {
    header('Location: login.php');
}

$queryLogins = "SELECT * FROM login WHERE user_login='" . "$login'" . "OR user_login='" . "$email'";
$stmtLogin = $db->query($queryLogins);
$logins = $stmtLogin->fetchAll(PDO::FETCH_ASSOC);

$queryActivity = "SELECT * FROM activity WHERE user_email='" . "$email'";
$stmtActivity = $db->query($queryActivity);
$activity = $stmtActivity->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Zabezpečená stránka</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN"
            crossorigin="anonymous"></script>

    <style>
        h1, h2, h3, h4, h5, h6 {
            margin: 3em 0 1em;
        }

        p, ul, ol {
            margin-bottom: 2em;
            color: #1d1d1d;
            font-family: sans-serif;
        }

        div {
            text-align: center;
        }
    </style>
    <link rel="stylesheet" href="https://unpkg.com/@picocss/pico@1.*/css/pico.min.css">
    <link rel="stylesheet" href="res/style.css">
</head>
<body>
<main style="text-align: center">
    <h1>Zabezpečená stránka</h1>
    <a role="button" class="secondary" href="partials/admin.php">Admin stránka</a></p>
    <a role="button" class="primary" href="logout.php">Odhlásenie</a></p>
</main>
<div class="container-md">
    <table class="table">
        <thead>
        <tr>
            <td>Login</td>
            <td>Metóda</td>
            <td>Čas prihlásenia</td>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($logins as $log) {
            $date = new DateTimeImmutable($log["timestamp"]);
            echo "<tr><td>" . $log["user_login"] . "</td><td>" . $log["method"] . "</td><td>" . $date->format("H:i:s d.m.Y") . "</td>";
        }
        ?>
        </tbody>
    </table>
    <table class="table">
        <thead>
        <tr>
            <td>Email používateľa</td>
            <td>Aktivita</td>
            <td>Cieľová tabuľka</td>
            <td>ID cieľového záznamu</td>
            <td>Čas</td>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($activity as $action) {
            $date = new DateTimeImmutable($action["timestamp"]);
            echo "<tr><td>" . $action["user_email"] . "</td><td>" . $action["activity"] . "</td><td>" . $action["target_table"] . "</td><td>" . $action["target_id"] . "</td><td>" . $date->format("H:i:s d.m.Y") . "</td>";
        }
        ?>
        </tbody>
    </table>
</div>
<script>
    document.getElementById("highlight").classList.remove("start-home");
    document.getElementById("highlight").classList.add("start-login");
</script>
<?php require_once 'partials/footer.php'; ?>
</body>
</html>