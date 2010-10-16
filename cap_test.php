<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

require 'cap.php';

$a = function($num) {
    $t = $num * $num;
    return "$num squared is $t!";
};
$id = CAP::register($a);
echo '<form action="cap.php" method="POST">';
echo '<input type="text" name="' . $id . '" />';
echo '<input type="submit" />';
echo '</form>';

?>