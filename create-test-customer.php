<?php
/**
 * One-off: create test customer hhuangweijia@gmail.com with address 222 E 43rd St.
 * Run from project root: php create-test-customer.php
 */

$password = 'Test1234';
$salt = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, 9);
$hash = sha1($salt . sha1($salt . sha1($password)));

$conn = new mysqli('127.0.0.1', 'root', '', 'dev_oc_4566');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset('utf8mb4');

$email = 'hhuangweijia@gmail.com';
$firstname = 'Weijia';
$lastname = 'Huang';
$telephone = '0000000000';
$customer_group_id = 1;
$store_id = 0;
$language_id = 1;
$newsletter = 0;
$ip = '127.0.0.1';
$status = 1;
$safe = 0;
$custom_field = '[]';

// Check if already exists
$chk = $conn->query("SELECT customer_id FROM oc_customer WHERE email = '" . $conn->real_escape_string($email) . "'");
if ($chk && $chk->num_rows > 0) {
    echo "Customer $email already exists. No change.\n";
    exit(0);
}

$conn->query("INSERT INTO oc_customer (customer_group_id, store_id, language_id, firstname, lastname, email, telephone, fax, password, salt, cart, wishlist, newsletter, address_id, custom_field, ip, status, safe, token, code, date_added) VALUES (
    " . (int)$customer_group_id . ",
    " . (int)$store_id . ",
    " . (int)$language_id . ",
    '" . $conn->real_escape_string($firstname) . "',
    '" . $conn->real_escape_string($lastname) . "',
    '" . $conn->real_escape_string($email) . "',
    '" . $conn->real_escape_string($telephone) . "',
    '',
    '" . $conn->real_escape_string($hash) . "',
    '" . $conn->real_escape_string($salt) . "',
    NULL,
    NULL,
    " . (int)$newsletter . ",
    0,
    '" . $conn->real_escape_string($custom_field) . "',
    '" . $conn->real_escape_string($ip) . "',
    " . (int)$status . ",
    " . (int)$safe . ",
    '',
    '',
    NOW()
)");

if ($conn->error) {
    die("Insert customer failed: " . $conn->error);
}
$customer_id = $conn->insert_id;

$address_1 = '222 E 43rd St';
$city = 'New York';
$postcode = '10017';
$country_id = 223;
$zone_id = 3655;

$conn->query("INSERT INTO oc_address (customer_id, firstname, lastname, company, address_1, address_2, city, postcode, country_id, zone_id, custom_field) VALUES (
    " . (int)$customer_id . ",
    '" . $conn->real_escape_string($firstname) . "',
    '" . $conn->real_escape_string($lastname) . "',
    '',
    '" . $conn->real_escape_string($address_1) . "',
    '',
    '" . $conn->real_escape_string($city) . "',
    '" . $conn->real_escape_string($postcode) . "',
    " . (int)$country_id . ",
    " . (int)$zone_id . ",
    '[]'
)");
if ($conn->error) {
    die("Insert address failed: " . $conn->error);
}
$address_id = $conn->insert_id;

$conn->query("UPDATE oc_customer SET address_id = " . (int)$address_id . " WHERE customer_id = " . (int)$customer_id);
if ($conn->error) {
    die("Update address_id failed: " . $conn->error);
}

echo "Created test customer:\n";
echo "  Email: $email\n";
echo "  Password: $password\n";
echo "  Address: $address_1, $city, NY $postcode\n";
echo "  Customer ID: $customer_id\n";
$conn->close();
