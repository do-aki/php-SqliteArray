<?php
require_once dirname(dirname(__FILE__)) . '/lime.php';
require_once dirname(dirname(__FILE__)) . '/src/SqliteArray.php';

$t = new lime_test();

/**
 * push/set 動作のテスト1
 *
 */
$array = new SqliteArray();
$real_array = array();

// array(0=>1)
$array->push(1);
$real_array[] = 1;
$t->is_deeply($array->getArray(), $real_array);

// array(0=>1, 1=>2)
$array->push(2);
$real_array[] = 2;
$t->is_deeply($array->getArray(), $real_array);

// array(0=>1, 1=>2, 'set' => 3)
$array->set('set', 3);
$real_array['set'] = 3;
$t->is_deeply($array->getArray(), $real_array);

// array(0=>1, 1=>2, 'set' => 3, 2=>4)
$array->push(4);
$real_array[] = 4;
$t->is_deeply($array->getArray(), $real_array);

// array(0=>1, 1=>2, 'set' => 3, 2=>4, 6=>5)
$array->set(6, 5);
$real_array[6] = 5;
$t->is_deeply($array->getArray(), $real_array);

// array(0=>1, 1=>2, 'set' => 3, 2=>4, 6=>5, 7=>6)
$array->push(6);
$real_array[] = 6;
$t->is_deeply($array->getArray(), $real_array);

// array(0=>7, 1=>2, 'set' => 3, 2=>4, 6=>5)
$array->set(0, 7);
$real_array[0] = 7;
$t->is_deeply($array->getArray(), $real_array);

