<?php
function await(\React\Promise\PromiseInterface $promise, \React\EventLoop\LoopInterface $loop, $timeout = null) {
    return \Clue\React\Block\await($promise, $loop, $timeout);
}

function rmdir_recursive($dir) {
    if(!is_dir($dir)) {
        return;
    }
    $files = array_diff(scandir($dir), array('.','..'));
    foreach ($files as $file) {
        (is_dir("$dir/$file")) ? rmdir_recursive("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
}

function css(DOMDocument $doc, $selector, DOMNode $context = null)
{
    $converter = new CssSelectorConverter(true);
    $expression = $converter->toXPath($selector);
    $xpath = new DOMXPath($doc);
    return $xpath->query($expression, $context);
}

function xpath(DOMDocument $doc, $expression, DOMNode $context = null)
{
    $xpath = new DOMXPath($doc);
    return $xpath->query($expression, $context);
}