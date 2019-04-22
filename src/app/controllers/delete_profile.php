<?php

// Controller's dependencies.
require_once '../models/Server.php';
require_once '../models/Page.php';
require_once '../models/EditProfile.php';
require_once '../models/LogIn.php';

use Manage\Server\Server;
use Manage\Page\Page;
use User\Manage\EditProfile\EditProfile;

session_start();

// Controller's redirect paths.
$indexPath = '../views/index.php';

$email = $_SESSION['profile']['email'];
$login = $_SESSION['login'];
$edit = Server::attemptInstance(EditProfile::class, null, $email, $login); // Attempt to construct EditProfile object.
// Call EditProfile method that deletes this user's profile and set message accordingly.
$delete = $edit->deleteProfile(...$_POST['password']);
$msg = $delete ? Server::MSG['delete_profile_true'] : Server::MSG['delete_profile_false'];
// Destroys session and restarts it to set new message.
session_destroy();
session_start();
Page::goTo($indexPath, $msg); // Go to log-in index.
