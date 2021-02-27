<?php
class ReverseDomain {
  public $list;
  public function Domain() {
    $site = $this->list;
    $exp = explode("\n", $site);
    $array = array_unique($exp);
    foreach ($array as $http) {
      if (!preg_match('/http(s?)\:\/\//i', $http)) {
        $a = "http://".$http;
      } else {
        $a = $http;
      }
      $parse = parse_url($a);
      $domain = preg_replace('/^www\./', '', $parse['host']);
      $www = "www.".$domain;
      $host = gethostbyname($www);
      for ($i = 0; $i < $host; $i++) {
        echo " [\e[32mInfo\e[0m] $domain // \e[31m$host\e[0m [\e[32mLIVE\e[0m] \n";
        $open = fopen("result.txt", 'a+');
        fwrite($open, "$domain - $host\n");
        fclose($open);
        break;
      }
    }
  }
  public function headerr() {
    echo "\n\tMass Reverse IP Scanner\n \n";
  }
}
$reverse = new ReverseDomain();
$reverse->headerr();
if (!isset($argv[1])) {
  echo " example : php mass_rev.php list.txt";
  exit(1);
} else {
  $link = $argv[1];
}
if (!file_exists($link)) die("File List ".$link." Not Found");
$domain = explode("\n", file_get_contents($link));
echo " [\e[32mInfo\e[0m] Total domain [ " .count($domain)." ]\n\n";
foreach ($domain as $env) {
  $reverse->list = trim($env);
  $reverse->Domain();
}
?>
