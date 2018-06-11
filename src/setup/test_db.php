<?php
function test_db()
{
    $dbHost = $_POST['dbhost'];
    $dbPort = $_POST['dbport'];
    $dbUser = $_POST['dbuser'];
    $dbPass = $_POST['dbpass'];
    $dbName = $_POST['dbname'];

    try {
        $db = new PDO(
            "mysql" . ':host=' . $dbHost . ';port=' . $dbPort . ';dbname=' . $dbName,
            $dbUser,
            $dbPass
        );
    } catch (PDOException $e) {
        http_response_code(406);
        echo "The connection to the database was not successful";
        return FALSE;
    }

    return TRUE;
}

if ($_POST['test'] == 1)
    test_db();