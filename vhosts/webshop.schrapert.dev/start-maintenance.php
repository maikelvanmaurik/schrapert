<?php
$duration = @$_GET['duration'] ?: 10;
$until = time() + $duration;
file_put_contents(__DIR__.'/etc/maintenance.json', json_encode([
    'until_ts' => $until,
    'until' => date('Y-m-d H:i:s', $until)
]));

printf("Entered maintenance mode until %s", date('Y-m-d H:i:s', $until));
exit;