<?php
ob_start();
date_default_timezone_set("Europe/Stockholm");
header("Content-Type: application/xml");
$env = parse_ini_file('.env');


if ($env["DEBUG"]) {

error_reporting(E_ALL);
ini_set("display_errors", "1");
}

header("Content-type: text/xml");

if (!isset($_GET["podcast"])) {
    echo "no podcast set";
    die();
}


$feedName = $_GET["podcast"];
$feedDesc = "Proxy podcast for " . $feedName;

if (isset($_SERVER['HTTPS']) &&
    ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
    isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
    $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
  $protocol = 'https://';
}
else {
  $protocol = 'http://';
}



if (!isset($_SERVER["HTTP_HOST"])) {
    $selfurl = "localhost";
    $host = "localhost";
    $uri = "/";
} else {
    $selfurl = $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
    $host = $_SERVER["HTTP_HOST"];
    $uri = $_SERVER["REQUEST_URI"];
}



echo '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
echo '<rss version="2.0" xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" xmlns:podcast="https://podcastindex.org/namespace/1.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/">' .
  PHP_EOL;
echo "  <channel>" . PHP_EOL;
echo '      <atom:link href="' . $protocol . $host . '" rel="self" type="application/rss+xml" />' .
  PHP_EOL;
echo "      <title>".$feedName."</title>" . PHP_EOL;
echo "      <description>".$feedDesc."</description>" .
  PHP_EOL;
echo "      <link>" . $env["URL"] . "</link>" . PHP_EOL;
echo "      <language>sv-se</language>" . PHP_EOL;
echo '      <itunes:category text="News">' . PHP_EOL;
echo '          <itunes:category text="Tech News" />' . PHP_EOL;
echo "      </itunes:category>" . PHP_EOL;
echo '      <itunes:category text="Technology" />' . PHP_EOL;
echo "      <itunes:explicit>false</itunes:explicit>" . PHP_EOL;
echo '      <itunes:image href="'.$protocol . $host . '/data/'. $feedName . '/artwork.png" />' . PHP_EOL;
echo "      <podcast:locked>yes</podcast:locked>" . PHP_EOL;
echo "      <itunes:author>" . $env["ITUNES_AUTHOR"] ."</itunes:author>" . PHP_EOL;
echo "      <copyright>&#169; original author</copyright>" . PHP_EOL;
echo "      <itunes:type>episodic</itunes:type>" . PHP_EOL;

echo PHP_EOL;



$items = [];

$file = fopen("data/".$_GET["podcast"]."/". $_GET["podcast"] . ".csv", "r");

// id   filename    timestamp   duration    title   description
// 0    1           2           3           4       5

while (($item = fgetcsv($file, null, ";")) !== false) {

  if (file_exists("data/" .$feedName ."/" . $item[1])) {
    $fileurl = $protocol . $host . "/data/" .$feedName ."/" . $item[1];
    $filepath = "data/" .$feedName ."/" . $item[1];
    echo "      <item>\n";
    echo "          <title><![CDATA[ " . $item[4] . " ]]></title>\n";
    echo '          <guid isPermaLink="false">' . $item[0] . '</guid>' . PHP_EOL;
    echo "          <description><![CDATA[ " . $item[5] . " ]]></description>" . PHP_EOL;
    echo '          <enclosure url="' . $fileurl . '" length="' . filesize($filepath) . '" type="'.mime_content_type($filepath).'" />' . PHP_EOL;
    echo "          <pubDate>" . date(DATE_RSS, $item[2]) . "</pubDate>\n";
    echo "          <itunes:duration>" . $item[3] . "</itunes:duration>" . PHP_EOL;

    echo "      </item>" . PHP_EOL;
  }
}
fclose($file);


echo PHP_EOL;
echo "  </channel>" . PHP_EOL;
echo "</rss>" . PHP_EOL;
header("Content-Length: " . ob_get_length());
ob_end_flush();
?>
