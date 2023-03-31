<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

require_once('../config.php');
require_once('navigation.php');

try {
    $db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!isset($_COOKIE['max'])) {
        setcookie("max", 25);
        $results_per_page = 25;
    } else {
        $results_per_page = $_COOKIE["max"];
    }

    $page = isset($_GET['page']) ? $_GET['page'] : 0;

    $query = "SELECT p.id, p.name, p.surname, p.birth_day, p.birth_place, COUNT(p.name) AS count FROM people p JOIN placement pl ON p.id=pl.person_id JOIN game g ON pl.game_id=g.id WHERE pl.placing=1 GROUP BY p.id ORDER BY count DESC LIMIT 10";

    $stmt = $db->query($query);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo $e->getMessage();
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>TOP 10 športovcov</title>
    <link href="/z1/res/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN"
            crossorigin="anonymous"></script>
    <script src="/z1/partials/script.js"></script>

</head>
<body>
<div class="container-md" style="text-align: center">
    <table class="table">
        <thead>
        <tr>
            <td>Miesto</td>
            <td>Meno</td>
            <td><a href="javascript:sort(1);">Priezvisko</a></td>
            <td>Dátum narodenia</td>
            <td>Rodné mesto</td>
            <td>Počet zlatých medailí</td>
        </tr>
        </thead>
        <tbody>
        <?php
        $place = 1;
        foreach ($results as $result) {
            $id = $result["id"];
            $date = new DateTimeImmutable($result["birth_day"]);
            echo "<tr><td>" . $place . "</td><td>" . $result["name"] . "</td><td><a href='person.php?id=$id'>" . $result["surname"] . "</a>" . "</td><td>" . $date->format("d.m.Y") . "</td><td>" . $result["birth_place"] . "</td><td>" . $result["count"] . "</td></tr>";
            $place++;
        }
        ?>
        </tbody>
    </table>
    <script>
        document.getElementById("highlight").classList.remove("start-home");
        document.getElementById("highlight").classList.add("start-top10");
    </script>
</div>
<?php require_once 'footer.php';?>
</body>
</html>
