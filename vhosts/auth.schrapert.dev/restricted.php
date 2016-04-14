<?php
if(!isset($_SESSION['allowed'])) {
    header("HTTP/1.1 401 Unauthorized");
    exit;
}



