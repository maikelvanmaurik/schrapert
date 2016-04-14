<?php
namespace Schrapert\Http\RobotsTxt;

interface ParseResultInterface
{
    public function isAllowed($ua, $page = '/');
}