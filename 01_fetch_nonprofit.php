<?php

require 'vendor/autoload.php';

use Goutte\Client;
use Symfony\Component\DomCrawler\Field\InputFormField;

$year = date('Y');
$yearPath = __DIR__ . '/data/' . $year;
if (!file_exists($yearPath)) {
    mkdir($yearPath, 0777, true);
}

$client = new Client();
$crawler = $client->request('GET', 'https://kid.tn.edu.tw/NonProfit/Public/Reg1Stat.aspx');
$form = $crawler->filter('form')->form();
$btn = false;
if ($crawler->filter('#ctl00\\$MainContent\\$btnQry')->count() > 0) {
    $btn = $form->get('ctl00$MainContent$btnQry');
}
if (false === $btn || $btn->isDisabled()) {
    $domdocument = new \DOMDocument;
    $ff = $domdocument->createElement('input');
    $ff->setAttribute('name', 'ctl00$MainContent$btnQry');
    $ff->setAttribute('value', '查詢');
    $formfield = new InputFormField($ff);
    $form->set($formfield);
}
$areas = array('永康區', '安平區', '安南區', '東區 ', '南區 ', '新營區', '麻豆區',);
$fh = fopen($yearPath . '/nonprofit.csv', 'w');
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
