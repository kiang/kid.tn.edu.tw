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

/*
    [0] => 區別
    [1] => 幼兒園名稱
    [2] => 核准設立日期
    [3] => 設立許可文號
    [4] => 幼兒園電話
    [5] => 分機
    [6] => 幼兒園住址
    [7] => 核定總招收人數
 */
$fh = fopen(__DIR__ . '/raw/ref/private.csv', 'r');
$header = fgetcsv($fh, 2048);
$header[0] = '區別';
$pool = [];
while ($line = fgetcsv($fh, 2048)) {
    if (empty($line[1])) {
        continue;
    }
    $pool[$line[1]] = array_combine($header, $line);
}
/*
    [0] => 行政區代碼
    [1] => 區別
    [2] => 幼兒園名稱
    [3] => 核准設立日期
    [4] => 設立許可文號
    [5] => 幼兒園電話
    [6] => 分機
    [7] => 幼兒園住址
    [8] => 核定總招收人數
 */
$fh = fopen(__DIR__ . '/raw/ref/public.csv', 'r');
fgetcsv($fh, 2048);
$result = array();
while ($line = fgetcsv($fh, 2048)) {
    array_shift($line);
    if (empty($line[1])) {
        continue;
    }
    $pool[$line[1]] = array_combine($header, $line);
}
foreach ($pool as $k => $v) {
    $parts = preg_split('/[^0-9]/', $pool[$k]['幼兒園電話']);
    if (!isset($parts[2])) {
        continue;
    }
    if (empty($v['幼兒園名稱'])) {
        unset($pool[$k]);
    }
    $pool[$k]['幼兒園電話'] = $parts[2];
}

$data2File = $yearPath . '/data2.csv';
$lot = [];
if(!file_exists($data2File)) {
    $lotFile = __DIR__ . '/data/lot/' . $year . '.json';
    $lot = json_decode(file_get_contents($lotFile), true);

    $fh = fopen($yearPath . '/data.csv', 'r');
} else {
    $fh = fopen($data2File, 'r');
}


