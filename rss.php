<?php

include "include/logic.php";

header('Content-type: text/html; charset=utf-8');

$posts = BlogPost::all_posts(3);

$header='<?xml version="1.0"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
  <channel>
    <title>seriot.ch</title>
    <link>http://seriot.ch</link>
    <description>Nicolas Seriot\'s Blog</description>
    <atom:link href="http://seriot.ch/rss.php" rel="self" type="application/rss+xml" />';

$items = array();
foreach($posts as $p) {
    $i = '<item>
       <title>'.$p->title.'</title>
       <link>http://seriot.ch/'.$p->permalink_url().'</link>
       <description>'.htmlentities(Markdown($p->text)).'</description>
       <guid>http://seriot.ch/'.$p->permalink_url().'</guid>
    </item>';
    array_push($items, $i);
}
$items_string = implode('', $items);

$footer='</channel>
</rss>';

echo $header.$items_string.$footer;

?>