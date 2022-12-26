<?php
function getMSecTime(): float
{
    list($m_sec, $sec) = explode(' ', microtime());
    $str = ($sec + $m_sec) * 1000;
    list($time) = explode('.', $str);
    return $time;
}