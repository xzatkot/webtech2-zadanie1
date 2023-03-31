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

    $query = "SELECT p.id, p.name, p.surname FROM placement pl JOIN people p ON p.id = pl.person_id WHERE pl.id=?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_GET['id']]);
    $person = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!empty($_POST) && !empty($_POST['placing'])) {
        $id = $_POST['placement_id'];

        $sql = "UPDATE placement SET placing=?, discipline=? where id=?";
        $stmt = $db->prepare($sql);
        $success = $stmt->execute([$_POST['placing'], $_POST['discipline'], $_POST['placement_id']]);

        $query = "INSERT INTO activity (user_email, activity, target_table, target_id) VALUES (" . "'" . $email . "','Edit placing', 'Placement', $id)";
        $stmt = $db->prepare($query);
        $stmt->execute();

        echo '<script>alert("Umiestnenie úspešne upravené!");</script>';

        header("Location: editPerson.php?id=" . $person['id']);
    }

    $query = "SELECT * FROM placement where id=?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_GET['id']]);
    $placement = $stmt->fetch(PDO::FETCH_ASSOC);

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
    <h2>Úprava umiestnenia</h2>
    <form action="#" method="post">
        <input type="hidden" name="placement_id" value="<?php echo $placement['id']; ?>">
        <div class="mb-3">
            <?php
            echo "<h3>Meno športovca: " . $person["name"] . " " . $person["surname"] . "</h3>";
            ?>
        </div>
        <div class="mb-3">
            <input hidden="hidden" name="personId" value="<?php echo $_GET['id']; ?>">
            <label for="InputPlacing" class="form-label">Umiestnenie:</label>
            <input type="number" min="1" name="placing" class="form-control" id="InputPlacing"
                   value="<?php echo $placement['placing']; ?>" required>
        </div>
        <label for="InputDiscipline" class="form-label">Disciplína:</label>
        <select name="discipline" class="form-select" id="InputDiscipline">
            <?php
            foreach ($disciplines as $disc) {
                echo '<option value="' . $disc['discipline'] . '"';
                if ($placement['discipline'] === $disc['discipline']) {
                    echo "selected";
                }
                echo '>' . $disc['discipline'] . '</option>';
            }
            ?>
        </select>
        <button type="submit" class="btn btn-primary">Uložiť</button>
    </form>
</div>
<script>
    document.getElementById("highlight").classList.remove("start-home");
    document.getElementById("highlight").classList.add("start-admin");
</script>
<?php require_once 'footer.php';?>
</body>
</html>