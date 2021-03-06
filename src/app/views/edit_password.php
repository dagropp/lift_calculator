<?php
require_once '../models/Page.php';
require_once '../models/Car.php';

use Manage\Page\Page;

session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <title>עריכת הסיסמה של <?= $_SESSION['profile']['name'] ?></title>
    <script src="../../private/packages/jquery-3.3.1.min.js"></script>
    <script src="../../../public/js/prototypes/input_handler.js"></script>
    <script src="../../../public/js/input/edit_password.js"></script>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css"
          integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
</head>
<body dir="rtl">
<div class="container">
    <header>
        <h1>עריכת הסיסמה של <?= $_SESSION['profile']['name'] ?></h1>
    </header>
    <section>
        <form action="../controllers/edit_password.php" method="post">
            <input type="password" name="password[]" id="passwordOld" placeholder="סיסמה ישנה">
            <p style="display:inline"></p><br>
            <input type="password" name="password[]" id="password" placeholder="סיסמה חדשה">
            <p style="display:inline"></p><br>
            <input type="password" name="password[]" id="passwordConfirm" placeholder="אישור סיסמה" disabled>
            <p style="display:inline"></p><br>
            <button type="submit" id="submitForm" disabled>שמירת שינויים</button>
        </form>
        <p style="color: red; font-weight: bold"><?php Page::printMsg() ?></p>
    </section>
</div>
</body>
</html>