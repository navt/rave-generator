<?php
/* 
 * start Generator
 */
error_reporting(E_ALL);
ini_set('display_errors', true);

require 'Rave.php';
try {
    $r = new Navt\Rave("data/zelazny.txt");
    $r->clean();
    //var_dump($r->getGistogram());
    $r->buildMap();
    $r->minQW = 5;
    $r->maxQW = 20;
    $r->qSentences = 10;
    $r->generate();
} catch (RaveException $e) {
    echo $e->getMessage();
    exit(1);
}
$r->printString($r->output, 10);
echo "\r\n\r\n";
$r = null;

$r = new Navt\Rave("Мама мыла раму, пока Маша ела кашу. Раму с мылом мыла, кашу с маслом ела!");
$r->buildMap();
$r->minQW = 6;
$r->maxQW = 6;
$r->qSentences = 1;
$r->generate();
echo $r->output;