$header = fgetcsv($fh, 2048);
while ($line = fgetcsv($fh, 2048)) {
    $line[0] = str_replace(' ', '', $line[0]);
    $data = array_combine($header, $line);
    if(isset($lot[$data['學校']])) {
        if(!empty($lot[$data['學校']]['錄取'][3])) {
            if(!empty($data['3-5歲'])) {
                $data['3-5歲'] = intval($data['3-5歲']) - $lot[$data['學校']]['錄取'][3];
            } else {
                $data['3歲'] = intval($data['3歲']) - $lot[$data['學校']]['錄取'][3];
            }
        }
        if(!empty($lot[$data['學校']]['錄取'][2])) {
            $data['2歲'] = intval($data['2歲']) - $lot[$data['學校']]['錄取'][2];
        }
    }
    if (isset($pool[$data['學校']])) {
        $info = $pool[$data['學校']];
    } else {
        $infoFound = false;
        foreach ($pool as $k => $v) {
            if (false !== strpos($data['電話'], $v['幼兒園電話'])) {
                $info = $v;
                $infoFound = true;
                break;
            }
        }
        if (false === $infoFound) {
            switch ($data['學校']) {
                case '七農非營利幼兒園':
                    $info = $pool['臺南市七農非營利幼兒園(臺南市七股區農會申請辦理)'];
                    break;
                case '大內國小附設幼兒園':
                    $info = $pool['臺南市大內區大內國民小學附設幼兒園'];
                    break;
                case '成功國小附設幼兒園':
                    $info = $pool['臺南市中西區成功國民小學附設幼兒園'];
                    break;
                case '忠義國小附設幼兒園':
                    $info = $pool['臺南市中西區忠義國民小學附設幼兒園'];
                    break;
                case '協進國小附設幼兒園':
                    $info = $pool['臺南市中西區協進國民小學附設幼兒園'];
                    break;
                case '仁和國小附設幼兒園':
                    $info = $pool['臺南市仁德區仁和國民小學附設幼兒園'];
                    break;
                case '永康復興國小附設幼兒園':
                    $info = $pool['臺南市永康區復興國民小學附設幼兒園'];
                    break;
                case '崑山國小附設幼兒園':
                    $info = $pool['臺南市永康區崑山國民小學附設幼兒園'];
                    break;
                case '傑尼爾幼兒園':
                    $info = $pool['臺南市私立傑尼爾幼兒園'];
                    break;
                case '大竹國小附設幼兒園':
                    $info = $pool['臺南市白河區大竹國民小學附設幼兒園'];
                    break;
                case '安平國小附設幼兒園':
                    $info = $pool['臺南市安平區安平國民小學附設幼兒園'];
                    break;
                case '新南國小附設幼兒園':
                    $info = $pool['臺南市安平區新南國民小學附設幼兒園'];
                    break;
                case '南安國小附設幼兒園':
                    $info = $pool['臺南市安定區南安國民小學附設幼兒園'];
                    break;
                case '長安國小附設幼兒園':
                    $info = $pool['臺南市安南區長安國民小學附設幼兒園'];
                    break;
                case '愛上學幼兒園':
                    $info = $pool['臺南市私立愛上學幼兒園'];
                    break;
                case '福祺幼兒園':
                    $info = $pool['臺南市私立福祺幼兒園'];
                    break;
                case '佳里國小附設幼兒園':
                    $info = $pool['臺南市佳里區佳里國民小學附設幼兒園'];
                    break;
                case '大同國小附設幼兒園':
                    $info = $pool['臺南市東區大同國民小學附設幼兒園'];
                    break;
                case '勝利國小附設幼兒園':
                    $info = $pool['臺南市東區勝利國民小學附設幼兒園'];
                    break;
                case '崇德幼兒園':
                    $info = $pool['臺南市私立領袖學院崇德幼兒園'];
                    break;
                case '麻豆國小附設幼兒園':
                    $info = $pool['臺南市麻豆區麻豆國民小學附設幼兒園'];
                    break;
                case '新市國小附設幼兒園':
                    $info = $pool['臺南市新市區新市國民小學附設幼兒園'];
                    break;
                case '楠西國小附設幼兒園':
                    $info = $pool['臺南市楠西區楠西國民小學附設幼兒園'];
                    break;
                case '歡雅國小附設幼兒園':
                    $info = $pool['臺南市鹽水區歡雅國民小學附設幼兒園'];
                    break;
                case '全人裕文非營利幼兒園':
                    $info = [
                        '區別' => '東區',
                        '幼兒園名稱' => '臺南市全人裕文非營利幼兒園(委託社團法人台灣全人發展福利學會辦理)',
                        '核准設立日期' => '',
                        '設立許可文號' => '',
                        '幼兒園電話' => '06-2085810、0921-207670',
                        '分機' => '',
                        '幼兒園住址' => '[701]臺南市東區後甲里8鄰瑞吉街255號',
                        '核定總招收人數' => '',
                    ];
                    break;
                case '臺灣文學館員工子女非營利幼兒園':
                    $info = [
                        '區別' => '東區',
                        '幼兒園名稱' => '國立臺灣文學館員工子女非營利幼兒園(委託社團法人台灣公共托育協會辦理)',
                        '核准設立日期' => '',
                        '設立許可文號' => '',
                        '幼兒園電話' => '(06)2201239',
                        '分機' => '',
                        '幼兒園住址' => '[701]臺南市東區成大里16鄰北門路二段16號',
                        '核定總招收人數' => '',
                    ];
                    break;
                case '沙崙高中附設幼兒園':
                    $info = [
                        '區別' => '歸仁區',
                        '幼兒園名稱' => '臺南市立沙崙國際高級中等學校附設幼兒園',
                        '核准設立日期' => '',
                        '設立許可文號' => '',
                        '幼兒園電話' => '06-3032062',
                        '分機' => '101或745',
                        '幼兒園住址' => '[711]臺南市歸仁區武東里１１鄰歸仁六路２號',
                        '核定總招收人數' => '',
                    ];
                    break;
            }
        }
    }
    $info['類型'] = $data['類型'];
    $info['簡章下載'] = $data['簡章下載'];
    $info['招生'] = [
        '2歲' => $data['2歲'],
        '3歲' => $data['3歲'],
        '4歲' => $data['4歲'],
        '5歲' => $data['5歲'],
        '3-5歲' => $data['3-5歲'],
    ];
    $result[] = $info;
}


