<?php

namespace Drive\Cost;

require_once 'Server.php';
require_once 'Car.php';

use Manage\Server\Server;
use User\Connect\LogIn\LogIn;
use Exception;

/**
 * Class Cost: handles drive cost calculation, representation and saving.
 * @package Drive\Cost
 */
class Cost
{
    // Object passed from UI and used in class.
    private $login;
    private $profile;
    private $car;
    private $database;
    // Drive parameters passed from UI and handled in class.
    private $origin;
    private $destination;
    private $distance;
    private $duration;
    private $kmPerLiter;
    private $passNum;
    private $equalDivision;
    private $tollRoads;
    // Parameters generated in class.
    private $fullCost;
    private $calculation;
    private const MIN_PASSENGERS = 4;
    // Based on average Israeli human weight, from: https://en.m.wikipedia.org/wiki/Human_body_weight
    private const AVG_PERSON_KG = 72;
    private const AVG_LUGGAGE_KG = 15;
    // Based on efficiency function per weight, from: https://www.wired.com/2012/08/fuel-economy-vs-mass
    private const CHANGE_PER_KG = 0.00025;

    /**
     * Cost constructor.
     * @param LogIn $login - this user's LogIn object.
     * @param string $origin - drive's origin.
     * @param string $destination - drive's destination.
     * @param float $distance - drive's distance (calculated with Google Maps API).
     * @param string $duration - drive's duration (calculated with Google Maps API).
     * @param int $passNum - passengers number.
     * @param bool $equalDivision - true if drive calculated equally, false if divide by minimum passengers (4).
     * @param array $tollRoads - of toll roads used.
     * @param bool $save - true to save drive on data-base, false (default) if otherwise.
     * @throws Exception - if login object not valid, if car data-base connection failed or not found user's car,
     * if origin & destination are the same, if distance/duration <= 0, if inputs are not the type they should be.
     */
    public function __construct
    (LogIn $login,
     string $origin,
     string $destination,
     float $distance,
     string $duration,
     int $passNum,
     bool $equalDivision,
     array $tollRoads,
     bool $save = false)
    {
        Server::insistOn(Server::ERROR['profile'], $login instanceof LogIn);
        $this->login = $login;
        // Fetches profile information and car from LogIn object.
        $this->profile = $this->login->getProfile();
        $this->car = $this->profile['car']['obj'];
        $this->database = Server::connectToDB(Server::CAR_DB);
        Server::insistOn(Server::ERROR['find_car'], $this->car);
        Server::insistOn(Server::ERROR['car_db'], $this->database);
        Server::insistOn(Server::ERROR['duplicate'], $origin != $destination);
        Server::insistOn(
            Server::ERROR['route'],
            Server::nonEmptyString($origin),
            Server::nonEmptyString($destination),
            is_numeric($distance) && $distance > 0
        );
        Server::insistOn(
            Server::ERROR['input'],
            Server::nonEmptyString($duration),
            is_int($passNum) && $passNum > 0,
            is_bool($equalDivision),
            is_array($tollRoads)
        );
        // After checking validity, assign class vars.
        $this->origin = $origin;
        $this->destination = $destination;
        $this->distance = $distance;
        $this->duration = $duration;
        $this->passNum = $passNum;
        $this->equalDivision = $equalDivision;
        $this->tollRoads = $tollRoads;
        $this->kmPerLiter = $this->car->getKPL();
        if ($save) $this->saveDrive(); // If save=true saves drive on data-base.
        // Calculates drive cost and assign it to $_SESSION array.
        $this->fullCost = $this->getFullCost();
        $this->calculation = $this->calculatePrice();
        $this->setSessionCost();
    }

    /**
     * String representation of class.
     * @return string - representing drive details: route, distance, duration, car used, passengers number and cost.
     */
    public function __toString(): string
    {
        $equal = $this->equalDivision ? "" : "אינם ";
        $tolls = $this->tollRoads ? join(", ", $this->tollRoads) : "ללא";
        $fullCost = round($this->fullCost);
        return
            "
            מסלול הנסיעה: $this->origin - $this->destination<br>
            מרחק כולל: $this->distance ק\"מ<br>
            זמן משוער: $this->duration שעות<br>
            סוג הרכב: $this->car<br>
            צריכת דלק: $this->kmPerLiter ק\"מ לליטר<br>
            מספר נוסעים: $this->passNum, אשר $equal מתחלקים שווה בשווה<br>
            כבישי אגרה: $tolls<br>
            סה\"כ הוצאות הנסיעה לכיוון: $fullCost ש\"ח<br>
            חישוב עלות לנוסע לכיוון: $this->calculation ש\"ח<br>
            ";
    }

