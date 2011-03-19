<?php

include('osb.inc.php');

$lat = $_GET[lat];
$lon = $_GET[lon];
$ll =  $_GET[ll];
if($ll) {
  $ll = split(",", $ll);
  $lat=$ll[0];
  $lon=$ll[1];
 }
if(!($lat && $lon)) {
  die("No valid lat/lon");
 }

$format = isset($_GET[format]) ? $_GET[format] : 'txt';

$db = osm_db();

$osb = OpenStreetBlock($lat, $lon, $db);

#print_r($osb);

if($format == 'json') {
  printf("%s",osb_json($osb));
 }
 else if($format == 'debug') {
   include('debug.tmpl.php');
 }
 else {
   printf("%s", osb_simple($osb));
 }

?>