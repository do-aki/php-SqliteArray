<?php
require_once dirname(dirname(__FILE__)) . '/lime.php';
require_once dirname(dirname(__FILE__)) . '/src/SqliteArray.php';

$t = new lime_test();

/**
* array からのオブジェクト構築テスト
*
*/
// array(0=>1, 1=>2, 2=>3, 3=>4, 4=>5, 5=>6)
$real_array = array(1,2,3,4,5,6);
$array = new SqliteArray($real_array);
$t->is_deeply($array->getArray(), $real_array);

// array(0=>1, 1=>2, 2=>3, 5=>'x', 'y'=>6, 6=>7)
$real_array = array(1,2,3, '5'=>'x', 'y'=>6, 7);
$array = new SqliteArray($real_array);
$t->is_deeply($array->getArray(), $real_array);

