<?php
require_once dirname(dirname(__FILE__)) . '/lime.php';
require_once dirname(dirname(__FILE__)) . '/src/SqliteArray.php';

$t = new lime_test();

$array = new SqliteArray();
$t->is_deeply(1, $array->set(1, 1));
$t->is_deeply(0, $array->set(1, 0));
$t->is_deeply(null, $array->set(1, null));
$t->is_deeply(true, $array->set(1, true));
$t->is_deeply(false, $array->set(1, false));

$object = new StdClass();
$t->is_deeply($object, $array->set(1, $object));

