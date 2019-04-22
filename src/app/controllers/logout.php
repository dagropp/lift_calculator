<?php

// Controller's dependencies.
require_once '../models/Page.php';

use Manage\Page\Page;

session_start();

// Controller's redirect paths.
$indexPath = '../views/index.php';

// Destroys session and restarts it to set new message
session_destroy();
session_start();
Page::goTo($indexPath, 'התנתקנו...'); // Go to log-in index.
