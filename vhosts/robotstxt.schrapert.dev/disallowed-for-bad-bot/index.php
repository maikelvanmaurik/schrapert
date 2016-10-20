<h2>This page should not be crawled by bad bot</h2>
<?php
file_put_contents(__DIR__.'/test.txt', $_SERVER['HTTP_USER_AGENT'], FILE_APPEND);