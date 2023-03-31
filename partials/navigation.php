<?php
session_start();

echo '<link href="../res/style.css" rel="stylesheet">
<nav>
    <a href="/z1/index.php?page=0" id="home">Domov</a>
    <a href="/z1/partials/top10.php" id="top10">TOP 10</a>
    <a href="/z1/login.php" id="login">Prihlásenie</a>
    <a href="/z1/partials/admin.php" id="login">Admin</a>
    <div id="highlight" class="animation start-home"></div>
</nav>';
?>

<p class="login-info">
    Prihlásený používateľ: <a href="/z1/login.php"><?php if(isset($_SESSION['email'])){echo $_SESSION['email'];}else{echo 'Neprihlásený';}?></a></p>
</p>
