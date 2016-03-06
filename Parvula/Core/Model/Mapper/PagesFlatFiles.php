<?php

namespace Parvula\Core\Model\Mapper;

use Parvula\Core\Model\Page;
use Parvula\Core\FilesSystem as Files;
use Parvula\Core\Exception\IOException;
use Parvula\Core\Exception\PageException;
use Parvula\Core\PageRenderer\PageRendererInterface;

/**
 * Flat file pages
 *
 * @package Parvula
 * @version 0.5.0
 * @since 0.5.0
 * @author Fabien Sa
 * @license MIT License
 */
class PagesFlatFiles extends Pages
{
	/**
	 * @var string Pages folder
	 */
	private $folder;

	/**
	 * @var string Default file name if the slug point to a folder
	 */
	private $folderDefaultFile = '/index';

	/**
	 * Constructor
	 *
	 * @param PageRendererInterface $pageRenderer Page renderer
	 * @param string $folder Pages folder
	 * @param string $fileExtension File extension
	 */
	function __construct(PageRendererInterface $pageRenderer, $folder, $fileExtension) {
		parent::__construct($pageRenderer);

		$this->folder = $folder;
		$this->fileExtension =  '.' . ltrim($fileExtension, '.');
		$this->fetchPages();
	}

	/**
	 * Get a page object in html string
	 *
	 * @param string $pageUID Page unique ID
	 * @param boolean ($eval) Evaluate PHP
	 * @throws IOException If the page does not exists
	 * @return Page|bool Return the selected page if exists, false if not
	 */
	public function read($pageUID, $parse = true, $eval = false) {
		$pageUID = trim($pageUID, '/');

		// If page was already loaded, return page
		if (isset($this->pages[$pageUID])) {
			return $this->pages[$pageUID];
		}

		$pageFullPath = $pageUID . $this->fileExtension;

		$fs = new Files($this->folder);

		if (!$fs->exists($pageFullPath)) {
			// Check if it can fallback to a default file in the folder
			$pageUID = $pageUID . $this->folderDefaultFile;
			if (!$fs->exists($pageFullPath = $pageUID . $this->fileExtension)) {
				return false;
			}
		}

		// Anonymous function to use renderer engine
		$renderer = $this->renderer;
		$fn = function (\SplFileInfo $fileInfo, $data) use ($pageUID, $renderer, $parse) {
			// Create the title from the filename
			if (strpos($pageUID, '/') !== false) {
				$pageUIDToken = explode('/', $pageUID);
				$pageTitle = array_pop($pageUIDToken);
				$parent = implode('/', $pageUIDToken);
			} else {
				$pageTitle = $pageUID;
			}

			$opts = [
				'slug' => $pageUID,
				'title' => ucfirst(strtr($pageTitle, '-', ' ')), // lisp-case to Normal case
				'date' => '@' . $fileInfo->getMTime()
			];

			isset($parent) ? $opts += ['parent' => $parent] : null;
			$pageUID[0] === '_' ? $opts += ['hidden' => true] : null;
			$pageUID[0] === '.' ? $opts += ['secret' => true] : null;

			return $renderer->parse($data, $opts, $parse);
		};

		$page = $fs->read($pageFullPath, $fn, $eval);
		$this->pages[$pageUID] = $page;

		return $page;
	}

	/**
	 * Create page object in "pageUID" file
	 *
	 * @param Page $page Page object
	 * @throws IOException If the destination folder is not writable
	 * @throws PageException If the page does not exists
	 * @return bool
	 */
	public function create($page) {
		if (!isset($page->slug)) {
			throw new IOException('Page cannot be created. It must have a slug');
		}

		$fs = new Files($this->folder);

		if (!$fs->isWritable()) {
			throw new IOException('Page destination folder is not writable');
		}

		$slug = $page->slug;
		$pagePath = $slug . $this->fileExtension;

		try {
			if ($fs->exists($pagePath)) {
				return false;
			}

			$data = $this->renderer->render($page);

			if (!$fs->write($pagePath, $data)) {
				return false;
			}
		} catch (IOException $e) {
			throw new PageException('Page cannot be created');
		}

		$this->pages[$slug] = $page;

		return true;
	}

	/**
	 * Update page object
	 *
	 * @param string $pageUID Page unique ID
	 * @param Page $page Page object
	 * @throws PageException If the page is not valid
	 * @throws PageException If the page already exists
	 * @throws PageException If the page does not exists
	 * @return bool Return true if page updated
	 */
	public function update($pageUID, $page) {
		$fs = new Files($this->folder);
		$pageFile = $pageUID . $this->fileExtension;
		if (!$fs->exists($pageFile)) {
			throw new PageException('Page `' . $pageUID . '` does not exists');
		}

		if (!isset($page->title, $page->slug)) {
			throw new PageException('Page not valid. Must have at least a `title` and a `slug`');
		}

		// New slug, need to rename
		if ($pageUID !== $page->slug) {
			$pageFileNew = $page->slug . $this->fileExtension;

			if ($fs->exists($pageFileNew)) {
				throw new PageException('Cannot rename, page `' . $page->slug . '` already exists');
			}

			$fs->rename($pageFile, $pageFileNew);
			$pageFile = $pageFileNew;
		}

		$data = $this->renderer->render($page);

		$fs->write($pageFile, $data);

		$this->pages[$page->slug] = $page;

		return true;
	}

