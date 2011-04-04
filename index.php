<?php

  /**
   * Copyright (c) 2011 Michael Frumin
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

$ll = "40.671899,-73.984074";
$res = "4th Avenue between 86th St and 85th St";

$req_dir = sprintf("http://%s%s", $_SERVER[HTTP_HOST], dirname($_SERVER[PHP_SELF]));
$script = "osb.php";
$service = sprintf("%s/%s", $req_dir, $script);

?><HTML>
<HEAD>
<TITLE>OpenStreetBlock</TITLE>
</HEAD>
<BODY style="width:1000px;">
<H1>What is OpenStreetBlock?</H1>
<p>
  OpenStreetBlock is a simple web service for mapping a specific latitude/longitude coordinate to an actual city "block" 
  using <a href="http://openstreetmap.org">OpenStreetMap</a> data.
  
  In other words, turning (<?= $ll ?>) into "<?= $res; ?>."
</p>

<p>    
    There are likely many applications for such a service.
  It should be quite useful any time you might need to succinctly describe a given location without using a map.
</p>

<H1>How Does it Work?</H1>
<H2>The Concept</H2>
  Conceptually speaking, OpenStreetBlock does the following, given a lat/lon coordinate (40.737813,-73.997887, for example).

<BR> <img src="docs/example/osb-coord.png"> <BR><BR>

<OL>
<li>
  Find the street segment ("way" in OpenStreetMap terminology) physically closest to the given coordinate.  
      Assume this is the street we are on: in this case, "14th St."
<BR> <img src="docs/example/osb-way.png"> <BR><BR>
</li>

<li>
      Find the two intersections ("nodes" in OpenStreetMap terminology) closest to the given coordinate on the selected street.
      Assume these are the intersections we are between.
<BR> <img src="docs/example/osb-nodes.png"> <BR><BR>


</li>

<li>
      For each of those intersections, find the streets passing through those intersections.
      Exclude any intersecting streets with the same name as the selected street (the one we are "on").
      Use the remaining streets to name the given intersection (the ones we are "between"):
      in this case, 6th Avenue and 7th  Avenue.
<BR> <img src="docs/example/osb-intersecting.png"> <BR><BR>
</li>

<li>
      OpenStreetBlock also uses a configurable threshold parameter to determine whether we are "at" a given intersecting street rather than "between" two intersections
      (this is the so-called "Corner Threshold").
      If we are within this threshold of the nearest intersection, drop the other intersection: 
      in this case, we are not.
<BR> <img src="docs/example/osb-corner.png"> <BR><BR>
</OL>

<H2>The Web Service</H2>
<p>
      The OpenStreetBlock will be available wherever/however you install it.
      The entry point to the web service is the <code><?= $script; ?></code> file.
      So, in this case, the web service is at:
  <a href="<?= $service; ?>"><?= $service; ?></a>
  </p>
  <p>
  The service is RESTful, so request parameters are specified in the URL.
  The latitude and longitude of the coordinate on which to search can be specified separately using:
  <ul>
  <li><code>lat</code> -- GET parameter specifying the decimal latitude of the search coordinate</li>
  <li><code>lon</code> -- GET parameter specifying the decimal longitude of the search coordinate </li>
  </ul>

  or togther using:
  <ul>
  <li><code>ll</code> -- GET parameter specifying the combined comma-separated decimal latitude and decimal longitude of the search coordinate</li>
  </ul>
  </p>

<p>
  
  The results are available in four (4) formats, specified specified using the <code>format</code> GET parameter:
      <UL>
      <LI>"Simple" -- the plan simple text of the result (<code>format=simple</code>, the default).</LI>
      <LI>"JSON" -- the synthesized results in JSON format (<code>format=json</code>). </LI>
      <LI>"Debug" -- an HTML page showing lots of information to help debug and analyze how OpenStreetBlock works for a given coordinate (<code>format=debug</code>).</LI>
	<LI>"Raw Data" -- uses the data-dumping features of PHP to show all the relevant raw data of relevance (<code>format=rawdata</code>). </LI>
      </UL>
</p>
<H1>Try it Out</H1>
	Some example searches: 
	<ul>
	<li>Where the author went to middle school
	(<a href="http://maps.google.com/?q=<?= $ll; ?>"><?= $ll; ?></a>):
	<a href="<?= sprintf('%s?ll=%s', $service, $ll);?>">Simple</a>
	| <a href="<?= sprintf('%s?ll=%s&format=json', $service, $ll);?>">JSON</a>
	| <a href="<?= sprintf('%s?ll=%s&format=debug', $service, $ll);?>">Debug</a>
	| <a href="<?= sprintf('%s?ll=%s&format=rawdata', $service, $ll);?>">Raw Data</a>
	</li>

	<? $ll = "40.704497,-74.013235"; ?>
	<li>New York City Transit headquarters
	(<a href="http://maps.google.com/?q=<?= $ll; ?>"><?= $ll; ?></a>):
	<a href="<?= sprintf('%s?ll=%s', $service, $ll);?>">Simple</a>
	| <a href="<?= sprintf('%s?ll=%s&format=json', $service, $ll);?>">JSON</a>
	| <a href="<?= sprintf('%s?ll=%s&format=debug', $service, $ll);?>">Debug</a>
	| <a href="<?= sprintf('%s?ll=%s&format=rawdata', $service, $ll);?>">Raw Data</a>
	</li>

	<? $ll = "40.735047,-73.991235"; ?>
	<li>Southern end of Union Square
	(<a href="http://maps.google.com/?q=<?= $ll; ?>"><?= $ll; ?></a>):
	<a href="<?= sprintf('%s?ll=%s', $service, $ll);?>">Simple</a>
	| <a href="<?= sprintf('%s?ll=%s&format=json', $service, $ll);?>">JSON</a>
	| <a href="<?= sprintf('%s?ll=%s&format=debug', $service, $ll);?>">Debug</a>
	| <a href="<?= sprintf('%s?ll=%s&format=rawdata', $service, $ll);?>">Raw Data</a>
	</li>

	<? $ll = "40.748216,-73.984798"; ?>
	<li>The Empire State Building
	(<a href="http://maps.google.com/?q=<?= $ll; ?>"><?= $ll; ?></a>):
	<a href="<?= sprintf('%s?ll=%s', $service, $ll);?>">Simple</a>
	| <a href="<?= sprintf('%s?ll=%s&format=json', $service, $ll);?>">JSON</a>
	| <a href="<?= sprintf('%s?ll=%s&format=debug', $service, $ll);?>">Debug</a>
	| <a href="<?= sprintf('%s?ll=%s&format=rawdata', $service, $ll);?>">Raw Data</a>
	</li>

	<? $ll = "40.737813,-73.997887"; ?>
	<li>The 14th Street example above.
	(<a href="http://maps.google.com/?q=<?= $ll; ?>"><?= $ll; ?></a>):
	<a href="<?= sprintf('%s?ll=%s', $service, $ll);?>">Simple</a>
	| <a href="<?= sprintf('%s?ll=%s&format=json', $service, $ll);?>">JSON</a>
	| <a href="<?= sprintf('%s?ll=%s&format=debug', $service, $ll);?>">Debug</a>
	| <a href="<?= sprintf('%s?ll=%s&format=rawdata', $service, $ll);?>">Raw Data</a>
	</li>


	
	</ul>


<H1>Is it Free?</H1>
<p>
  Yes!

  OpenStreetBlock is an <a href="https://github.com/fruminator/openstreetblock">open source</a> project,
  first developed by <a href="http://frumin.net/ation/">Michael Frumin</a>.
  
  OpenStreetBlock is published under the <a href="http://www.apache.org/licenses/LICENSE-2.0">Apache License, Version 2.0</a>.
  It uses <a href="http://openstreetmap.org">OpenStreetMap</a> data, which is free.
</p>

<p>
  All of the software packages it depends upon (<a href="http://www.postgresql.org/">PostgreSQL</a>
						, <a href="http://postgis.refractions.net/">PostGIS</a>
						, <a href="http://wiki.openstreetmap.org/wiki/Osm2pgsql">osm2pgsql</a>
						, <a href="http://wiki.openstreetmap.org/wiki/Osmosis">Osmosis</a>
						, <a href="http://httpd.apache.org/">Apache</a>
						, and <a href="http://www.php.net/">PHP</a>) 
    are also free and open source.
</p>

<div style="font-size: small; text-align: center; padding-top: 3em;">
  <a rel="license" href="http://creativecommons.org/licenses/by/3.0/"><img alt="Creative Commons License" style="border-width:0" src="http://i.creativecommons.org/l/by/3.0/88x31.png" /></a><br /><span xmlns:dct="http://purl.org/dc/terms/" property="dct:title">OpenStreetBlock Documentation</span> by <a xmlns:cc="http://creativecommons.org/ns#" href="https://github.com/fruminator/openstreetblock" property="cc:attributionName" rel="cc:attributionURL">Michael  Frumin</a> is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by/3.0/">Creative Commons Attribution 3.0 Unported License</a>. Based on a work at <a xmlns:dct="http://purl.org/dc/terms/" href="https://github.com/fruminator/openstreetblock" rel="dct:source">github.com</a>.
</div>

</BODY>
</HEAD>