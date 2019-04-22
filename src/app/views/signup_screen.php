<?php
require_once '../models/Page.php';
require_once '../models/Car.php';

use Manage\Page\Page;
use Drive\Car\Car;

session_start();

Car::createCarsJSON();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <title>Sign Up now</title>
    <script src="../../private/packages/jquery-3.3.1.min.js"></script>
    <script src="../../../public/js/DOM/car_select.js"></script>
    <script src="../../../public/js/prototypes/input_handler.js"></script>
    <script src="../../../public/js/input/user_details.js"></script>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css"
          integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    <style>

    </style>
</head>
<body dir="rtl">
<div class="container">
    <header>
        <h1>הרשמה</h1>
    </header>
    <section>
        <form action="../controllers/signup.php" method="post">
            <input type="email" name="email" id="email" placeholder="דואר אלקטרוני">
            <p style="display:inline"></p><br>
            <input type="password" name="password" id="password" placeholder="סיסמה">
            <p style="display:inline"></p><br>
            <input type="password" name="password_confirm" id="passwordConfirm" placeholder="אישור סיסמה"
                   disabled>
            <p style="display:inline"></p><br>
            <input type="text" name="first_name" id="firstName" placeholder="שם פרטי">
            <p style="display:inline"></p><br>
            <input type="text" name="last_name" id="lastName" placeholder="שם משפחה">
            <p style="display:inline"></p><br>
            <input type="text" name="phone_num" id="phoneNum" maxlength="10" placeholder="מספר טלפון">
            <p style="display:inline"></p><br>
            <h3>מכונית</h3>
            <select name="c_company" id="carCompany"></select><br>
            <select name="c_model" id="carModel"></select><br>
            <select name="c_years" id="carYears"></select>
            <p style="display:inline"></p><br>
            <button type="submit" id="submitForm" disabled>הרשמה</button>
            <button type="reset" id="reset">אתחול</button>
        </form>
        <p style="color: red; font-weight: bold"><?php Page::printMsg() ?></p>
    </section>
</div>
</body>
</html>