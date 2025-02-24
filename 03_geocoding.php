<?php

/*
  Array
  (
  [0] => 行政區
  [1] => 類型
  [2] => 幼兒園
  [3] => 可招生名額
  [4] => 登記名額
  [5] => 錄取名額
  [6] => 簡章下載
  [7] => -
  )
 */

$year = date('Y');
$yearPath = __DIR__ . '/data/' . $year;
if (!file_exists($yearPath)) {
    mkdir($yearPath, 0777, true);
}

$fh = fopen($yearPath . '/data.csv', 'r');
$header = fgetcsv($fh, 2048);
$countHeader = count($header);
$data = array();
while ($line = fgetcsv($fh, 2048)) {
    if (count($line) !== $countHeader) {
        array_pop($line);
    }
    $line[0] = str_replace(' ', '', $line[0]);
    if (!isset($data[$line[0]])) {
        $data[$line[0]] = array();
    }
    if (!isset($data[$line[0]][$line[2]])) {
        $data[$line[0]][$line[2]] = array();
    }
    $data[$line[0]][$line[2]][$line[1]] = array_combine($header, $line);
}

/*

source: https://data.tainan.gov.tw/dataset/tn-preschool
  Array
  (
  [0] => ﻿區別
  [1] => 幼兒園名稱
  [2] => 核准設立日期
  [3] => 設立許可文號
  [4] => 幼兒園電話
  [5] => 幼兒園住址
  [6] => 核定總招收人數
  )
 */
$fh = fopen(__DIR__ . '/15_publickindergarten.csv', 'r');
$header = fgetcsv($fh, 2048);
$result = array();
while ($line = fgetcsv($fh, 2048)) {
    foreach ($data[$line[1]] as $k => $v) {
        if ($line[2] === $k) {
            $row = array_combine($header, $line);
            if (!isset($result[$row['幼兒園名稱']])) {
                $result[$row['幼兒園名稱']] = $row;
                $result[$row['幼兒園名稱']]['招生'] = array();
            }
            foreach ($v as $type => $typeData) {
                $result[$row['幼兒園名稱']]['招生'][$type] = array(
                    '類型' => $type,
                    '2歲' => $typeData['2歲'],
                    '3歲' => $typeData['3歲'],
                    '4歲' => $typeData['4歲'],
                    '5歲' => $typeData['5歲'],
                    '3-5歲' => $typeData['3-5歲'],
                    '登記名額' => 0,
                    '錄取名額' => 0,
                    '簡章下載' => $typeData['簡章下載'],
                );
            }
            unset($data[$line[0]][$k]);
        }
    }
}

