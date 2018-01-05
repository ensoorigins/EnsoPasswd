function testEmail()
{
    $.ajax({
        type: "POST",
        dataType: "html",
        cache: false,
        url: "test_email.php",
        data: {
            emailhost: $("#edit-emailhost").val(),
            emailport: $("#edit-emailport").val(),
            emailuser: $("#edit-emailuser").val(),
            emailpass: $("#edit-emailpass").val(),
            emailto: $("#edit-emailto").val(),
            emailfrom: $("#edit-emailfrom").val(),
            test: 1
        },
        success: function (response) {
            $("#alert-modal .modal-content").empty().html("Everything seems ok, make sure you have received the test email.");
            $("#alert-modal").modal("open");
        },
        error: function (response) {
            console.log(response);
            $("#alert-modal .modal-content").empty().html("Authentication was not succesfull please check your credentials.<br><br>" + response.responseText);
            $("#alert-modal").modal("open");
        }
    });
}

function testDB()
{
    $.ajax({
        type: "POST",
        dataType: "html",
        cache: false,
        url: "test_db.php",
        data: {
            dbhost: $("#edit-dbhost").val(),
            dbport: $("#edit-dbport").val(),
            dbuser: $("#edit-dbuser").val(),
            dbpass: $("#edit-dbpass").val(),
            dbname: $("#edit-dbname").val(),
            test: 1
        },
        success: function (response) {
            $("#alert-modal .modal-content").empty().html("Connection sucessful");
            $("#alert-modal").modal("open");
        },
        error: function (response) {
            console.log(response);
            $("#alert-modal .modal-content").empty().html("Connection not succesfull please check your credentials and/or make sure the database specified has been created.<br><br>" + response.responseText);
            $("#alert-modal").modal("open");
        }
    });
}

function testLDAP()
{
    $.ajax({
        type: "POST",
        dataType: "html",
        cache: false,
        url: "test_ldap.php",
        data: {
            ldaphost: $("#edit-ldaphost").val(),
            ldapport: $("#edit-ldapport").val(),
            ldaptimeout: $("#edit-ldaptimeout").val(),
            ldapmaindn: $("#edit-ldapmaindn").val(),
            ldapquery: $("#edit-ldapquery").val(),
            ldaptestuser: $("#edit-ldaptestuser").val(),
            ldaptestpass: $("#edit-ldaptestpass").val(),
            test: 1
        },
        success: function (response) {
            $("#alert-modal .modal-content").empty().html("Connection sucessful");
            $("#alert-modal").modal("open");
        },
        error: function (response) {
            console.log(response);
            $("#alert-modal .modal-content").empty().html("Connection not succesfull please check your credentials and/or make sure the database specified has been created.<br><br>" + response.responseText);
            $("#alert-modal").modal("open");
        }
    });
}

function callSetupScript()
{

    $("#main-content").hide();
    $("#loader").show();
    $.ajax({
        type: "POST",
        dataType: "html",
        cache: false,
        url: "setup.php",
        data: {
            ldaphost: $("#edit-ldaphost").val(),
            ldapport: $("#edit-ldapport").val(),
            ldaptimeout: $("#edit-ldaptimeout").val(),
            ldapmaindn: $("#edit-ldapmaindn").val(),
            ldapquery: $("#edit-ldapquery").val(),
            ldaptestuser: $("#edit-ldaptestuser").val(),
            ldaptestpass: $("#edit-ldaptestpass").val(),
            dbhost: $("#edit-dbhost").val(),
            dbport: $("#edit-dbport").val(),
            dbuser: $("#edit-dbuser").val(),
            dbpass: $("#edit-dbpass").val(),
            dbname: $("#edit-dbname").val(),
            emailhost: $("#edit-emailhost").val(),
            emailport: $("#edit-emailport").val(),
            emailuser: $("#edit-emailuser").val(),
            emailpass: $("#edit-emailpass").val(),
            emailto: $("#edit-emailto").val(),
            emailfrom: $("#edit-emailfrom").val(),
            username: $("#edit-username").val(),
            password: $("#edit-password").val(),
            email: $("#edit-email").val(),
            keylocation: $("#edit-keylocation").val()
        },
        success: function (response) {
            $("#alert-modal .modal-content").empty().html("Everything went ok. Don't forget that you can still change these settings at any time by editing the \"passwd.conf.php\" file.");
            $("#alert-modal").modal("open");
            $("#main-content").empty().append("<i class='material-icons large green-text'>check</i>");
        },
        error: function (response) {
            $("#alert-modal .modal-content").empty().html("Setup response: <br><br>" + response.responseText);
            $("#alert-modal").modal("open");

            $("#main-content").show();
            $("#loader").hide();
        }
    });
}

$(document).ready(function() 
{
    $(".modal").modal();
    $("#loader").hide();
});
