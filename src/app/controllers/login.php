<?php

// Controller's dependencies.
require_once '../models/Server.php';
require_once '../models/Page.php';
require_once '../models/LogIn.php';
require_once '../models/UpdatePrices.php';

use Manage\Server\Server;
use Manage\Page\Page;
use User\Connect\LogIn\LogIn;
use Manage\UpdatePrices\UpdatePrices;

session_start();

// Controller's redirect paths.
$indexPath = '../views/index.php';
$userScreen = '../views/user_screen.php';

// If LogIn object not set yet, construct it and UpdatePrices class.
if (!isset($_SESSION['login'])) {
    $_SESSION['login'] = Server::attemptInstance(LogIn::class, $indexPath, $_POST['email'], $_POST['password']);
    Server::attemptInstance(UpdatePrices::class, null);
}
// In any case, update drives select and user's profile details.
$_SESSION['drivesList'] = $_SESSION['login']->getDrivesList();
$_SESSION['drivesListSelect'] = Page::createDrivesSelect($_SESSION['drivesList']);
$_SESSION['profile'] = $_SESSION['login']->getProfile();
$_SESSION['profile']['name'] = $_SESSION['profile']['first_name'] . " " . $_SESSION['profile']['last_name'];
// Go to user_screen.
Page::goTo($userScreen, $_SESSION['msg']);
