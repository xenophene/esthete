<?php

/**
 * SumBasic algorithm implementation using Naive probability computation
 * @version 0.1
 * @return String summary of the input text
 */
class SumBasic {
  private $stopwords = array('i', 'me', 'my', 'myself', 'we', 'our', 'ours', 'ourselves', 'you', 'your', 'yours', 'yourself', 'yourselves', 'he', 'him', 'his', 'himself', 'she', 'her', 'hers', 'herself', 'it', 'its', 'itself', 'they', 'them', 'their', 'theirs', 'themselves', 'what', 'which', 'who', 'whom', 'this', 'that', 'these', 'those', 'am', 'is', 'are', 'was', 'were', 'be', 'been', 'being', 'have', 'has', 'had', 'having', 'do', 'does', 'did', 'doing', 'a', 'an', 'the', 'and', 'but', 'if', 'or', 'because', 'as', 'until', 'while', 'of', 'at', 'by', 'for', 'with', 'about', 'against', 'between', 'into', 'through', 'during', 'before', 'after', 'above', 'below', 'to', 'from', 'up', 'down', 'in', 'out', 'on', 'off', 'over', 'under', 'again', 'further', 'then', 'once', 'here', 'there', 'when', 'where', 'why', 'how', 'all', 'any', 'both', 'each', 'few', 'more', 'most', 'other', 'some', 'such', 'no', 'nor', 'not', 'only', 'own', 'same', 'so', 'than', 'too', 'very', 's', 't', 'can', 'will', 'just', 'don', 'should', 'now');
  
  private $p;
  private $_p;
  private $words;
  private $sentences;
  private $twc;
  private $wp;  /* word probabilities */
  private $wh;  /* word hash          */
  private $sw;  /* sentence hash      */
  
  private function is_not_stopword($word) {
    return ( ! in_array($word, $this->stopwords));
  }
  private function clean_word($word) {
    $w = str_replace('?', '',
                     str_replace(',', '',
                                 str_replace('.', '', $word)));
    return trim($w);
  }
  private function remove_sentence($i) {
    foreach ($this->wh as $w => $a) {
      if (($k = array_search($i, $a)) !== false) {
        unset($this->wh[$w][$k]);
      }
    }
  }
  public function __construct() {
  }
  private function fix_for_actors($str, $actors) {
    foreach ($actors as $actor) {
      $str = str_replace($actor, '<b>' . str_replace('.', '-', $actor) . '</b>',
                         $str);
    }
    return $str;
  }
  public function set_subject($str, $actors) {
    $this->wp = array();
    $this->wh = array();
    $this->sw = array();
    $this->_p = $this->fix_for_actors(strtolower($str), $actors);
    $this->_p = str_replace('mr.', 'mr',
                            str_replace('dr', 'dr', $this->_p));
    $this->_p = str_replace('\n', ' ',
                           str_replace('.', ' . ',
                                       str_replace(',', ' , ', $this->_p)));
    $this->_sentences = array_filter(array_map('trim', explode('.', $this->_p)));
    $this->_p = implode(' ', array_filter(explode(' ', $this->_p),
                                          array($this, 'is_not_stopword')));
    
    $this->words = array_map('trim', explode(' ', $this->_p));
    $this->words = array_filter(array_map(array($this, 'clean_word'),
                                          $this->words));
    $this->twc = sizeof($this->words);
    $this->sentences = array_filter(array_map('trim', explode('.', $this->_p)));
    
    $this->word_prob();
    $this->sentence_hash();
    $this->sentence_weight();
  }
  
  private function word_prob() {
    foreach ($this->words as $w) {
      if (isset($this->wp[$w])) {
        $this->wp[$w]++;
      } else {
        $this->wp[$w] = 1;
      }
    }
    foreach ($this->wp as $k => $v) {
      $this->wp[$k] /= $this->twc;
    }
  }
  
  private function sentence_hash() {
    $i = 0;
    foreach ($this->sentences as $sentence) {
      $words = explode(' ', $sentence);
      foreach ($words as $word) {
        $w = $this->clean_word($word);
        if ($w === '')  continue;
        if (isset($this->wh[$w])) {
          array_push($this->wh[$w], $i);
        } else {
          $this->wh[$w] = array($i);
        }
      }
      $i++;
    }
  }
  
  private function sentence_weight() {
    $i = 0;
    foreach ($this->sentences as $sentence) {
      $words = explode(' ', $sentence);
      $c = 1;
      $s = 0;
      foreach ($words as $word) {
        $w = $this->clean_word($word);
        if ($w === '')  continue;
        $s += $this->wp[$w];
        $c++;
      }
      $s /= $c;
      $this->sw[$i] = $s;
      $i++;
    }
  }
  
  public function sum_basic() {
    $mpws = array_keys($this->wp, max($this->wp));
    $mpw = $mpws[0];
    $mpw_sentences = $this->wh[$mpw];
    $dict = array();
    foreach ($mpw_sentences as $mpw_sentence) {
      $dict[$mpw_sentence] = $this->sw[$mpw_sentence];
    }
    
    $max_wt_sentences = array_keys($dict, max($dict));
    while ( ! isset($this->sentences[$max_wt_sentences[0]])) {
      array_shift($max_wt_sentences);
      if ( ! sizeof($max_wt_sentences)) {
        return '';
      }
    }
    $mws = $max_wt_sentences[0];
    
    $mws_words = explode(' ', $this->sentences[$mws]);
    $this->remove_sentence($mws);
    foreach ($mws_words as $mws_word) {
      $mws_w = $this->clean_word($mws_word);
      if ($mws_w === '')  continue;
      $this->wp[$mws_w] = pow($this->wp[$mws_w], 2);
    }
    $this->sentence_weight();
    return $this->_sentences[$mws];
  }
  
  public function run($t) { /* t -- threshold -- */
    $summary = '...';
    for ($i = 0; $i < $t; $i++) {
      $r = $this->sum_basic();
      if (sizeof(explode(' ', $r)) > 8)
        $summary .= $r . '...<br/>';
    }
    return $summary;
  }
}