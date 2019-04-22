<?php

// Controller's dependencies.
require_once '../models/Page.php';
require_once '../models/Server.php';
require_once '../models/LogIn.php';
require_once '../models/EditProfile.php';

use Manage\Page\Page;
use Manage\Server\Server;
use User\Manage\EditProfile\EditProfile;

session_start();

// Controller's redirect paths.
$loginController = 'login.php';

$drivesList = $_SESSION['drivesList'];
// 'Submit Saved Drive' button submitted.
if (isset($_POST['submitSavedDrive'])) {
    // Use Page::parseCost() method to construct Cost object with $_POST data, and create message according to result.
    $_SESSION['driveCalc'] = Page::parseCost($drivesList[$_POST['drives']], $_SESSION['login']);
    $msg = $_SESSION['driveCalc'] ? 'הנסיעה חושבה בהצלחה.' : 'לא ניתן היה לחשב את הנסיעה.';
}
// 'Delete Drive' button submitted.
if (isset($_POST['deleteDrive'])) {
    $driveID = intval($drivesList[$_POST['drives']]['driveID']);
    $email = $_SESSION['profile']['email'];
    // Attempt to construct EditProfile object.
    $edit = Server::attemptInstance(EditProfile::class, null, $email, $_SESSION['login']);
    $delete = $edit->deleteDrive($driveID); // Call EditProfile method that deletes drive and set message with result.
    $_SESSION['driveCalc'] = null;
    $msg = $delete ? "נסיעה $driveID נמחקה בהצלחה" : "לא ניתן היה למחוק את נסיעה $driveID";
}
Page::goTo($loginController, $msg); // Return to login controller to update data after actions performed.