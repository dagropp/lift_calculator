<?php

namespace Manage\UpdatePrices;

require_once 'Server.php';

use Manage\Server\Server;
use Exception;
use DateTime;

/**
 * Class UpdatePrices: performs price fetching from the web on a daily basis, and storing it on the data-base.
 * @package Manage\UpdatePrices
 */
class UpdatePrices
{
    private $database;
    private $date;
    private $updateState;

    /**
     * UpdatePrices constructor.
     * @throws Exception if Car data-base connection failed.
     */
    public function __construct()
    {
        $this->database = Server::connectToDB(Server::CAR_DB);
        Server::insistOn(Server::ERROR['car_db'], $this->database);
        $this->date = date_format(new DateTime('now'), 'Y-m-d'); // Assign var with today's date.
        // Checks if data-base was updated today. If not updates it.
        $this->updateState = $this->updateToday() ? $this->updatePrices() : true;
    }

    /**
     * Checks if fields in the prices table were updated today.
     * @return bool - true if need to update (table was not updated today), false if otherwise.
     */
    private function updateToday(): bool
    {
        $sql = "SELECT date_modified FROM prices ORDER BY date_modified DESC";
        $dates = array();
        // Assign all dates to array.
        foreach (Server::queryAllRows($this->database, $sql) as $row)
            $dates[] = $row['date_modified'];
        return !in_array($this->date, $dates); // if any date is today return false, else return true.
    }

    /**
     * Uses Server::fetchPriceFromURL() method to fetch price from URL.
     * @return array - containing float price results (or null) for each entry.
     */
    private function getPrices(): array
    {
        $half23 = Server::fetchPriceFromURL
        (
            'https://www.carmeltunnels.co.il/rates/',
            "'<td tabindex=\"0\" class=\"t-b  s-14\" style=\"background-color: #dfe0e2;\">(.*?)&nbsp;<span>&nbsp;₪</span></td>'"
        );

        return [
            Server::GAS_TYPES['petrol'] => Server::fetchPriceFromURL
            (
                'https://www.gov.il/he/Departments/General/fuel_prices_xls',
                "'<td class=\"rtecenter\">(.*?) ש\"ח</td>'"
            ),
            Server::GAS_TYPES['diesel'] => Server::fetchPriceFromURL
            (
                'https://www.delek.co.il/Fuel-prices',
                "'<div class=\"field field-name-field-solr field-type-number-decimal field-label-hidden\">" .
                "<div class=\"field-items\"><div class=\"field-item even\">(.*?)</div>'"
            ),
            Server::GAS_TYPES['electric'] => 0.47,
            '6 צפון' => Server::fetchPriceFromURL
            (
                'https://www.kvish6.co.il/taarif.aspx',
                "'<td id=\"contentMain_rptTolls_td8_1\" class=\"RowActive\">(.*?) ש\"ח</td>'"
            ),
            '6 יקנעם' => Server::fetchPriceFromURL
            (
                'https://6cn.co.il/prices',
                "'<span id=\"lblRushHourYokneam\" aria-hidden=\"true\">(.*?)</span>'"
            ),
            'מנהרות הכרמל (חלקי)' => $half23,
            'מנהרות הכרמל (מלא)' => 2 * $half23
        ];
    }

    /**
     * Call getPrices() method and updates the new data on the data-base.
     * @return bool - true if all queries were updated successfully, false if otherwise.
     */
    private function updatePrices(): bool
    {
        // Filters the array for only float price values (eliminate null entries if any).
        $pricesArr = array_filter($this->getPrices(), 'is_float');
        // Update each array price field to the relevant data-base entry.
        foreach ($pricesArr as $service => $price) {
            $sql = "UPDATE prices SET price = ?, date_modified = CURDATE() WHERE prices.service = '$service'";
            // Performing query with secured method. If failed return false.
            if (!Server::queryStatus($this->database, $sql, 'd', $price))
                return false;
        }
        return true;
    }
}
