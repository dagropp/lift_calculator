<?php
require_once '../models/Page.php';

use Manage\Page\Page;

session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD9BbzQz7f9iADHjsvjtaLlWzB6WnFrfcg&libraries=places&callback=initMap"
            async defer></script>
    <script src="../../private/packages/jquery-3.3.1.min.js"></script>
    <script src="../../../public/js/prototypes/input_handler.js"></script>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css"
          integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    <title>כניסה למסך ניהול</title>
</head>
<body dir="rtl">
<div class="container">
    <section>
        <h1>כניסה למסך ניהול</h1>
        <form action="../controllers/admin_connect.php" method="post" autocomplete="off">
            <input type="email" name="email" id="email" placeholder="דואר אלקטרוני">
            <p style="display:inline"></p><br>
            <input type="password" name="password" id="password" placeholder="סיסמת חשבון">
            <p style="display:inline"></p><br>
            <input type="password" name="admin_password" id="passwordAdmin" placeholder="סיסמת ניהול">
            <p style="display:inline"></p><br>
            <input type="password" name="key" id="aesKey" placeholder="מפתח הצפנה">
            <p style="display:inline"></p><br>
            <button type="submit" id="submitForm">התחברות</button>
        </form>
        <p style="color: red; font-weight: bold"><?php Page::printMsg() ?></p>
    </section>
</div>
</body>
</html>