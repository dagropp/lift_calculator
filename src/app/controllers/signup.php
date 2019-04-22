<?php

// Controller's dependencies.
require_once '../models/Server.php';
require_once '../models/Page.php';
require_once '../models/SignUp.php';

use Manage\Server\Server;
use Manage\Page\Page;
use User\Connect\SignUp\SignUp;

session_start();

// Controller's redirect paths.
$indexPath = '../views/index.php';
$signUpScreen = '../views/signup_screen.php';

list('email' => $email, 'password' => $password, 'first_name' => $firstName, 'last_name' => $lastName,
    'phone_num' => $phoneNum, 'c_company' => $carCompany, 'c_model' => $carModel, 'c_years' => $carYears) = $_POST;
// Attempt to construct SignUp object.
$_SESSION['sign_up'] = Server::attemptInstance(SignUp::class, $signUpScreen,
    $email, $password, $firstName, $lastName, $phoneNum, $carCompany, $carModel, $carYears);
Page::goTo($indexPath, Server::MSG['sign_up_true']); // Go to log-in index.
