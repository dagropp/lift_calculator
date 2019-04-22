<?php

namespace Manage\Server;

require_once 'Page.php';

use Manage\Page\Page;
use Exception;
use mysqli;
use stdClass;

/**
 * Class Server: Handles various back-end situations, carries date constants. All static methods, no constructor.
 * @package Manage\Server
 */
class Server
{
    // Data-base related constants.
    public const CAR_DB = 'Car';
    public const USER_DB = 'User';
    private const SERVER_NAME = 'localhost';
    private const SERVER_USER = 'root';
    private const SERVER_PASSWORD = '';
    public const USER_DB_ROWS = [
        'userID' => 'user_id',
        'email' => 'email',
        'password' => 'password',
        'firstName' => 'first_name',
        'lastName' => 'last_name',
        'phoneNum' => 'phone_num',
        'carYearsID' => 'car_years_id'
    ];
    // Action messages constants.
    public const MSG = [
        'sign_up_true' => 'נוצר משתמש חדש. כעת ניתן להתחבר למערכת.',
        'sign_up_false' => 'יצירת המשתמש נכשלה. ',
        'edit_profile_true' => 'עריכת שדות פרופיל המשתמש בוצעה בהצלחה.',
        'edit_profile_false' => 'עריכת שדות פרופיל המשתמש נכשלה.',
        'edit_password_true' => 'הסיסמה שונתה בהצלחה. להמשך, יש להתחבר שנית למערכת.',
        'delete_profile_true' => 'פרופיל המשתמש נמחק בהצלחה.',
        'delete_profile_false' => 'מחיקת פרופיל המשתמש נכשלה. לניסיון נוסף, יש להתחבר שנית למערכת.',
        'drive_calc_true' => 'הנסיעה חושבה בהצלחה.',
        'drive_calc_false' => 'חישוב הנסיעה נכשל. ',
        'action_false' => 'הפעולה נכשלה',
        'admin_add_true' => 'למשתמש זה נוספו הרשאות ניהול.',
        'admin_remove_true' => 'ממשתמש זה הוסרו הרשאות ניהול.',
        'add_car_true' => 'הוספת הרכב למאגר הנתונים בוצעה בהצלחה.'
    ];
    // Error messages constants.
    public const ERROR = [
        'user' => self::MSG['sign_up_false'] . 'המשתמש כבר קיים במאגר הנתונים.',
        'no_user' => 'המשתמש אינו קיים במאגר הנתונים.',
        'password' => self::MSG['sign_up_false'] . 'הסיסמה אינה בטוחה.',
        'no_password' => 'הסיסמה אינה תקינה.',
        'email' => 'כתובת הדואר האלקטרוני אינה תקינה.',
        'name' => self::MSG['sign_up_false'] . 'השם אינו תקין.',
        'phone' => self::MSG['sign_up_false'] . 'מספר הטלפון אינו תקין.',
        'profile' => 'לא קיים פרופיל למשתמש זה.',
        'car_db' => 'ההתחברות למאגר הרכבים נכשלה.',
        'user_db' => 'ההתחברות למאגר המשתמשים נכשלה.',
        'find_car' => 'לא נמצא רכב במאגר הנתונים.',
        'car_exists' => 'הרכב כבר קיים במאגר הנתונים.',
        'insert' => 'הכנסת המשתמש למאגר נכשלה.',
        'input' => 'שדות הטופס אינם תקינים.',
        'route' => 'המוצא או היעד אינם תקינים ולא ניתן היה לחשב את המרחק ביניהם.',
        'duplicate' => 'המוצא והיעד זהים זה לזה.'
    ];
    // Regular Expressions patterns constants.
    public const REGEX = [
        'lower_case' => '/[a-z]/',
        'capitals' => '/[A-Z]/',
        'numbers' => '/[0-9]/',
        'no_space' => '/^\S*$/',
        'cellphone_num' => '/^05\d{8}$/',
        'name' => '/^[a-zA-Z\s]+$/',
        'hebrew' => '/^[א-ת\s]+$/',
        'int' => '/^\d+$/',
        'float' => '/\d\.\d$/',
        'carYears' => ['half' => '/^\d{4}-$/', 'full' => '/^\d{4}-\d{4}$/']
    ];
    // Car constants.
    public const GAS_TYPES = ['petrol' => 'petrol', 'diesel' => 'diesel', 'electric' => 'electric'];
    // Directory constants.
    private const SESSION_PATH = '../../temp_files/';

