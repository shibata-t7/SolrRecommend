<?php
/**
 * Created by PhpStorm.
 * User: teruo
 * Date: 2014/10/05
 * Time: 18:43
 */

$client = new SolrClient(array(
    'hostname' => '192.168.33.100',
    'port'     => 8983,
    'path'     => '/solr/solrbook'
));
$query = new SolrQuery('ハードディスク');
$query->setStart(0);
$query->setRows(300);
$query_response = $client->query($query);
$response = $query_response->getResponse();
var_dump($response);
/*
object(SolrObject)#4 (2) {
  ["responseHeader"]=>
  object(SolrObject)#5 (3) {
    ["status"]=>
    int(0)
    ["QTime"]=>
    int(0)
    ["params"]=>
    object(SolrObject)#6 (4) {
      ["indent"]=>
      string(2) "on"
      ["wt"]=>
      string(3) "xml"
      ["q"]=>
      string(3) "*:*"
      ["version"]=>
      string(3) "2.2"
    }
  }
  ["response"]=>
  object(SolrObject)#7 (3) {
    ["numFound"]=>
    int(2)
    ["start"]=>
    int(0)
    ["docs"]=>
    array(2) {
      [0]=>
      object(SolrObject)#8 (8) {
        ["created"]=>int(123456789)
        ["face"]=>string(5) "梅雨"
        ["id"]=>string(1) "1"
        ["is_public"]=>bool(true)
        ["tag"]=>string(15) "蒸し暑い,湿気"
      }
      [1]=>
      object(SolrObject)#9 (8) {
        ["created"]=>int(123456789)
        ["face"]=>string(12) "秋雨"
        ["id"]=>string(1) "2"
        ["is_public"]=>bool(true)
        ["tag"]=>string(15) "蒸し暑い,湿気"
      }
    }
  }
}
*/