<?php
$headers = apache_request_headers();
print json_encode($headers);
exit;