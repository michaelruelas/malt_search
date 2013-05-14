<?php defined('SYSPATH') or die('No direct script access.');

class Search_Elasticsearch {
	
	public $es = null;
	private $search_server = null;
	private $search_port = null;
	private $search_index = null;
	private $search_type = null;
	
	public function __construct($params, $config) {
		require_once Kohana::find_file('vendor', 'elasticsearch/Client');
		
		$this->params = $params;
		$this->config = $config;
		
		$this->search_server = Arr::get($params, 'host', Arr::get($config, 'host', Kohana::$config->load('search.elasticsearch.host')));
		$this->search_port = Arr::get($params, 'port', Arr::get($config, 'port', Kohana::$config->load('search.elasticsearch.port')));
		$this->search_index = Arr::get($params, 'index', Arr::get($config, 'index', Kohana::$config->load('search.elasticsearch.index')));
		$this->search_type = Arr::get($params, 'type', Arr::get($config, 'type', Kohana::$config->load('search.elasticsearch.type')));
		
		$this->es = ElasticSearch\Client::connection('http://'.$this->search_server.':'.$this->search_port.'/'.$this->search_index.'/'.$this->search_type);
		
		if ($this->search_type == 'all')
		{
			$this->es->setType('*');
		}
	}
	
	public function execute($params)
	{
		$return = new stdClass;
		$return->total_count = 0;
		$return->results = array();
		
		$from = Arr::get($params, 'page_limit')*(Arr::get($params, 'page')-1);
		
		$sort_by = Arr::get($params, 'sort_by', '_score');
		if ($sort_by == 'relevance')
		{
			$sort_by = '_score';
		}
		$sort_direction = Arr::get($params, 'sort_direction', 'desc');
		$sort = array($sort_by => $sort_direction);
		
		$results = $this->es->search(
			array(
				'from' => $from,
				'size' => Arr::get($params, 'page_limit'),
				'sort' => $sort,
				'query' => array(
					'query_string' => array(
						// 'fields' => '*',
						'query' => '*'.Arr::get($params, 'q').'*',
						// 'default_operator' => 'AND',
						'use_dis_max' => 'true'
					)
				),
				'explain' => true
			)
		);
		$error = Arr::get($results, 'error', false);
		if ($error)
		{
			echo '<pre>';
			echo $results['error'];
			echo '</pre>';
		}
		$hits = Arr::get($results, 'hits', false);
		
		if ($hits)
		{
			$return->total_count = Arr::get($hits, 'total', 0);
			
			$results_array = array();
			foreach (Arr::get($hits, 'hits', array()) as $result_item)
			{
				$temp_item = new stdClass;
				$temp_item->_index = Arr::get($result_item, '_index', 'default');
				$temp_item->_type = Arr::get($result_item, '_type', 'default');
				$temp_item->id = Arr::get($result_item, '_id', 0);
				
				$source_items = Arr::get($result_item, '_source', array());
				
				foreach ($source_items as $source_item_key => $source_item_value)
				{
					$temp_item->$source_item_key = $source_item_value;
				}
				
				$results_array[] = $temp_item;
			}
			
			$return->results = $results_array;
		}
		
		return $return;
	}
	
	public function index($data, $type)
	{
		$index_data = array();
		foreach ($data as $key => $value)
		{
			$index_data[$key] = $value;
		}
		
		$es = ElasticSearch\Client::connection('http://'.$this->search_server.':'.$this->search_port.'/'.$this->search_index.'/'.$type);
		$result = $es->index($index_data, $data['id']);
		return $result;
	}
	
	public function delete($id, $type)
	{
		$es = ElasticSearch\Client::connection('http://'.$this->search_server.':'.$this->search_port.'/'.$this->search_index.'/'.$type);
		$result = $es->delete($id);
	}
}