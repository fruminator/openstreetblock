<?php

  /**
   * Copyright (c) 2011 Metropolitan Transportation Authority
   *
   * Licensed under the Apache License, Version 2.0 (the "License"); you may not
   * use this file except in compliance with the License. You may obtain a copy of
   * the License at
   *
   * http://www.apache.org/licenses/LICENSE-2.0
   *
   * Unless required by applicable law or agreed to in writing, software
   * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
   * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
   * License for the specific language governing permissions and limitations under
   * the License.
   */


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
 else if($format == 'rawdata') {
   printf("<PRE>%s</PRE>", print_r($osb, true));
   #printf("%s", json_encode($osb));
 }
 else {
   printf("%s", osb_simple($osb));
 }

?>