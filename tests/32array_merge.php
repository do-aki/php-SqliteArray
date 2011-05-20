<?php
require_once dirname(dirname(__FILE__)) . '/lime.php';
require_once dirname(dirname(__FILE__)) . '/src/SqliteArray.php';

$t = new lime_test();


/**
 * array_merge –Í•íƒeƒXƒg
 *
 */
$real_array = array();
$array = new SqliteArray();

// array(0=>1, 1=>2, 'x'=>3, 'y'=>4)
$rhs = array(1,2,'x'=>3, 'y'=>4);
$real_array = array_merge($real_array, $rhs);
$array->merge($rhs);
$t->is_deeply($array->getArray(), $real_array);

// array(0=>1, 1=>2, 'x'=>5, 'y'=>4, 2=>4, 3=>6, 'z'=>7)
$rhs = array(4, 'x'=>5, 6, 'z'=>7);
$real_array = array_merge($real_array, $rhs);
$array->merge($rhs);
$t->is_deeply($array->getArray(), $real_array);

