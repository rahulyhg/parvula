<?php

// ----------------------------- //
//  System config
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

	// Default home page
	'homePage' => 'home',

	// Error page
	'errorPage' => '_404',

	// Extension for files in ./data/pages
	'fileExtension' => 'md',

	// How to sort pages (SORT_ASC, SORT_DESC) (php.net/manual/en/function.array-multisort.php)
	'typeOfSort' => SORT_ASC,

	// Sort pages from specific field (like title, index or whatYouWant)
	'sortField' => 'slug',

	// File extensions in 'media' folder
	'mediaExtensions' => ['jpg', 'jpeg', 'png', 'gif'],

	// User config file to read
	'userConfig' => 'site.conf.yaml',

	// Class to parse pages (must implements ContentParserInterface), can be null
	'headParser' => '\Parvula\Core\Parser\Yaml',

	// Class to parse pages (must implements ParserInterface)
	'contentParser' => '\Parvula\Core\ContentParser\Markdown',

	// Class to fetch/render pages (must implements PageRendererInterface)
	'pageRenderer' => 'Parvula\Core\PageRenderer\ParvulaPageRenderer',

	// Force the login to use transport layer protection (SSL/TLS) (MUST be *true* in production)
	// SSL versions 1, 2, and 3 should not longer be used
	// The best practice is to only provide support for the TLS protocols (1.0, 1.1 and 1.2)
	// For more informations https://owasp.org/index.php/Transport_Layer_Protection_Cheat_Sheet
	'forceLoginOnTLS' => false,

];
