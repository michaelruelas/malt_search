<?php defined('SYSPATH') or die('No direct script access.');

class Search_Amazonsearch {
	
	public $aws = null;
	private $search_server = null;
	private $search_port = null;
	private $search_index = null;
	private $search_type = null;
	
	public function __construct($params, $config) {
		require_once Kohana::find_file('vendor', 'amazon/cloudsearch/awsCloudSearch');
		
		$this->params = $params;
		$this->config = $config;
		
		$this->search_server = Arr::get($params, 'host', Arr::get($config, 'host', Kohana::$config->load('search.amazon.host')));
		$this->search_port = Arr::get($params, 'port', Arr::get($config, 'port', Kohana::$config->load('search.amazon.port')));
		$this->search_index = Arr::get($params, 'index', Arr::get($config, 'index', Kohana::$config->load('search.amazon.index')));
		$this->search_type = Arr::get($params, 'type', Arr::get($config, 'type', Kohana::$config->load('search.amazon.type')));
		
		$this->aws = new awsCloudSearch(Kohana::$config->load('search.amazon.domain'), Kohana::$config->load('search.amazon.domain_id'));
		
	}
	
	public function execute($params)
	{
		$return = new stdClass;
		$return->total_count = 0;
		$return->results = array();
		
		$from = Arr::get($params, 'page_limit')*(Arr::get($params, 'page')-1);
		$fields = Arr::get($params, 'fields');
		$sort_by = Arr::get($params, 'sort_by', 'text_relevance');
		$sort_direction = Arr::get($params, 'sort_direction', 'asc');
		
		switch ($sort_direction)
		{
			case 'asc':
				$sort_symbol = '';
				break;
			case 'desc':
				$sort_symbol = '-';
				break;
		}

		if ($sort_by == 'relevance')
		{
			$sort_by = 'default';
			$sort_symbol = '-';
		}
		
		if ($this->search_type == Kohana::$config->load('search.amazon.type'))
		{
			$results = $this->aws->search(Arr::get($params, 'q'), array(
				'return-fields' => $fields,
				'rank' => $sort_symbol.$sort_by,
				'size' => Arr::get($params, 'page_limit'),
				'start' => $from
				
				)
			);
		}
		else
		{
			$results = $this->aws->boolean_search(Arr::get($params, 'boolean_fields'), array(
				'return-fields' => $fields,
				'rank' => $sort_symbol.$sort_by,
				'size' => Arr::get($params, 'page_limit'),
				'start' => $from
				)
			);
		}
		
		
		$results = json_decode($results, true);
		
		$error = Arr::get($results, 'error', false);
		if ($error)
		{
			//echo '<pre>';
			//echo 'Search Error! Amazon Driver '.$results['error'];
			//echo '</pre>';
		}
		$hits = Arr::get($results, 'hits', false);
		
		if ($hits)
		{
			$return->total_count = Arr::get($hits, 'found', 0);
			
			$results_array = array();
			foreach (Arr::get($hits, 'hit', array()) as $result_item)
			{
				$temp_item = new stdClass;
				$temp_item->_index = Arr::get($result_item, '_index', 'default');
				$temp_item->_type = Arr::get($result_item, '_type', 'default');
				$temp_item->id = Arr::get($result_item, 'id', 0);
				
				$source_items = Arr::get($result_item, 'data', array());
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
	
	public function index($data, $type = 'add')
	{
		$index_data = array();
		$document = array('type'=> 'add');
		foreach ($data as $key => $value)
		{
			$index_data[$key] = $value;
		}
		$count = ORM::factory('Searchversion')->where('id', '=', $index_data['id'])->find();
		if($count->id != null OR $count->id != 0)
		{
			$count->version = $count->version +1;
			$count->save();
			$document['version'] = $count->version;
		}
		else
		{
			$document['version'] = 1;
			$count = ORM::factory('Searchversion');
			$count->version = 1;
			$count->id = $index_data['id'];
			$count->save();
		}
		$document['id'] = $index_data['id'];
		$document['fields'] = $index_data;
		$document['lang'] = 'en';
		$aws = new awsCloudSearch(Kohana::$config->load('search.amazon.domain'), Kohana::$config->load('search.amazon.domain_id'));
		$result = $aws->document($type = 'add', array($document));
		return $result;
	}
	
	public function delete($id, $type = 'delete')
	{
		$delete_data = array();
		$delete_data['type'] = 'delete';
		$delete_data['id'] = $id;
		
		$count = ORM::factory('Searchversion')->where('id', '=', $id)->find();
		if($count->id != null)
		{
			$count->version = $count->version +1;
			$count->save();
			$delete_data['version'] = $count->version;
			
		}
		else
		{
			$delete_data['version'] = 1;
			$count = ORM::factory('Searchversion');
			$count->version = 1;
			$count->id = $id;
			$count->save();
		}
		
		$aws = new awsCloudSearch(Kohana::$config->load('search.amazon.domain'), Kohana::$config->load('search.amazon.domain_id'));
		
		$result = $aws->document($type, $delete_data);
		return $result;
	}
}