<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Search extends Controller_Website {
	
	public function action_index()
	{
		$view = View::factory('search/customer/index');
		
		/*
		$blog_posts = ORM::factory('Blogs_Post')->where('status', '=', 'published')->find_all();
				foreach ($blog_posts as $blog_post)
				{
					$search->index($blog_post->as_array(), 'Blogs_Post');
				}*/
		/*
		$prayer = ORM::factory('Prayer');
				$prayers = $prayer->get_prayers($page = 1, $params = array());
				foreach ($prayers as $prayer)
				{
					$search->index($prayer->as_array(), 'Prayer');
				}*/
		
		$q = Arr::get($_GET, 'q', '');
		$view->q = $q;
		$page = Arr::get($_GET, 'page', 1);
		$view->page = $page;
		$sort_by = Arr::get($_GET, 'sort_by', 'relevance');
		$view->sort_by = $sort_by;
		$sort_direction = Arr::get($_GET, 'sort_direction', 'desc');
		$view->sort_direction = $sort_direction;
		$type = Arr::get($_GET, 'type', 'all');
		
		$search_params = array('type' => $type);
		$search = new Model_Search($search_params);
		$search->set_param('q', $q);
		$search->set_param('page', $page);
		$search->set_param('sort_by', $sort_by);
		$search->set_param('sort_direction', $sort_direction);
		$results = $search->execute();
		
		$view->results = $results;
		$view->pagination = $search->pagination;
		
		$this->template->body = $view;
	}
}