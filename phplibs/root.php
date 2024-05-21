<?php
error_reporting(E_ALL);
session_name("pintech_findit");
@session_regenerate_id();
set_time_limit(0);
ini_set('session.gc_maxlifetime', 200000);
ini_set('memory_limit', '256M');
//中文;
header("Content-Type:text/html; charset=utf-8");
date_default_timezone_set('Asia/Taipei');

if (!isset($_SESSION)) {
    session_start();
}

$mysqli = new mysqli("localhost", "feng_root", 'L^dOt0n5Dt8mJJL8', "feng_master");

$mysqli->set_charset("utf8mb4");
//$mysqli->set_charset("utf8");
$mysqli->query("SET time_zone='" . set_mysql_timezone() . "';");

$liff_full = "2004852493-KE6xyzLE";
$liff_close = "2004852493-bA6DO8JW";
$oa = "https://lin.ee/yrIpNyR";

//MYSQL時區同步PHP時區
function set_mysql_timezone()
{
    $now = new DateTime();
    $mins = $now->getOffset() / 60;
    $sgn = ($mins < 0 ? -1 : 1);
    $mins = abs($mins);
    $hrs = floor($mins / 60);
    $mins -= $hrs * 60;
    return sprintf('%+d:%02d', $hrs * $sgn, $mins);
}

//$affected_rows = $mysqli->affected_rows	;	察看影響列數
