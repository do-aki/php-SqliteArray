<?php
require_once dirname(dirname(__FILE__)) . '/lime.php';
require_once dirname(dirname(__FILE__)) . '/src/SqliteArray.php';

$t = new lime_test();


/**
 * イテレーション
 */
$ra = array('a'=>1, 2, 'b'=>3, 5=>4, 5);
$a = new SqliteArray($ra);

foreach ($ra as $k => $v) {
    $key_ra[] = $k;
    $value_ra[] = $v;
}

$counter = 0;
foreach ($a as $k => $v) {
    $key_a[] = $k;
    $value_a[] = $v;
    $counter++;
}

$t->is_deeply($counter, count($ra), "count");
$t->is_deeply($key_a, $key_ra, "key");
$t->is_deeply($value_a, $value_ra, "value");


$a = new SqliteArray(array('a','b','c'));
foreach ($a as $k => &$v) {
    var_dump(array($k, $v));
    $v = "X" . $v;
    var_dump(array($k, $v));

}

var_dump($a->toArray());
