<?php
function await(\React\Promise\PromiseInterface $promise, \React\EventLoop\LoopInterface $loop, $timeout = null) {
    return \Clue\React\Block\await($promise, $loop, $timeout);
}