foreach ($result as $k1 => $v1) {
    if(!isset($v1['幼兒園住址'])) {
        print_r($v1); exit();
    }
    $pos = strpos($v1['幼兒園住址'], ']');
    if (false !== $pos) {
        $v1['幼兒園住址'] = substr($v1['幼兒園住址'], $pos + 1);
    }
    $rawFile = __DIR__ . '/raw/' . $v1['幼兒園住址'] . '.json';
    if (!file_exists($rawFile)) {
        $command = <<<EOD
curl 'https://api.nlsc.gov.tw/MapSearch/ContentSearch?word=___KEYWORD___&mode=AutoComplete&count=1&feedback=XML' \
   -H 'Accept: application/xml, text/xml, */*; q=0.01' \
   -H 'Accept-Language: zh-TW,zh;q=0.9,en-US;q=0.8,en;q=0.7' \
   -H 'Connection: keep-alive' \
   -H 'Origin: https://maps.nlsc.gov.tw' \
   -H 'Referer: https://maps.nlsc.gov.tw/' \
   -H 'Sec-Fetch-Dest: empty' \
   -H 'Sec-Fetch-Mode: cors' \
   -H 'Sec-Fetch-Site: same-site' \
   -H 'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36' \
   -H 'sec-ch-ua: "Google Chrome";v="123", "Not:A-Brand";v="8", "Chromium";v="123"' \
   -H 'sec-ch-ua-mobile: ?0' \
   -H 'sec-ch-ua-platform: "Linux"'
EOD;
                $nlscResult = shell_exec(strtr($command, [
                    '___KEYWORD___' => urlencode($v1['幼兒園住址']),
                ]));
                $cleanKeyword = trim(strip_tags($nlscResult));
                if (!empty($cleanKeyword)) {
                    $command = <<<EOD
                    curl 'https://api.nlsc.gov.tw/MapSearch/QuerySearch' \
                      -H 'Accept: application/xml, text/xml, */*; q=0.01' \
                      -H 'Accept-Language: zh-TW,zh;q=0.9,en-US;q=0.8,en;q=0.7' \
                      -H 'Connection: keep-alive' \
                      -H 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8' \
                      -H 'Origin: https://maps.nlsc.gov.tw' \
                      -H 'Referer: https://maps.nlsc.gov.tw/' \
                      -H 'Sec-Fetch-Dest: empty' \
                      -H 'Sec-Fetch-Mode: cors' \
                      -H 'Sec-Fetch-Site: same-site' \
                      -H 'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36' \
                      -H 'sec-ch-ua: "Google Chrome";v="123", "Not:A-Brand";v="8", "Chromium";v="123"' \
                      -H 'sec-ch-ua-mobile: ?0' \
                      -H 'sec-ch-ua-platform: "Linux"' \
                      --data-raw 'word=___KEYWORD___&feedback=XML&center=120.218280%2C23.007292'
                    EOD;
                    $nlscResult = shell_exec(strtr($command, [
                        '___KEYWORD___' => urlencode(urlencode($cleanKeyword)),
                    ]));
                    $json = json_decode(json_encode(simplexml_load_string($nlscResult)), true);
                    if (!empty($json['ITEM']['LOCATION'])) {
                        $parts = explode(',', $json['ITEM']['LOCATION']);
                        if (count($parts) === 2) {
                            file_put_contents($rawFile, json_encode([
                                'AddressList' => [
                                    [
                                        'X' => $parts[0],
                                        'Y' => $parts[1],
                                    ],
                                ],
                            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                        }
                    }
                }
    }
    if (file_exists($rawFile)) {
        $content = file_get_contents($rawFile);
        $json = json_decode(file_get_contents($rawFile), true);
    } else {
        $content = '';
        $json = [];
    }


    $result[$k1]['longitude'] = 0;
    $result[$k1]['latitude'] = 0;
    $result[$k1]['cunli'] = '';
    if (isset($json['AddressList'][0])) {
        $result[$k1]['longitude'] = $json['AddressList'][0]['X'];
        $result[$k1]['latitude'] = $json['AddressList'][0]['Y'];
    } else {
        switch ($k1) {
            case '臺南市新營區新橋國民小學附設幼兒園':
                $result[$k1]['longitude'] = 120.277234;
                $result[$k1]['latitude'] = 23.267999;
                $result[$k1]['cunli'] = '五興里';
                break;
            case '臺南市鹽水區仁光國民小學附設幼兒園':
                $result[$k1]['longitude'] = 120.257428;
                $result[$k1]['latitude'] = 23.300257;
                $result[$k1]['cunli'] = '三明里';
                break;
            case '臺南市鹽水區文昌國民小學附設幼兒園':
                $result[$k1]['longitude'] = 120.252375;
                $result[$k1]['latitude'] = 23.309564;
                $result[$k1]['cunli'] = '文昌里';
                break;
            case '臺南市鹽水區竹埔國民小學附設幼兒園':
                $result[$k1]['longitude'] = 120.246008;
                $result[$k1]['latitude'] = 23.266783;
                $result[$k1]['cunli'] = '竹林里';
                break;
            case '臺南市鹽水區坔頭港國民小學附設幼兒園':
                $result[$k1]['longitude'] = 120.249887;
                $result[$k1]['latitude'] = 23.254829;
                $result[$k1]['cunli'] = '坔頭港里';
                break;
            case '臺南市白河區玉豐國民小學附設幼兒園':
                $result[$k1]['longitude'] = 120.416289;
                $result[$k1]['latitude'] = 23.379351;
                $result[$k1]['cunli'] = '詔豐里';
                break;
            case '臺南市後壁區新東國民小學附設幼兒園':
                $result[$k1]['longitude'] = 120.31591;
                $result[$k1]['latitude'] = 23.344694;
                $result[$k1]['cunli'] = '長短樹里';
                break;
            case '臺南市後壁區菁寮國民小學附設幼兒園':
                $result[$k1]['longitude'] = 120.339171;
                $result[$k1]['latitude'] = 23.379014;
                $result[$k1]['cunli'] = '菁寮里';
                break;
            case '臺南市麻豆區安業國民小學附設幼兒園':
                $result[$k1]['longitude'] = 120.246187;
                $result[$k1]['latitude'] = 23.160281;
                $result[$k1]['cunli'] = '井東里';
                break;
            case '臺南市麻豆區紀安國民小學附設幼兒園':
                $result[$k1]['longitude'] = 120.237652;
                $result[$k1]['latitude'] = 23.151529;
                $result[$k1]['cunli'] = '謝厝寮里';
                break;
            case '臺南市官田區渡拔國民小學附設幼兒園':
                $result[$k1]['longitude'] = 120.326753;
                $result[$k1]['latitude'] = 23.171166;
                $result[$k1]['cunli'] = '渡拔里';
                break;
            case '臺南市佳里區延平國民小學附設幼兒園':
                $result[$k1]['longitude'] = 120.1526;
                $result[$k1]['latitude'] = 23.178826;
                $result[$k1]['cunli'] = '下營里';
                break;
            case '臺南市佳里區通興國民小學附設幼兒園':
                $result[$k1]['longitude'] = 120.157462;
                $result[$k1]['latitude'] = 23.144763;
                $result[$k1]['cunli'] = '塭內里';
                break;
            case '臺南市佳里區塭內國民小學附設幼兒園':
                $result[$k1]['longitude'] = 120.165017;
                $result[$k1]['latitude'] = 23.125652;
                $result[$k1]['cunli'] = '塭內里';
                break;
            case '臺南市北門區蚵寮國民小學附設幼兒園':
                $result[$k1]['longitude'] = 120.138302;
                $result[$k1]['latitude'] = 23.286455;
                $result[$k1]['cunli'] = '蚵寮里';
                break;
            case '臺南市新化區那拔國民小學附設幼兒園':
                $result[$k1]['longitude'] = 120.349533;
                $result[$k1]['latitude'] = 23.068337;
                $result[$k1]['cunli'] = '𦰡拔里';
                break;
            case '臺南市安定區南興國民小學附設幼兒園':
                $result[$k1]['longitude'] = 120.218791;
                $result[$k1]['latitude'] = 23.080518;
                $result[$k1]['cunli'] = '嘉同里';
                break;
            case '臺南市安定區南安國民小學附設幼兒園':
                $result[$k1]['longitude'] = 120.22497;
                $result[$k1]['latitude'] = 23.098769;
                $result[$k1]['cunli'] = '文科里';
                break;
            case '臺南市立小康幼兒園文元分班':
                $result[$k1]['longitude'] = 120.194407;
                $result[$k1]['latitude'] = 23.010028;
                $result[$k1]['cunli'] = '元美里';
                break;
            case '臺南市永康區三村國民小學附設幼兒園':
                $result[$k1]['longitude'] = 120.237161;
                $result[$k1]['latitude'] = 23.041737;
                $result[$k1]['cunli'] = '塩洲里';
                break;
            case '臺南市立九份子國民中小學附設幼兒園':
                $result[$k1]['longitude'] = 120.183607;
                $result[$k1]['latitude'] = 23.022506;
                $result[$k1]['cunli'] = '國安里';
                break;
            default:
                echo "{$k1}\n";
        }
    }
}
file_put_contents(__DIR__ . '/result.json', json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
