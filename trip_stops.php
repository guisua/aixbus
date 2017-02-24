<?php
error_reporting(E_ALL);

$tripId = $_GET["tripId"];

ini_set("Allow_url_include", true);
$content = file_get_contents('http://ivu.aseag.de/interfaces/ura/instant_V2?tripId='.$tripId.'&ReturnList=StopPointName,StopID,StopPointIndicator,EstimatedTime');

$stringsToDelete = array("\"", "[", "]", "\r");
$filtered = str_replace($stringsToDelete, "", $content);

$lines1 = explode("\n",$filtered);
unset($lines1[0]); // remove item at index 0
$lines = array_values($lines1); // 'reindex' array

$halts = array();

foreach ($lines as $line) {
    
    $elements = explode(",",$line);
    
    $stopName = str_replace(".","",$elements[1]);
    $stopId = intval($elements[2]);
    $stopPointIndicator = $elements[3];
    $eta = intval($elements[4]);
    
    $halts[] = array (
        "eta" => $eta,
        "stop" => array (
             "id" => $stopId,
            "name" => $stopName,
            "indicator" => $stopPointIndicator
        )
    );
};

usort($halts, function ($item1, $item2) {
    return $item1['eta'] <=> $item2['eta'];
});

$encoded = json_encode ($halts,JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
header ('Content-type: application/json;charset=utf-8');
exit ($encoded);
?>