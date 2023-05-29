<?php 
if (empty($this)) die;
require_once('functions/klasa.helper.php');
$x = count($this->routeParam)-1;

$tmp = explode('.', $this->routeParam[$x]);

$this->addClass('solr', new solr($this->config));
$this->addClass('buffer', new marcBuffer()); 
$this->addClass('helper', 	new helper()); 


$rec_id = current($tmp);
$format = end($tmp);

$rec_id=str_replace('.html', '', $this->routeParam[$x]);
$record = $this->solr->getRecord('biblio', $rec_id);
if (!empty($record->id)) {
	$marcJson = $this->buffer->getJsonRecord($record->id, $record->fullrecord);
	$this->addClass('record', new marc21($marcJson));
		
	echo $this->render('record/mini/preView.php', ['result'=>$record]);
	
	} else 
	echo "error!";