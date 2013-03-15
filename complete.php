<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <title>Complete</title>
  </head>
  <body>
    <iframe name="interface" seamless="seamless" height="500">
      <?php
        $ch = curl_init();
        curl_setopt_array($ch, array(
          CURLOPT_PROXY          => '10.10.78.62',
          CURLOPT_PROXYPORT      => '3128',
          CURLOPT_PROXYUSERPWD   => 'cs5080224:xyz',
          CURLOPT_URL            => 'http://news.google.com',
        ));
        $contents = curl_exec($ch);
        echo $contents;
      ?>
    </iframe>
  </body>
</html>