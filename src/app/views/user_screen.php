<?php
require_once '../models/Page.php';
require_once '../models/LogIn.php';

use Manage\Page\Page;

session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <script>
        let CostObj = <?= isset($_SESSION['cost']) ? json_encode($_SESSION['cost']) : 'undefined' ?>;
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD9BbzQz7f9iADHjsvjtaLlWzB6WnFrfcg&language=he&region=IL&libraries=places&callback=initMap"
            async defer></script>
    <script src="../../private/packages/jquery-3.3.1.min.js"></script>
    <script src="../../../public/js/prototypes/maps_api.js"></script>
    <script src="../../../public/js/DOM/user_screen.js"></script>
    <script src="../../../public/js/prototypes/input_handler.js"></script>
    <script src="../../../public/js/input/drive_new.js"></script>
    <script src="../../../public/js/prototypes/drive_message.js"></script>
    <script src="../../../public/js/prototypes/time_handler.js"></script>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css"
          integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    <link rel="stylesheet" href="../../../public/styles/main.css">
    <title>הפרופיל של <?= $_SESSION['profile']['name'] ?></title>
</head>
<body dir="rtl">
<div class="container">
    <header>
        <h1>הפרופיל של <?= $_SESSION['profile']['name'] ?></h1>
    </header>
    <section class="show"><?= ($_SESSION['drivesList']) ? $_SESSION['drivesListSelect'] : '' ?></section>
    <h2 class="drop_down">נסיעה חדשה</h2>
    <section class="hide">
        <input type="text" id="origin" placeholder="מוצא">
        <p style="display:inline"></p><br>
        <input type="text" id="destination" placeholder="יעד">
        <p style="display:inline"></p><br>
        <form method="post" action="../controllers/calculate_new_drive.php" id="new_drive_form">
            <input type="hidden" name="origin" id="originH">
            <input type="hidden" name="destination" id="destinationH">
            <input type="hidden" name="distance" id="distance">
            <input type="hidden" name="duration" id="duration">
            <label for="passNum">מס' הנוסעים:</label>
            <select name="passNum" id="passNum"></select><br>
            <input type="hidden" name="equalDivision" value="0">
            <input type="checkbox" name="equalDivision" value="1" checked>
            <label for="equalDivision">חלוקה שווה בין הנוסעים</label><br>
            <input type="checkbox" name="tollRoads[]" id="road6" value="6 צפון">
            <label for="road6">כביש 6 צפון</label>
            <input type="checkbox" name="tollRoads[]" id="road6y" value="6 יקנעם">
            <label for="road6y">כביש 6 יקנעם</label>
            <input type="checkbox" name="tollRoads[]" id="road23" value="מנהרות הכרמל (חלקי)">
            <label for="road23">מנהרות הכרמל (מקטע 1)</label><br>
            <input type="hidden" name="save" value="0">
            <input type="checkbox" name="save" value="1" checked>
            <label for="save">שמירת הנסיעה</label><br>
            <button type="button" id="submitForm" disabled>חישוב מחיר</button>
        </form>
    </section>
    <?php
    if (isset($_SESSION['driveCalc'])) {
        $calculation = $_SESSION['driveCalc'];
        echo "
            <h2 class='drop_down'>תוצאות חישוב הנסיעה</h2>
            <section class='hide'>
            <p>$calculation</p>
            </section>
            ";
    }
    ?>
    <h2 class="drop_down">יצירת הודעה</h2>
    <section id="driveMsg" hidden class="hide">
        <form>
            <label for="depDate">תאריך היציאה</label>
            <input type="date" id="depDate">
            <label for="depTime">שעת היציאה</label>
            <select id="depTime"></select>
            <label for="msgType">אופי ההודעה</label>
            <select id="msgType">
                <option value="0">רגילה</option>
                <option value="1">קלילה</option>
                <option value="2">רשמית</option>
            </select>
            <button type="button">יצירת הודעה</button>
        </form>
        <p id="msgDisplay"></p>
    </section>
    <p style="color: red; font-weight: bold"><?php Page::printMsg() ?></p>
    <footer>
        <p><a href="edit_profile.php">עריכת הפרופיל</a></p>
        <form method="post" action="../controllers/logout.php">
            <button>התנתקות</button>
        </form>
    </footer>
</div>
</body>
</html>