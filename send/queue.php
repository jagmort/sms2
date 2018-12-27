<?php

$webdir = "/var/www/html/sms2/send";

$files = array();
$out = "<li>";
$sum = 0;
for ($i = 1; $i < 7; $i++) {
    unset($response);
    $response = file("$webdir/in/smsVB$i.txt");
    $count = sizeof($response);
    foreach($response as $line) {
        $files[] = $line . "($i)";
    }
    if($i < 2) $out .= $count; 
    else $out .= " + " . $count;
    $sum += $count;
}
$out .= " = $sum</li>";

sort($files);
$i = 0;
$d = 135;
foreach ($files as $line) {
    if($i < 16) echo "<li>" . mb_substr($line, 0, 61) . "</li>";
    else if ($d-- > 0) echo ".";
    $i++;
}

if ($sum > 0) echo $out;
