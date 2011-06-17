<?php
require_once dirname(dirname(__FILE__)) . '/lime.php';
require_once dirname(dirname(__FILE__)) . '/src/SqliteArray.php';

$t = new lime_test();

/**
 * count のテスト
 */
$a = new SqliteArray();
$t->is_deeply($a->count(), 0);
$a->push(1);
$t->is_deeply($a->count(), 1);
$a->set("xxx", 1);
$t->is_deeply($a->count(), 2);
$a->remove("xxx");
$t->is_deeply($a->count(), 1);

