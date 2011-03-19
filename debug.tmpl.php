<H1>OpenStreetBlock Debugging Output</H1>

<FORM>LAT: <input  name="lat" value="<?=$lat?>"> LON: <input type="text" name="lon" value="<?=$lon?>"> <input type="submit" name="GO"></FORM>

<p>
Closest OSM Way is: <strong><?= $osb[way][name] ?> (<?=$osb[way][osm_id] ?>)</strong> (Normalized: <?=street_norm($osb[way][name]);?>)
</p>

<p>
   Which has <?= count($osb[node_ids]) ?> nodes: (<?= join(",", $osb[node_ids]); ?>)
</p>

The <?= OSB_MAX_NODES_EXPAND ?> closest nodes are:



   <p>Final Answer: <strong><?= osb_simple($osb); ?></strong></p>

<iframe src="http://maps.google.com/maps?q=<?=$lat?>,<?=$lon?>" width="100%" height="500px">
