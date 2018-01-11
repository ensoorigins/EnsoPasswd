<?php
require "test_db.php";
require "test_ldap.php";
require "test_email.php";

/* Database settings */

$dbHost = $_POST["dbhost"];
$dbPort = $_POST["dbport"];
$dbUser = $_POST["dbuser"];
$dbPass = $_POST["dbpass"];
$dbName = $_POST["dbname"];

/* Admin data */

$user = $_POST["username"];
$password = $_POST["password"];
$email = $_POST["email"];

/* LDAP Data */

$ldapHost = $_POST["ldaphost"];
$ldapPort = $_POST["ldapport"];
$ldapMainDn = $_POST["ldapmaindn"];
$ldapQuery = $_POST["ldapquery"];
$ldapTimeout = $_POST["ldaptimeout"];
$ldapTestUser = $_POST["ldaptestuser"];
$ldapTestPass = $_POST["ldaptestpass"];

/* Email */

$emailHost = $_POST["emailhost"];
$emailPort = $_POST["emailport"];
$emailFrom = $_POST["emailfrom"];
$emailUser = $_POST["emailuser"];
$emailPass = $_POST["emailpass"];

$keyLocation = $_POST["keylocation"];

if($keyLocation[strlen($keyLocation) - 1] != '/')
    $keyLocation .= "/";

/* Verificar Validade de Dados */

if ( ($user == "" || $password == "" || $email == "") ||
    ($emailFrom == "" || $emailHost == "" || $emailPass == "" || $emailPort == "" || $emailUser == "") ||
        !( ($ldapHost == "" && $ldapMainDn == "" && $ldapPort == "" && $ldapQuery == "" && $ldapTimeout == "" && $ldapTestUser == "" && $ldapTestPass == "") || ($ldapHost != "" && $ldapMainDn != "" && $ldapPort != "" && $ldapQuery != "" && $ldapTimeout != "" && $ldapTestUser != "" && $ldapTestPass != "")) || $keyLocation == "") {
    http_response_code(406);
    echo "Not all required fields were sent.";
} else {

    /* check all */

    if (test_db() === FALSE) {
        echo "bad db settings";
        http_response_code(406);
        return;
    } else if (testEmail() === FALSE) {
        echo "bad email settings";
        http_response_code(406);
        return;
    } else if (! ($ldapHost == "" && $ldapMainDn == "" && $ldapPort == "" && $ldapQuery == "" && $ldapTimeout == "") && testLdap() == FALSE) {
        echo "bad ldap settings";
        http_response_code(406);
        return;
    } else if (!is_writable("../api/passwd.conf.php")) {
        echo "no permissions to write the config file. Tip: this script is being ran by the user: " . system("whoami");
        http_response_code(406);
        return;
    } else if (!is_writable($keyLocation)) {
        echo "no permissions to write the the encryption key. Tip: this script is being ran by the user: " . system("whoami") .
            "<br><br> Please make sure that either the directory is writable to the user running the php script";
        http_response_code(406);
        return;
    }

    /* Set up DB */

    $db = new PDO(
        "mysql" . ":host=" . $dbHost . ";port=" . $dbPort . ";dbname=" . $dbName,
        $dbUser,
        $dbPass
    );

    $myfile = fopen("./setup.sql", "r") or die("Unable to open setup sql script");
    $sql = fread($myfile, filesize("./setup.sql"));
    fclose($myfile);
    $query = $db->prepare($sql);
    $query->execute();
    $query->closeCursor();

    /* Define settings */

    $phpConf = '<?php
    
    /* LDAP Settings */
    
    $ldapConfig["host"] = "' . $ldapHost . '";
    $ldapConfig["port"] = "' . $ldapPort . '";
    $ldapConfig["mainDn"] = "' . $ldapMainDn . '";
    $ldapConfig["timeout"] = "' . $ldapTimeout . '";
    $ldapConfig["query"] = "' . $ldapQuery . '";
    
    /* Notification Settings */
    
    $ensoMailConfig["host"] = "' . $emailHost . '";
    $ensoMailConfig["port"] = ' . $emailPort . ';
    $ensoMailConfig["user"] = "' . $emailUser . '";
    $ensoMailConfig["pass"] = "' . $emailPass . '";
    $ensoMailConfig["from"] = "' . $emailFrom . '";
    $ensoMailConfig["encryption"] = null; // tls || ssl || null
    
    /* Database Settings */
    
    $databaseConfig["database_type"] = "mysql";
    $databaseConfig["server"] = "' . $dbHost . '";
    $databaseConfig["username"] = "' . $dbUser . '";
    $databaseConfig["password"] = "' . $dbPass . '";
    $databaseConfig["charset"] = "utf8";
    $databaseConfig["port"] = "' . $dbPort . '";
    $databaseConfig["database_name"] = "' . $dbName . '";
    
    /* Key location */
    
    $encryptionKeyLocation = "' . $keyLocation . '";';

    $myfile = fopen("../api/passwd.conf.php", "w") or die("Unable to create conf file");
    fwrite($myfile, $phpConf, strlen($phpConf));
    fclose($myfile);

    require "../api/libs/ensoshared/include.php";

    /* Create the encryption key */

    $key = EnsoShared::generateSecret(32);

    if(is_writable($keyLocation))
    {
        $myfile = fopen($keyLocation . "encryption.key", "w") or die("Unable to create encription key file");
        fwrite($myfile, $key, strlen($key));
        fclose($myfile);
    }
    
    /* Add first admin user */

    require "../api/passwd.conf.php";
    require "../api/controllers.autoload/models/UserModel.php";
    require "../api/libs/ensorbac/include.php";
    require "../api/libs/ensodb/include.php";

    UserModel::addUser($user, $email, 0, $password);
    EnsoRBACModel::addRoleToUser($user, "NormalUser");
    EnsoRBACModel::addRoleToUser($user, "SysAdmin");


}