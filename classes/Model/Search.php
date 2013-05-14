<?php defined('SYSPATH') or die('No direct script access.');

class Model_Search extends Model {
	
	public $driver = null;
	public $search_engine = null;
	public $pagination = null;
	public $total_results = 0;
	public $results = array();
	public $params = array();
	
	public function __construct($params = array()) {
		$config = Kohana::$config->load('search.'.Arr::get($params, 'config', 'default'));
		$driver = ucfirst($config['driver']);
		$this->driver = $driver;
		
		$this->set_default_params();
		$this->process_params($params);
		
		$search_classname = 'Search_'.$driver;
		if (class_exists($search_classname))
		{
			$this->search_engine = new $search_classname($params, $config);
		}
		else
		{
			echo 'Error: Search engine '.$driver.' doesn\'t exist';
			die();
		}
	}
	
	private function set_default_params()
	{
		$this->params['q'] = '';
		$this->params['page'] = 1;
		$this->params['page_limit'] = 10;
		$this->params['sort_by'] = 'relevance';
		$this->params['sort_direction'] = 'desc';
	}
	
	private function process_params($params)
	{
		foreach ($params as $param_key => $param_value)
		{
			$this->params[$param_key] = $param_value;
		}
	}
	
	public function set_param($key, $value)
	{
		$this->params[$key] = $value;
	}
	
	public function execute()
	{
		$results = $this->search_engine->execute($this->params);
		$this->results = $results;
		
		$pagination = Pagination::factory(array(
            'items_per_page' => $this->params['page_limit'],
            'total_items' => $results->total_count,
        ));
		$this->pagination = $pagination;
		
		return $results;
	}
	
	public function index($data, $type)
	{
		$result = $this->search_engine->index($data, $type);
		return $result;
	}
	
	public function delete($id, $type)
	{
		$result = $this->search_engine->delete($id, $type);
		return $result;
	}
}