<?php
session_start();
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

require_once 'config.php';
require_once('partials/navigation.php');
require_once 'vendor/autoload.php';
require_once 'PHPGangsta/GoogleAuthenticator.php';

if ((isset($_SESSION['access_token']) && $_SESSION['access_token']) || (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true)) {
    header('Location: restricted.php');
}

// Inicializacia Google API klienta
$client = new Google\Client();

// Definica konfiguracneho JSON suboru pre autentifikaciu klienta.
// Subor sa stiahne z Google Cloud Console v zalozke Credentials.
$client->setAuthConfig('client_secret.json');

// Nastavenie URI, na ktoru Google server presmeruje poziadavku po uspesnej autentifikacii.
$redirect_uri = "https://site250.webte.fei.stuba.sk/z1/redirect.php";
$client->setRedirectUri($redirect_uri);

// Definovanie Scopes - rozsah dat, ktore pozadujeme od pouzivatela z jeho Google uctu.
$client->addScope("email");
$client->addScope("profile");

// Vytvorenie URL pre autentifikaciu na Google server - odkaz na Google prihlasenie.
$auth_url = $client->createAuthUrl();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // TODO: Skontrolovat ci login a password su zadane (podobne ako v register.php).

    $sql = "SELECT fullname, email, login, password, created_at, 2fa_code FROM users WHERE login = :login OR email = :login";

    $stmt = $db->prepare($sql);

    // TODO: Upravit SQL tak, aby mohol pouzivatel pri logine zadat login aj email.
    $stmt->bindParam(":login", $_POST["login"], PDO::PARAM_STR);
    $stmt->bindParam(":email", $_POST["login"], PDO::PARAM_STR);

    if ($stmt->execute()) {
        if ($stmt->rowCount() == 1) {
            // Uzivatel existuje, skontroluj heslo.
            $row = $stmt->fetch();
            $hashed_password = $row["password"];

            if (password_verify($_POST['password'], $hashed_password)) {
                // Heslo je spravne.
                $g2fa = new PHPGangsta_GoogleAuthenticator();
                if ($g2fa->verifyCode($row["2fa_code"], $_POST['2fa'], 2)) {
                    // Heslo aj kod su spravne, pouzivatel autentifikovany.

                    // Uloz data pouzivatela do session.
                    $_SESSION["loggedin"] = true;
                    $_SESSION["login"] = $row['login'];
                    $_SESSION["fullname"] = $row['fullname'];
                    $_SESSION["email"] = $row['email'];
                    $_SESSION["created_at"] = $row['created_at'];

                    $login = $row['login'];
                    $query = "INSERT INTO login (user_login, method) VALUES ('$login','2FA')";
                    $stmt = $db->prepare($query);
                    $stmt->execute();

                    // Presmeruj pouzivatela na zabezpecenu stranku.
                    header("location: restricted.php");
                } else {
                    echo '<script>alert("Neplatný kód 2FA!");</script>';
                }
            } else {
                echo '<script>alert("Nesprávne meno alebo heslo!");</script>';
            }
        } else {
            echo '<script>alert("Nesprávne meno alebo heslo!");</script>';
        }
    } else {
        echo '<script>alert("Ups. Niečo sa pokazilo!");</script>';
    }

    unset($stmt);
    unset($pdo);
}
?>

<!doctype html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Prihlásenie</title>
    <link href="res/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <link href="https://unpkg.com/@picocss/pico@1.*/css/pico.min.css"rel="stylesheet" >
</head>
<body>
<div class="container-sm" style="text-align: center; width: 30%">
    <h1>Prihlásenie používateľa</h1>
    <?php
    if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
        var_dump($_SESSION);
        echo '<h3>Vitaj ' . $_SESSION['name'] . '</h3>';
        echo '<p>Si prihlásený ako: ' . $_SESSION['email'] . '</p>';
        echo '<p><a role="button" href="restricted.php">Zabezpečená stránka</a>';
        echo '<a role="button" class="secondary" href="logout.php">Odhlás ma</a></p>';
    } elseif ((isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true)) {
        var_dump($_SESSION);
        echo '<h3>Vitaj ' . $_SESSION['fullname'] . ' </h3>';
        echo '<a href="restricted.php">Zabezpečená stránka</a>';
    }
    else {
    echo '<a role="button" style="margin-bottom: 40px" href="' . filter_var($auth_url, FILTER_SANITIZE_URL) . '">Google prihlásenie</a>';
    echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF']) . '" method="post">' . '<label for="login">
        Prihlasovacie meno:
            <input type="text" name="login" value="" id="login" required>
        </label>
        <br>
        <label for="password">
        Heslo:
            <input type="password" name="password" value="" id="password" required>
        </label>
        <br>
        <label for="2fa">
        2FA kód:
            <input type="number" name="2fa" value="" id="2fa" required>
        </label>

        <button type="submit">Prihlasit sa</button>
    </form>
    <p>Ešte nemáte vytvorené konto? <a href="register.php">Registrujte sa tu.</a></p>';
    }
    ?>
</div>
<script>
    document.getElementById("highlight").classList.remove("start-home");
    document.getElementById("highlight").classList.add("start-login");
</script>
<?php require_once 'partials/footer.php';?>
</body>
</html>