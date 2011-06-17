<?php
require_once dirname(dirname(__FILE__)) . '/lime.php';
require_once dirname(dirname(__FILE__)) . '/src/SqliteArray.php';

$t = new lime_test();

/**
 * 共通項の計算テスト
 */
$ra1 = array('a'=>1,'b'=>2,'c'=>3,'d'=>4,'e'=>5);
$a1 = new SqliteArray($ra1);

$ra2 = array('c'=>1,'d'=>2,'e'=>3, 'f'=>4, 'g'=>5);
$a2 = new SqliteArray($ra2);


$t->is_deeply($a1->intersectKey($a2)->getArray(), array_intersect_key($ra1, $ra2), "rhs=SqliteArray"); // array('c'=>3,'d'=>4,'e'=>5);
$t->is_deeply($a1->intersectKey($ra2)->getArray(), array_intersect_key($ra1, $ra2), "rhs=Real Array"); // array('c'=>3,'d'=>4,'e'=>5);


$ra1 = array(1=>1,2=>2,3=>3);
$a1 = new SqliteArray($ra1);

$ra2 = array(2=>2,'3'=>3, 4=>4);
$a2 = new SqliteArray($ra2);

$t->is_deeply($a1->intersectKey($a2)->getArray(), array_intersect_key($ra1, $ra2), "rhs=SqliteArray"); // array(2=>2, 3=>3);
$t->is_deeply($a1->intersectKey($ra2)->getArray(), array_intersect_key($ra1, $ra2), "rhs=Real Array"); // array(2=>2, 3=>3);


