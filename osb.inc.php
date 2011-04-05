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


include('conf.inc.php');

function OpenStreetBlock($lat, $lon, $db, $max_nodes_expand = OSB_MAX_NODES_EXPAND) {

  $res = array();

  # this is not really SQL safe is it?
try {
  $flat = floatval($lat);
  $flon = floatval($lon);
  $wkt_point = sprintf("Point(%f %f)", $flon, $flat);
} catch (Exception $e) {
    $res['error'] = sprintf("Could not parse: %s,%s", $lat, $lon);
    return $res;
}

  # Find the way that is closes to the point in question

  $way_query = sprintf("
select l.*, w.nodes 

from osm_line l
join osm_ways w on l.osm_id = w.id

where 
buffer(PointFromText('%s', 4326), .001) && l.way

and (l.railway is null and l.name is not null and l.name != '')

and intersects(way
  , buffer(PointFromText('%s', 4326), .002)
)

order by distance(way, PointFromText('%s', 4326)) 
limit 1
", $wkt_point, $wkt_point, $wkt_point);
  
  #print "$way_query";

  $way_query = $db->prepare($way_query);
  $way_query->execute();
  $way = $way_query->fetch(PDO::FETCH_ASSOC);
  
  if(!$way) {
    $res['error'] = sprintf("NO WAY FOUND NEAR: %s,%s", $lat, $lon);
    return $res;
  }

  $result[way] = $way;

  $way_id = $way[osm_id];
  $way_name = street_norm($way[name]);

  
  # Find all the nodes referenced by this way
  
  $node_ids = split(",", substr(substr($way[nodes],1), 0, -1));

  $result[node_ids] = $node_ids;

  $nodes_query = sprintf("
select n.id, x(n.geom) as lon, y(n.geom) as lat
, distance_sphere(PointFromText('%s', 4326), n.geom) as dist
from nodes n
where n.id in (%s)
order by distance(n.geom, PointFromText('%s', 4326))
limit %d
", $wkt_point, join(",", $node_ids), $wkt_point, $max_nodes_expand * 2);

  #print_r($nodes_query);
  
  $nodes_query = $db->prepare($nodes_query);
  $nodes_query->execute();
  $nodes = $nodes_query->fetchAll(PDO::FETCH_ASSOC);

  $nodes_final = array();
  
  # For each of nodes, pull out the ways that intersect that node
  for($i = 0; $i < count($nodes); $i++) {
#    $nodes[$i][dist] = ll_dist($lat, $lon, $nodes[$i][lat], $nodes[$i][lon]);
    
    $nodes[$i][ways] = array();
    
    $ways_query = sprintf("
select l.name, l.osm_id
from osm_line l
join way_nodes wn
on l.osm_id = wn.way_id
where wn.node_id = ?
and wn.way_id != ?
and l.name is not null and l.name != ''
");

    $ways_query = $db->prepare($ways_query);
    $ways_query->execute(array($nodes[$i][id], $way_id));
    $ways = $ways_query->fetchAll(PDO::FETCH_ASSOC);
    
    $nodes[$i][all_ways] = $ways;
    
    for($j = 0; $j < count($ways); $j++) {
      $w = $ways[$j];
      $w[name] = street_norm($w[name]);

      # Only use that way if it does not share the id or the normalized name of the original way
      if($w[osm_id] != $way_id && strcasecmp($way_name, $w[name]) != 0) {
	$nodes[$i][ways][] = $w;
      }
    }
    
    # Only keep this node if it has one or more valid ways intersecting the original way
    if(count($nodes[$i][ways]) > 0) {
      $nodes_final[] = $nodes[$i];
    }
  }

  $result[all_nodes] = $nodes;
  
  $nodes = $nodes_final;

  $result[nodes_valid] = $nodes;
  
  if($nodes[0][dist] <= OSB_CORNER_THRESH) {
    $nodes = array($nodes[0]);
  }
  else {
    $nodes = array_slice($nodes,0,2);
  }

  $result[nodes] = $nodes;

  return $result;
}

# Formatting funtions for OSB results

# name an intersection by the ways hitting the node there
function node_intersection_name($node) {
  return join("/", array_unique(array_map(create_function('$w', 'return $w[name];'), $node[ways])));
}

function osb_simple($osb) {
  return(sprintf("%s %s %s", $osb[way][name]
		 , count($osb[nodes]) == 1 ? '@' : 'bet.'
		 , join(" & ", array_map(create_function('$n', 'return node_intersection_name($n);'), $osb[nodes]))
		 )
	 );
}

function osb_json($osb) {
  $x = array();

  $x[street] = $osb[way][name];

  $x[intersections] = array();
  
  for($i = 0; $i < count($osb[nodes]); $i++) {
    $n = $osb[nodes][$i];

    $x[intersections][] = array(
				name => node_intersection_name($n)
				, dist => $n[dist]
				, intersecting_streets => array_unique(array_map(create_function('$w', 'return $w[name];'), $n[ways]))
				);
  }

  $x[text] = osb_simple($osb);

  return json_encode($x);
}



# little hack for normalizing some of the most common street suffices
# if we were smart, we'd run this over the data in the OSM DB in a pre-processing step, 
# rather than running it on every street name we get from the DB...

function street_norm($s) {
  # do the easy ones w/out regexes, for ostensible performance

  $repl = array(
		   ' street' => ' St.'
		   , ' avenue' => ' Ave.'
		   , ' boulevard' => ' Blvd.'
		   , ' place' => ' Pl.'
		   );

  $s = str_ireplace(array_keys($repl), array_values($repl), $s);


  # but somewhat sublter ones need regexes

  $repl = array(
		'/^Avenue\s/' => 'Ave. '
		, '/Ave$/' => 'Ave.'
		, '/St$/' => 'St.'
		, '/Blvd$/' => 'Blvd.'
		, '/Pl$/' => 'Pl.'
		);

  $s = preg_replace(array_keys($repl), array_values($repl), $s);

  
  return $s;

}


# typical hack for approxmating distance (in miles?) from 2 lat/lon coordinates

function ll_dist($lat1,$lng1,$lat2,$lng2)
{
  // If 2 coords are the same dist=0
  if (($lat1 == $lat2) && ($lng1 == $lng2)){
    return 0;
  }
  // Convert degrees to radians.
  $lat1=deg2rad($lat1);
  $lng1=deg2rad($lng1);
  $lat2=deg2rad($lat2);
  $lng2=deg2rad($lng2);

  // Calculate delta longitude and latitude.
  $delta_lat=($lat2 - $lat1);
  $delta_lng=($lng2 - $lng1);

  //Calculate distance based on curvature of the earth.
  $temp=pow(sin($delta_lat/2.0),2) + cos($lat1) * cos($lat2) * pow(sin($delta_lng/2.0),2);
  $distance=number_format(3956 * 2 * atan2(sqrt($temp),sqrt(1-$temp)),2,'.','');

  return $distance;
} 


# silly functions for generating a PDO database handle

function db($host, $dbname, $uname, $pword) {
  $db = new PDO(sprintf("pgsql:host=%s;dbname=%s", $host,  $dbname), $uname, $pword);
  $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
  return $db;
}

function osm_db() {
  return db(OSB_DB_HOST, OSB_DB, OSB_DB_UNAME, OSB_DB_PWORD);
}