foreach ($result as $k1 => $v1) {
    if (!isset($v1['幼兒園住址'])) {
        print_r($v1);
        exit();
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
            if(isset($json['ITEM'][0])) {
                $json['ITEM'] = $json['ITEM'][0];
            }
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
    if (isset($json['AddressList'][0])) {
        $result[$k1]['longitude'] = $json['AddressList'][0]['X'];
        $result[$k1]['latitude'] = $json['AddressList'][0]['Y'];
    } else {
        switch ($k1) {
            case '臺南市新營區新橋國民小學附設幼兒園':
                $result[$k1]['longitude'] = 120.277234;
                $result[$k1]['latitude'] = 23.267999;
                break;
            case '臺南市鹽水區仁光國民小學附設幼兒園':
                $result[$k1]['longitude'] = 120.257428;
                $result[$k1]['latitude'] = 23.300257;
                break;
            case '臺南市鹽水區文昌國民小學附設幼兒園':
                $result[$k1]['longitude'] = 120.252375;
                $result[$k1]['latitude'] = 23.309564;
                break;
            case '臺南市鹽水區竹埔國民小學附設幼兒園':
                $result[$k1]['longitude'] = 120.246008;
                $result[$k1]['latitude'] = 23.266783;
                break;
            case '臺南市鹽水區坔頭港國民小學附設幼兒園':
                $result[$k1]['longitude'] = 120.249887;
                $result[$k1]['latitude'] = 23.254829;
                break;
            case '臺南市白河區玉豐國民小學附設幼兒園':
                $result[$k1]['longitude'] = 120.416289;
                $result[$k1]['latitude'] = 23.379351;
                break;
            case '臺南市後壁區新東國民小學附設幼兒園':
                $result[$k1]['longitude'] = 120.31591;
                $result[$k1]['latitude'] = 23.344694;
                break;
            case '臺南市後壁區菁寮國民小學附設幼兒園':
                $result[$k1]['longitude'] = 120.339171;
                $result[$k1]['latitude'] = 23.379014;
                break;
            case '臺南市麻豆區安業國民小學附設幼兒園':
                $result[$k1]['longitude'] = 120.246187;
                $result[$k1]['latitude'] = 23.160281;
                break;
            case '臺南市麻豆區紀安國民小學附設幼兒園':
                $result[$k1]['longitude'] = 120.237652;
                $result[$k1]['latitude'] = 23.151529;
                break;
            case '臺南市官田區渡拔國民小學附設幼兒園':
                $result[$k1]['longitude'] = 120.326753;
                $result[$k1]['latitude'] = 23.171166;
                break;
            case '臺南市佳里區延平國民小學附設幼兒園':
                $result[$k1]['longitude'] = 120.1526;
                $result[$k1]['latitude'] = 23.178826;
                break;
            case '臺南市佳里區通興國民小學附設幼兒園':
                $result[$k1]['longitude'] = 120.157462;
                $result[$k1]['latitude'] = 23.144763;
                break;
            case '臺南市佳里區塭內國民小學附設幼兒園':
                $result[$k1]['longitude'] = 120.165017;
                $result[$k1]['latitude'] = 23.125652;
                break;
            case '臺南市北門區蚵寮國民小學附設幼兒園':
                $result[$k1]['longitude'] = 120.138302;
                $result[$k1]['latitude'] = 23.286455;
                break;
            case '臺南市新化區那拔國民小學附設幼兒園':
                $result[$k1]['longitude'] = 120.349533;
                $result[$k1]['latitude'] = 23.068337;
                break;
            case '臺南市安定區南興國民小學附設幼兒園':
                $result[$k1]['longitude'] = 120.218791;
                $result[$k1]['latitude'] = 23.080518;
                break;
            case '臺南市安定區南安國民小學附設幼兒園':
                $result[$k1]['longitude'] = 120.22497;
                $result[$k1]['latitude'] = 23.098769;
                break;
            case '臺南市立小康幼兒園文元分班':
                $result[$k1]['longitude'] = 120.194407;
                $result[$k1]['latitude'] = 23.010028;
                break;
            case '臺南市永康區三村國民小學附設幼兒園':
                $result[$k1]['longitude'] = 120.237161;
                $result[$k1]['latitude'] = 23.041737;
                break;
            case '臺南市立九份子國民中小學附設幼兒園':
                $result[$k1]['longitude'] = 120.183607;
                $result[$k1]['latitude'] = 23.022506;
                break;
            default:
                echo "{$k1}\n";
        }
    }
}
file_put_contents(__DIR__ . '/result.json', json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
