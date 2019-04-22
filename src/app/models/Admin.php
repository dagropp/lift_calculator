<?php

namespace Manage\Admin;

require_once 'Server.php';
require_once 'User.php';
require_once 'Car.php';

use Manage\Server\Server;
use User\Main\User;
use Drive\Car\Car;
use Exception;

/**
 * Class Admin: handles admin connection, creation, authentication and actions.
 * @package Manage\Admin
 */
class Admin extends User
{
    /**
     * Admin constructor.
     * @param string $email - admin/user email address.
     * @param string $password - user password.
     * @param string $adminPassword - admin password.
     * @param string $key - admin password encryption key.
     * @throws Exception - if $password/$adminPassword don't match passwords from data-base.
     */
    public function __construct(string $email, string $password, string $adminPassword, string $key)
    {
        parent::__construct($email);
        // Authenticates user and admin password. If failed, throws exception.
        Server::insistOn(
            Server::ERROR['no_password'],
            $this->passwordMatch($password),
            $this->authenticateAdmin($adminPassword, $key)
        );
    }

    /**
     * Deletes specified users from the data-base.
     * @param int ...$usersID - users to delete.
     * @return bool - true if successfully deleted all specified users, false if at least 1 deletion failed.
     * @throws Exception - if $usersID no int.
     */
    public function deleteUser(int ...$usersID): bool
    {
        $db = Server::connectToDB(Server::USER_DB); // Creates User data-base connection.
        // Deletes each specified user.
        foreach ($usersID as $user) {
            Server::insistOn(Server::ERROR['input'], is_int($user));
            $sql = "
                DELETE FROM users
                WHERE user_id = ?
                ";
            // Performing query with secured method. If failed return false.
            if (!Server::queryStatus($db, $sql, 'i', $user))
                return false;
        }
        return true;
    }

    /**
     * Gives a user admin privileges, by adding encrypted admin password to the user (instead of default null).
     * @param string $adminPass - admin password
     * @param string $key - admin password encryption key.
     * @param int $userID - user to give admin privileges to.
     * @return bool - true if successfully gave admin privileges, false if otherwise.
     * @throws Exception - if $userID no int, if $adminPass not valid and secure.
     */
    public function createAdmin(string $adminPass, string $key, int $userID): bool
    {
        Server::insistOn(Server::ERROR['input'], is_int($userID));
        Server::insistOn(Server::ERROR['password'], parent::securePassword($adminPass));
        $db = Server::connectToDB(Server::USER_DB); // Connect to User data-base.
        $sql = "UPDATE users SET admin_password = AES_ENCRYPT(?, ?) WHERE user_id = ?";
        // Performing query with secured method that returns boolean value.
        return Server::queryStatus($db, $sql, 'ssi', $adminPass, $key, $userID);
    }

    /**
     * Remove admin privileges from user, by replacing admin password with default null.
     * @param int ...$userID - admins to remove privileges from.
     * @return bool - true if successfully removed all specified admins, false if at least 1 remove failed.
     * @throws Exception - if $userID not int, if $userID is the same as this admin's ID.
     */
    public function removeAdmin(int ...$userID): bool
    {
        $db = Server::connectToDB(Server::USER_DB); // Connect to User data-base.
        // Remove each specified admin.
        foreach ($userID as $user) {
            Server::insistOn(Server::ERROR['input'], is_int($user), $user != $this->userId);
            $sql = "UPDATE users SET admin_password = NULL WHERE user_id = ?";
            // Performing query with secured method. If failed return false.
            if (!Server::queryStatus($db, $sql, 'i', $user))
                return false;
        }
        return true;
    }

    /**
     * Recalls createUsersTable() method, specified for users, admins or this admin.
     * @param bool $admin - true if admin user, false (default) if regular user.
     * @param bool $thisAdmin - true if this admin user, false (default) if all other admin users.
     * @return null|string - null if no users/admins, string with HTML table if otherwise.
     */
    public function getUsersTable(bool $admin = false, bool $thisAdmin = false): ?string
    {
        return $this->createUsersTable($admin, $thisAdmin);
    }

