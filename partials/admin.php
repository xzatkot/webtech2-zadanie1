<?php
session_start();
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

if (!isset($_SESSION['access_token']) && (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true)) {
    header('Location: ../login.php');
}

require_once('../config.php');
require_once('navigation.php');

try {
    $db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query = "SELECT * FROM people";
    $stmt = $db->query($query);
    $persons = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getMessage();
}

if (isset($_POST['del_person_id'])) {
    $sql = "DELETE FROM people WHERE id=?";
    $stmt = $db->prepare($sql);

    $target_id = $_POST['del_person_id'];
    $stmt->execute([intval($_POST['del_person_id'])]);

    $email = $_SESSION['email'];
    $query = "INSERT INTO activity (user_email, activity, target_table, target_id) VALUES ('" . $email . "','Delete person', 'People', $target_id)";
    $stmt = $db->prepare($query);
    $stmt->execute();

    echo '<script>alert("Osoba úspešne vymazaná!");</script>';

    echo "<script>window.location.assign('admin.php')</script>";
}

if (!empty($_POST) && !empty($_POST['name'])) {
    $queryCheck = "SELECT * FROM people WHERE name='" . $_POST['name'] . "' AND surname='" . $_POST['surname'] . "' AND birth_day='" . $_POST['birth_day'] . "' AND birth_place='" . $_POST['birth_place'] . "' AND birth_country='" . $_POST['birth_country'] . "'";
    $stmtCheck = $db->query($queryCheck);
    $result = $stmtCheck->fetchAll(PDO::FETCH_ASSOC);

    $email = $_SESSION['email'];

    if (isset($result[0]["id"])) {
        echo '<script>alert("Osoba už je v databáze!");</script>';
    } else {
        if ($_POST['death_day'] !== "" && $_POST['death_place'] !== "" && $_POST['death_country'] !== "") {
            $sql = "INSERT INTO people (name, surname, birth_day, birth_place, birth_country, death_day, death_place, death_country) VALUES (?,?,?,?,?,?,?,?)";
            $stmt = $db->prepare($sql);
            $success = $stmt->execute([$_POST['name'], $_POST['surname'], $_POST['birth_day'], $_POST['birth_place'], $_POST['birth_country'], $_POST['death_day'], $_POST['death_place'], $_POST['death_country']]);
        } else {
            $sql = "INSERT INTO people (name, surname, birth_day, birth_place, birth_country) VALUES (?,?,?,?,?)";
            $stmt = $db->prepare($sql);
            $success = $stmt->execute([$_POST['name'], $_POST['surname'], $_POST['birth_day'], $_POST['birth_place'], $_POST['birth_country']]);
        }
        $idQuery = "SELECT id FROM people ORDER BY id DESC LIMIT 1";
        $stmt = $db->query($idQuery);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $newID = $result[0]['id'];

        $query = "INSERT INTO activity (user_email, activity, target_table, target_id) VALUES (" . "'" . $email . "','Add person', 'People', $newID)";
        $stmt = $db->prepare($query);
        $stmt->execute();

        echo '<script>alert("Osoba úspešne pridaná!");</script>';

        echo "<script>window.location.assign('admin.php')</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Zabezpečená stránka</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN"
            crossorigin="anonymous"></script>
</head>
<body>
<div class="container-md">
    <h1>Admin panel</h1>
    <h2>Pridaj športovca</h2>
    <form action="#" method="post">
        <div class="mb-3">
            <label for="InputName" class="form-label">Meno:</label>
            <input type="text" name="name" class="form-control" id="InputName" required>
        </div>
        <div class="mb-3">
            <label for="InputSurname" class="form-label">Priezvisko:</label>
            <input type="text" name="surname" class="form-control" id="InputSurname" required>
        </div>
        <div class="mb-3">
            <label for="InputDate" class="form-label">Dátum narodenia:</label>
            <input type="date" name="birth_day" class="form-control" id="InputDate" required>
        </div>
        <div class="mb-3">
            <label for="InputbrPlace" class="form-label">Miesto narodenia:</label>
            <input type="text" name="birth_place" class="form-control" id="InputBrPlace" required>
        </div>
        <div class="mb-3">
            <label for="InputBrCountry" class="form-label">Krajina pôvodu:</label>
            <input type="text" name="birth_country" class="form-control" id="InputBrCountry" required>
        </div>
        <div class="mb-3">
            <label for="InputDeathDate" class="form-label">Dátum úmrtia:</label>
            <input type="date" name="death_day" class="form-control" id="InputDeathDate">
        </div>
        <div class="mb-3">
            <label for="InputDeathPlace" class="form-label">Miesto úmrtia:</label>
            <input type="text" name="death_place" class="form-control" id="InputDeathPlace">
        </div>
        <div class="mb-3">
            <label for="InputDeathCountry" class="form-label">Krajina úmrtia:</label>
            <input type="text" name="death_country" class="form-control" id="InputDeathCountry">
        </div>
        <button type="submit" class="btn btn-primary">Pridaj</button>
    </form>

    <table class="table">
        <thead>
        <tr>
            <td>Meno</td>
            <td>Priezvisko</td>
            <td>Narodenie</td>
            <td>Akcia</td>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($persons as $person) {
            $date = new DateTimeImmutable($person["birth_day"]);
            echo "<tr><td><a href='editPerson.php?id=" . $person["id"] . "'>" . $person["name"] . "</a></td><td>" . $person["surname"] . "</td><td>" . $date->format("d.m.Y") . "</td>";
            echo '<td><form action="#" method="post"><input type="hidden" name="del_person_id" value="' . $person['id'] . '"><button type="submit" class="btn btn-primary">Vymaž</button></form></td></tr>';
        }
        ?>
        </tbody>
    </table>
</div>
<script>
    document.getElementById("highlight").classList.remove("start-home");
    document.getElementById("highlight").classList.add("start-admin");
</script>
<?php require_once 'footer.php';?>
</body>
</html>