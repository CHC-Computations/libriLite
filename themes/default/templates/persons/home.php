<div class='main'>
	<div class='sidebar'>
		<?= $this->render('persons/facets-sidebar.php', ['labels'=>$labels]) ?>
		
	</div>
	<div class='mainbody' id='content'>
		<?= $this->render('persons/summary.php', ['resultsCount'=>$totalResults]) ?>
		<div class="results">
		  <?php if (!empty($results) && is_array($results)): ?>
		  <div id="resultsBox" class="results-list">
				<?php 
				foreach ($results as $result) {
					
					$AP = (object)$result;

					if (!empty($AP->wikiq)) {
						$AP->field = "personBoxQ".$AP->wikiq;
						$base = base64_encode(json_encode($AP));
						$this->addJS("page.post('{$AP->field}', 'wiki/person/box/{$AP->wikiq}?', ".json_encode($AP).");");
						} else {
						$AP->field = "personBoxB".$this->buffer->shortHash($AP->name);	
						}
						
					echo '<div class="person-info" id="'.$AP->field.'">';
					echo $this->render('persons/results/list.php',['activePerson'=>$AP]);
					echo '</div>';
					}
				?>
			  <br/>
		  </div>
		  <?= $this->render('persons/paggination.php') ?>
		  <?php else: ?>
		  <h1><?=$this->transEsc('No results')?></h1>
		  <?php endif; ?>
		</div>
		
		
	</div>
</div>

