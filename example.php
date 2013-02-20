<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title>Quick start - Document Clustering Server - Carrot2</title>
    <style type="text/css">
      body { font-family: Tahoma, sans-serif; font-size: 11px; }
      img { float: left; margin: 0.5ex 1ex 2ex 0; }
      p { clear: both; margin-bottom: 2ex; width: 40em }
      h1 { margin-top: 2ex; border-bottom: 1px solid #a0a0a0; width: 50%}
      .xml { height: 15em; width: 80em; overflow-x: hidden; overflow-y: auto; border: 1px solid #ddd; padding: 2px 5px; }
    </style>
  </head>

  <body>

<?php
  // This is an example of using Carrot2 PHP API. Please refer to the
  // documentation of specific methods for the available options.

  // Import Carrot2 integration code
  require_once 'Carrot2.php';
  include 'config_survey.php';
  include 'Article.php';
  include 'aux_functions.php';
  
  
  
  $r = craft_and_run_query(array('robert vadra'), array(), '2012-01-01', '2012-12-01', 'hindu', 1000);
  $articles = array();
  
  for ($i = 0; $i < mysql_num_rows($r); $i++) {
    $row = mysql_fetch_assoc($r);
    $article = new Article($row);
    array_push($articles, $article);
  }
  
  // Create a Carrot2 processor that will handle all clustering requests
  $processor = new Carrot2Processor();

  //
  //
  // The DCS can fetch results from an external source, such as a search
  // engine, and cluster these results. The code below shows this scenario.
  //
  //
  
  echo '<h1>Clustering directly provided documents</h1>';

  $job = new Carrot2Job();
  $algorithm = "kmeans";

  addExampleDocuments($job, $articles);
  $job->setAlgorithm($algorithm);
  $job->setQuery("data mining"); // set the query as a hint for the clustering algorithm (optional)
  $job->setAttributes(array (
        'TermDocumentMatrixBuilder.termWeighting'   => 'org.carrot2.text.vsm.LinearTfIdfTermWeighting',
        'BisectingKMeansClusteringAlgorithm.clusterCount'   =>  30
  ));

  $result = $processor->cluster($job);
  $clusters = $result->getClusters();
  $documents = $result->getDocuments();

  echo "<h2>Clusters</h2>";
  if (count($clusters) > 0) {
    echo "<ul>";
    foreach ($clusters as $cluster) {
      displayCluster($cluster);
    }
    echo "</ul>";
  }
  
  foreach ($clusters as $cluster) {
    echo br . $cluster->getLabel() . br;
    foreach ($cluster->getAllDocumentIds() as $documentId) {
      displayDocument($documents[$documentId]);
    }
  }

  echo "<h2>Other attributes</h2>";
  $attributes = $result->getAttributes();
  foreach ($attributes as $key => $value) {
    echo "<strong>" . $key . ":</strong> " . $value . "<br />";
  }

  // Display raw XML response from DCS
  echo "<h2>Response as XML</h2>";
  displayRawXml($result->getXml());

  //
  // Examples end here, below are utility functions.
  //

  /**
   * A utility function to display clusters.
   */
  function displayCluster(Carrot2Cluster $cluster)
  {
    echo '<li>' . $cluster->getLabel() . ' (' . $cluster->size() . ')';
    if (count($cluster->getSubclusters()) > 0) {
       echo '<ul>';
       foreach ($cluster->getSubclusters() as $subcluster) {
         displayCluster($subcluster);
       }
       echo '</ul>';
    }
    echo '</li>';
  }
  
  /**
   * A utility function to display documents.
   */
  function displayDocument(Carrot2Document $document)
  {
    echo '<p>';
    // Here we'll handle one specific optional field of documents
    // Refer to Carrot2 documentation for a list of other fields
    $thumbnailUrl = $document->getField('thumbnail-url');
    echo ($document->getId() + 1) . '. ';
    echo '<strong>' . $document->getTitle() . '</strong><br />';
    /*
    if ($thumbnailUrl) {
      echo '<img src="' . htmlspecialchars($thumbnailUrl) . '" alt="' . $document->getTitle() . '" />';
    }
    echo $document->getContent();
    echo '<br /><a href="' . htmlspecialchars($document->getUrl()) . '">' . htmlspecialchars($document->getUrl()) . '</a>';
    echo '</p>';
    */
  }

  /**
   * Returns some example hard coded data for clustering.
   */
  function addExampleDocuments(Carrot2Job $job, $articles)
  {
    
    foreach ($articles as $article) {
      $job->addDocument($article->get_headline(), $article->get_body(), '');
    }
  }

  /**
   * Displays the raw XML received from the DCS.
   */
  function displayRawXml($xml)
  {
    echo "<pre class='xml'>";
    echo htmlspecialchars($xml);
    echo "</pre>";
  }
?>
  </body>
</html>
