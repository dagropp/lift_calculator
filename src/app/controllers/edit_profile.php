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
$loginController = 'login.php';
$editProfileScreen = '../views/edit_profile.php';

$email = $_SESSION['profile']['email'];
$login = $_SESSION['login'];
// Reformat $_POST values to fit user's details valid inputs.
$_POST['phone_num'] = Server::convertPhone($_POST['phone_num']);
$postFilter = array_filter($_POST, 'filterCallback', ARRAY_FILTER_USE_BOTH);
$edit = Server::attemptInstance(EditProfile::class, null, $email, $login); // Attempt to construct EditProfile object.
// For each changed value, attempt to call relevant EditProfile method and update the data.
foreach ($postFilter as $field => $change) {
    switch ($field) {
        case Server::USER_DB_ROWS['email']:
            Server::attemptMethod($edit, 'editEmail', $editProfileScreen, $change);
            break;
        case Server::USER_DB_ROWS['firstName']:
            Server::attemptMethod($edit, 'editFirstName', $editProfileScreen, $change);
            break;
        case Server::USER_DB_ROWS['lastName']:
            Server::attemptMethod($edit, 'editLastName', $editProfileScreen, $change);
            break;
        case Server::USER_DB_ROWS['phoneNum']:
            Server::attemptMethod($edit, 'editPhoneNum', $editProfileScreen, $change);
            break;
        case Server::USER_DB_ROWS['carYearsID']:
            Server::attemptMethod($edit, 'editCar', $editProfileScreen, ...$change);
            break;
    }
}
// Return to login controller to update data after actions performed
Page::goTo($loginController, Server::MSG['edit_profile_true']);

/**
 * Filters $_POST data, to eliminate data that remains the same or empty fields.
 * @param string|array $value - associative array value.
 * @param string $key - associative array key.
 * @return bool - true if data is different and not empty, false if otherwise.
 */
function filterCallback($value, string $key)
{
    $profile = $_SESSION['profile'];
    // If $_POST entry is array, represents car data.
    if (is_array($value)) {
        $idx = count($value);
        // Loops array in reverse and checks if car details were changed.
        foreach (array_reverse($profile['car']['str']) as $field) {
            $idx--;
            // If not empty and changed, return true.
            if (!empty($field) && $field != $value[$idx])
                return true;
        }
        return false;
    }
    // $_POST entry not array. Checks if changed and not empty.
    $duplicate = $value == $profile[$key];
    return !empty($value) && !$duplicate;
}
