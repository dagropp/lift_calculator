<?php

namespace User\Main;

require_once 'Server.php';

use Manage\Server\Server;
use Exception;

/**
 * Class User: abstract class that is extended by all User related models (LogIn, SignUp, Admin, EditProfile).
 * Handles fetching relevant user ID and test validating inputs.
 * @package User\Main
 */
abstract class User
{
    protected $email;
    protected $database;
    protected $userId;
    protected $firstName;
    protected $lastName;
    protected $phoneNum;
    protected $carYearsId;
    protected $car;
    protected $carDB;
    private const MIN_PASSWORD = 8;
    private const MIN_NAME = 2;

    /**
     * User constructor.
     * @param string $email - user's e-mail address.
     * @throws Exception - if e-mail address is not valid, or either User or Car data-bases fail to connect.
     */
    public function __construct(string $email)
    {
        Server::insistOn(Server::ERROR['email'], filter_var($email, FILTER_VALIDATE_EMAIL));
        // After checking validity, reformat and assign class vars.
        $this->email = strtolower($email);
        $this->database = Server::connectToDB(Server::USER_DB);
        Server::insistOn(Server::ERROR['user_db'], $this->database);
        $this->userId = $this->fetchUserID();
        $this->carDB = Server::connectToDB(Server::CAR_DB);
        Server::insistOn(Server::ERROR['car_db'], $this->carDB);
    }

    /**
     * @return null|int - this user's ID if exists, null if not.
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * Fetches user ID based on user's e-mail address.
     * @return null|int - user ID number if successful, null if failed.
     */
    private function fetchUserID(): ?int
    {
        $sql = "SELECT user_id FROM users WHERE email = ?";
        // Performing query with secured method and returns the requested field.
        return Server::queryField($this->database, $sql, 's', $this->email);
    }

    /**
     * Checks if password input matches the password hash stored in the data-base.
     * @param string $password - password attempt.
     * @return bool - true if password matches, false if otherwise.
     */
    protected function passwordMatch(string $password): bool
    {
        $sql = "SELECT password FROM users WHERE user_id = ?";
        // Performing query with secured method and assigns the requested field to var.
        $realPassword = Server::queryField($this->database, $sql, 'i', $this->userId);
        return password_verify($password, $realPassword);
    }

    /**
     * Fetches car's years_id based on car's company, model and manufacture year range.
     * @param string $carCompany - car company.
     * @param string $carModel - car model.
     * @param string $carYears - car manufacture year range.
     * @return int|null - car years ID if successful, null if failed.
     */
    protected function fetchYearsID(string $carCompany, string $carModel, string $carYears): ?int
    {
        $sql = "
            SELECT year_range.years_id
            FROM company
            INNER JOIN model
              ON company.company_name = ?
              AND company.company_id = model.company_id
              AND model.model_name = ?
            INNER JOIN year_range
              ON model.model_id = year_range.model_id 
              AND year_range.years = ? 
            ";
        // Performing query with secured method and returns the requested field.
        return Server::queryField($this->carDB, $sql, 'sss', $carCompany, $carModel, $carYears);
    }

    /**
     * Checks if input's value is a valid name (in Hebrew).
     * @param string ...$names - names to test.
     * @return bool - true if all names are valid, false if at least 1 name is not.
     */
    protected static function validName(string ...$names): bool
    {
        foreach ($names as $name) {
            $length = strlen($name) >= self::MIN_NAME; // Check if name is long enough.
            $regexResult = preg_match(Server::REGEX['hebrew'], $name); // Test input with relevant RegExp pattern.
            if (!$length && $regexResult)
                return false;
        }
        return true;
    }

    /**
     * Checks if input's value is a valid phone number (05XXXXXXXX).
     * @param string $input - phone number to test.
     * @return bool - true if phone number is valid, false if otherwise.
     */
    protected static function validPhone(string $input): bool
    {
        // Test input with relevant RegExp pattern.
        return boolval(preg_match(Server::REGEX['cellphone_num'], $input));
    }

    /**
     * Checks if input's value is a valid and secure password.
     * (8 letters minimum, and includes: capital and lower-cased letters, numbers and no spaces).
     * @param string $input - password to test.
     * @return bool - true if password is valid and secure, false if otherwise.
     */
    protected static function securePassword(string $input): bool
    {
        $length = strlen($input) >= self::MIN_PASSWORD; // Check if password is long enough.
        // Test input with relevant RegExp patterns.
        $regexResult = self::regexTests($input, 'lower_case', 'capitals', 'numbers', 'no_space');
        return $length && $regexResult;
    }

    /**
     * General method to perform multiple RegExp tests on input.
     * @param string $input - input to test.
     * @param string ...$keys - RegExp patterns to match.
     * @return bool - true if all test passed, false if at least 1 test failed.
     */
    private static function regexTests(string $input, string ...$keys): bool
    {
        foreach ($keys as $key)
            if (!preg_match(Server::REGEX[$key], $input))
                return false;
        return true;
    }
}
