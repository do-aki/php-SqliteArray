<?php
require_once dirname(dirname(__FILE__)) . '/lime.php';
require_once dirname(dirname(__FILE__)) . '/src/SqliteArray.php';

$t = new lime_test();

$a = new SqliteArray();

$t->is_deeply(isset($a[1]), false);
$a[1] = 1;
$t->is_deeply(isset($a[1]), true);
unset($a[1]);
$t->is_deeply(isset($a[1]), false);

$a[1] = 1;
$t->is_deeply(isset($a[1]), true);
$a[1] = null;
$t->is_deeply(isset($a[1]), false);

