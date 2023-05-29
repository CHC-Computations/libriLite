<?php
if (empty($this)) die;
$facetCode = $this->routeParam[0];
$this->facetsCode = $this->routeParam[1];

$this->addClass('helper', new helper()); 
$this->addClass('solr', new solr($this->config)); 
$this->addClass('buffer', 	new marcBuffer()); 


$stepSetting = clone $this->settings->facets->defaults;
$facet = (object)$this->POST['list'];
#echo $this->helper->pre($facet);


if (!empty($facet->template))
	$stepSetting->template = $facet->template;
if (!empty($facet->translated))
	$stepSetting->translated = $facet->translated;
if (!empty($facet->formatter))
	$stepSetting->formatter = $facet->formatter;
if (!empty($facet->child))
	$stepSetting->child = $facet->child;

switch ($stepSetting->template) {
	case 'box' : 
			$query[] = $uf = $this->buffer->getFacets($this->sql, $this->facetsCode);	
			if (!empty($this->GET['sj'])) {
				$query['q'] = [ 'field' => 'q',	'value' => $this->solr->advandedSearch($this->GET['sj'])];
				} else 
				$query['q'] = $this->solr->lookFor($lookfor = $this->getParam('GET', 'lookfor'), $type = $this->getParam('GET', 'type') );			
			
			$query['limit'] = [
					'field' => 'facet.limit',
					'value' => $stepSetting->facetLimit
					];
			
			$query[] =  $this->solr->facetsCountCode($facet->solr_index);
			$results = $this->solr->getFacets('biblio', [$facet->solr_index], $query);

			if (is_Array($results)) {
				$lines = [];
				if (!empty($results[$facet->solr_index])) {
					foreach ($results[$facet->solr_index] as $name=>$count) {
						if ($count>0) {
							$tname = $name;
							if (!empty($stepSetting->formatter)) {
								$formatter = $stepSetting->formatter;
								$tname = $this->helper->$formatter($name);
								}
							if ($stepSetting->translated)
								$tname = $this->transEsc($tname);
							
							if ($this->buffer->isActiveFacet($facet->solr_index, $name)) {
								$key = $this->buffer->createFacetsCode(
										$this->sql, 
										$this->buffer->removeFacet($facet->solr_index, $name)
										);
								$lines[] = '<a href="'.$this->buildUri('search/results/1/'.$this->getUserParam('sort').'/'.$key, $this->GET).'" class="facet js-facet-item active" >
												<span class="text">'.$this->transEsc($tname).'</span>
												<i class="right-icon glyphicon glyphicon-remove" ></i>
											</a>';
							
								} else {
								$key = $this->buffer->createFacetsCode(
										$this->sql, 
										$this->buffer->addFacet($facet->solr_index, $name)
										);
								$lines[] = '<a href="'.$this->buildUri('search/results/1/'.$this->getUserParam('sort').'/'.$key, $this->GET).'" class="facet js-facet-item" >
												<span class="text">'.$this->transEsc($tname).'</span>
												<span class="badge">'.$this->helper->numberFormat($count).'</span>
											</a>';
								}
							}
						}
					
					}
				if (count($lines)>1) {
					echo $this->render('search/facet-cascade-search.php', ['lines' => $lines, 'facet'=>$facet, 'stepSetting'=>$stepSetting, 'total' => $this->solr->getFacetsCount($facet->solr_index) ]);
					} else {
					if (!empty($facet->parent) && ($facet->parent == 'group'))	
						$this->addJS("
							$('#facetBase{$facetCode}').css('opacity','0.5');
							$('#caret_{$facetCode}').css('color','transparent');
							");	
					}
				}
			break;
	case 'groupBox' :
			#echo $this->helper->pre($facet);
			echo $this->render('search/facet-cascade-groupBox.php', [
						'groupName'  => $facet->name, 
						'list' 	 	 => $facet->groupList,
						'stepSetting' => $stepSetting
						] );
			
			break;			
	case 'graph' :		
			$filter = $this->buffer->getFacets($this->sql, $this->facetsCode);	
			if (!empty($filter))
				$query[] = $filter;
			if (!empty($this->GET['sj'])) {
				$query['q'] = [ 'field' => 'q',	'value' => $this->solr->advandedSearch($this->GET['sj'])];
				} else 
				$query['q'] = $this->solr->lookFor($lookfor = $this->getParam('GET', 'lookfor'), $type = $this->getParam('GET', 'type') );			
			$query[] =  $this->solr->facetsCountCode($facet->solr_index);
			
			$blocks = $this->solr->getFullList2('biblio', $facet->solr_index, $query);
			$extra = [];
			$hasValues = false;
			foreach ($blocks->results as $k=>$v) 
				if (!is_numeric($k)) {
					$extra[$k]=$v;
					unset($blocks->results[$k]);
					} elseif ($v>0)
					$hasValues = true;
			ksort($blocks->results);
			#echo "<pre>".print_r($blocks,1).'</pre>';	
			if ($hasValues)
				echo $this->render('search/facet-cascade-centuries-box.php', [
							'facet' => $facet->solr_index, 
							'facetName' => $facet->name, 
							'facets' => $blocks->results,
							'extraFacets' => $extra,
							'currFacet' => $facet->solr_index,
							] );
				else if (!empty($facet->parent) && ($facet->parent == 'group'))	
						$this->addJS("
							$('#facetBase{$facetCode}').css('opacity','0.5');
							$('#caret_{$facetCode}').css('color','transparent');
							");			
			break;
	}



#echo $this->helper->pre($uf); 
#echo 'facet:'.$this->helper->pre($facet);

# echo "TEST: ";
#print_r($this->routeParam);
#print_r($this->GET);



?>