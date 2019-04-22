<?php

// Controller's dependencies.
require_once '../models/Page.php';
require_once '../models/Server.php';
require_once '../models/LogIn.php';
require_once '../models/Cost.php';

use Manage\Page\Page;
use Manage\Server\Server;

session_start();

// Controller's redirect paths.
$loginController = 'login.php';

if (!isset($_POST['tollRoads'])) $_POST['tollRoads'] = array(); // If tollRoads array not set, assign empty array.
// Use Page::parseCost() method to construct Cost object with $_POST data, and create message according to result.
$_SESSION['driveCalc'] = Page::parseCost($_POST, $_SESSION['login'], $_POST['save']);
$msg = $_SESSION['driveCalc'] ? Server::MSG['drive_calc_true'] : Server::MSG['drive_calc_false'] . $_SESSION['msg'];
Page::goTo($loginController, $msg); // Return to login controller to update data after actions performed.
