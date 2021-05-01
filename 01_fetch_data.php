<?php

require 'vendor/autoload.php';

use Goutte\Client;
use Symfony\Component\DomCrawler\Field\InputFormField;

$client = new Client();
$crawler = $client->request('GET', 'https://kid.tn.edu.tw/KidAdm/Public/Reg1Stat.aspx');
$form = $crawler->selectButton('查詢')->form();
$btn = $form->get('ctl00$MainContent$btnQry');
if ($btn->isDisabled()) {
    $domdocument = new \DOMDocument;
    $ff = $domdocument->createElement('input');
    $ff->setAttribute('name', 'ctl00$MainContent$btnQry');
    $ff->setAttribute('value', '查詢');
    $formfield = new InputFormField($ff);
    $form->set($formfield);
}
$areas = array('七股區', '下營區', '大內區', '山上區', '中西區', '仁德區', '六甲區',
    '北門區', '北區 ', '左鎮區', '永康區', '玉井區', '白河區', '安平區', '安定區', '安南區',
    '西港區', '佳里區', '官田區', '東山區', '東區 ', '南化區', '南區 ', '後壁區', '柳營區',
    '將軍區', '麻豆區', '善化區', '新化區', '新市區', '新營區', '楠西區', '學甲區', '龍崎區',
    '歸仁區', '關廟區', '鹽水區');
$fh = fopen(__DIR__ . '/data/2021/data.csv', 'w');
fputcsv($fh, array('行政區', '類型', '幼兒園', '可招生名額', '登記名額', '錄取名額', '招生簡章網址', '-'));
foreach ($areas AS $area) {
    $client->submit($form, array('ctl00$MainContent$ddlArea' => $area, 'ctl00$MainContent$rbStage' => '2'));
    $y2 = getTableRows($client->getResponse()->getContent());
    if (!empty($y2)) {
        foreach ($y2 AS $line) {
            fputcsv($fh, array_merge(array($area, '2歲'), $line));
        }
    }
    $client->submit($form, array('ctl00$MainContent$ddlArea' => $area, 'ctl00$MainContent$rbStage' => '3'));
    $y3 = getTableRows($client->getResponse()->getContent());
    if (!empty($y3)) {
        foreach ($y3 AS $line) {
            fputcsv($fh, array_merge(array($area, '3歲以上'), $line));
        }
    }
}

function getTableRows($c) {
    if (false !== strpos($c, '<font color="Red">查無資料!!')) {
        return [];
    }
    $pos = strpos($c, 'id="MainContent_gvStat">');
    if (false === $pos) {
        return [];
    }
    $posEnd = strpos($c, '</table>', $pos);
    $lines = explode('</tr>', substr($c, $pos, $posEnd - $pos));
    $result = array();
    foreach ($lines AS $line) {
        $cols = explode('</td>', $line);
        foreach ($cols AS $k => $v) {
            $cols[$k] = trim(strip_tags($v));
        }
        if (count($cols) === 6) {
            $result[] = $cols;
        }
    }
    return $result;
}
