<?php

namespace Manage\CarNew;

require_once 'Server.php';

use Manage\Server\Server;
use Exception;

/**
 * Class CarNew: handles adding new car model / year range to existing model to data-base.
 * @package Manage\CarNew
 */
class CarNew
{
    private $database;
    private $model;
    private $years;
    private $kpl;
    private $company;
    private $gasType;
    private $newModel;
    private $modelID;
    private $companyID;

    /**
     * CarNew constructor.
     * @param string $company - existing car company.
     * @param string $model - new/existing car model.
     * @param string $years - new car manufacture year range.
     * @param float $kpl - new car fuel economy (KM per liter).
     * @param string|null $gasType - string of gas type (petrol/diesel/electric) if new model, null if otherwise.
     * @throws Exception - if data-base connection failed, if car company/model not found on data-base, if new model
     * name already exists, if any input is invalid, if insert action failed.
     */
    public function __construct
    (string $company,
     string $model,
     string $years,
     float $kpl,
     ?string $gasType = null)
    {
        $this->database = Server::connectToDB(Server::CAR_DB); // Creates Car data-base connection.
        Server::insistOn(Server::ERROR['car_db'], $this->database);
        $this->companyID = $this->fetchCompanyID($company); // Fetches existing company ID with dedicated method.
        Server::insistOn(Server::ERROR['find_car'], $this->companyID);
        $this->newModel = !is_null($gasType); // Assigns bool var as new model, if gasType is not null.
        // If new model, check if model doesn't exist and gasType input valid.
        if ($this->newModel) {
            Server::insistOn(Server::ERROR['car_exists'], !$this->fetchModelID($model));
            Server::insistOn(Server::ERROR['input'], $this->validGasType($gasType));
        } else {
            $this->modelID = $this->fetchModelID($model); // Fetches existing model ID with dedicated method.
            Server::insistOn(Server::ERROR['find_car'], $this->modelID);
        }
        Server::insistOn(Server::ERROR['input'], $this->validYears($years), is_float($kpl));
        // After checking validity, assign class vars.
        $this->company = $company;
        $this->model = $model;
        $this->years = $years;
        $this->kpl = $kpl;
        $this->gasType = $gasType;
        // All car details were constructed and valid. Tries to insert car to DB. If failed throw exception
        $insert = $this->newModel ? $this->addCarModel() : $this->addCarYears();
        Server::insistOn(Server::MSG['action_false'], $insert);
    }

    /**
     * Triggered if newModel = true. Adds new car model and its year range to the data-base.
     * @return bool - true if inserts were successful, false if otherwise.
     */
    private function addCarModel(): bool
    {
        $modelSQL = "INSERT INTO model (company_id, model_id, model_name, gas_type) VALUES (?, NULL, ?, ?)";
        // Performing model insert query with secured method that returns boolean value.
        $insertModel =
            Server::queryStatus($this->database, $modelSQL, 'iss', $this->companyID, $this->model, $this->gasType);
        $this->modelID = $this->fetchModelID($this->model); // Fetches new model ID with dedicated method.
        $yearsSQL = "INSERT INTO year_range (model_id, years_id, years, km_per_liter) VALUES (?, NULL, ?, ?)";
        // Performing year range insert query with secured method that returns boolean value.
        $insertYears =
            Server::queryStatus($this->database, $yearsSQL, 'isd', $this->modelID, $this->years, $this->kpl);
        return $insertModel && $insertYears; // Checks and returns both inserts boolean values.
    }

    /**
     * Triggered if newModel = false. Adds new year range to existing car model to the data-base.
     * @return bool - true if insert was successful, false if otherwise.
     */
    private function addCarYears(): bool
    {
        // Alters previous model's year range if necessary with dedicated method.
        $this->alterYears();
        $sql = "INSERT INTO year_range (model_id, years_id, years, km_per_liter) VALUES (?, NULL, ?, ?)";
        // Performing insert query with secured method that returns boolean value.
        return Server::queryStatus($this->database, $sql, 'isd', $this->modelID, $this->years, $this->kpl);
    }

    /**
     * Converts year range string (YYYY-YYYY or YYYY-) to array of integers.
     * @param string $years - with car manufacture year range.
     * @return array - of (int) with 1-2 year ranges.
     */
    private function yearsToArray(string $years): array
    {
        $result = explode('-', $years); // Separates the string to array using hyphen as delimiter.
        return array_map('intval', array_filter($result)); // Returns the array with values converted to int.
    }

