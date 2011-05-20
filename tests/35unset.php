<?php
require_once dirname(dirname(__FILE__)) . '/lime.php';
require_once dirname(dirname(__FILE__)) . '/src/SqliteArray.php';

$t = new lime_test();


/**
 * unset
 */
$r = array('A' => 1, 'B' => 2, 'C' => 3);
$a = new SqliteArray($r);
$t->is_deeply($a->getArray(), $r);

unset($a['B']);
unset($r['B']);
$t->is_deeply($a->getArray(), $r);

$a['B'] = 2;
$r['B'] = 2;
$t->is_deeply($a->getArray(), $r);

