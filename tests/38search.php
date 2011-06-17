<?php
require_once dirname(dirname(__FILE__)) . '/lime.php';
require_once dirname(dirname(__FILE__)) . '/src/SqliteArray.php';

$t = new lime_test();


/**
 * 値の検索
 *
 */
$ra = array(1, 2, '3'=>3, array(4), '5');
$a = new SqliteArray($ra);

$t->is_deeply($a->searchValue(1), array_search(1, $ra, true));
$t->is_deeply($a->searchValue(2), array_search(2, $ra, true));
$t->is_deeply($a->searchValue(3), array_search(3, $ra, true));
$t->is_deeply($a->searchValue('3'), array_search('3', $ra, true));
$t->is_deeply($a->searchValue(array(4)), array_search(array(4), $ra, true));
$t->is_deeply($a->searchValue(array('4')), array_search(array('4'), $ra, true));
$t->is_deeply($a->searchValue('5'), array_search('5', $ra, true));
$t->is_deeply($a->searchValue(5), array_search(5, $ra, true));
$t->is_deeply($a->searchValue(6), array_search(6, $ra, true));


$ra = array(1, 2, 2);
$a = new SqliteArray($ra);

$t->is_deeply($a->searchValue(2), array_search(2, $ra, true));
