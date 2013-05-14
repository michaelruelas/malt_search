<div class="row">
	<div class="span12 page_header_container">
		<div class="row page">
			<div class="page_header span4">
				<div class="page_header_container">
					<div class="page_header">
						<h1>Search <small><?php echo $q ?> - <?php echo $results->total_count?> results</small></h1>
					</div>
				</div>
			</div>
			<div class="span12 page_body_container">
				<div class="page_body search_results">
					<?php
						foreach ($results->results as $result)
						{
							try
							{
								$view = View::factory('search/types/'.$result->_type);
							}
							catch (Kohana_Exception $e)
							{
								$view = View::factory('search/types/default');
							}
							$view->result = $result;
							echo $view;
						}
					?>
					<?php echo $pagination ?>
				</div>
			</div>
		</div>
	</div>
</div>