<?php

namespace Manage\Page;

require_once 'Cost.php';
require_once 'Server.php';

use Drive\Cost\Cost;
use Manage\Server\Server;
use User\Connect\LogIn\LogIn;

/**
 * Class Page: Handles various front-end situations. All static methods, no constructor.
 * @package Manage\Page
 */
class Page
{
    /**
     * Designed for controller use. Go to path after controller tasks with result message.
     * @param string $path - of page to go to.
     * @param string $msg - result message if any (default = '').
     */
    public static function goTo(string $path, string $msg = ''): void
    {
        self::setMsg($msg);
        header("Location: $path");
        exit;
    }

    /**
     * Set $_SESSION message that carries result messages and indicators.
     * @param string $msg - session message (default = '').
     */
    public static function setMsg(string $msg = ''): void
    {
        $_SESSION['msg'] = $msg;
    }

    /**
     * Echo message once and reset it, to avoid lingering messages.
     */
    public static function printMsg(): void
    {
        echo $_SESSION['msg'];
        self::setMsg();
    }

    /**
     * Creates HTML select input with user's drives.
     * @param array|null $drivesList - array of user's drives if any, null if none.
     * @return string - '' if no drives, string with HTML select input if otherwise.
     */
    public static function createDrivesSelect(?array $drivesList): string
    {
        $selectHTML = '';
        // Create select option from each row in the array.
        for ($row = 0; $row < count($drivesList); $row++) {
            list('origin' => $origin, 'destination' => $destination, 'distance' => $distance, 'passNum' => $p_num,)
                = $drivesList[$row];
            $driveNum = $row + 1;
            $selectHTML .= "
                <option value=$row>
                נסיעה $driveNum:
                $origin - $destination  
                ($distance ק''מ,
                $p_num נוסעים)
                </option>
                ";
        }
        $HTML = "
            <h2>נסיעות שמורות</h2>
            <form method='post' action='private/controllers/calculate_saved_drive.php'>
                <select id='drives' name='drives'>$selectHTML</select>
                <button name='submitSavedDrive'>חישוב מחיר</button>
                <button name='deleteDrive'>מחיקת נסיעה</button>
            </form>
            ";
        return $HTML;
    }

    /**
     * Parses $_POST data and attempts to create Cost object with data.
     * @param array $drive - $_POST data array.
     * @param LogIn $login - this user's LogIn object.
     * @param bool $save - true to save drive, false (default) if otherwise.
     * @return Cost|null - Cost object with cost calculation if successful, null if failed.
     */
    public static function parseCost(array $drive, LogIn $login, bool $save = false): ?Cost
    {
        list(
            'origin' => $origin,
            'destination' => $destination,
            'distance' => $distance,
            'duration' => $duration,
            'passNum' => $passNum,
            'equalDivision' => $equalDivision,
            'tollRoads' => $tollRoads
            ) = $drive;
        // Converts vars from mySQL data types to PHP data types.
        $distance = floatval($distance);
        $passNum = intval($passNum);
        $equalDivision = boolval($equalDivision);
        $save = boolval($save);
        return Server::attemptInstance(Cost::class, null,
            $login, $origin, $destination, $distance, $duration, $passNum, $equalDivision, $tollRoads, $save);
    }
}
