<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

require_once('navigation.php');
require_once('../config.php');

try {
    $db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!isset($_GET['id'])) {
        echo "Nie je vybraný športovec!";
        return;
    }

    $id = $_GET['id'];

    $query = "SELECT * FROM people p WHERE p.id=" . "'" . "$id" . "'";

    $gamesQuery = "SELECT g.year, g.city, g.country, g.type, pl.placing, pl.discipline FROM people p JOIN placement pl ON p.id=pl.person_id JOIN game g ON pl.game_id=g.id WHERE p.id=$id";

    $stmt = $db->query($query);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt2 = $db->query($gamesQuery);
    $results2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
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
    <title>Detail osoby</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN"
            crossorigin="anonymous"></script>
</head>
<body>
<div class="container-md">
    <a href="../index.php?page=0">Späť</a>
    <h1><?php foreach($results as $result) {echo $result['name'] . ' ' . $result['surname'];} ?></h1>
    <table class="table">
        <thead>
        <tr><td>Narodenie</td><td>Miesto narodenia</td><td>Krajina narodenia</td><?php
            foreach($results as $result){
                if ($result['death_day'] != null && $result['death_place'] != null && $result['death_country'] != null) {
                    echo "<td>Úmrtie</td><td>Miesto úmrtia</td><td>Krajina úmrtia</td>";
                }
            }
            echo "</tr>"; ?>
        </thead>
        <tbody>
        <?php
        foreach($results as $result){
            $date = new DateTimeImmutable($result["birth_day"]);
            echo "<tr><td>" . $date->format("d.m.Y") . "</td><td>" . $result["birth_place"] . "</td><td>" . $result["birth_country"] . "</td>";
            if ($result['death_day'] != null && $result['death_place'] != null && $result['death_country'] != null) {
                $deathDate = new DateTimeImmutable($result["death_day"]);
                echo "<td>" . $deathDate->format("d.m.Y") . "</td><td>" . $result["death_place"] . "</td><td>" . $result["death_country"] . "</td>";
            }
            echo "</tr>";
        }
        ?>
        </tbody>
    </table>
    <h2>Umiestnenia na OH</h2>
    <table class="table">
        <thead>
        <tr><td>Rok</td><td>Mesto</td><td>Krajina</td><td>Typ OH</td><td>Umiestnenie</td><td>Disciplína</td></tr>
        </thead>
        <tbody>
        <?php
        foreach($results2 as $result){
            echo "<tr><td>" . $result["year"] . "</td><td>" . $result["city"] . "</td><td>" . $result["country"] . "</td><td>" . $result["type"] . "</td><td>" . $result["placing"] . "</td><td>" . $result["discipline"] . "</td></tr>";
        }
        ?>
        </tbody>
    </table>
</div>
<?php require_once 'footer.php';?>
</body>
</html>