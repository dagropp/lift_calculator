<?php

// Controller's dependencies.
require_once '../models/Server.php';
require_once '../models/Page.php';
require_once '../models/Admin.php';

use Manage\Server\Server;
use Manage\Page\Page;
use Manage\Admin\Admin;

session_start();

// Controller's redirect paths.
$adminConnect = '../views/admin_connect.php';
$adminScreen = '../views/admin_screen.php';

// If Admin object not set yet, construct it.
if (!isset($_SESSION['admin'])) {
    list('email' => $email, 'password' => $password, 'admin_password' => $adminPassword, 'key' => $key) = $_POST;
    $_SESSION['admin'] = Server::attemptInstance(Admin::class, $adminConnect, $email, $password, $adminPassword, $key);
}
// In any case, update each table and select inputs.
$_SESSION['usersTable'] = $_SESSION['admin']->getUsersTable();
$_SESSION['usersSelect'] = $_SESSION['admin']->getUsersSelect();
$_SESSION['adminsTable'] = $_SESSION['admin']->getUsersTable(true);
$_SESSION['thisAdmin'] = $_SESSION['admin']->getUsersTable(true, true);;
Page::goTo($adminScreen, $_SESSION['msg']); // Go to admin_screen.
