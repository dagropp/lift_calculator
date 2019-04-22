<?php

namespace User\Manage\EditProfile;

require_once 'Server.php';
require_once 'User.php';

use Manage\Server\Server;
use User\Connect\LogIn\LogIn;
use User\Main\User;
use Exception;

/**
 * Class EditProfile: handles user's profile edit, password change, drive delete and user delete.
 * @package User\Manage\EditProfile
 */
class EditProfile extends User
{
    /**
     * EditProfile constructor.
     * @param string $email - user email address.
     * @param LogIn $login - this user's LogIn object.
     * @throws Exception - if login object not valid.
     */
    function __construct(string $email, LogIn $login)
    {
        parent::__construct($email);
        Server::insistOn(Server::ERROR['profile'], $login instanceof LogIn);
    }

    /**
     * Deletes user drive from data-base.
     * @param int $driveID - drive ID to delete.
     * @return bool - true if delete successful, false if otherwise.
     */
    public function deleteDrive(int $driveID): bool
    {
        $sql = "DELETE FROM drives WHERE drive_id = ?";
        // Performing query with secured method that returns boolean value.
        return Server::queryStatus($this->database, $sql, 'i', $driveID);
    }

    /**
     * Edit user's e-mail using editProfile() method.
     * @param string $change - new e-mail address.
     * @return bool - true if change successful, false if otherwise.
     * @throws Exception - if new e-mail address is not valid, or e-mail is already used for another user.
     */
    public function editEmail(string $change): bool
    {
        Server::insistOn(Server::ERROR['email'], filter_var($change, FILTER_VALIDATE_EMAIL));
        Server::insistOn(Server::ERROR['user'], !$this->userId);
        return $this->editProfile(Server::USER_DB_ROWS['email'], $change);
    }

    /**
     * Edit user's password using editProfile() method.
     * @param string $old - old password to be verified.
     * @param string $change - new password.
     * @param string $confirm - confirmation of new password.
     * @return bool - true if change successful, false if otherwise.
     * @throws Exception - if old password doesn't match, if password not valid and secure, or confirmation failed.
     */
    public function editPassword(string $old, string $change, string $confirm): bool
    {
        Server::insistOn(Server::ERROR['no_password'], $this->passwordMatch($old));
        Server::insistOn(Server::ERROR['password'], parent::securePassword($change), $change == $confirm);
        return $this->editProfile(Server::USER_DB_ROWS['password'], password_hash($change, PASSWORD_DEFAULT));
    }

    /**
     * Edit user's first name using editProfile() method.
     * @param string $change - new first name.
     * @return bool - true if change successful, false if otherwise.
     * @throws Exception - if name not valid.
     */
    public function editFirstName(string $change): bool
    {
        Server::insistOn(Server::ERROR['name'], parent::validName($change));
        return $this->editProfile(Server::USER_DB_ROWS['firstName'], $change);
    }

    /**
     * Edit user's last name using editProfile() method.
     * @param string $change - new last name.
     * @return bool - true if change successful, false if otherwise.
     * @throws Exception - if name not valid.
     */
    public function editLastName(string $change): bool
    {
        Server::insistOn(Server::ERROR['name'], parent::validName($change));
        return $this->editProfile(Server::USER_DB_ROWS['lastName'], $change);
    }

    /**
     * Edit user's phone number using editProfile() method.
     * @param string $change - new phone number.
     * @return bool - true if change successful, false if otherwise.
     * @throws Exception - if phone number not valid.
     */
    public function editPhoneNum(string $change): bool
    {
        Server::insistOn(Server::ERROR['phone'], parent::validPhone($change));
        return $this->editProfile(Server::USER_DB_ROWS['phoneNum'], $change);
    }

    /**
     * Edit user's car using editProfile() method.
     * @param string $company - new car company.
     * @param string $model - new car model.
     * @param string $years - new car manufacture year range.
     * @return bool - true if change successful, false if otherwise.
     * @throws Exception - if new car not found on Car data-base.
     */
    public function editCar(string $company, string $model, string $years): bool
    {
        $change = $this->fetchYearsID($company, $model, $years);
        Server::insistOn(Server::ERROR['find_car'], $change);
        return $this->editProfile(Server::USER_DB_ROWS['carYearsID'], $change);
    }

    /**
     * Deletes user profile.
     * @param string $password - user's password to be verified.
     * @param string $passwordConfirm - password confirmation.
     * @return bool - true if change successful, false if otherwise.
     */
    public function deleteProfile(string $password, string $passwordConfirm): bool
    {
        // If passwords match, delete user from data-base.
        if ($password == $passwordConfirm && $this->passwordMatch($password)) {
            $sql = "DELETE FROM users WHERE user_id = ?";
            // Performing query with secured method that returns boolean value.
            return Server::queryStatus($this->database, $sql, 'i', $this->userId);
        }
        return false;
    }

    /**
     * Edits profile field. Used for all profile fields editing.
     * @param string $field - data-base column to edit.
     * @param string $change - new and approved value for the column.
     * @return bool - true if change successful, false if otherwise.
     */
    private function editProfile(string $field, string $change): bool
    {
        $sql = "UPDATE users SET $field = ? WHERE user_id = ?";
        // Performing query with secured method that returns boolean value.
        return Server::queryStatus($this->database, $sql, 'si', $change, $this->userId);
    }
}
