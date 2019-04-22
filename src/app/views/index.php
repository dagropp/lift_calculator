<?php
require_once '../models/Page.php';
require_once '../models/LogIn.php';

use Manage\Page\Page;
use User\Connect\LogIn\LogIn;

session_start();

if (isset($_SESSION['login'])) Page::goTo('user_screen.php');
if (!isset($_SESSION['msg'])) Page::setMsg();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <title>Log In</title>
    <script src="../../private/packages/jquery-3.3.1.min.js"></script>
    <script src="../../../public/js/prototypes/input_handler.js"></script>
    <script src="../../../public/js/input/user_connect.js"></script>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css"
          integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
</head>
<body dir="rtl">
<div class="container">
    <section>
        <h1>התחברות</h1>
        <form action="../controllers/login.php" method="post">
            <input type="email" name="email" id="email" placeholder="דואר אלקטרוני">
            <p style="display:inline"></p><br>
            <input type="password" name="password" id="password" placeholder="סיסמה">
            <p style="display:inline"></p><br>
            <button type="submit" id="submitForm" disabled>התחברות</button>
            <button type="reset">אתחול</button>
        </form>
        <p style="color: red; font-weight: bold"><?php Page::printMsg() ?></p>
        <p>אין לך חשבון? <a href="signup_screen.php">ניתן להירשם כאן</a></p>
        <p><a href="admin_connect.php">כניסת ניהול</a></p>
        <?= "Attempts: " . LogIn::$attempts ?>
    </section>
</div>
</body>
</html>