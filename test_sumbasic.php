<?php
include 'simple_html_dom.php';
//include 'SumBasic.php';
//$s = new SumBasic('I have this test. Will this test work? I have my doubts.');
//echo $s->run(2);

$url = 'http://freesummarizer.com/';
$data = array(
  'text'          =>  "Here is a sample text, taken from the news:
    A television station employee was shot dead on Friday in the northwestern city of Peshawar as violent crowds filled the streets of several cities on a day of government-sanctioned protests against an anti-Islam film made in the United States.
    The unrest came as governments and Western institutions in many parts of the Muslim world braced for protests after Friday Prayer   an occasion often associated with demonstrations as worshipers leave mosques. In Tunisia, the authorities invoked emergency powers to outlaw all demonstrations, fearing an outpouring of anti-Western protest inspired both by the American-made film and by cartoons depicting the Prophet Muhammad in a French satirical weekly.",
  'maxsentences'    =>  '3',
  'maxtopwords'     =>  '40',
  'email'           =>  ''
);
$options = array(
  'http'    =>    array(
                        'method'  =>  'POST',
                        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                        'content' =>  http_build_query($data)
                      )
);
$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);
$html = str_get_html($result);
$summary = $html->find('div[class=summary]', 0)->find('p', 0);
echo $summary->innertext;