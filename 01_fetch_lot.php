<?php

require 'vendor/autoload.php';

use Goutte\Client;
use Symfony\Component\DomCrawler\Field\InputFormField;

$year = date('Y');
$yearPath = __DIR__ . '/data/lot/' . $year;
if (!file_exists($yearPath)) {
    mkdir($yearPath, 0777, true);
}

$client = new Client();
$crawler = $client->request('GET', 'https://kid.tn.edu.tw/KidsAll/Public/Lot1QryP.aspx');
$form = $crawler->filter('form')->form();
$domdocument = new \DOMDocument;
$ff = $domdocument->createElement('input');
$ff->setAttribute('name', 'ctl00$MainContent$btnQry');
$ff->setAttribute('value', '查詢');
$formfield = new InputFormField($ff);
$form->set($formfield);

$areas = array(
    '七股區', '下營區', '大內區', '山上區', '中西區', '仁德區', '六甲區',
    '北門區', '北區 ', '左鎮區', '永康區', '玉井區', '白河區', '安平區', '安定區', '安南區',
    '西港區', '佳里區', '官田區', '東山區', '東區 ', '南化區', '南區 ', '後壁區', '柳營區',
    '將軍區', '麻豆區', '善化區', '新化區', '新市區', '新營區', '楠西區', '學甲區', '龍崎區',
    '歸仁區', '關廟區', '鹽水區'
);
//$fh = fopen($yearPath . '/Lot1QryP.csv', 'w');

foreach ($areas as $area) {
    $yearLotPath = __DIR__ . '/raw/lotp/' . $year;
    if (!file_exists($yearLotPath)) {
        mkdir($yearLotPath, 0777, true);
    }
    $crawler = $client->submit($form, array('ctl00$MainContent$ddlArea' => $area));
    $form = $crawler->filter('form')->form();
    $domdocument = new \DOMDocument;
    $ff = $domdocument->createElement('input');
    $ff->setAttribute('name', 'ctl00$MainContent$btnQry');
    $ff->setAttribute('value', '查詢');
    $formfield = new InputFormField($ff);
    $form->set($formfield);

    $ff = $domdocument->createElement('input');
    $ff->setAttribute('name', 'ctl00$MainContent$btnQryAdm');
    $ff->setAttribute('value', '錄取名單');
    $formfield = new InputFormField($ff);
    $form->set($formfield);

    $ff = $domdocument->createElement('input');
    $ff->setAttribute('name', 'ctl00$MainContent$btnQryPre');
    $ff->setAttribute('value', '備取名單');
    $formfield = new InputFormField($ff);
    $form->set($formfield);

    $schoolsPage = $client->getResponse()->getContent();
    $pos = strpos($schoolsPage, '<select name="ctl00$MainContent$ddlSch"');
    $posEnd = strpos($schoolsPage, '</select>', $pos);
    $schools = explode('</option>', substr($schoolsPage, $pos, $posEnd - $pos));
    foreach ($schools as $school) {
        $pos = strpos($school, '>');
        $schoolName = substr($school, $pos + 1);
        $schoolName = trim(strip_tags($schoolName));
        if(empty($schoolName)) {
            continue;
        }
        $client->submit($form, [
            'ctl00$MainContent$ddlSch' => $schoolName,
            'ctl00$MainContent$rbStage' => '2',
            'ctl00$MainContent$btnQryAdm' => '錄取名單',
            'ctl00$MainContent$btnQryPre' => '',
        ]);
        $school2 = $client->getResponse()->getContent();
        $pos = strpos($school2, '<span id="MainContent_lbMsg"');
        $posEnd = strpos($school2, '<span id="lbBOEadmin"', $pos);
        $lot2Part1 = substr($school2, $pos, $posEnd - $pos);

        $client->submit($form, [
            'ctl00$MainContent$ddlSch' => $schoolName,
            'ctl00$MainContent$rbStage' => '2',
            'ctl00$MainContent$btnQryAdm' => '',
            'ctl00$MainContent$btnQryPre' => '備取名單',
        ]);
        $school2 = $client->getResponse()->getContent();
        $pos = strpos($school2, '<span id="MainContent_lbMsg"');
        $posEnd = strpos($school2, '<span id="lbBOEadmin"', $pos);
        $lot2Part2 = substr($school2, $pos, $posEnd - $pos);

        $client->submit($form, [
            'ctl00$MainContent$ddlSch' => $schoolName,
            'ctl00$MainContent$rbStage' => '3',
            'ctl00$MainContent$btnQryAdm' => '錄取名單',
            'ctl00$MainContent$btnQryPre' => '',
        ]);
        $school3 = $client->getResponse()->getContent();
        $pos = strpos($school3, '<span id="MainContent_lbMsg"');
        $posEnd = strpos($school3, '<span id="lbBOEadmin"', $pos);
        $lot3Part1 = substr($school3, $pos, $posEnd - $pos);

        $client->submit($form, [
            'ctl00$MainContent$ddlSch' => $schoolName,
            'ctl00$MainContent$rbStage' => '3',
            'ctl00$MainContent$btnQryAdm' => '',
            'ctl00$MainContent$btnQryPre' => '備取名單',
        ]);
        $school3 = $client->getResponse()->getContent();
        $pos = strpos($school3, '<span id="MainContent_lbMsg"');
        $posEnd = strpos($school3, '<span id="lbBOEadmin"', $pos);
        $lot3Part2 = substr($school3, $pos, $posEnd - $pos);
        
        $rawFile = $yearLotPath . '/' . $area . '_' . $schoolName . '.html';
        file_put_contents($rawFile, $lot2Part1 . "\n--\n" . $lot3Part1 . "\n--\n" . $lot2Part2 . "\n--\n" . $lot3Part2);
    }
}

