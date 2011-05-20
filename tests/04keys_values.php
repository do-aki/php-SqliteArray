<?php
require_once dirname(dirname(__FILE__)) . '/lime.php';
require_once dirname(dirname(__FILE__)) . '/src/SqliteArray.php';

$t = new lime_test();

//---- test1
$ra = array('a'=>10, 'b'=>20, 'c'=>30, 'd'=>40, 'e'=>50);
$a = new SqliteArray($ra);

$t->is_deeply($a->keys()->toArray(), array_keys($ra), "test1 key");
$t->is_deeply($a->values()->toArray(), array_values($ra), "test1 value");


//---- test2
$ra = array(1=>'a', null=>'b', '3'=>'c');
$a = new SqliteArray($ra);

$t->is_deeply($a->keys()->toArray(), array_keys($ra), "test2 key");
$t->is_deeply($a->values()->toArray(), array_values($ra), "test2 value");
