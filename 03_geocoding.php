<?php

$config = require __DIR__ . '/config.php';
/*
  Array
  (
  [0] => 行政區
  [1] => 類型
  [2] => 幼兒園
  [3] => 可招生名額
  [4] => 登記名額
  [5] => 錄取名額
  [6] => 招生簡章網址
  [7] => -
  )
 */
$fh = fopen(__DIR__ . '/data/2023/data.csv', 'r');
$header = fgetcsv($fh, 2048);
$data = array();
while ($line = fgetcsv($fh, 2048)) {
    $line[2] = str_replace('國小附設幼兒園', '國民小學附設幼兒園', $line[2]);
    switch ($line[2]) {
        case '中山國中附設幼兒園':
            $line[2] = '中山國民中學附設幼兒園';
            break;
        case '永康勝利國民小學附設幼兒園':
            $line[2] = '勝利國民小學附設幼兒園';
            break;
        case '永康復興國民小學附設幼兒園':
            $line[2] = '復興國民小學附設幼兒園';
            break;
        case '安定南興國民小學附設幼兒園':
            $line[2] = '南興國民小學附設幼兒園';
            break;
        case '海佃國中附設幼兒園':
            $line[2] = '海佃國民中學附設幼兒園';
            break;
        case '臺南市立第四幼兒園安順分班':
            $line[2] = '臺南市立第四幼兒園新順分班';
            break;
        case '西港成功國民小學附設幼兒園':
            $line[2] = '成功國民小學附設幼兒園';
            break;
        case '忠孝國中附設幼兒園':
            $line[2] = '忠孝國民中學附設幼兒園';
            break;
        case '善化大同國民小學附設幼兒園':
            $line[2] = '大同國民小學附設幼兒園';
            break;
        case '南梓國民小學附設幼兒園':
            $line[2] = '南梓實驗小學附設幼兒園';
            break;
        case '新營新興國民小學附設幼兒園':
            $line[2] = '新興國民小學附設幼兒園';
            break;
        case '':
            $line[2] = '';
            break;
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

$fh = fopen(__DIR__ . '/data/2023/nonprofit.csv', 'r');
$header = fgetcsv($fh, 2048);
while ($line = fgetcsv($fh, 2048)) {
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
    foreach ($data[$line[0]] as $k => $v) {
        if (false !== strpos($line[1], '臺南市立') && false === strpos($line[1], '國民中學')) {
            if ($line[1] === $k) {
                $row = array_combine($header, $line);
                if (!isset($result[$row['幼兒園名稱']])) {
                    $result[$row['幼兒園名稱']] = $row;
                    $result[$row['幼兒園名稱']]['招生'] = array();
                }
                foreach ($v as $type => $typeData) {
                    $result[$row['幼兒園名稱']]['招生'][$type] = array(
                        '類型' => $type,
                        '可招生名額' => $typeData['可招生名額'],
                        '登記名額' => $typeData['登記名額'],
                        '錄取名額' => $typeData['錄取名額'],
                        '招生簡章網址' => $typeData['招生簡章網址'],
                    );
                }
                unset($data[$line[0]][$k]);
            }
        } elseif (false !== strpos($line[1], $k)) {
            $row = array_combine($header, $line);
            if (!isset($result[$row['幼兒園名稱']])) {
                $result[$row['幼兒園名稱']] = $row;
                $result[$row['幼兒園名稱']]['招生'] = array();
            }
            foreach ($v as $type => $typeData) {
                $result[$row['幼兒園名稱']]['招生'][$type] = array(
                    '類型' => $type,
                    '可招生名額' => $typeData['可招生名額'],
                    '登記名額' => $typeData['登記名額'],
                    '錄取名額' => $typeData['錄取名額'],
                    '招生簡章網址' => $typeData['招生簡章網址'],
                );
            }
            unset($data[$line[0]][$k]);
        }
    }
}

$missing = array(
    '錦湖國民小學附設幼兒園' => array( // http://www.jhes.tn.edu.tw/modules/tadnews/index.php?nsn=290
        '序號' => '',
        '區別' => '北門區',
        '幼兒園名稱' => '臺南市錦湖國小附設幼兒園',
        '幼兒園電話' => '06-7863454',
        '幼兒園住址' => '臺南市北門區錦湖里75號',
        '核定總招收數' => 15,
        '核准設立日期' => '',
        '設立許可文號' => '',
        '類型' => '公立',
        '招生' => array(),
    ),
    '九份子國中小附設幼兒園' => array( //http://www.jfzjps.tn.edu.tw/modules/tadnews/index.php?ncsn=21&nsn=75
        '序號' => '',
        '區別' => '安南區',
        '幼兒園名稱' => '臺南市立九份子國民中小學附設幼兒園',
        '幼兒園電話' => '06-7000818 #69026',
        '幼兒園住址' => '台南市安南區九份子大道8號',
        '核定總招收數' => 51,
        '核准設立日期' => '',
        '設立許可文號' => '',
        '類型' => '公立',
        '招生' => array(),
    ),
    '中洲國民小學附設幼兒園' => array( //https://schoolweb.tn.edu.tw/~jjps_www/modules/tadnews/index.php?ncsn=1&nsn=521
        '序號' => '',
        '區別' => '學甲區',
        '幼兒園名稱' => '臺南市學甲區中洲國民小學附設幼兒園',
        '幼兒園電話' => '06-7833214',
        '幼兒園住址' => '臺南市學甲區光華里645號',
        '核定總招收數' => 12,
        '核准設立日期' => '',
        '設立許可文號' => '',
        '類型' => '公立',
        '招生' => array(),
    ),
    '頂洲國民小學附設幼兒園' => array( //https://www.djues.tn.edu.tw/modules/tadnews/index.php?nsn=613
        '序號' => '',
        '區別' => '學甲區',
        '幼兒園名稱' => '臺南市頂洲國小附設幼兒園',
        '幼兒園電話' => '06-7810231 #12',
        '幼兒園住址' => '臺南市學甲區三慶里頂洲108號',
        '核定總招收數' => 15,
        '核准設立日期' => '',
        '設立許可文號' => '',
        '類型' => '公立',
        '招生' => array(),
    ),
    '坔頭港國民小學附設幼兒園' => array( // https://schoolweb.tn.edu.tw/~hnes_www/modules/tadnews/index.php?nsn=2856
        '序號' => '',
        '區別' => '鹽水區',
        '幼兒園名稱' => '臺南市鹽水區坔頭港國小附設幼兒園',
        '幼兒園電話' => '06-6892014 #10',
        '幼兒園住址' => '臺南市鹽水區坔頭港里202號',
        '核定總招收數' => 10,
        '核准設立日期' => '',
        '設立許可文號' => '',
        '類型' => '公立',
        '招生' => array(),
    ),
    '南興國民小學附設幼兒園' => array( // https://sites.google.com/nses.tn.edu.tw/nseschild
        '序號' => '',
        '區別' => '安南區',
        '幼兒園名稱' => '臺南市安南區南興國民小學附設幼兒園',
        '幼兒園電話' => '06-2873204 #101, #102',
        '幼兒園住址' => '台南市安南區公學路五段627號',
        '核定總招收數' => 11,
        '核准設立日期' => '',
        '設立許可文號' => '',
        '類型' => '公立',
        '招生' => array(),
    ),
    '新生國民小學附設幼兒園' => array( // https://reurl.cc/oLx0xq
        '序號' => '',
        '區別' => '新營區',
        '幼兒園名稱' => '臺南市新營區新生國民小學附設幼兒園',
        '幼兒園電話' => '(06)6552524#11',
        '幼兒園住址' => '臺南市新營區姑爺里52號',
        '核定總招收數' => 11,
        '核准設立日期' => '',
        '設立許可文號' => '',
        '類型' => '公立',
        '招生' => array(),
    ),
    '文和國民小學附設幼兒園' => array( // http://www.whps.tn.edu.tw/modules/tadnews/index.php?nsn=4766
        '序號' => '',
        '區別' => '關廟區',
        '幼兒園名稱' => '臺南市關廟區文和實驗國民小學附設幼兒園',
        '幼兒園電話' => '06-5551937',
        '幼兒園住址' => '臺南市關廟區布袋里3鄰長文街37號',
        '核定總招收數' => 18,
        '核准設立日期' => '',
        '設立許可文號' => '',
        '類型' => '公立',
        '招生' => array(),
    ),
    '臺南市立第四幼兒園新順分班' => array(
        '序號' => '',
        '區別' => '安南區',
        '幼兒園名稱' => '臺南市立第四幼兒園安順分班',
        '幼兒園電話' => '06-3563074',
        '幼兒園住址' => '臺南市安南區安順里14鄰總安街1段146巷90號',
        '核定總招收數' => 30,
        '核准設立日期' => '',
        '設立許可文號' => '',
        '類型' => '公立',
        '招生' => array(),
    ),
    '七股國民小學附設幼兒園' => array( // https://www.cgps.tn.edu.tw/modules/tadnews/index.php
        '序號' => '',
        '區別' => '七股區',
        '幼兒園名稱' => '七股國民小學附設幼兒園',
        '幼兒園電話' => '06-7872076',
        '幼兒園住址' => '臺南巿七股區大埕里395號',
        '核定總招收數' => 15,
        '核准設立日期' => '',
        '設立許可文號' => '',
        '類型' => '公立',
        '招生' => array(),
    ),
    '篤加國民小學附設幼兒園' => array(
        '序號' => '',
        '區別' => '七股區',
        '幼兒園名稱' => '篤加國民小學附設幼兒園',
        '幼兒園電話' => '06-7872536',
        '幼兒園住址' => '台南市七股區篤加里120號',
        '核定總招收數' => 15,
        '核准設立日期' => '',
        '設立許可文號' => '',
        '類型' => '公立',
        '招生' => array(),
    ),
    '仙草國民小學附設幼兒園' => array(
        '序號' => '',
        '區別' => '白河區',
        '幼兒園名稱' => '仙草國民小學附設幼兒園',
        '幼兒園電話' => '06-6854144',
        '幼兒園住址' => '台南市白河區仙草里22號',
        '核定總招收數' => 6,
        '核准設立日期' => '',
        '設立許可文號' => '',
        '類型' => '公立',
        '招生' => array(),
    ),
    '苓和國民小學附設幼兒園' => array(
        '序號' => '',
        '區別' => '將軍區',
        '幼兒園名稱' => '苓和國民小學附設幼兒園',
        '幼兒園電話' => '06-7942031',
        '幼兒園住址' => '臺南市將軍區苓仔寮里17鄰苓和132號',
        '核定總招收數' => 15,
        '核准設立日期' => '',
        '設立許可文號' => '',
        '類型' => '公立',
        '招生' => array(),
    ),
    '將軍國民小學附設幼兒園' => array(
        '序號' => '',
        '區別' => '將軍區',
        '幼兒園名稱' => '將軍國民小學附設幼兒園',
        '幼兒園電話' => '06-7942034',
        '幼兒園住址' => '臺南市將軍區將軍里將貴58號 ',
        '核定總招收數' => 15,
        '核准設立日期' => '',
        '設立許可文號' => '',
        '類型' => '公立',
        '招生' => array(),
    ),
    '宅港國民小學附設幼兒園' => array(
        '序號' => '',
        '區別' => '學甲區',
        '幼兒園名稱' => '宅港國民小學附設幼兒園',
        '幼兒園電話' => '06-7833227',
        '幼兒園住址' => '台南市學甲區宅港里13號',
        '核定總招收數' => 15,
        '核准設立日期' => '',
        '設立許可文號' => '',
        '類型' => '公立',
        '招生' => array(),
    ),
    '七農非營利幼兒園' => array(
        '序號' => '',
        '區別' => '七股區',
        '幼兒園名稱' => '七農非營利幼兒園',
        '幼兒園電話' => '06-7871711 #312',
        '幼兒園住址' => '台南市七股區大埕里272號',
        '核定總招收數' => 31,
        '核准設立日期' => '',
        '設立許可文號' => '',
        '類型' => '非營利',
        '招生' => array(),
    ),
    '永仁非營利幼兒園' => array(
        '序號' => '',
        '區別' => '永康區',
        '幼兒園名稱' => '永仁非營利幼兒園',
        '幼兒園電話' => '06‐312-2552',
        '幼兒園住址' => '台南市永康區興國街181號',
        '核定總招收數' => 30,
        '核准設立日期' => '',
        '設立許可文號' => '',
        '類型' => '非營利',
        '招生' => array(),
    ),
    '市府員工子女非營利幼兒園' => array(
        '序號' => '',
        '區別' => '',
        '幼兒園名稱' => '臺南市政府員工子女非營利幼兒園',
        '幼兒園電話' => '06-2999391',
        '幼兒園住址' => '臺南市安平區府前路二段311號',
        '核定總招收數' => 44,
        '核准設立日期' => '',
        '設立許可文號' => '',
        '類型' => '非營利',
        '招生' => array(),
    ),
    '崑山土城非營利幼兒園' => array(
        '序號' => '',
        '區別' => '安南區',
        '幼兒園名稱' => '臺南市崑山土城非營利幼兒園',
        '幼兒園電話' => '06-2571278',
        '幼兒園住址' => '台南市安南區城安路21號',
        '核定總招收數' => 16,
        '核准設立日期' => '',
        '設立許可文號' => '',
        '類型' => '非營利',
        '招生' => array(),
    ),
    '成大員工子女非營利幼兒園' => array(
        '序號' => '',
        '區別' => '東區',
        '幼兒園名稱' => '國立成功大學員工子女非營利幼兒園',
        '幼兒園電話' => '06-2757575*36030.36031',
        '幼兒園住址' => '台南市東區林森路二段251號',
        '核定總招收數' => 2,
        '核准設立日期' => '',
        '設立許可文號' => '',
        '類型' => '非營利',
        '招生' => array(),
    ),
    '水交社非營利幼兒園' => array(
        '序號' => '',
        '區別' => '南區',
        '幼兒園名稱' => '臺南市水交社非營利幼兒園',
        '幼兒園電話' => '06-2642161',
        '幼兒園住址' => '台南市南區西門路一段306號',
        '核定總招收數' => 62,
        '核准設立日期' => '',
        '設立許可文號' => '',
        '類型' => '非營利',
        '招生' => array(),
    ),
    '擇仁非營利幼兒園' => array(
        '序號' => '',
        '區別' => '南區',
        '幼兒園名稱' => '臺南市擇仁非營利幼兒園',
        '幼兒園電話' => '06 7030435',
        '幼兒園住址' => '台南市南區南門路232號',
        '核定總招收數' => 20,
        '核准設立日期' => '',
        '設立許可文號' => '',
        '類型' => '非營利',
        '招生' => array(),
    ),
    '西拉雅非營利幼兒園' => array(
        '序號' => '',
        '區別' => '麻豆區',
        '幼兒園名稱' => '臺南市西拉雅非營利幼兒園',
        '幼兒園電話' => '05-711939',
        '幼兒園住址' => '臺南市麻豆區南勢36-211號',
        '核定總招收數' => 18,
        '核准設立日期' => '',
        '設立許可文號' => '',
        '類型' => '非營利',
        '招生' => array(),
    ),
    '南新非營利幼兒園' => array(
        '序號' => '',
        '區別' => '新營區',
        '幼兒園名稱' => '南新非營利幼兒園',
        '幼兒園電話' => '06 6560938',
        '幼兒園住址' => '台南市新營區三興街39號',
        '核定總招收數' => 32,
        '核准設立日期' => '',
        '設立許可文號' => '',
        '類型' => '非營利',
        '招生' => array(),
    ),
    '中信員工子女非營利幼兒園' => array(
        '序號' => '',
        '區別' => '安南區',
        '幼兒園名稱' => '中信員工子女非營利幼兒園',
        '幼兒園電話' => '06-2873317',
        '幼兒園住址' => '台南市安南區台江大道三段600號',
        '核定總招收數' => 32,
        '核准設立日期' => '',
        '設立許可文號' => '',
        '類型' => '非營利',
        '招生' => array(),
    ),
    '蓮潭國中小附設幼兒園' => array(
        '序號' => '',
        '區別' => '善化區',
        '幼兒園名稱' => '蓮潭國中小附設幼兒園',
        '幼兒園電話' => '(06)5837019',
        '幼兒園住址' => '台南市善化區陽光大道366號',
        '核定總招收數' => 15,
        '核准設立日期' => '',
        '設立許可文號' => '',
        '類型' => '公立',
        '招生' => array(),
    ),
);

foreach ($data as $area => $v1) {
    foreach ($v1 as $school => $v2) {
        foreach ($v2 as $type => $typeData) {
            $row = $missing[$typeData['幼兒園']];
            $result[$row['幼兒園名稱']] = $row;
            $result[$row['幼兒園名稱']]['招生'][$type] = array(
                '類型' => $type,
                '可招生名額' => $typeData['可招生名額'],
                '登記名額' => $typeData['登記名額'],
                '錄取名額' => $typeData['錄取名額'],
                '招生簡章網址' => $typeData['招生簡章網址'],
            );
        }
    }
}

$context = stream_context_create(array(
    "ssl" => array(
        "verify_peer" => false,
        "verify_peer_name" => false,
    ),
));

foreach ($result as $k1 => $v1) {
    $pos = strpos($v1['幼兒園住址'], ']');
    if (false !== $pos) {
        $v1['幼兒園住址'] = substr($v1['幼兒園住址'], $pos + 1);
    }
    $tgosFile = __DIR__ . '/tgos/' . $v1['幼兒園住址'] . '.json';
    if (!file_exists($tgosFile)) {
        $apiUrl = $config['tgos']['url'] . '?' . http_build_query(array(
            'oAPPId' => $config['tgos']['APPID'], //應用程式識別碼(APPId)
            'oAPIKey' => $config['tgos']['APIKey'], // 應用程式介接驗證碼(APIKey)
            'oAddress' => $v1['幼兒園住址'], //所要查詢的門牌位置
            'oSRS' => 'EPSG:4326', //回傳的坐標系統
            'oFuzzyType' => '2', //模糊比對的代碼
            'oResultDataType' => 'JSON', //回傳的資料格式
            'oFuzzyBuffer' => '0', //模糊比對回傳門牌號的許可誤差範圍
            'oIsOnlyFullMatch' => 'false', //是否只進行完全比對
            'oIsLockCounty' => 'true', //是否鎖定縣市
            'oIsLockTown' => 'false', //是否鎖定鄉鎮市區
            'oIsLockVillage' => 'false', //是否鎖定村里
            'oIsLockRoadSection' => 'false', //是否鎖定路段
            'oIsLockLane' => 'false', //是否鎖定巷
            'oIsLockAlley' => 'false', //是否鎖定弄
            'oIsLockArea' => 'false', //是否鎖定地區
            'oIsSameNumber_SubNumber' => 'true', //號之、之號是否視為相同
            'oCanIgnoreVillage' => 'true', //找不時是否可忽略村里
            'oCanIgnoreNeighborhood' => 'true', //找不時是否可忽略鄰
            'oReturnMaxCount' => '0', //如為多筆時，限制回傳最大筆數
            'oIsSupportPast' => 'true',
            'oIsShowCodeBase' => 'true',
        ));
        $content = file_get_contents($apiUrl, false, $context);
        if (!empty($content)) {
            file_put_contents($tgosFile, $content);
        }
    }
    if (file_exists($tgosFile)) {
        $content = file_get_contents($tgosFile);
        $pos = strpos($content, '{');
        $posEnd = strrpos($content, '}');
        $json = json_decode(substr($content, $pos, $posEnd - $pos + 1), true);
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
        $result[$k1]['cunli'] = $json['AddressList'][0]['VILLAGE'];
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
