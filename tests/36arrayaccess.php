<?php
require_once dirname(dirname(__FILE__)) . '/lime.php';
require_once dirname(dirname(__FILE__)) . '/src/SqliteArray.php';

$t = new lime_test();


/**
 * ArrayAccess
 */

$a = new SqliteArray();

$a[] = 1;
$a['3'] = 2;
$a['x'] = 3;
$a[] = 4;
$a[] = 5;

$r = array(1, 3=>2, 'x'=>3, 4, 5);
$t->is_deeply($a->getArray(), $r);

unset($a[4]);
unset($r[4]);
$t->is_deeply($a->getArray(), $r);

$t->is_deeply(isset($a[3]), isset($r[3]));
$t->is_deeply(isset($a[4]), isset($r[4]));
$t->is_deeply(isset($a[5]), isset($r[5]));
$t->is_deeply(isset($a[6]), isset($r[6]));

