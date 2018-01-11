<?php
require '../api/libs/ensomail/include.php';

$ensoMailConfig['host'] = $_POST['emailhost'];
$ensoMailConfig['port'] = $_POST['emailport'];
$ensoMailConfig['user'] = $_POST['emailuser'];
$ensoMailConfig['pass'] = $_POST['emailpass'];
$ensoMailConfig["encryption"] = null;

function testEmail()
{

    if ($_POST['emailhost'] == "" || $_POST['emailport'] == "" || $_POST['emailfrom'] == "" || $_POST['emailuser'] == "" || $_POST['emailpass'] == "") {
        http_response_code(406);
        echo "One or more email settings are missing";
        return false;
    }

    if (Ensomail::sendMail($_POST['emailfrom'], $_POST['emailto'], "Passwd test", "Confirmation Email") == false) {
        http_response_code(406);
        echo "Unable send email correctly";
        return false;
    } else {
        return true;
    }
}

if ($_POST['test'] == 1)
    testEmail();