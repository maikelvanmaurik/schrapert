<?php
session_start();
if($_SERVER['REQUEST_METHOD'] == 'POST') {

    $username = $_POST['username'];
    $password = $_POST['password'];

    $_SESSION['authorized'] = $username == 'schrapert' && $password == strrev('schrapert');

    if($_SESSION['authorized']) {
        header('Location: restricted.php');
        exit;
    }
}
?>
<html>
    <head>

    </head>
    <body>
        <form method="POST">
            <input type="text" name="username" />
            <input type="text" name="password" />
            <button type="submit">Login</button>
        </form>
    </body>
</html>