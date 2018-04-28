<?php
$fh = fopen(__DIR__ . '/data.csv', 'r');
$head = fgetcsv($fh, 2048);
while($line = fgetcsv($fh, 2048)) {
  $data = array_combine($head, $line);
  $available = $data['可招生名額'] - $data['登記名額'];
  if($available > 0) {
    echo "{$data['行政區']} - {$data['幼兒園']}[{$data['類型']}] : ({$available}/{$data['可招生名額']}) - 網址： {$data['招生簡章網址']}\n";
  }
}
