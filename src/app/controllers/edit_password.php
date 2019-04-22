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
$editPasswordScreen = '../views/edit_password.php';

$email = $_SESSION['profile']['email'];
$login = $_SESSION['login'];
$edit = Server::attemptInstance(EditProfile::class, null, $email, $login); // Attempt to construct EditProfile object.
// Attempt EditProfile method that edits password.
Server::attemptMethod($edit, 'editPassword', $editPasswordScreen, ...$_POST['password']);
// Destroys session and restarts it to set new message.
session_destroy();
session_start();
Page::goTo($indexPath, Server::MSG['edit_password_true']); // Go to log-in index.
