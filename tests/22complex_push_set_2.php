<?php
require_once dirname(dirname(__FILE__)) . '/lime.php';
require_once dirname(dirname(__FILE__)) . '/src/SqliteArray.php';

$t = new lime_test();

/**
 * push/set 動作のテスト2
 *
 */
$array = new SqliteArray();
$real_array = array();

// array(3=>1)
$array->set("3", 1);
$real_array["3"] = 1;
$t->is_deeply($array->getArray(), $real_array);

// array(3=>1, 5=>2)
$array->set(5.0, 2);
$real_array[5.0] = 2;
$t->is_deeply($array->getArray(), $real_array);

// array(3=>1, 5=>2, 8=>3)
$array->set(8.8, 3);
$real_array[8.8] = 3;
$t->is_deeply($array->getArray(), $real_array);

// array(3=>1, 5=>2, 8=>3, 1=>4)
$array->set(true, 4);
$real_array[true] = 4;
$t->is_deeply($array->getArray(), $real_array);

// array(3=>1, 5=>2, 8=>3, 1=>4, 0=>5)
$array->set(false, 5);
$real_array[false] = 5;
$t->is_deeply($array->getArray(), $real_array);

// array(3=>1, 5=>2, 8=>3, 1=>4, 0=>5, -5=>6)
$array->set(-5, 6);
$real_array[-5] = 6;
$t->is_deeply($array->getArray(), $real_array);

// array(3=>1, 5=>2, 8=>3, 1=>4, 0=>5, -5=>6, '09'=>7)
$array->set('09', 7);
$real_array['09'] = 7;
$t->is_deeply($array->getArray(), $real_array);

// array(3=>1, 5=>2, 8=>3, 1=>4, 0=>5, -5=>6, '09'=>7, '2.1'=>8)
$array->set('2.1', 8);
$real_array['2.1'] = 8;
$t->is_deeply($array->getArray(), $real_array);

// array(3=>1, 5=>2, 8=>3, 1=>4, 0=>5, -5=>6, '09'=>7, '2.1'=>8, ''=>9)
$array->set(null, 9);
$real_array[null] = 9;
$t->is_deeply($array->getArray(), $real_array);

// array(3=>1, 5=>2, 8=>3, 1=>4, 0=>5, -5=>6, '09'=>7, '2.1'=>8, ''=>9, 9=>10)
$array->push(10);
$real_array[] = 10;
$t->is_deeply($array->getArray(), $real_array);

