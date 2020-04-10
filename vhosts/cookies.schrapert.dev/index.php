<?php

session_start();

if (! isset($_COOKIE['times_visited'])) {
    $timesVisited = 1;
} else {
    $timesVisited = $_COOKIE['times_visited'] + 1;
}

setcookie('times_visited', $timesVisited);

printf("You've visited this page %s time(s)", $timesVisited);
