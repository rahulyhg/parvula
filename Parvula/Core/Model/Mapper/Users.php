<?php

namespace Parvula\Core\Model\Mapper;

use Parvula\Core\Model\User;
use Parvula\Core\ArrayTrait;
use Parvula\Core\FileParser;
use Parvula\Core\Model\Mapper\AbstractDataMapper;

class Users
{

	/**
	 * @var array Array of User
	 */
	private $parser;

	/**
	 * @var array User[]
	 */
	protected $data;

	public function __construct(FileParser $parser, $usersFile) {
		$this->parser = $parser;
		$this->data = $parser->read($usersFile);
	}

	/**
	 * Index ressources
	 *
	 * @return array List of ressources
	 */
	public function index() {
		// Return users username
		return array_map(function($user) {
			return $user['username'];
		}, $this->data);
	}

	/**
	 * Read a user from ID
	 *
	 * @param  string $id ID (username)
	 * //@throws Exception If the ressource does not exists
	 * @return User|bool The user or false if user not found
	 */
	public function read($id) {
		foreach ($this->data as $user) {
			if ($user['username'] === $id) {
				return new User($user);
			}
		}

		return false;
	}

	/**
	 * Update
	 *
	 * @param string $id ID
	 * @param mixed $data Data
	 * @return bool
	 */
	public function update($username, $user) {
		// ovveride ?
		if ($userOld = $parser->read($user->username)) {

		}

		return false;
	}

	/**
	 * Create
	 *
	 * @param User $user User
	 * @return bool
	 */
	public function create($user) {
		if (!$parser->read($user->username)) {

		}

		return false;
	}

	/**
	 * Delete
	 *
	 * @param string $id ID
	 * @return bool
	 */
	public function delete($id) {

	}

}
