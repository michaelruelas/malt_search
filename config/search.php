<?php defined('SYSPATH') or die('No direct script access.');

return array(
	'default' => array(
		'driver' => 'elasticsearch',
		'host' => 'localhost',
		'port' => 9200,
		'index' => 'default',
		'type' => 'default'
	),
	'amazon' => array(
		'driver' => 'amazonsearch',
		'host' => 'localhost',
		'port' => 9200,
		'index' => 'default',
		'type' => 'default',
		'domain' => 'tstuds',
		'domain_id' => 'x6mr6gq77paxobo22rdoh27ifq',
		'search_endpoint' => 'search-tstuds-x6mr6gq77paxobo22rdoh27ifq.us-east-1.cloudsearch.amazonaws.com',
		'document_endpoint' => 'doc-tstuds-x6mr6gq77paxobo22rdoh27ifq.us-east-1.cloudsearch.amazonaws.com'
	),
);