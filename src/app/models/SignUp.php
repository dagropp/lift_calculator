<?php

namespace User\Connect\SignUp;

require_once 'User.php';
require_once 'Server.php';
require_once 'Car.php';

use User\Main\User;
use Manage\Server\Server;
use Exception;

/**
 * Class SignUp: handles user creation.
 * @package User\Connect\SignUp
 */
class SignUp extends User
{
    private $password;

    /**
     * SignUp constructor.
     * @param string $email - user's e-mail address.
     * @param string $password - user's password.
     * @param string $firstName - user's first name.
     * @param string $lastName - user's last name.
     * @param string $phoneNum - user's phone number.
     * @param string $carCompany - Car company.
     * @param string $carModel - Car model.
     * @param string $carYears - Car manufacture year range.
     * @throws Exception - if e-mail already used on data-base, if password not secure, if any input tested not valid,
     * if user's car was not found in Car data-base, or user insert to data-base failed.
     */
    public function __construct
    (string $email,
     string $password,
     string $firstName,
     string $lastName,
     string $phoneNum,
     string $carCompany,
     string $carModel,
     string $carYears)
    {
        parent::__construct($email);
        Server::insistOn(Server::ERROR['user'], !$this->userId);
        Server::insistOn(Server::ERROR['password'], parent::securePassword($password));
        Server::insistOn(Server::ERROR['name'], parent::validName($firstName, $lastName));
        Server::insistOn(Server::ERROR['phone'], parent::validPhone($phoneNum));
        // After checking validity, reformat and assign class vars.
        $this->password = password_hash($password, PASSWORD_DEFAULT);
        $this->firstName = ucwords($firstName);
        $this->lastName = ucwords($lastName);
        $this->phoneNum = Server::convertPhone($phoneNum);
        // Find user's car and if not found throws exception.
        $this->carYearsId = $this->fetchYearsID($carCompany, $carModel, $carYears);
        Server::insistOn(Server::ERROR['find_car'], $this->carYearsId);
        // All user details were constructed and are valid. Try to insert user to DB. If failed throw exception
        Server::insistOn(Server::ERROR['insert'], $this->insertUser());
    }

    /**
     * Inserts user details to User data-base.
     * @return bool - true if insert successful, false if otherwise.
     */
    private function insertUser(): bool
    {
        $sql = "
            INSERT INTO users (user_id, email, password, first_name, last_name, phone_num, car_years_id)
            VALUES (NULL, ?, ?, ?, ?, ?, ?)
            ";
        // Performing query with secured method that returns boolean value.
        return Server::queryStatus($this->database, $sql, 'sssssi', $this->email, $this->password, $this->firstName,
            $this->lastName, $this->phoneNum, $this->carYearsId);
    }
}
