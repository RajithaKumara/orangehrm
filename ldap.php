
<?php

// using ldap bind
$ldaprdn  = 'uname';     // ldap rdn or dn
$ldappass = 'password';  // associated password

// connect to ldap server
$ldapconn = ldap_connect("ldaps://ldap.orangehrm.com:636")
    or die("Could not connect to LDAP server.");

ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);

if ($ldapconn) {

    // binding to ldap server
    $ldapbind = ldap_bind($ldapconn, 'uid=rajitha,ou=developers,ou=engineers,ou=employees,ou=users,dc=orangehrm,dc=com', 'cXRJeP0ti6I4');

    // verify binding
    if ($ldapbind) {
        echo "LDAP bind successful...";
    } else {
        echo "LDAP bind failed...";
    }
}


//// using ldap bind
//$ldaprdn = 'uname';     // ldap rdn or dn
//$ldappass = 'password';  // associated password
//
//// connect to ldap server
//$ldapconn = ldap_connect("ldap://host.docker.internal:10389")
//or die("Could not connect to LDAP server.");
//
//if ($ldapconn) {
//    // binding to ldap server
//    $ldapbind = ldap_bind($ldapconn, 'uid=admin,ou=system', 'secret');
//
//    // verify binding
//    if ($ldapbind) {
//        echo "LDAP bind successful...";
//    } else {
//        echo "LDAP bind failed...";
//    }
//}