    /**
     * General mySQL data-base connection method.
     * @param string $dbName - with specified data-base name.
     * @return mysqli|null - mysqli connection object if successful, null if failed.
     */
    public static function connectToDB(string $dbName): ?mysqli
    {
        // Creates new connection.
        $connection = new mysqli(self::SERVER_NAME, self::SERVER_USER, self::SERVER_PASSWORD, $dbName);
        $connection->set_charset('utf8'); // Set connection characters to utf-8, for Hebrew readability.
        if ($connection->connect_error)
            return null;
        return $connection;
    }

    /**
     * Perform secure query using secureStatement() method, and return specific field.
     * @param mysqli $db - mysqli connection object.
     * @param string $sql - of SQL requests to perform.
     * @param string $types - which types of params to bind (s:string, i:int, d:double, b:blob).
     * @param mixed ...$params - params to bind to statement.
     * @return mixed|null - any type of field (string, int, float) specified if found, null if not found or failed.
     */
    public static function queryField(mysqli $db, string $sql, string $types = '', ...$params)
    {
        $query = self::secureStatement($db, $sql, $types, ...$params);
        if ($query->resultStatus)
            return $query->result->fetch_row()[0];
        return null;
    }

    /**
     * Perform secure query using secureStatement() method, and return relevant data-base row.
     * @param mysqli $db - mysqli connection object.
     * @param string $sql - of SQL requests to perform.
     * @param string $types - which types of params to bind (s:string, i:int, d:double, b:blob).
     * @param mixed ...$params - params to bind to statement.
     * @return array|null - associative array with specified data-base row if found, null if not found or failed.
     */
    public static function queryRow(mysqli $db, string $sql, string $types = '', ...$params): ?array
    {
        $query = self::secureStatement($db, $sql, $types, ...$params);
        if ($query->resultStatus)
            return $query->result->fetch_assoc();
        return null;
    }

    /**
     * Perform secure query using secureStatement() method, and return all relevant data-base rows.
     * @param mysqli $db - mysqli connection object.
     * @param string $sql - of SQL requests to perform.
     * @param string $types - which types of params to bind (s:string, i:int, d:double, b:blob).
     * @param mixed ...$params - params to bind to statement.
     * @return array|null - array with associative arrays with data-base rows if found, null if not found or failed.
     */
    public static function queryAllRows(mysqli $db, string $sql, string $types = '', ...$params): ?array
    {
        $query = self::secureStatement($db, $sql, $types, ...$params);
        if ($query->resultStatus)
            return $query->result->fetch_all(MYSQLI_ASSOC);
        return null;
    }

    /**
     * Perform secure query using secureStatement() method, and return boolean value.
     * @param mysqli $db - mysqli connection object.
     * @param string $sql - of SQL requests to perform.
     * @param string $types - which types of params to bind (s:string, i:int, d:double, b:blob).
     * @param mixed ...$params - params to bind to statement.
     * @return bool - true if query was successful, false if otherwise.
     */
    public static function queryStatus(mysqli $db, string $sql, string $types = '', ...$params): bool
    {
        return self::secureStatement($db, $sql, $types, ...$params)->execStatus;
    }

    /**
     * Perform mySQL secure prepared statement.
     * @param mysqli $db - mysqli connection object.
     * @param string $sql - of SQL requests to perform.
     * @param string $types - which types of params to bind (s:string, i:int, d:double, b:blob).
     * @param mixed ...$params - params to bind to statement.
     * @return stdClass - execStatus->bool, resultStatus->bool|null, result->array|mixed.
     */
    private static function secureStatement(mysqli $db, string $sql, string $types = '', ...$params): stdClass
    {
        $statement = $db->prepare($sql); // Create prepared statement.
        if ($params) $statement->bind_param($types, ...$params); // Bind specified types and params to the statement.
        $statement->execute(); // Execute statement.
        $result = $statement->get_result(); // get results, if any.
        return (object)[
            'execStatus' => boolval($statement->affected_rows),
            'resultStatus' => $result ? boolval($result->num_rows) : null,
            'result' => $result ? $result : null
        ];
    }

