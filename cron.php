<?php
$rootPath = __DIR__;

$now = date('Y-m-d H:i:s');

exec("cd {$rootPath} && /usr/bin/git pull");

exec("php -q {$rootPath}/01_fetch_data.php");
//exec("php -q {$rootPath}/02_calc.php");
exec("php -q {$rootPath}/03_geocoding.php");

exec("cd {$rootPath} && /usr/bin/git add -A");

exec("cd {$rootPath} && /usr/bin/git commit --author 'auto commit <noreply@localhost>' -m 'auto update @ {$now}'");

exec("cd {$rootPath} && /usr/bin/git push origin master");
