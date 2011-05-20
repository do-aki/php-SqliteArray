<?php
require_once dirname(dirname(__FILE__)) . '/lime.php';
require_once dirname(dirname(__FILE__)) . '/src/SqliteArray.php';

$t = new lime_test();


/**
 * ファイル保存/読み込み のテスト
 */
$name = tempnam(sys_get_temp_dir(), __CLASS__);
$a = new SqliteArray(array(1, 2, '3'=>3, array(4), '5'));
$a->saveFile($name);

$b = new SqliteArray();
$b->loadFile($name);

$t->is_deeply($a->getArray(), $b->getArray());

unlink($name);

