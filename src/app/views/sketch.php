<?php

require_once '../models/LogIn.php';
require_once '../models/SignUp.php';
require_once '../models/Car.php';
require_once '../models/Cost.php';
require_once '../models/Server.php';
require_once '../models/User.php';
require_once '../models/Page.php';
require_once '../models/EditProfile.php';
require_once '../models/Admin.php';
require_once '../models/UpdatePrices.php';
require_once '../models/CarNew.php';

use User\Connect\LogIn\LogIn;
use User\Connect\SignUp\SignUp;
use Drive\Car\Car;
use Drive\Cost\Cost;
use User\Main\User;
use Manage\Server\Server;
use Manage\Page\Page;
use User\Manage\EditProfile\EditProfile;
use Manage\Admin\Admin;
use Manage\UpdatePrices\UpdatePrices;
use Manage\CarNew\CarNew;

header("Content-Type: text/html;charset=UTF-8");

const SPACER = '<br>';

function log_in($email, $password)
{
    try {
        $login = new LogIn($email, $password);
        echo 'LogIn creation success.' . SPACER;
        echo $login;
        $login->getDrivesList();
    } catch (Exception $e) {
        echo 'LogIn creation failed. ' . $e->getMessage();
    }
}

function car_cost($origin, $destination, $distance, $duration, $p_num, $division, $toll_roads, $save = false)
{
    try {
        $profile = new LogIn('amosgropp@gmail.com', 'X5rrcfbr');
        $cost = new Cost($profile, $origin, $destination, $distance, $duration, $p_num, $division, $toll_roads, $save);
        echo 'Cost calculation success' . SPACER;
        echo $cost . SPACER;
    } catch (Exception $e) {
        echo 'Cost calculation failed. ' . $e->getMessage();
    }
}

function car($years_id = null)
{
    try {
        $car = new Car($years_id);
        echo 'Car creation success' . SPACER;
        echo $car;
        $car->createCarsJSON();
    } catch (Exception $e) {
        echo 'Car creation failed: ' . $e->getMessage();
    }
}

function sign_up($email, $password, $first_name, $last_name, $phone_num, $c_company, $c_model, $c_years)
{
    try {
        $signup = new SignUp($email, $password, $first_name, $last_name, $phone_num, $c_company, $c_model, $c_years);
        var_dump($signup->getUserId());
        echo 'User creation success.' . SPACER;
        echo $signup . SPACER;
    } catch (Exception $e) {
        echo 'User Sign Up failed. ' . $e->getMessage();
    }
}

//log_in('amosgropp@gmail.com', 'X5rrcfbr');
// car_cost('ירושלים', 'חיפה', 138.6, 4, false, [6, 23], true);
// car();
//sign_up('amnon.levi@gmail.com', 'X5rrcfbr', 'אמנון', 'לוי', '0544438336', 'Daihatsu', 'Sirion', '2006-2012');
// db_tests();

//echo Cost::gasPrice('electric');
function insertGasPriceDB()
{
    $date = new DateTime('now');
    $old = new DateTime('2019-02-01');
    $difference = $date->diff($old);
    var_dump($difference->format('%a'));
    $try = new DateTime('now');
    var_dump($date);
}

session_start();

//$admin = Server::attemptInstance(Admin::class, null, 'dgropp@gmail.com', 'X5rrcfbr', '8q4ppr87', 'daniel');
//$a = $admin->createAdmin($login, '8q4ppr87', 'daniel');
//var_dump($admin);
//echo $admin;

//$id = '14';
//$field = 'עמוס';
//$email = 'dgropp@gmail.com';
//$which = 'first_name';
//$db = Server::connectToDB(Server::USER_DB);
//$sql = "UPDATE users SET $which = ? WHERE user_id = ?";
//$sql2 = "SELECT * FROM users WHERE user_id = ?";
//$res = statementSQL($db, $sql, 'si', $field, $id);
//$res2 = statementSQL($db, $sql2, 'i', $id);
//var_dump($res, $res2);
//var_dump($res2['result']->fetch_all());
//
//function statementSQL(mysqli $db, string $sql, string $types, ...$params): ?stdClass
//{
//    $statement = $db->prepare($sql);
//    $statement->bind_param($types, ...$params);
//    $statement->execute();
//    $results = $statement->get_result();
//    if (!$statement->affected_rows)
//        return null;
//    return (object)[
//        'execStatus' => boolval($statement->affected_rows),
//        'resultStatus' => $results ? boolval($results->num_rows) : null,
//        'results' => $results ? $results->fetch_all(MYSQLI_ASSOC) : null
//    ];
//}
//$admin = Server::attemptInstance(Admin::class, null, 'dgropp@gmail.com', 'X5rrcfbr', '8q4ppr87', 'daniel');
//$sql = "SELECT user_id, email, first_name, last_name, phone_num, car_years_id
//            FROM users
//            WHERE admin_password IS NULL AND user_id = ?
//            ORDER BY last_name, first_name";
//var_dump('rockin\' like a hurricane', Server::secureStatement($db, $sql, false, 'i', 11));
