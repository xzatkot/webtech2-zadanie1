<?php
// Konfiguracia PDO
require_once('config.php');
$db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
require_once('partials/navigation.php');
// Kniznica pre 2FA
require_once 'PHPGangsta/GoogleAuthenticator.php';

// ------- Pomocne funkcie -------
function checkEmpty($field)
{
    // Funkcia pre kontrolu, ci je premenna po orezani bielych znakov prazdna.
    // Metoda trim() oreze a odstrani medzery, tabulatory a ine "whitespaces".
    if (empty(trim($field))) {
        return true;
    }
    return false;
}

function checkLength($field, $min, $max)
{
    // Funkcia, ktora skontroluje, ci je dlzka retazca v ramci "min" a "max".
    // Pouzitie napr. pre "login" alebo "password" aby mali pozadovany pocet znakov.
    $string = trim($field);     // Odstranenie whitespaces.
    $length = strlen($string);      // Zistenie dlzky retazca.
    if ($length < $min || $length > $max) {
        return false;
    }
    return true;
}

function checkUsername($username)
{
    // Funkcia pre kontrolu, ci username obsahuje iba velke, male pismena, cisla a podtrznik.
    if (!preg_match('/^[a-zA-Z0-9_]+$/', trim($username))) {
        return false;
    }
    return true;
}

function checkGmail($email)
{
    // Funkcia pre kontrolu, ci zadany email je gmail.
    if (!preg_match('/^[\w.+\-]+@gmail\.com$/', trim($email))) {
        return false;
    }
    return true;
}

function userExist($db, $login, $email)
{
    // Funkcia pre kontrolu, ci pouzivatel s "login" alebo "email" existuje.
    $exist = false;

    $param_login = trim($login);
    $param_email = trim($email);

    $sql = "SELECT id FROM users WHERE login = :login OR email = :email";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(":login", $param_login, PDO::PARAM_STR);
    $stmt->bindParam(":email", $param_email, PDO::PARAM_STR);

    $stmt->execute();

    if ($stmt->rowCount() == 1) {
        $exist = true;
    }

    unset($stmt);

    return $exist;
}

// ------- ------- ------- -------


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errmsg = "";

    // Validacia username
    if (checkEmpty($_POST['login']) === true) {
        $errmsg .= "<p>Zadajte login.</p>";
    } elseif (checkLength($_POST['login'], 6, 32) === false) {
        $errmsg .= "<p>Login musí mať min. 6 a max. 32 znakov.</p>";
    } elseif (checkUsername($_POST['login']) === false) {
        $errmsg .= "<p>Login môže obsahovať iba veľké, malé písmená, číslice a podtržník.</p>";
    }

    // Kontrola pouzivatela
    if (userExist($db, $_POST['login'], $_POST['email']) === true) {
        $errmsg .= "Používateľ s týmto e-mailom / loginom už existuje.</p>";
    }

    // Validacia mailu
    if (checkGmail($_POST['email'])) {
        echo '<script>alert("Prihláste sa pomocou Google prihlásenia!");</script>';
        // Ak pouziva google mail, presmerujem ho na prihlasenie cez Google.
         header("Location: login.php");
    }

    // TODO: Validacia hesla
    if (checkEmpty($_POST['password']) === true) {
        $errmsg .= "<p>Zvoľte si heslo.</p>";
    } elseif (checkLength($_POST['password'], 6, 32) === false) {
        $errmsg .= "<p>Heslo musí mať min. 6 a max. 32 znakov.</p>";
    } elseif (checkUsername($_POST['password']) === false) {
        $errmsg .= "<p>Heslo môže obsahovať iba veľké, malé písmená, číslice a podtržník.</p>";
    }

    // TODO: Validacia mena, priezviska
    if (checkEmpty($_POST['firstname']) === true) {
        $errmsg .= "<p>Zadajte meno.</p>";
    }
    if (checkEmpty($_POST['lastname']) === true) {
        $errmsg .= "<p>Zadajte priezvisko.</p>";
    }

    if (empty($errmsg)) {
        $sql = "INSERT INTO users (fullname, login, email, password, 2fa_code) VALUES (:fullname, :login, :email, :password, :2fa_code)";

        $fullname = $_POST['firstname'] . ' ' . $_POST['lastname'];
        $email = $_POST['email'];
        $login = $_POST['login'];
        $hashed_password = password_hash($_POST['password'], PASSWORD_ARGON2ID);

        // 2FA pomocou PHPGangsta kniznice: https://github.com/PHPGangsta/GoogleAuthenticator
        $g2fa = new PHPGangsta_GoogleAuthenticator();
        $user_secret = $g2fa->createSecret();
        $codeURL = $g2fa->getQRCodeGoogleUrl('Olympic Games', $user_secret);

        // Bind parametrov do SQL
        $stmt = $db->prepare($sql);

        $stmt->bindParam(":fullname", $fullname, PDO::PARAM_STR);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->bindParam(":login", $login, PDO::PARAM_STR);
        $stmt->bindParam(":password", $hashed_password, PDO::PARAM_STR);
        $stmt->bindParam(":2fa_code", $user_secret, PDO::PARAM_STR);

        if ($stmt->execute()) {
            // qrcode je premenna, ktora sa vykresli vo formulari v HTML.
            $qrcode = $codeURL;
        } else {
            echo '<script>alert("Ups. Niečo sa pokazilo!");</script>';
        }
        unset($stmt);
    }
    unset($db);
}

?>

<!doctype html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Registrácia</title>
    <link rel="stylesheet" href="res/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN"
            crossorigin="anonymous"></script>
</head>
<body>
<div class="container-md" style="text-align: center">
    <h1>Registrácia</h1>
    <h2>Vytvorenie nového konta používateľa</h2>
    <main>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <label for="firstname">
                Meno:
                <input type="text" name="firstname" value="" id="firstname" placeholder="napr. Jonatan" required>
            </label>

            <label for="lastname">
                Priezvisko:
                <input type="text" name="lastname" value="" id="lastname" placeholder="napr. Petrzlen" required>
            </label>

            <br>

            <label for="email">
                E-mail:
                <input type="email" name="email" value="" id="email" placeholder="napr. jpetrzlen@example.com" required>
            </label>

            <label for="login">
                Login:
                <input type="text" name="login" value="" id="login" placeholder="napr. jperasin" required">
            </label>

            <br>

            <label for="password">
                Heslo:
                <input type="password" name="password" value="" id="password" required>
            </label>

            <button type="submit">Vytvoriť konto</button>

            <?php
            if (!empty($errmsg)) {
                // Tu vypis chybne vyplnene polia formulara.
                echo $errmsg;
            }
            if (isset($qrcode)) {
                // Pokial bol vygenerovany QR kod po uspesnej registracii, zobraz ho.
                $message = '<p>Naskenujte QR kód do aplikácie Authenticator pre 2FA: <br><img src="' . $qrcode . '" alt="qr kód pre aplikáciu authenticator"></p>';

                echo $message;
                echo '<p>Teraz sa môžete prihlásiť: <a href="login.php" role="button">Login</a></p>';
            }
            ?>

        </form>
        <p>Máte vytvorené konto? <a href="login.php">Prihláste sa tu.</a></p>
    </main>
</div>
<script>
    document.getElementById("highlight").classList.remove("start-home");
    document.getElementById("highlight").classList.add("start-login");
</script>
<?php require_once 'partials/footer.php';?>
</body>
</html>