    /**
     * Calculates drive full cost regarding car's fuel consumption, gas price, toll prices, passengers weight.
     * @return float - of full drive cost in NIS.
     */
    private function getFullCost(): float
    {
        $gasPrice = $this->getServicePrice($this->car->getGasType()); // Fetches current gas price.
        $tollPrice = 0;
        if ($this->tollRoads)
            foreach ($this->tollRoads as $road)
                $tollPrice += $this->getServicePrice($road); // Adding each toll road price to tollPrice var.
        // Estimated total passengers weight, considering average Israeli person weight and average luggage weight.
        $passengersWeight = $this->passNum * (self::AVG_PERSON_KG + self::AVG_LUGGAGE_KG);
        // Calculating efficiency change based on estimated passengers weight * efficiency change per KG constant.
        $efficiencyChange = ($passengersWeight * self::CHANGE_PER_KG) * $this->kmPerLiter;
        // Calculating full drive cost based on all variables.
        $fullCost = ($this->distance / ($this->kmPerLiter - $efficiencyChange)) * $gasPrice + $tollPrice;
        return $fullCost;
    }

    /**
     * Fetches specified service price (fuel or toll road) from the data-base.
     * @param string $service - relevant service to get price.
     * @return float|null - float of service price in NIS if successful, or null if failed.
     */
    private function getServicePrice(string $service): ?float
    {
        $sql = "SELECT price FROM prices WHERE service = ?";
        // Performing query with secured method that returns field (or null).
        return Server::queryField($this->database, $sql, 's', $service);
    }

    /**
     * Checks how many passengers share this drive and if they share the cost equally.
     * @return int - of drive passengers divider.
     */
    private function divider(): int
    {
        // If equalDivision=true or there are more passengers than the minimum, division is by true passengers number.
        if ($this->equalDivision || $this->passNum > self::MIN_PASSENGERS)
            return $this->passNum;
        // Otherwise, division is by minimum passengers number.
        return self::MIN_PASSENGERS;
    }

    /**
     * Calculate price to be paid by each passenger, based on (full cost/divider).
     * @return int - of each passenger price in NIS, rounded to multiples of 5.
     */
    private function calculatePrice(): int
    {
        $distribution = $this->fullCost / $this->divider();
        return self::roundTo($distribution);
    }

    /**
     * Rounds number to multiples of $base.
     * @param float $num - number to be rounded.
     * @param int $base - multiples to round to (default = 5).
     * @return int - with rounded number.
     */
    private static function roundTo(float $num, int $base = 5): int
    {
        return intval($base * round($num / $base));
    }

    /**
     * Saves drive to data-base under this user.
     * @return bool - true if saved successfully, false if otherwise.
     */
    private function saveDrive(): bool
    {
        $userDB = Server::connectToDB(Server::USER_DB); // Creates User data-base connection.
        $userId = $this->login->getUserId();
        // Converts vars to fit mySQL data types.
        $equalDivision = intval($this->equalDivision);
        $tollRoads = serialize($this->tollRoads);
        $sql = "
            INSERT INTO drives 
              (user_id, drive_id, origin, destination, distance, duration, passengers_num, equal_division, toll_roads) 
              VALUES (?, NULL, ?, ?, ?, ?, ?, ?, ?);
            ";
        // Performing query with secured method that returns boolean value.
        return Server::queryStatus($userDB, $sql, 'issdsiis', $userId, $this->origin, $this->destination,
            $this->distance, $this->duration, $this->passNum, $equalDivision, $tollRoads);
    }

    /**
     * Sets $_SESSION var (if set) with drive details.
     */
    private function setSessionCost(): void
    {
        if (isset($_SESSION)) {
            $_SESSION['cost'] =
                [
                    'origin' => $this->origin,
                    'destination' => $this->destination,
                    'duration' => $this->duration,
                    'cost' => $this->calculation,
                    'tolls' => $this->tollRoads ? 'דרך כביש ' . join(", ", $this->tollRoads) : "ללא כבישי אגרה",
                    'firstName' => $this->profile['first_name'],
                    'lastName' => $this->profile['last_name'],
                    'phoneNum' => $this->profile['phone_num']
                ];
        }
    }
}
