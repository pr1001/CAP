<?php

require_once('cap.php');
require_once('form.php');

$form = new Form('cap_test.php', function() {
    print '<p>Called first</p>';
});
$x_id = $form->text('x', function($x) {
    return $x * $x;
});
$y_id = $form->text('y', function($y) {
    return $y * $y;
});
$form->submit('Submit that mutha!', function() use ($x_id, $y_id) {
    printf("<p>x squared is %d, y squared is %d.", CAP::$results[$x_id], CAP::$results[$y_id]);
});
echo $form->toHTML();

?>