<?php
require_once '../models/Page.php';
require_once '../models/Admin.php';
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
    <title>מסך ניהול</title>
    <script src="../../private/packages/jquery-3.3.1.min.js"></script>
    <script src="../../../public/js/DOM/car_select.js"></script>
    <script src="../../../public/js/prototypes/input_handler.js"></script>
    <script src="../../../public/js/input/car_new.js"></script>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css"
          integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
</head>
<body dir="rtl">
<div class="container">
    <header><h1>מסך ניהול</h1></header>
    <section>
        <h2>ניהול משתמשים</h2>
        <h3>מנהל זה</h3>
        <?= $_SESSION['thisAdmin'] ?>
        <h3>משתמשים נוספים בעלי הרשאות ניהול</h3>
        <form action="../controllers/admin_actions.php" method="post">
            <?= $_SESSION['adminsTable'] ?><br>
            <button type="submit" name="remove_admin">הסרת ניהול</button>
            <button type="submit" name="send_email">שליחת דואר למנהל</button>
        </form>
        <h3>מאגר משתמשים</h3>
        <form action="../controllers/admin_actions.php" method="post">
            <?= $_SESSION['usersTable'] ?><br>
            <button type="submit" name="delete_user">מחיקת משתמש</button>
            <button type="submit" name="send_email">שליחת דואר למשתמש</button>
        </form>
        <h3>מתן הרשאות ניהול</h3>
        <form action="../controllers/admin_actions.php" method="post">
            <?= $_SESSION['usersSelect'] ?>
            <input type="text" name="admin_password" placeholder="סיסמת ניהול">
            <input type="text" name="admin_key" placeholder="מפתח הצפנה">
            <button type="submit" name="add_admin">הפיכה למנהל</button>
        </form>
        <p style="color: red; font-weight: bold"><?php Page::printMsg() ?></p>
        <form action="../controllers/admin_actions.php" method="post">
            <h2>הוספת רכב</h2>
            <button type="reset" class="newCarFields" disabled>הוספת דגם חדש</button>
            <button type="reset" class="newCarFields">הוספת שנת ייצור לדגם</button>
            <br>
            <select name="car[company]" id="carCompany"></select>
            <p style="display:inline"></p><br>
            <input type="text" name="car[model][]" id="carModelNew">
            <p style="display:inline"></p>
            <select name="car[model][]" id="carModel" hidden></select>
            <p style="display:inline"></p><br>
            <input type="text" name="car[years]" id="carYearsNew" maxlength="9">
            <p style="display:inline"></p><br>
            <select name="car[gasType]" id="carGasType">
                <option value=0>בחירת סוג הדלק</option>
                <option value="petrol">בנזין</option>
                <option value="diesel">סולר</option>
                <option value="electric">חשמל</option>
            </select>
            <p style="display:inline"></p><br>
            <input type="text" name="car[kpl]" id="carKPL" placeholder='צריכת דלק (ק"מ / ליטר)'>
            <p style="display:inline"></p><br>
            <button type="submit" name="add_car" id="addCar">הוספת רכב</button>
        </form>
        <form method="post" action="../controllers/logout.php">
            <button>התנתקות</button>
        </form>
    </section>
</div>
</body>
</html>