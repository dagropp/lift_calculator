<?php

namespace Drive\Car;

require_once 'Server.php';

use Manage\Server\Server;
use Exception;

/**
 * Class Car: creates car object from data-base and handles cars list creation (JSON).
 * @package Drive\Car
 */
class Car
{
    private $database;
    private $company;
    private $model;
    private $gasType;
    private $yearRange;
    private $kmPerLiter;

    /**
     * Car constructor.
     * @param int $yearsID - used to fetch specified car details from the data-base.
     * @throws Exception - if data-base connection failed, if car not found on data-base.
     */
    public function __construct(int $yearsID)
    {
        $this->database = Server::connectToDB(Server::CAR_DB); // Connect to Car database.
        Server::insistOn(Server::ERROR['car_db'], $this->database);
        // Retrieve rows as associative arrays from database containing relevant company, model and year range.
        if ($yearsID) {
            $carDetails = self::fetchCarDetails($yearsID);
            Server::insistOn(Server::ERROR['find_car'], $carDetails);
            // Assign class vars this car data.
            $this->company = $carDetails['company_name'];
            $this->model = $carDetails['model_name'];
            $this->gasType = $carDetails['gas_type'];
            $this->yearRange = $carDetails['years'];
            $this->kmPerLiter = $carDetails['km_per_liter'];
        }
    }

    /**
     * String representation of class.
     * @return string - representing this car main details.
     */
    public function __toString(): string
    {
        return "<b>$this->company</b> $this->model ($this->yearRange)";
    }

    /**
     * @return float - this car's fuel consumption in KM per Liter.
     */
    public function getKPL(): float
    {
        return $this->kmPerLiter;
    }

    /**
     * @return string - this car's gas type (petrol/diesel/electric).
     */
    public function getGasType(): string
    {
        return $this->gasType;
    }

    /**
     * @return array - this car's company, model and manufacture year range.
     */
    public function getCarString(): array
    {
        return [
            'company' => $this->company,
            'model' => $this->model,
            'yearRange' => $this->yearRange
        ];
    }

    /**
     * Fetching car details from data-base, based on years_id from 'year_range' table.
     * @param int $yearsID - this car's year range ID.
     * @return array|null - array with car details if successful, null if failed.
     */
    public static function fetchCarDetails(int $yearsID): ?array
    {
        $carDB = Server::connectToDB(Server::CAR_DB); // Creates Car data-base connection.
        $sql = "
            SELECT company.company_name, model.model_name, model.gas_type, year_range.years, year_range.km_per_liter
            FROM company
            INNER JOIN model
              ON company.company_id = model.company_id
            INNER JOIN year_range
              ON model.model_id = year_range.model_id 
              AND year_range.years_id = ?
            ";
        // Performing query with secured method that returns array with details (or null).
        return Server::queryRow($carDB, $sql, 'i', $yearsID);
    }

    /**
     * Creates JSON file with all car companies, models and manufacture years.
     * @return bool - true if JSON file was successfully created, false if otherwise.
     */
    public static function createCarsJSON(): bool
    {
        $carDB = Server::connectToDB(Server::CAR_DB); // Creates Car data-base connection.
        $sql = "
            SELECT company.company_name, model.model_name, year_range.years
            FROM company
            INNER JOIN model
              ON company.company_id = model.company_id
            INNER JOIN year_range
              ON model.model_id = year_range.model_id
            ORDER BY company.company_name, model.model_name, year_range.years
        ";
        // Performing query with secured method that return array with all cars.
        $resultArr = Server::queryAllRows($carDB, $sql);
        $yearsArr = [];
        $finalArr = [];
        // Create associative array with (model => all manufacture years).
        foreach ($resultArr as $row)
            $yearsArr[$row['model_name']][] = $row['years'];
        // Create associative array with (company => all [model => all manufacture years]).
        foreach ($resultArr as $row)
            $finalArr[$row['company_name']][$row['model_name']] = $yearsArr[$row['model_name']];
        // Calls JSON creation method that returns boolean value.
        return Server::arrayToJSON('cars.json', $finalArr);
    }
}
