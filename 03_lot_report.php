<?php
$year = date('Y');
$yearPath = __DIR__ . '/data/' . $year;

$lotFile = __DIR__ . '/data/lot/' . $year . '.json';
$lot = json_decode(file_get_contents($lotFile), true);

$fh = fopen($yearPath . '/data.csv', 'r');
$header = fgetcsv($fh, 2048);
$headerOut = false;
while ($line = fgetcsv($fh, 2048)) {
    $data = array_combine($header, $line);
    if ($data['類型'] == '準公共') {
        continue;
    }
    unset($data['簡章下載']);
    unset($data['電話']);
    $data['3-5歲'] = intval($data['3-5歲']) + intval($data['3歲']) + intval($data['4歲']) + intval($data['5歲']);
    unset($data['3歲']);
    unset($data['4歲']);
    unset($data['5歲']);
    $data['錄取1(2歲)'] = 0;
    $data['錄取1(3-5歲)'] = 0;
    $data['備取1(2歲)'] = 0;
    $data['備取1(3-5歲)'] = 0;
    if (isset($lot[$data['學校']])) {
        if (isset($lot[$data['學校']]['錄取'][2])) {
            $data['錄取1(2歲)'] = $lot[$data['學校']]['錄取'][2];
        }
        if (isset($lot[$data['學校']]['錄取'][3])) {
            $data['錄取1(3-5歲)'] = $lot[$data['學校']]['錄取'][3];
        }
        if (isset($lot[$data['學校']]['備取'][2])) {
            $data['備取1(2歲)'] = $lot[$data['學校']]['備取'][2];
        }
        if (isset($lot[$data['學校']]['備取'][3])) {
            $data['備取1(3-5歲)'] = $lot[$data['學校']]['備取'][3];
        }
    }
    if (false === $headerOut) {
        $headerOut = true;
        $oFh = fopen($yearPath . '/data_with_lot.csv', 'w');
        fputcsv($oFh, array_keys($data));
    }
    fputcsv($oFh, $data);
}
