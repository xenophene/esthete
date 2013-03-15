<?php
  function getUrlAsString ($url) {
    $ch = curl_init();
    curl_setopt_array($ch, array(
      CURLOPT_URL             =>  $url,
      CURLOPT_RETURNTRANSFER  =>  '1',
      CURLOPT_PROXY           =>  '10.10.78.62',
      CURLOPT_PROXYPORT       =>  '3128',
      CURLOPT_PROXYUSERPWD    =>  'cs5080224:xyz'
    ));
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
  }
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Iframe Test</title>
  </head>
  <body>
    <p>Iframe below</p>
    <?php echo getUrlAsString('http://news.google.com'); ?>
    <iframe src="<?php echo getUrlAsString('http://news.google.com'); ?>"
            name="google-news" height="90%"
            seamless="seamless" width="90%">
    </iframe>
  </body>
</html>