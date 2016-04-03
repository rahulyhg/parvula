<?php

namespace Parvula\Core\Model\Mapper;

use Parvula\Core\Model\User;
use Parvula\Core\ArrayTrait;
use Parvula\Core\FileParser;
use Parvula\Core\Model\Mapper\AbstractDataMapper;

class UsersMongo
{
	/**
	 * @var array Array of User
	 */
	private $parser;

	/**
	 * @var array User[]
	 */
	protected $data;

	/**
	 * @param Collection $collection
	 */
	public function __construct($collection) {
		$this->collection = $collection;
	}

	private function exists($username) {
		if ($this->read($username)) {
			return true;
		}
		return false;
	}




	/**
	 * Index ressources
	 *
	 * @return array List of ressources
	 */
	public function index() {
		return $this->collection->distinct('username');
	}

	/**
	 * Read a user from ID
	 *
	 * @param  string $username
	 * @throws Exception If the ressource does not exists
	 * @return User|bool The user or false if user not found
	 */
	public function read($username) {
		if ($username === null) {
			return false;
		}

		$user = $this->collection->findOne(['username' => $username]);

		if ($user === null) {
			return false;
		}

		return new User(iterator_to_array($user));
	}

	/**
	 * Update @next
	 *
	 * @param string $username
	 * @param mixed $data Data
	 * @return bool
	 */
	public function update($username, $data) {
		return false;
	}

	/**
	 * Create @next
	 *
	 * @param User $user User
	 * @throws
	 * @return bool
	 */
	public function create($user) {
		if (get_class($user) !== 'Parvula\Core\Model\User') {

			#throw ''; # TODO
			return false;
		}

		if (in_array($user->username, $this->collection->distinct('name')) ||
			in_array($user->email, $this->collection->distinct('email'))) {
			return false; # TODO
		}

		return $this->collection->insertOne($user)->getInsertedCount() > 0 ? true : false;
	}

	/**
	 * Delete @next
	 *
	 * @param string $username name
	 * @return bool
	 */
	public function delete($username) {
		if ($username === null) {
			return false;
		}

		if ($this->collection->findOneAndDelete(['username' => $username]) === null) {
			return false;
		}

		return true;
	}

}
