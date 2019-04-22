<?php

// Controller's dependencies
require_once '../models/Server.php';
require_once '../models/Page.php';
require_once '../models/Admin.php';
require_once '../models/CarNew.php';

use Manage\Server\Server;
use Manage\Page\Page;
use Manage\CarNew\CarNew;

session_start();

// Controller's redirect paths.
$adminScreen = '../views/admin_screen.php';
$adminController = 'admin_connect.php';

// 'Delete User' button submitted.
if (isset($_POST['delete_user'])) {
    $id = filterAndMap($_POST['users_details']);
    // Call Admin method to delete specified users.
    $_SESSION['admin']->deleteUser(...$id);
    $msg = Server::MSG['delete_profile_true'];
}
// 'Add Admin' button submitted.
if (isset($_POST['add_admin'])) {
    $id = intval($_POST['user_for_admin']);
    // Attempt Admin method to give admin privileges to user.
    Server::attemptMethod(
        $_SESSION['admin'], 'createAdmin', $adminScreen, $_POST['admin_password'], $_POST['admin_key'], $id);
    $msg = Server::MSG['admin_add_true'];
}
// 'Remove Admin' button submitted.
if (isset($_POST['remove_admin'])) {
    $id = filterAndMap($_POST['admin_details']);
    // Attempt Admin method that removes admin privileges from user.
    Server::attemptMethod($_SESSION['admin'], 'removeAdmin', $adminScreen, ...$id);
    $msg = Server::MSG['admin_remove_true'];
}
// 'Add Car' button submitted.
if (isset($_POST['add_car'])) {
    if (!isset($_POST['car']['gasType'])) $_POST['car']['gasType'] = null; // If gasType not set, assign null.
    list('company' => $company, 'model' => $model, 'years' => $years, 'kpl' => $kpl, 'gasType' => $gasType)
        = $_POST['car'];
    // Reformat and convert vars from mySQL data types to PHP data types.
    $temp = array_filter($model, function ($val) {
        return $val && $val != 'הרכב';
    });
    $model = $temp[array_key_last($temp)];
    $kpl = floatval($kpl);
    // Attempt to construct CarNew object to insert car to data-base.
    Server::attemptInstance(CarNew::class, $adminScreen, $company, $model, $years, $kpl, $gasType);
    $msg = Server::MSG['add_car_true'];
}
Page::goTo($adminController, $msg); // Return to admin_connect controller to update data after actions performed.

/**
 * Filter array to include only numbers and convert them to int type.
 * @param array $arr - array to manipulate.
 * @return array|null - mapped array.
 */
function filterAndMap(array $arr): ?array
{
    $filter = array_filter($arr, 'is_numeric');
    return array_map('intval', $filter);
}
