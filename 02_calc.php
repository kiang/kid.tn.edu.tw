<?php
$tasks = [
  [
    'population' => '/population/2018/03/data.csv',
    'data' => '/2018/data.csv',
    'target' => '/2018/calc.csv',
  ],
  [
    'population' => '/population/2020/03/data.csv',
    'data' => '/2020/data.csv',
    'target' => '/2020/calc.csv',
  ],
  [
    'population' => '/population/2021/03/data.csv',
    'data' => '/data.csv',
    'target' => '/calc.csv',
  ],
];
foreach ($tasks as $task) {
  $fh = fopen('/home/kiang/public_html/tw_population' . $task['population'], 'r');
  $head = fgetcsv($fh, 20480);
  fgetcsv($fh, 20480);
  $result = [];
  while ($line = fgetcsv($fh, 20480)) {
    if (false !== strpos($line[2], '南市')) {
      $data = array_combine($head, $line);
      $area = mb_substr($data['site_id'], 3, null, 'utf-8');
      $area = str_replace('　', '', $area);
      if (!isset($result[$area])) {
        $result[$area] = array(
          '人口[3-4]' => 0,
          '登記名額' => 0,
          '可招生名額' => 0,
          '差額' => 0,
        );
      }
      $result[$area]['人口[3-4]'] += $data['people_age_003_m'];
      $result[$area]['人口[3-4]'] += $data['people_age_003_f'];
      $result[$area]['人口[3-4]'] += $data['people_age_004_m'];
      $result[$area]['人口[3-4]'] += $data['people_age_004_f'];
    }
  }

  $fh = fopen(__DIR__ . $task['data'], 'r');
  $head = fgetcsv($fh, 2048);
  while ($line = fgetcsv($fh, 2048)) {
    $data = array_combine($head, $line);
    $data['行政區'] = str_replace(' ', '', $data['行政區']);
    $result[$data['行政區']]['登記名額'] += $data['登記名額'];
    $result[$data['行政區']]['可招生名額'] += $data['可招生名額'];
    $result[$data['行政區']]['差額'] += ($data['可招生名額'] - $data['登記名額']);
  }

  $fh = fopen(__DIR__ . $task['target'], 'w');
  $headOut = false;
  foreach ($result as $area => $data) {
    $data['登記佔人口比例'] = round($data['登記名額'] / $data['人口[3-4]'], 2);
    $data['缺額佔登記比例'] = 0;
    if ($data['差額'] < 0) {
      $data['缺額佔登記比例'] = round(abs($data['差額']) / $data['登記名額'], 2);
    }
    if (false === $headOut) {
      fputcsv($fh, array_merge(array('行政區'), array_keys($data)));
      $headOut = true;
    }
    fputcsv($fh, array_merge(array($area), $data));
  }
}