    /**
     * Convert PHP array to JSON file (and format) using writeFile() method.
     * @param string $fileName - new file name.
     * @param array $array - to convert to JSON.
     * @return bool - true if JSON file was successfully created, false if otherwise.
     */
    public static function arrayToJSON(string $fileName, array $array): bool
    {
        return self::writeFile(self::SESSION_PATH, $fileName, json_encode($array));
    }

    /**
     * Create file in specified directory in the server.
     * @param string $dirPath - directory path (existent/no-existent).
     * @param string $fileName - new file name.
     * @param string $data - to be written on the file.
     * @return bool - true if successfully written file, false if otherwise.
     */
    private static function writeFile(string $dirPath, string $fileName, string $data): bool
    {
        if (!file_exists($dirPath)) mkdir($dirPath); // If directory doesn't exist, create one.
        // Create the file and write the data on it.
        $openFile = fopen($dirPath . $fileName, 'w');
        $writeFile = fwrite($openFile, $data);
        fclose($openFile);
        return boolval($writeFile);
    }


    /**
     * General method that throws exception if specified conditions are not met.
     * @param string $msg - exception message.
     * @param mixed ...$tests - conditions to test.
     * @throws Exception - if at least 1 condition not met.
     */
    public static function insistOn(string $msg, ...$tests): void
    {
        foreach ($tests as $condition)
            if (!$condition) throw new Exception($msg);
    }

    /**
     * General method that attempts to create class instance, and sets error message (or redirects) if failed.
     * @param $class - class::class to initiate.
     * @param null|string $redirect - path to redirect if failed to initiate, or null (default) if no redirect.
     * @param mixed ...$params - class construct params.
     * @return null|object - null if failed to construct, new instance object if successful.
     */
    public static function attemptInstance($class, ?string $redirect = null, ...$params): ?object
    {
        try {
            return new $class(...$params);
        } catch (Exception $e) {
            if ($redirect)
                Page::goTo($redirect, $e->getMessage());
            else
                Page::setMsg($e->getMessage());
            return null;
        }
    }

    /**
     * General method that attempts method that throws exception.
     * @param object $object - Class object of which the method belongs to.
     * @param string $method - method name in string format.
     * @param null|string $redirect - path to redirect if failed to perform, or null (default) if no redirect.
     * @param mixed ...$params - method params.
     * @return mixed|null - any return value (or void) of the attempted method if successful, or null if failed.
     */
    public static function attemptMethod(object $object, string $method, ?string $redirect = null, ...$params)
    {
        try {
            return $object->$method(...$params);
        } catch (Exception $e) {
            $redirect ? Page::goTo($redirect, $e->getMessage()) : Page::setMsg($e->getMessage());
            return null;
        }
    }

    /**
     * Call fetchContentFromURL() method, and isolate specific field (with price) in the HTML content using RegExp.
     * @param string $url - from where to fetch the content.
     * @param string $regex - the pattern to fetch the price field.
     * @return float|null - float of the found price if successful, null if failed.
     */
    public static function fetchPriceFromURL(string $url, string $regex): ?float
    {
        $fetchURL = self::fetchContentFromURL($url);
        preg_match($regex, $fetchURL, $result);
        if ($result)
            return floatval($result[1]);
        return null;
    }

    /**
     * Fetches HTML content from specified URL using cURL library.
     * @param string $url - from where to fetch the content.
     * @return string - with the HTML content.
     */
    private static function fetchContentFromURL(string $url): string
    {
        $channel = curl_init(); // Initiates cURL channel.
        curl_setopt($channel, CURLOPT_URL, $url); // Fetch content from the URL.
        curl_setopt($channel, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($channel, CURLOPT_USERAGENT, 'cURL');
        curl_setopt($channel, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($channel); // Assign results to var.
        curl_close($channel); // Close cURL channel.
        return $result;
    }

    /**
     * Checks if given string is empty or not.
     * @param string $input - string to check.
     * @return bool - true if not empty, false if otherwise.
     */
    public static function nonEmptyString(string $input): bool
    {
        return !empty($input) && is_string($input);
    }

    /**
     * Convert phone number from only numbers to a more readable result (05XXXXXXXX -> 05X-XXXXXXX).
     * @param string $phoneNum - phone number to convert.
     * @return null|string - converted result if successful, null if failed.
     */
    public static function convertPhone(string $phoneNum): ?string
    {
        if (strlen($phoneNum) == 10)
            return substr_replace($phoneNum, '-', 3, 0);
        return null;
    }
}
