<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

require_once('config.php');
require_once('partials/navigation.php');

try {
    $db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!isset($_COOKIE['max'])) {
        setcookie("max", 25);
        $results_per_page = 25;
    } else {
        $results_per_page = $_COOKIE["max"];
    }

    $page = isset($_GET['page']) ? $_GET['page'] : $_GET['page'] = 0;

    $query = "SELECT p.id, p.name, p.surname, g.year, g.city, g.type, pl.discipline FROM people p JOIN placement pl ON p.id=pl.person_id JOIN game g ON pl.game_id=g.id WHERE pl.placing=1";

    if (isset($_GET['search'])) {
        $search = $_GET['search'];
        $query .= " AND p.name LIKE '%$search%' OR p.surname LIKE '%$search%' OR g.year LIKE '%$search%' OR g.city LIKE '%$search%' OR g.type LIKE '%$search%' OR pl.discipline LIKE '%$search%'";
    }

    if (isset($_GET['sort'])) {
        if ($_GET['sort'] == 'surname') {
            $query .= " ORDER BY p.surname";
        } elseif ($_GET['sort'] == 'year') {
            $query .= " ORDER BY g.year";
        } elseif ($_GET['sort'] == 'type') {
            $query .= " ORDER BY g.type, g.year";
        }
    }

    $stmt = $db->query($query);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $count = count($results);

    $query .= " LIMIT " . $_GET['page'] * $results_per_page . ", " . $results_per_page;

    $stmt = $db->query($query);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!isset($_COOKIE['count']) || $count != $_COOKIE['count']) {
        setcookie("count", $count);
    } else {
        $count = $_COOKIE["count"];
    }
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
    <title>Domov</title>
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
    <div>
        <input type="text" id="searchField">
        <button onclick="search()">Hľadať</button>
        <button onclick="window.location.assign('/z1/index.php?page=0')">Reset</button>
    </div>
    <table class="table" style="margin-top: 20px">
        <thead>
        <tr>
            <td>Meno</td>
            <td><a href="javascript:sort(1);">Priezvisko</a></td>
            <td><a href="javascript:sort(2);">Rok zisku medaile</a></td>
            <td>Miesto konania</td>
            <td><a href="javascript:sort(3);">Typ OH</a></td>
            <td>Disciplína</td>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($results as $result) {
            $id = $result["id"];
            echo "<tr><td>" . $result["name"] . "</td><td><a href='partials/person.php?id=$id'>" . $result["surname"] . "</a>" . "</td><td>" . $result["year"] . "</td><td>" . $result["city"] . "</td><td>" . $result["type"] . "</td><td>" . $result["discipline"] . "</td></tr>";
        }
        ?>
        </tbody>
    </table>
    <div style="display: flex; flex-direction: row">
        <button onclick="prev(<?php echo $count . ", " . $_GET['page'] . ", " . $results_per_page; ?>)" style="width: 5rem; border-radius: 0.8rem; margin-right: auto">&#8592;</button>
        <form action="" method="POST">
            <label for="pagination">Počet záznamov na stranu:</label>
            <select name="pagination" id="pagination">
                <option value="10">10</option>
                <option value="25" selected>25</option>
                <option value="50">50</option>
                <option value="100">100</option>
                <option value="500">500</option>
            </select>
            <input type='submit' name='submit' value="Potvrdiť">
        </form>
        <?php
        if (isset($_POST['submit'])) {
            $max = $_POST['pagination'];
            echo "<script>document.cookie = 'max=$max';window.location.assign(window.location)</script>";
        }
        ?>
        <button onclick="next(<?php echo $count . ", " . $_GET['page'] . ", " . $results_per_page; ?>)" style="width: 5rem; border-radius: 0.8rem; margin-left: auto">&#8594;</button>
    </div>
</div>
<?php require_once 'partials/footer.php';?>
</body>
</html>
