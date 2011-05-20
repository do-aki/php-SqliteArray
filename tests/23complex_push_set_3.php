<?php
require_once dirname(dirname(__FILE__)) . '/lime.php';
require_once dirname(dirname(__FILE__)) . '/src/SqliteArray.php';

$t = new lime_test();


/**
 * push/set “®ì‚ÌƒeƒXƒg3
 *
 */
$array = new SqliteArray();
$real_array = array();

// array(1=>1)
$array->set('1', 1);
$real_array['1'] = 1;
$t->is_deeply($array->getArray(), $real_array);

// array(1=>1, 0=>2)
$array->set('0', 2);
$real_array['0'] = 2;
$t->is_deeply($array->getArray(), $real_array);

// array(1=>1, 0=>2, 2=>3)
$array->push(3);
$real_array[] = 3;
$t->is_deeply($array->getArray(), $real_array);


$array = new SqliteArray();
$real_array = array();

// array(-5=>4)
$array->set(-5, 4);
$real_array[-5] = 4;
$t->is_deeply($array->getArray(), $real_array);

// array(-5=>3, 0=>5)
$array->push(5);
$real_array[] = 5;
$t->is_deeply($array->getArray(), $real_array);

// array(-5=>3, 0=>6)
$array->set(0, 6);
$real_array[0] = 6;
$t->is_deeply($array->getArray(), $real_array);

// array(-5=>3, 0=>6, 'test'=>7)
$array->set('test', 7);
$real_array['test'] = 7;
$t->is_deeply($array->getArray(), $real_array);

// array(-5=>3, 0=>6, 'test'=>8)
$array->set('test', 8);
$real_array['test'] = 8;
$t->is_deeply($array->getArray(), $real_array);

