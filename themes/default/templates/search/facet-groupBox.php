<?php 
$panelId = uniqid();

$lines = [];	
$lp = 0;
$maks = 5;
if (is_object($list)) {
	foreach ($list as $key=>$facet) {
		$lp++;
		if (empty($facet->groupList)) {
			$facet->parent = 'group';
			$this->addJS("facets.cascade2('$key', '{$this->facetsCode}', ".json_encode($facet).");");
			$lines[] = '
				<div class="facetTop" OnMouseOver="facets.place(\''.$key.'\')" OnMouseOut="facets.out(\''.$key.'\')">
				  <a id="facetBase'.$key.'" class="facet js-facet-item">
					<span class="text">'.$this->transEsc($facet->name).'</span>
					<i class="ph-caret-right-bold" id="caret_'.$key.'" style=" margin-right:-7px; margin-top:4px; font-size:0.8em;"></i>
				  </a>
				  <div class="facetCascade" id="facetLink'.$key.'">
					'.$this->render('search/facet-cascade-empty.php').'
				  </div>
				</div>';
			} else {
			$lines[] = '
				<div class="facetTop" OnMouseOver="facets.place(\''.$key.'\')" OnMouseOut="facets.out(\''.$key.'\')">
				  <a id="facetBase'.$key.'" class="facet js-facet-item">
					<span class="text">'.$this->transEsc($facet->name).'</span>
					<i class="ph-caret-right-bold" id="caret_'.$key.'" style=" margin-right:-7px; margin-top:4px; font-size:0.8em;"></i>
				  </a>
				  <div class="facetCascade" id="facetLink'.$key.'">
					'.$this->render('search/facet-cascade-groupBox.php', [
										'groupName'  => $facet->name, 
										'list' 	 	 => $facet->groupList,
										'stepSetting' => $stepSetting
										] ).'
				  </div>
				</div>';
			}
		}
	
	
	if (count($lines)>0) {	
		echo $this->helper->PanelCollapse(
			$panelId,
			$this->transEsc($groupName),
			implode('',$lines) 
			);
		}
	}
?>
