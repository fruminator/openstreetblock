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

?>
<HTML>
<HEAD>
<TITLE>OpenStreetBlock Debugging Output for (<?=$lat?>,<?=$lon?>)</TITLE>
</HEAD>
<BODY>
<H1>OpenStreetBlock Debugging Output</H1>

<FORM>LAT: <input  name="lat" value="<?=$lat?>"> LON: <input type="text" name="lon" value="<?=$lon?>">
<input type="hidden" name="format" value="debug">
<input type="submit" name="GO">
</FORM>

<p>
Closest OSM Way is: <strong><?= $osb[way][name] ?> (<?=$osb[way][osm_id] ?>)</strong> (Normalized: <?=street_norm($osb[way][name]);?>)
</p>

<p>
   Which has <?= count($osb[node_ids]) ?> OSM nodes: (<?= join(",", $osb[node_ids]); ?>)
</p>

The <?= OSB_MAX_NODES_EXPAND ?> closest nodes are:

<ul>
								     
<? 
for($i = 0; $i < OSB_MAX_NODES_EXPAND && $i < count($osb[all_nodes]); $i++) {
  $n = $osb[all_nodes][$i];

  printf('<LI><a href="http://maps.google.com/maps?q=%f,%f">%s</a> (%d meters away): %s</LI>'
	 , $n[lat], $n[lon]
	 , $n[id]
	 , $n[dist]
	 , join(", ", array_map(create_function('$w', 'return $w[name];'), $n[all_ways]))
	 );
}
?>


</ul>


<p>Final Answer: <strong><?= osb_simple($osb); ?></strong></p>

<iframe src="http://maps.google.com/maps?q=<?=$lat?>,<?=$lon?>" width="100%" height="500px">

</BODY>
</HTML>