    /**
     * Checks if last year on this model year range is an open range (e.g. YYYY-), and if so alters it to match the
     * new year range (e.g. old: '2010-', new: '2015-' => old: 2010-2014, new: 2015-).
     * @return bool - if alteration successful or not needed, false if otherwise.
     */
    private function alterYears(): bool
    {
        $selectSQL = "SELECT years_id, years FROM year_range WHERE model_id = ?";
        // Performing query with secured method that returns array with all results.
        $queryArr = Server::queryAllRows($this->database, $selectSQL, 'i', $this->modelID);
        // Filters query array for false entries and maps it with dedicated function that finds the open year range.
        $sortedArr = array_filter(array_map([$this, 'findMaxYear'], $queryArr));
        // If open year range was found, do:
        if ($sortedArr) {
            // Assign var with the open year range and its yearID.
            $result = $sortedArr[array_key_last($sortedArr)];
            $lastYear = $result['year'];
            // Assign var with new year bottom value - 1, and generate closed year range string.
            $newYear = min($this->yearsToArray($this->years)) - 1;
            $alteredYears = "$lastYear-$newYear";
            $alterSQL = "UPDATE year_range SET years = ? WHERE years_id = ?";
            // Performing query with secured method that returns boolean value.
            return Server::queryStatus($this->database, $alterSQL, 'si', $alteredYears, $result['id']);
        }
        return true; // Open year range not found, alteration not needed.
    }

    /**
     * Callback method for array map in alterYears(). Finds (if exists) an open year range in the given array.
     * @param array $val - consisting of year range and yearID.
     * @return array|null - array with open year range and yearID, or null if not open year range.
     */
    private function findMaxYear(array $val): ?array
    {
        $years = $this->yearsToArray($val['years']); // Converts year range string to array of int.
        // If array size is only 1, then open year range. Returns array with open year range details.
        if (count($years) == 1)
            return ['year' => max($years), 'id' => $val['years_id']];
        return null; // Array size bigger than 1, returns null.
    }

    /**
     * Checks if new year range input is valid with tests regarding: (1) RegExp pattern,
     * (2) logical - if YYYY left < YYYY right, (3) if new year range doesn't intersects with existing year ranges.
     * @param string $input - year range to test.
     * @return bool - true if new year range is valid, false if otherwise.
     */
    private function validYears(string $input): bool
    {
        // Performs RegExp pattern test. If failed, returns false.
        $testRegex = preg_match(Server::REGEX['carYears']['half'], $input) ||
            preg_match(Server::REGEX['carYears']['full'], $input);
        if (!$testRegex)
            return false;
        $newYearsArr = $this->yearsToArray($input); // Converts new year range to array of int.
        // Performs logical test, unless new range is an open range. If failed returns false.
        $testLogical = count($newYearsArr) > 1 ? $newYearsArr[1] > $newYearsArr[0] : true;
        if (!$testLogical)
            return false;
        $sql = "SELECT years FROM year_range WHERE model_id = ?";
        // Performing query with secured method that returns array with all year range results.
        $yearsArr = Server::queryAllRows($this->database, $sql, 'i', $this->modelID);
        // If year range array is empty returns true, as no intersections are possible.
        if (is_null($yearsArr))
            return true;
        $sortedArr = [];
        // Merges all year ranges as 1 array with all years mentioned (also in between ranges).
        foreach (array_map([$this, 'sortYearsArray'], $yearsArr) as $row)
            $sortedArr = array_merge($sortedArr, $row);
        // If new year's min and max don't match any of the existing years, return true.
        return !in_array(min($newYearsArr), $sortedArr) && !in_array(max($newYearsArr), $sortedArr);
    }

    /**
     * Callback method for array map in validYears(). Sorts the given array to show all years in the year range.
     * e.g. input: ['years' => '1999-2002'] => output: [1999, 2000, 2001, 2002].
     * @param array $val - consisting of year range.
     * @return array - with all years in the range.
     */
    private function sortYearsArray(array $val): array
    {
        $result = $this->yearsToArray($val['years']); // Converts year range to array of int.
        // If not open range:
        if (count($result) > 1) {
            // Assign each year in the range to the array until reaching the max year, and return the array.
            $floor = $result[0];
            $ceil = $result[1];
            $temp = [];
            while ($floor <= $ceil) {
                $temp[] = $floor;
                $floor++;
            }
            return $temp;
        }
        return $result; // Open range - returns the result as is.
    }

    /**
     * Checks if new car gas type input is valid, namely, if it exists in GAS_TYPES array constant.
     * @param string $input - car gas type to test.
     * @return bool - true if valid, false if otherwise.
     */
    private function validGasType(string $input): bool
    {
        return in_array($input, Server::GAS_TYPES);
    }

    /**
     * Fetches existing car company ID from the data-base.
     * @param string $company - car company.
     * @return int|null - int of company ID if successful, null if failed or not found.
     */
    private function fetchCompanyID(string $company): ?int
    {
        $sql = "SELECT company_id FROM company WHERE company_name = ?";
        // Performing query with secured method and returns the requested field.
        return Server::queryField($this->database, $sql, 's', $company);
    }

    /**
     * Fetches existing/new car model ID from the data-base.
     * @param string $model - car model.
     * @return int|null - int of model ID if successful, null if failed or not found.
     */
    private function fetchModelID(string $model): ?int
    {
        $sql = "SELECT model_id FROM model WHERE model_name = ? AND company_id = ?";
        // Performing query with secured method and returns the requested field.
        return Server::queryField($this->database, $sql, 'si', $model, $this->companyID);
    }
}
