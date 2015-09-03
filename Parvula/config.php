<?php

// ----------------------------- //
// # Core config
// ----------------------------- //

return [
	// Show errors if debug is enabled
	'debug' => true,

	// Class aliases
	'aliases' => [
		'HTML' => 'Parvula\Core\Html',
		'Conf' => 'Parvula\Core\Config',
		'Asset' => 'Parvula\Core\Asset',
		'Component' => 'Parvula\Core\Component'
	],

	// List of disabled plugins
	'disabledPlugins' => [

	],

	// You can force this option with a boolean or leave it on 'auto' detection
	'URLRewriting' => 'auto',

	// Home page
	'homePage' => 'home',

	// Error page
	'errorPage' => '_404',

	// Extension for files in ./data/pages
	'fileExtension' => 'md',

	// How to sort pages (php.net/manual/en/function.array-multisort.php)
	'typeOfSort' => SORT_ASC,

	// Sort pages from specific field (like title, index or whatYouWant)
	'sortField' => 'index',

	// Config file to read
	'userConfig' => 'site.conf.php',

	// Class to parse pages (must implements ContentParserInterface), can be null
	// 'defaultContentParser' => null,
	'defaultContentParser' => 'Parvula\Core\Parser\MarkdownContentParser',

	// Class to (un)serialize pages (must implements PageSerializerInterface)
	'defaultPageSerializer' => 'Parvula\Core\Serializer\ParvulaPageSerializer',
	// 'defaultPageSerializer' => 'Parvula\Core\Serializer\ParvulaJsonPageSerializer',

];
