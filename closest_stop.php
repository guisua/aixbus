<?php

ini_set("Allow_url_include", true);
//error_reporting(E_ALL);

$userLat = $_GET["lat"];
$userLon = $_GET["lon"];
$resultCount = 10;

if (isset($_GET["resultCount"])) {
    $resultCount = intval($_GET["resultCount"]);
}

$userGeoLocation = array(
    "lat" => doubleval($userLat),
    "lon" => doubleval($userLon)
);

function distanceMeters($geo1, $geo2) {
    
    $lat1 = $geo1["lat"];
    $lon1 = $geo1["lon"];
    $lat2 = $geo2["lat"];
    $lon2 = $geo2["lon"];
    
  $x = deg2rad( $lon1 - $lon2 ) * cos( deg2rad( $lat1 ) );
  $y = deg2rad( $lat1 - $lat2 ); 
  $dist = 6371000.0 * sqrt( $x*$x + $y*$y );

  return $dist;
}

function departuresForStopId($stopId) {
    $content = file_get_contents('http://ivu.aseag.de/interfaces/ura/instant_V1?ReturnList=VehicleID,LineName,TripID,DirectionID,DestinationName,EstimatedTime&StopId='.$stopId);
    
    $stringsToDelete = array("\"", "[", "]", "\r");
    $filtered = str_replace($stringsToDelete, "", $content);

    $lines1 = explode("\n",$filtered);
    unset($lines1[0]); // remove item at index 0
    $lines = array_values($lines1); // 'reindex' array

    $connections = array();

    foreach ($lines as $line) {
    
        $elements = explode(",",$line);
    
        $lineName = str_replace(".","",$elements[1]);
        $vehicleId = $elements[4];
        $tripId = intval($elements[5]);
        $lineDestination = $elements[3];
        $eta = intval($elements[6]);
        $departureTime = new DateTime();
        date_timezone_set($departureTime, timezone_open('Europe/Berlin'));
        date_timestamp_set($departureTime, $eta/1000);
        
        $connections[] = array (
            "eta" => $eta/1000,
            "local_time" => date_format($departureTime, "H:i:s"),
            "line" => $lineName,
            "destination" => $lineDestination,
            "live_info" => ($vehicleId != "null"),
            "tripId" => $tripId,
        );
        
        if (count($connections) >= 10) {
            break;
        }
    };
    usort($connections, function ($item1, $item2) {
        return $item1["eta"] <=> $item2["eta"];
    });
    return $connections;
}

$stopsString = file_get_contents("stops.txt");

$stops = explode("\n",$stopsString);
array_shift($stops);

$results = array();

foreach ($stops as $stop) {
    
    $stop = str_replace("\"","", $stop);
    $elements = explode(",",$stop);
    $result = array();
    
    $id = intval($elements[0]);
    $name = $elements[2];
    $lat = doubleval($elements[3]);
    $lon = doubleval($elements[4]);
    
    $result["id"] = $id;
    $result["name"] = $name;
    $stopGeoLoc = array(
        "lat" => $lat,
        "lon" => $lon
    );
    $result["geoLocation"] = $stopGeoLoc;
    $result["distance"] = distanceMeters($userGeoLocation, $stopGeoLoc);
    $results[] = $result;
}

usort($results, function ($item1, $item2) {
    return $item1["distance"] <=> $item2["distance"];
});

array_splice($results, $resultCount);

$i = 0;
foreach ($results as $result) {
    $results[$i]["departures"] = departuresForStopId($result["id"]);
    $i++;
}

$encoded = json_encode($results,JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
//$encoded = json_encode($results);
header ('Content-type: application/json;charset=utf-8');
exit ($encoded);
?>