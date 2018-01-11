<?php
require '../api/controllers.autoload/models/AuthenticationModel.php';

$ldapConfig['host'] = $_POST['ldaphost'];
$ldapConfig['port'] = $_POST["ldapport"];
$ldapConfig['mainDn'] = $_POST["ldapmaindn"];
$ldapConfig['timeout'] = $_POST["ldaptimeout"];
$ldapConfig['query'] = $_POST["ldapquery"];

function testLdap()
{
    if (AuthenticationModel::performExternalCredentialCheck($_POST['ldaptestuser'], $_POST['ldaptestpass']) == 1)
        return true;
    else {
        http_response_code(406);
        echo "Ldap authentication failed";
        return FALSE;
    }
}

if ($_POST['test'] == 1)
    testLdap();