<?php
require_once dirname(dirname(__FILE__)) . '/lime.php';
require_once dirname(dirname(__FILE__)) . '/src/SqliteArray.php';

$t = new lime_test();

// offsetSet ‚Ì‹““®‚É‚æ‚èA–Í•í‚Å‚«‚È‚¢
$ra = array();
$a = new SqliteArray();

$ra[null] = 1;
$a[null] = 1;

$t->is_deeply($ra          , array(''=>1));
$t->is_deeply($a->toArray(), array(0 =>1));

