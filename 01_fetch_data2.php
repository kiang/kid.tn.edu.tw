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
$crawler = $client->request('GET', 'https://kid.tn.edu.tw/KidsAll/Public/AllSch2.aspx');
$form = $crawler->filter('form')->form();
$domdocument = new \DOMDocument;
$ff = $domdocument->createElement('input');
$ff->setAttribute('name', 'ctl00$MainContent$btnQry');
$ff->setAttribute('value', '查詢');
$formfield = new InputFormField($ff);
$form->set($formfield);

$areas = array(
    '七股區', '下營區', '大內區', '山上區', '中西區', '仁德區', '六甲區',
    '北門區', '北區', '左鎮區', '永康區', '玉井區', '白河區', '安平區', '安定區', '安南區',
    '西港區', '佳里區', '官田區', '東山區', '東區', '南化區', '南區', '後壁區', '柳營區',
    '將軍區', '麻豆區', '善化區', '新化區', '新市區', '新營區', '楠西區', '學甲區', '龍崎區',
    '歸仁區', '關廟區', '鹽水區'
);
$fh = fopen($yearPath . '/data2.csv', 'w');
fputcsv($fh, array('行政區', '類型', '學校', '2歲', '3歲', '4歲', '5歲', '3-5歲', '簡章下載', '電話'));
foreach ($areas as $area) {
    $client->submit($form, array('ctl00$MainContent$rbArea' => $area));
    $y2 = getTableRows($client->getResponse()->getContent());
    if (!empty($y2)) {
        foreach ($y2 as $line) {
            fputcsv($fh, array_merge([$area], $line));
        }
    }
}

function getTableRows($c)
{
    if (false !== strpos($c, '<font color="Red">查無資料!!')) {
        return [];
    }
    $c = str_replace('&nbsp;', '', $c);
    $pos = strpos($c, 'id="MainContent_gv">');
    if (false === $pos) {
        return [];
    }
    $posEnd = strpos($c, '</table>', $pos);
    $lines = explode('</tr>', substr($c, $pos, $posEnd - $pos));
    $result = array();
    foreach ($lines as $line) {
        $cols = explode('</td>', $line);
        if (!isset($cols[7])) {
            continue;
        }
        $parts = explode('"', $cols[7]);
        $cols[7] = '';
        if (isset($parts[3])) {
            $cols[7] = 'https://kid.tn.edu.tw/KidsAll/' . substr($parts[3], 3);
        }
        foreach ($cols as $k => $v) {
            $cols[$k] = trim(strip_tags($v));
        }
        $cols[8] = '06-' . $cols[8];
        array_pop($cols);
        $result[] = $cols;
    }
    return $result;
}
