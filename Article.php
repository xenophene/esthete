<?php
  class Article {
    function set_title($row) {
      if (array_key_exists('headline', $row) and !empty($row['headline'])) {
        $title = $row['headline'];
        $this->title = $title;
      } else {
        $text = $row['afull'];
        preg_match('/<h1>(.*)<\/h1>/', $text, $match);
        $title = str_replace('</h1>', '', str_replace('<h1>', '', $match[0]));
        $title = str_replace('.html', '', str_replace('-', ' ', $title));
        $this->title = $title;
      }
    }
    function set_uactors($uactors) {
      $patt = '/u00\d{1}\w{1}/';
      $matches = array();
      $uactors = preg_replace_callback($patt, "add_slash", $uactors);
      $this->uactors = array_filter(array_unique(array_map('trim', explode(',', strtolower($uactors)))));
    }
    function set_new_uactors($uactors) {
      $this->uactors = array_unique($uactors);
    }
    function set_utopics($utopics) {
      // check for both , and ; deliminators
      $this->utopics = array();
      $topics = strtolower($utopics);
      if (strpos($topics, ';') === false) {
        foreach (explode(',', strtolower($topics)) as $topic) {
          $t = trim($topic);
          if (!empty($t) and strlen($t) <= 25) array_push($this->utopics, $t);
        }
      } else {
        foreach (explode(';', strtolower($topics)) as $topic) {
          $t = trim($topic);
          if (!empty($t) and strlen($t) <= 25) array_push($this->utopics, $t);
        }
      }
      $this->utopics = array_unique($this->utopics);
    }
    function set_summary($summary) {
      $this->summary = $summary;
    }
    function get_body() {
      return $this->body;
    }
    function get_clean_body() {
      $str = str_replace('<p>', '', $this->body);
      $str = str_replace('</p>', '', $str);
      return $str;
    }
    function get_headline() {
      return $this->title;
    }
    function get_uactors() {
      return $this->uactors;
    }
    function get_uactors_string() {
      return implode(',', $this->uactors);
    }
    function get_utopics() {
      return $this->utopics;
    }
    function get_summary() {
      return $this->summary;
    }
    function days_since($ts) {
      return ceil((strtotime($this->date) - $ts) / (60*60*24));
    }
    function get_string_utopics() {
      return implode(', ', $this->get_utopics());
    }
    function get_start_date() {
      return date('F j Y', strtotime($this->date));
    }
    function get_start_date_timelinejs() {
      return date('Y,n,j', strtotime($this->date));
    }
    function get_start_date_ts() {
      return strtotime($this->date);
    }
    function get_end_date() {
      return date('F j Y', strtotime("+1 days", strtotime($this->date)));
    }
    function get_end_date_timelinejs() {
      return date('Y,n,j', strtotime("+1 days", strtotime($this->date)));
    }
    function get_farther_end_date($n) { // $n number of days ahead
      return date('F j Y', strtotime("+" . $n . " days", strtotime($this->date)));
    }
    function get_farther_end_date_timelinejs($n) { // $n number of days ahead
      return date('Y,n,j', strtotime("+" . $n . " days", strtotime($this->date)));
    }
    function get_id() {
      return $this->id;
    }
    function get_identifier() {
      return array($this->id, $this->get_headline());
    }
    
    function remove_actors($fa) {
      foreach ($fa as $a) {
        $key = array_search($a, $this->uactors);
        if ($key !== false) {
          unset($this->uactors[$key]);
        }
      }
      if (empty($this->uactors)) $this->set_new_uactors($fa);
    }
    
    function remove_topics($ft) {
      // first check if we will end up deleting all, if not go ahead, else no
      // ? whether this is to be done?

      foreach ($ft as $t) {
        $key = array_search($t, $this->utopics);
        if ($key !== false) {
          unset($this->utopics[$key]);
        }
      }
    }
    function keep_topics($topics) {
      $retain_topics = array();
      foreach ($this->get_utopics() as $t) {
        $key = array_search($t, $topics);
        if ($key !== false) array_push($retain_topics, $topics[$key]);
      }
      $this->utopics = $retain_topics;
    }
    var $id;
    var $title;
    var $uactors;
    var $utopics;
    var $date;
    var $summary;
    var $body;
    function __construct($row) {
      $this->id = $row['aid'];
      $this->set_title($row);
      $this->set_summary($row['asumm']);
      $this->set_uactors($row['uactors']);
      $this->set_utopics($row['utopics']);
      $this->date = $row['adate'];
      $this->body = $row['afull'];
    }
  }