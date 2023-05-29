<?php
if (empty($this)) die;
require_once('functions/klasa.persons.2.php');
require_once('functions/klasa.wikidata.php');

$this->setTitle($this->transEsc('Persons'));

$this->addClass('buffer', 	new marcBuffer()); 
$this->addClass('helper', 	new helper()); 
$this->addClass('persons', 	new persons($this->config)); 

$this->persons->register('psql', $this->psql);
$this->buffer->setSql($this->sql);


if (!empty($this->GET['limit']))
	$this->saveUserParam('limit',$this->GET['limit']);
	else if (empty($this->getUserParam('limit')))
	$this->saveUserParam('limit', $this->config['search']['pagination']['default_rpp']);


$lookFor = $this->postParam('lookfor');
if (empty($lookFor) && !empty($this->GET['lookfor']))
	$lookFor = $this->GET['lookfor'];

$WAR = [];
if ($lookFor<>'') {
	$queryString = explode(' ', $this->urlName2($lookFor));
	$WAR[] = $WHERE = "(name_search ILIKE '%".implode("%' AND name_search ILIKE '%", $queryString)."%')";
	} 
	

	
####################################################################################################################################################	

$labels = [];
$labels['All persons'] = $totalResults =  $this->persons->getResultsCount($WAR);

$labels['Persons with creative roles'] = $this->persons->getResultsCount($WAR,['as_author>0']);
$labels['Persons as subject'] = $this->persons->getResultsCount($WAR,['as_topic>0']);

$this->setLastPage(ceil($totalResults/$this->getUserParam('limit')));

$currentPage = $this->getCurrentPage();
$limit = $this->getUserParam('limit');
$offset = $limit*($currentPage-1);
$res = $this->psql->querySelect("SELECT * FROM  persons {$this->persons->where($WAR)} ORDER BY rec_total DESC, name LIMIT {$limit} OFFSET {$offset};");



?>


<?= $this->render('head.php') ?>
<?= $this->render('core/header.php') ?>
<?= $this->render('persons/home.php', ['labels'=>$labels, 'results'=>$res, 'totalResults'=>$totalResults] ) ?>
<?= $this->render('core/footer.php') ?>