$crawler = $client->request('GET', 'https://kid.tn.edu.tw/KidsAll/Public/Lot1QryN.aspx');
$form = $crawler->filter('form')->form();
$domdocument = new \DOMDocument;
$ff = $domdocument->createElement('input');
$ff->setAttribute('name', 'ctl00$MainContent$btnQry');
$ff->setAttribute('value', '查詢');
$formfield = new InputFormField($ff);
$form->set($formfield);

$areas = array(
    '永康區', '安南區', '東區 ', '南區 ', '新營區', '麻豆區', '七股區',
);

//$fh = fopen($yearPath . '/Lot1QryP.csv', 'w');

foreach ($areas as $area) {
    $yearLotPath = __DIR__ . '/raw/lotn/' . $year;
    if (!file_exists($yearLotPath)) {
        mkdir($yearLotPath, 0777, true);
    }
    $crawler = $client->submit($form, array('ctl00$MainContent$ddlArea' => $area));
    $form = $crawler->filter('form')->form();
    $domdocument = new \DOMDocument;
    $ff = $domdocument->createElement('input');
    $ff->setAttribute('name', 'ctl00$MainContent$btnQry');
    $ff->setAttribute('value', '查詢');
    $formfield = new InputFormField($ff);
    $form->set($formfield);

    $ff = $domdocument->createElement('input');
    $ff->setAttribute('name', 'ctl00$MainContent$btnQryAdm');
    $ff->setAttribute('value', '錄取名單');
    $formfield = new InputFormField($ff);
    $form->set($formfield);

    $ff = $domdocument->createElement('input');
    $ff->setAttribute('name', 'ctl00$MainContent$btnQryPre');
    $ff->setAttribute('value', '備取名單');
    $formfield = new InputFormField($ff);
    $form->set($formfield);

    $schoolsPage = $client->getResponse()->getContent();
    $pos = strpos($schoolsPage, '<select name="ctl00$MainContent$ddlSch"');
    $posEnd = strpos($schoolsPage, '</select>', $pos);
    $schools = explode('</option>', substr($schoolsPage, $pos, $posEnd - $pos));
    foreach ($schools as $school) {
        $pos = strpos($school, '>');
        $schoolName = substr($school, $pos + 1);
        $schoolName = trim(strip_tags($schoolName));
        if(empty($schoolName)) {
            continue;
        }
        $client->submit($form, [
            'ctl00$MainContent$ddlSch' => $schoolName,
            'ctl00$MainContent$rbStage' => '2',
            'ctl00$MainContent$btnQryAdm' => '錄取名單',
            'ctl00$MainContent$btnQryPre' => '',
        ]);
        $school2 = $client->getResponse()->getContent();
        $pos = strpos($school2, '<span id="MainContent_lbMsg"');
        $posEnd = strpos($school2, '<span id="lbBOEadmin"', $pos);
        $lot2Part1 = substr($school2, $pos, $posEnd - $pos);

        $client->submit($form, [
            'ctl00$MainContent$ddlSch' => $schoolName,
            'ctl00$MainContent$rbStage' => '2',
            'ctl00$MainContent$btnQryAdm' => '',
            'ctl00$MainContent$btnQryPre' => '備取名單',
        ]);
        $school2 = $client->getResponse()->getContent();
        $pos = strpos($school2, '<span id="MainContent_lbMsg"');
        $posEnd = strpos($school2, '<span id="lbBOEadmin"', $pos);
        $lot2Part2 = substr($school2, $pos, $posEnd - $pos);

        $client->submit($form, [
            'ctl00$MainContent$ddlSch' => $schoolName,
            'ctl00$MainContent$rbStage' => '3',
            'ctl00$MainContent$btnQryAdm' => '錄取名單',
            'ctl00$MainContent$btnQryPre' => '',
        ]);
        $school3 = $client->getResponse()->getContent();
        $pos = strpos($school3, '<span id="MainContent_lbMsg"');
        $posEnd = strpos($school3, '<span id="lbBOEadmin"', $pos);
        $lot3Part1 = substr($school3, $pos, $posEnd - $pos);

        $client->submit($form, [
            'ctl00$MainContent$ddlSch' => $schoolName,
            'ctl00$MainContent$rbStage' => '3',
            'ctl00$MainContent$btnQryAdm' => '',
            'ctl00$MainContent$btnQryPre' => '備取名單',
        ]);
        $school3 = $client->getResponse()->getContent();
        $pos = strpos($school3, '<span id="MainContent_lbMsg"');
        $posEnd = strpos($school3, '<span id="lbBOEadmin"', $pos);
        $lot3Part2 = substr($school3, $pos, $posEnd - $pos);
        
        $rawFile = $yearLotPath . '/' . $area . '_' . $schoolName . '.html';
        file_put_contents($rawFile, $lot2Part1 . "\n--\n" . $lot3Part1 . "\n--\n" . $lot2Part2 . "\n--\n" . $lot3Part2);
    }
}