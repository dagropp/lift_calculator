<?php

namespace User\Connect\LogIn;

require_once 'User.php';
require_once 'Server.php';
require_once 'Car.php';

use User\Main\User;
use Manage\Server\Server;
use Drive\Car\Car;
use Exception;

/**
 * Class LogIn: handles user connection, authentication and fetching profile details.
 * @package User\Connect\LogIn
 */
class LogIn extends User
{
    public static $attempts = 0;
    private const MAX_ATTEMPTS = 3;

    /**
     * LogIn constructor.
     * @param string $email - user's e-mail.
     * @param string $password - user's password.
     * @throws Exception - if no userID was found for this e-mail, or password doesn't match.
     */
    public function __construct(string $email, string $password)
    {
        self::setAttempts(); // On each attempt LogIn construction, append counter.
        Server::insistOn("Too many attempts", self::testAttempts());
        parent::__construct($email);
        Server::insistOn(Server::ERROR['no_user'], $this->userId);
        Server::insistOn(Server::ERROR['no_password'], $this->passwordMatch($password));
        self::setAttempts(true); // Login successful: reset attempts counter.
    }

    /**
     * Recalls fetchProfile() method to update profile details.
     * @return array - containing user main details: email, name, phone number and car.
     */
    public function getProfile(): array
    {
        list(
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone_num' => $phoneNum,
            'car_years_id' => $carYearsId
            ) = $this->fetchProfile();
        $car = Server::attemptInstance(Car::class, null, $carYearsId);
        return [
            'email' => $this->email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone_num' => $phoneNum,
            'car' => ['obj' => $car, 'str' => $car->getCarString()]
        ];
    }

    /**
     * Recalls createDrivesList() method to update drives list.
     * @return array|null - array of user's saved drives if any, or null if none.
     */
    public function getDrivesList(): ?array
    {
        return $this->createDrivesList();
    }

    /**
     * Fetches selected profile details from User data-base.
     * @return array|null - array with user's details if successful, null if failed.
     */
    private function fetchProfile(): ?array
    {
        $db = Server::connectToDB(Server::USER_DB); // Creates User data-base connection.
        $sql = "SELECT email, first_name, last_name, phone_num, car_years_id FROM users WHERE user_id = ?";
        // Performing query with secured method and returns array with results.
        return Server::queryRow($db, $sql, 'i', $this->userId);
    }

    /**
     * Creates associative array with user's drives.
     * @return array|null - array with user's drives if successful, null if none given.
     */
    private function createDrivesList(): ?array
    {
        $fetchArr = $this->fetchDrives();
        if ($fetchArr) {
            $resultArr = array();
            foreach ($fetchArr as $row)
                $resultArr[] = [
                    'driveID' => $row['drive_id'],
                    'origin' => $row['origin'],
                    'destination' => $row['destination'],
                    'distance' => floatval($row['distance']),
                    'duration' => $row['duration'],
                    'passNum' => intval($row['passengers_num']),
                    'equalDivision' => boolval($row['equal_division']),
                    'tollRoads' => unserialize($row['toll_roads'])
                ];
            return $resultArr;
        }
        return null;
    }

    /**
     * Fetches user's drives from User data-base.
     * @return array|null - array with user's drives if successful, null if none found.
     */
    private function fetchDrives(): ?array
    {
        $db = Server::connectToDB(Server::USER_DB); // Creates User data-base connection.
        $sql = "
            SELECT drive_id, origin, destination, distance, duration, passengers_num, equal_division, toll_roads 
            FROM drives
            WHERE user_id = ?
            ";
        // Performing query with secured method and returns array with all results.
        return Server::queryAllRows($db, $sql, 'i', $this->userId);
    }

    // delete later
    private static function setAttempts(bool $reset = false): void
    {
        self::$attempts = $reset ? 0 : self::$attempts++;
    }

    private static function testAttempts(): bool
    {
        return self::$attempts <= self::MAX_ATTEMPTS;
    }
}
