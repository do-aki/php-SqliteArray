<?php
require_once dirname(dirname(__FILE__)) . '/lime.php';
require_once dirname(dirname(__FILE__)) . '/src/SqliteArray.php';

$t = new lime_test();

$array = new SqliteArray();


$fixture = array(
    0  => 1,
    100 => '100',
    'hoge' => 'hogehoge',
    '' => 'empty string',
);

foreach ($fixture as $k=>$v) {
    $array->set($k, $v);
    $t->is_deeply($array->get($k), $v);
}

