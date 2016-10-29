<?php
header('Cache-Control: no-cache');

printf("THIS PAGE SHOULD NOT BE CACHED. CURRENT TIME IS %s", date('Y-m-d H:i:s'));
exit;