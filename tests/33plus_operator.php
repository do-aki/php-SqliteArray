<?php
require_once dirname(dirname(__FILE__)) . '/lime.php';
require_once dirname(dirname(__FILE__)) . '/src/SqliteArray.php';

$t = new lime_test();

/**
 * + 演算子 の模倣テスト
 */
$real_array = array();
$array = new SqliteArray();

// array(0=>1, 1=>2, 'x'=>3, 'y'=>4)
$real_array = $real_array + array(1,2,'x'=>3, 'y'=>4);
$array->plus(array(1,2,'x'=>3, 'y'=>4));
$t->is_deeply($array->getArray(), $real_array);

// array(0=>1, 1=>2, 'x'=>3, 'y'=>4, 'z'=>7)
$real_array = $real_array + array(4, 'x'=>5, 6, 'z'=>7);
$array->plus(array(4, 'x'=>5, 6, 'z'=>7));
$t->is_deeply($array->getArray(), $real_array);