    /**
     * Recalls createUsersSelect() method.
     * @return null|string - null if no users, string with HTML select input containing users details if otherwise.
     */
    public function getUsersSelect()
    {
        return $this->createUsersSelect();
    }

    /**
     * Authenticate admin password with the given password in class constructor.
     * @param string $adminPassword - admin password.
     * @param string $key - admin password encryption key (not stored anywhere).
     * @return bool - true if password matches decrypted password from data-base, false if otherwise.
     */
    private function authenticateAdmin(string $adminPassword, string $key): bool
    {
        $sql = "SELECT AES_DECRYPT(admin_password, ?) FROM users WHERE user_id = ?";
        // Performing query with secured method and assigns the requested field to var.
        $realPassword = Server::queryField($this->database, $sql, 'si', $key, $this->userId);
        return $realPassword === $adminPassword;
    }

    /**
     * Creates HTML table with user/admins/this admin details.
     * @param bool $admin - true if admin user, false (default) if regular user.
     * @param bool $thisAdmin - true if this admin user, false (default) if all other admin users.
     * @return null|string - null if no users/admins, string with HTML table if otherwise.
     */
    private function createUsersTable(bool $admin = false, bool $thisAdmin = false): ?string
    {
        $usersArr = $this->fetchUsers($admin, $thisAdmin); // Assign specified users type details to array.
        $fieldName = $admin ? 'admin_details[]' : 'users_details[]'; // Sets the relevant input name for checkboxes.
        $HTML = "<table border='1px'>";
        // Create table row from each row in the array.
        foreach ($usersArr as $row) {
            list(
                'user_id' => $userID,
                'email' => $email,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone_num' => $phoneNum,
                'car_years_id' => $carYearsID
                ) = $row;
            $car = Car::fetchCarDetails($carYearsID); // Converts carYearsID to presentable car string.
            list('company_name' => $carCompany, 'model_name' => $carModel, 'years' => $carYears) = $car;
            $HTML .= "
                    <tr>
                        <td><input type='checkbox' name=$fieldName value=$userID></td>
                        <td>$lastName $firstName</td>
                        <td>$email</td>
                        <td>$phoneNum</td>
                        <td>$carCompany $carModel ($carYears)</td>    
                    </tr>
                    ";
        }
        $HTML .= "</table>";
        return $HTML;
    }

    /**
     * Creates HTML select input with users details.
     * @return null|string - null if no users, string with HTML select input if otherwise.
     */
    private function createUsersSelect(): ?string
    {
        $HTML = "<select name='user_for_admin'>";
        // Create select option from each row in the array.
        foreach ($this->fetchUsers() as $row) {
            list('user_id' => $userID, 'email' => $email, 'first_name' => $firstName, 'last_name' => $lastName) = $row;
            $HTML .= "<option value=$userID>$lastName $firstName ($email)</option>";
        }
        $HTML .= "</select>";
        return $HTML;
    }

    /**
     * Fetches user/admins/this admin details from User data-base.
     * @param bool $admin - true if admin user, false (default) if regular user.
     * @param bool $thisAdmin - true if this admin user, false (default) if all other admin users.
     * @return array|null - array of users/admins details, null if no users/admins.
     */
    private function fetchUsers(bool $admin = false, bool $thisAdmin = false): ?array
    {
        $db = Server::connectToDB(Server::USER_DB); // Connect to User data-base.
        $isAdmin = $admin ? 'NOT' : '';
        $isThisAdmin = $thisAdmin ? "=" : "!=";
        $sql = "
            SELECT user_id, email, first_name, last_name, phone_num, car_years_id 
            FROM users
            WHERE admin_password IS $isAdmin NULL AND user_id $isThisAdmin ?
            ORDER BY last_name, first_name
            ";
        // Performing query with secured method and returns array with all results.
        return Server::queryAllRows($db, $sql, 'i', $this->userId);
    }
}