	/**
	 * Patch page
	 *
	 * @param string $pageUID
	 * @param array $infos Patch infos
	 * @return boolean True if the page was correctly patched
	 */
	public function patch($pageUID, array $infos) {
		$fs = new Files($this->folder);
		$pageFile = $pageUID . $this->fileExtension;
		if (!$fs->exists($pageFile)) {
			throw new PageException('Page `' . $pageUID . '` does not exists');
		}

		/**
		 * Patch helper
		 * @param  array $struct Array to patch
		 * @param  array $patch Patch to apply
		 * @return array Patched array
		 */
		function patchHelper($struct, $patch) {
			foreach ($patch as $key => $value) {
				if (is_array($value)) {
					// current value is an array, nothing to replace, use recursion
					if ((object) $struct === $struct) {
						$value = patchHelper($struct->$key, $value);
					}
					else if ((array) $struct === $struct) {
						$value = patchHelper($struct[$key], $value);
					}
				}

				if ((array) $struct === $struct) {
					if ($value === null || $value === '') {
						unset($struct[$key]);
					} else {
						$struct[$key] = $value;
					}
				}
				else if ((object) $struct === $struct) {
					if ($value === null || $value === '') {
						unset($struct->$key);
					} else {
						$struct->$key = $value;
					}
				}
			}

			return $struct;
		}

		$page = $this->read($pageUID, false);
		$pagePatched = patchHelper((array) $page, $infos);

		$infos = Page::pageFactory($pagePatched);

		return $this->update($pageUID, $infos);
	}

	/**
	 * Delete a page
	 *
	 * @param string $pageUID
	 * @throws IOException If the page does not exists
	 * @return boolean If page is deleted
	 */
	public function delete($pageUID) {
		$pageFullPath = $pageUID . $this->fileExtension;

		$fs = new Files($this->folder);
		return $fs->delete($pageFullPath);
	}

	/**
	 * Index pages and get an array of pages slug
	 *
	 * @param boolean ($listHidden) List hidden files & folders
	 * @param string ($pagesPath) Pages path
	 * @throws IOException If the pages directory does not exists
	 * @return array Array of pages paths
	 */
	public function index($listHidden = false, $pagesPath = '') {
		$pages = [];

		try {
			// Filter secret (.*) and hiddent files (_*)
			$filter = function ($current) use ($listHidden) {
				return ($listHidden || $current->getFilename()[0] !== '_')
					&& $current->getFilename()[0] !== '.';
			};

			$ext = $this->fileExtension;
			(new Files($this->folder))->index($pagesPath,
				function (\SplFileInfo $file, $dir) use (&$pages, $ext) {
				$currExt = '.' . $file->getExtension();

				// If files have the right extension
				if ($currExt === $ext) {
					if ($dir) {
						$dir = trim($dir, '/\\') . '/';
					}
					$pages[] = $dir . $file->getBasename($currExt); // page path
				}
			}, $filter);

			return $pages;
		} catch (IOException $e) {
			exceptionHandler($e);
		}
	}

	/**
	 * Fetch all pages
	 * This method will read each pages
	 * If you want an array of Page use `toArray()` method
	 * Exemple: `$pages->all()->toArray();`
	 *
	 * @param string ($path) Pages in a specific sub path
	 * @return Pages
	 */
	public function all($path = '') {
		$that = clone $this;
		$that->pages = [];

		$pagesIndex = $this->index(true, $path);

		foreach ($pagesIndex as $pageUID) {
			if (!isset($that->pages[$pageUID])) {
				$page = $this->read($pageUID);
				$that->pages[$page->slug] = $page;
			}
		}

		return $that;
	}

	/**
	 * Fetch pages
	 *
	 * @return array Array of all Page
	 */
	private function fetchPages() {
		$_pages = [];
		$_pagesChildren = [];

		$pagesIndex = $this->index(true);

		foreach ($pagesIndex as $pageUID) {
			$page = $this->read($pageUID);
			$_pages[] = $page;

			if (isset($page->parent)) {
				$parent = $page->parent;
				$that = $this;
				// Add lazy function to resolve parent when function is called
				$page->addLazy('parent', function () use ($parent, $that) {
					return $that->read($parent);
				});
				if (!isset($_pagesChildren[$parent])) {
					$_pagesChildren[$parent] = [];
				}
				$_pagesChildren[$parent][] = $page;
			}
		}

		foreach ($_pages as $page) {
			if (isset($_pagesChildren[$page->slug])) {
				$page->setChildren($_pagesChildren[$page->slug]);
			}
		}

		return $_pages;
	}

}