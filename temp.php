<?php
/**
  * read in the graph file generated from python, select some set of nodes,
  * filtered on days, create the appropriate graphviz file and render the
  * graph by dot.
  */

$data = json_decode(file_get_contents('GraphFile.json'));
$startdate = $_POST['start'];
$enddate = $_POST['end'];
$actors = $_POST['actors'];
$nodes = array(); // list of all relevant node ids for this time period
$neighbours = array(); // list of neighbours for node at same index in $nodes
foreach ($data as $node => $nbrs) {
  $id = explode(',', $node);
  if ($id[0] >= $startdate and $id[0] <= $enddate) {
    array_push($nodes, $node);
    $edges = array();
    foreach ($nbrs as $nbr) {
      if ($nbr[0] >= $startdate and $nbr[0] <= $enddate)
        array_push($edges, $nbr);
    }
    array_push($neighbours, $edges);
  }
}
/* nodesE is the original nodes array as filtered from complete graph */
$nodesE = json_encode($nodes);
$neighboursE = json_encode($neighbours);
$fp = fopen('/home/xenoph/nodes.json', 'w');
fwrite($fp, json_encode($nodes));
fclose($fp);
$fp = fopen('/home/xenoph/neighbours.json', 'w');
fwrite($fp, json_encode($neighbours));
fclose($fp);
$fp = fopen('/home/xenoph/actors.json', 'w');
fwrite($fp, json_encode($actors));
fclose($fp);
// read in the Transformations.json file. It has (t,d) indexed array of
// transformations happening at the node (t,d) which is stored separately in
// $nodes. Now for every element of Transformation, find where the (t,d) appears
// and store the array of transformations at that node
$trans = array(); // indexed by "t,d" instead of (t,d)
$transforms = json_decode(file_get_contents('Transformations.json'));
foreach ($transforms as $node => $ts) {
  $id = array_search($node, $nodes);
  $trans[$id] = $ts;
}
$fp = fopen('/home/xenoph/transformation.json', 'w');
fwrite($fp, json_encode($trans));
fclose($fp);
$cmd = "/usr/bin/python /opt/lampp/htdocs/esthete/temp.py 2";
$ret = exec($cmd, $output);
echo json_encode($output);
?>
