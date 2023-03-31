<?php
session_start();
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

require_once('../config.php');
require_once('navigation.php');

if (!isset($_GET['id'])) {
    exit("id not exist");
}

try {
    $db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $email = $_SESSION['email'];

    if (!empty($_POST) && !empty($_POST['name'])) {
        $id = $_POST['person_id'];

        $sql = "UPDATE people SET name=?, surname=?, birth_day=?, birth_place=?, birth_country=? where id=?";
        $stmt = $db->prepare($sql);
        $success = $stmt->execute([$_POST['name'], $_POST['surname'], $_POST['birth_day'], $_POST['birth_place'], $_POST['birth_country'], intval($_POST['person_id'])]);

        $query = "INSERT INTO activity (user_email, activity, target_table, target_id) VALUES (" . "'" . $email . "','Edit person', 'People', $id)";
        $stmt = $db->prepare($query);
        $stmt->execute();

        echo '<script>alert("Osoba úspešne upravená!");</script>';
    }

    if (!empty($_POST) && !empty($_POST['game_id'])) {
        $sql = "INSERT INTO placement (person_id, game_id, placing, discipline) VALUES (?,?,?,?)";
        $stmt = $db->prepare($sql);
        $success = $stmt->execute([$_GET['id'], $_POST['game_id'], $_POST['placing'], $_POST['discipline']]);

        $idQuery = "SELECT id FROM placement ORDER BY id DESC LIMIT 1";
        $stmt = $db->query($idQuery);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $newID = $result[0]['id'];

        $query = "INSERT INTO activity (user_email, activity, target_table, target_id) VALUES (" . "'" . $email . "','Add placing', 'Placement', $newID)";
        $stmt = $db->prepare($query);
        $stmt->execute();

        echo '<script>alert("Umiestnenie úspešne pridané!");</script>';
    }

    $query = "SELECT * FROM people where id=?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_GET['id']]);
    $person = $stmt->fetch(PDO::FETCH_ASSOC);

    if (isset($_POST['del_placement_id'])) {
        $id = $_POST['del_placement_id'];

        $sql = "DELETE FROM placement WHERE id=?";
        $stmt = $db->prepare($sql);
        $stmt->execute([intval($_POST['del_placement_id'])]);

        $query = "INSERT INTO activity (user_email, activity, target_table, target_id) VALUES (" . "'" . $email . "','Delete placing', 'Placement', $id)";
        $stmt = $db->prepare($query);
        $stmt->execute();

        echo '<script>alert("Umiestnenie úspešne odstránené!");</script>';
    }

    $query = "select placement.*, game.city from placement join game on placement.game_id = game.id where placement.person_id=?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_GET['id']]);
    $placements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $gameQuery = "SELECT * FROM game";
    $stmt2 = $db->query($gameQuery);
    $games = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    $disciplineQuery = "SELECT DISTINCT discipline FROM placement";
    $stmt3 = $db->query($disciplineQuery);
    $disciplines = $stmt3->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getMessage();
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    <h2>Info o športovcovi</h2>
    <form action="#" method="post">
        <input type="hidden" name="person_id" value="<?php echo $person['id']; ?>">
        <div class="mb-3">
            <label for="InputName" class="form-label">Meno:</label>
            <input type="text" name="name" class="form-control" id="InputName" value="<?php echo $person['name']; ?>"
                   required>
        </div>
        <div class="mb-3">
            <label for="InputSurname" class="form-label">Priezvisko:</label>
            <input type="text" name="surname" class="form-control" id="InputSurname"
                   value="<?php echo $person['surname']; ?>" required>
        </div>
        <div class="mb-3">
            <label for="InputDate" class="form-label">Dátum narodenia:</label>
            <input type="date" name="birth_day" class="form-control" id="InputDate"
                   value="<?php echo $person['birth_day']; ?>" required>
        </div>
        <div class="mb-3">
            <label for="InputbrPlace" class="form-label">Miesto narodenia:</label>
            <input type="text" name="birth_place" class="form-control" id="InputBrPlace"
                   value="<?php echo $person['birth_place']; ?>" required>
        </div>
        <div class="mb-3">
            <label for="InputBrCountry" class="form-label">Krajina pôvodu:</label>
            <input type="text" name="birth_country" class="form-control" id="InputBrCountry"
                   value="<?php echo $person['birth_country']; ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Uložiť</button>
    </form>


    <h2>Umiestnenia</h2>
    <table class="table">
        <thead>
        <tr>
            <td>Umiestnenie</td>
            <td>Disciplína</td>
            <td>OH</td>
            <td>Akcia</td>
        </tr>
        </thead>
        <tbody>
        <?php //var_dump($results)
        foreach ($placements as $placement) {
            //var_dump($placement);
            echo '<tr><td><a href="editPlacement.php?id=' . $placement["id"] . '">' . $placement['placing'] . '</a></td><td>' . $placement['discipline'] . '</td><td>' . $placement['city'] . '</td><td>';
            echo '<form action="#" method="post"><input type="hidden" name="del_placement_id" value="' . $placement['id'] . '"><button type="submit" class="btn btn-primary">Vymaž</button></form>';
            echo '</td></tr>';
        }
        ?>
        </tbody>
    </table>
    <h2>Pridaj umiestnenie</h2>
    <form action="#" method="post">
        <div class="mb-3">
            <label for="game_id" class="form-label">OH:</label>
            <select name="game_id" class="form-select" id="game_id">
                <?php
                foreach ($games as $game) {
                    echo '<option value="' . $game['id'] . '">' . $game['city'] . ', ' . $game['year'] . '</option>';
                }
                ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="InputPlacing" class="form-label">Umiestnenie:</label>
            <input type="number" value="1" name="placing" class="form-control" id="InputPlacing" min="1" required>
        </div>
        <div class="mb-3">
            <label for="discipline" class="form-label">Disciplína:</label>
            <select name="discipline" class="form-select" id="discipline">
                <?php
                foreach ($disciplines as $disc) {
                    echo '<option value="' . $disc['discipline'] . '">' . $disc['discipline'] . '</option>';
                }
                ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Pridaj</button>
    </form>
</div>
<script>
    document.getElementById("highlight").classList.remove("start-home");
    document.getElementById("highlight").classList.add("start-admin");
</script>
<?php require_once 'footer.php';?>
</body>
</html>