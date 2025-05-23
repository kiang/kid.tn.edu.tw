<?php
$files = [
    'lot' => [
        __DIR__ . '/raw/lotn/2025/*.html',
        __DIR__ . '/raw/lotp/2025/*.html',
    ],
    'lot2' => [
        __DIR__ . '/raw/lot2n/2025/*.html',
        __DIR__ . '/raw/lot2p/2025/*.html',
    ],
];

foreach($files AS $target => $paths) {
    $result = [];
    foreach ($paths as $path) {
        foreach (glob($path) as $htmlFile) {
            $raw = file_get_contents($htmlFile);
            $blocks = explode("\n--\n", $raw);

            foreach ($blocks as $age => $block) {
                $age += 2;
                $lines = explode('</tr>', $block);
                if (count($lines) > 1) {
                    foreach ($lines as $line) {
                        $cols = explode('</td>', $line);
                        if (count($cols) <= 1) {
                            continue;
                        }
                        foreach ($cols as $k => $v) {
                            $cols[$k] = trim(strip_tags($v));
                        }
                        if (!isset($result[$cols[0]])) {
                            $result[$cols[0]] = [];
                        }
                        $key = mb_substr($cols[1], 0, 2, 'utf-8');
                        if (!isset($result[$cols[0]][$key])) {
                            $result[$cols[0]][$key] = [];
                        }
                        if(!isset($result[$cols[0]][$key][$age])) {
                            $result[$cols[0]][$key][$age] = 0;
                        }
                        ++$result[$cols[0]][$key][$age];
                    }
                }
            }
        }
    }

    file_put_contents(__DIR__ . '/data/' . $target . '/2025.json', json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}