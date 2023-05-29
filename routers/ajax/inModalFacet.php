<?php 
if (empty($this)) die;
$this->addClass('buffer', new marcBuffer()); 
$this->addClass('helper', new helper()); 
$this->addClass('solr', new solr($this->config)); 

$this->buffer->setSql($this->sql);
#echo "<prE>".print_r($this,1)."</pre>";


$facets = $this->getConfig('facets');


$currAction=$this->routeParam[0];
$currFacet=$this->routeParam[1];
$this->facetsCode = $this->routeParam[count($this->routeParam)-1];

#echo 'facetCode: '.$this->facetsCode;
$facetName = $facets['facetList'][$currFacet];
$queryoptions=[];
if (!empty ($this->GET['sort'])) {
	$queryoptions[]=[ 
				'field' => 'facet.sort',
				'value' => $this->GET['sort']
				];
			
	}
if (!empty ($this->GET['q'])) {
	$ss = $this->GET['q'];
	
	/*
	// eksperyment z "adam mickiewicz" itp. 
	
	$tmp = explode(' ', $ss);
	if (count($tmp)>1) {
		$ss = $this->buffer->getSearchString($facetName, $tmp);
		echo "<small>Szukam: $ss</small><br/>";
		}
	*/
	
	
	$queryoptions[]=[ 
				'field' => 'facet.contains.ignoreCase',
				'value' => 'true'
				];
	$queryoptions[]=[ 
				'field' => 'facet.contains',
				'value' => $ss
				];
	/* 
	$queryoptions[]=[ 
				'field' => 'json.facet', //json.facet={x:"unique(author_facet_s)"}
				'value' => '{x:"unique('.$currFacet.')"}'
				];
	*/
	}
$queryoptions[] = $this->buffer->getFacets($this->sql, $this->facetsCode);
$queryoptions['q'] = $this->solr->lookFor($lookfor = $this->getParam('GET', 'lookfor'), $type = $this->getParam('GET', 'type') );			
if (!empty($this->GET['sj'])) {
	$queryoptions[] = [ 
			'field' => 'q',
			'value' => $this->solr->advandedSearch($this->GET['sj'])
			];
	}
	
if (!empty ($this->GET['add'])) {
	$_SESSION['facets_chosen'][$currFacet][$this->GET['add']]='ok';
	}	
if (!empty ($this->GET['remove'])) {
	unset($_SESSION['facets_chosen'][$currFacet][$this->GET['remove']]);
	}	
	 



switch ($currAction) {
	case 'build' :
			if (!empty($facets['facetList'][$currFacet])) {

				switch ($currFacet) {
					default: {
						$results = $this->solr->getFacets('biblio', [$currFacet], $queryoptions);
						
						#echo implode(' ',$this->solr->alert);
						echo $this->render('search/inmodal/facet-search-box.php', ['facetName'=>$facetName, 'currFacet'=>$currFacet, 'facets'=>$results[$currFacet] ] );
						} break;
					case 'year_str_mv' : { // rok wydania - inny graf 
					
						$results = $this->solr->getCleanedYears('biblio', [$currFacet], $queryoptions);
						if (empty($results[$currFacet])) 
							echo $this->render('search/inmodal/no-results.php');
							else 
							# echo $this->render('search/inmodal/facet-years-box.php', ['facetName'=>$facetName, 'currFacet'=>$currFacet, 'facets'=>$results[$currFacet] ] );
							echo $this->helper->drawTimeGraph($results[$currFacet]);
						} break;
					} // switch 
			 
				} else {
				echo $this->transEsc('We don`t know a facet like').': '.$currFacet.'<br/>';	
				}
		break;		
	case 'search' : 			
			if (!empty($facets['facetList'][$currFacet])) {
				$results = $this->solr->getFacets('biblio', [$currFacet], $queryoptions);
				if (empty($results[$currFacet])) 
					echo $this->render('search/inmodal/no-results.php');
					else 
					echo $this->render('search/inmodal/facet-search-box-results.php', ['facetName'=>$facetName, 'currFacet'=>$currFacet, 'facets'=>$results[$currFacet] ] );
				
				} else {
				echo $this->transEsc('We don`t know a facet like').': '.$currFacet.'<br/>';	
				}
				
			#echo "params<pre>".print_r($this->routeParam,1).'</pre>';
			#echo "GET<pre>".print_r($this->GET,1).'</pre>';
			#echo time();
				
		break;
	}
	
#echo "results<pre>".print_r($results,1).'</pre>';

?>