<?php namespace Robbo\SchemaBuilder; 

use Closure;

interface ConnectionInterface {

	/**
	 * Execute an SQL statement and return the boolean result.
	 *
	 * @param  string  $query
	 * @return bool
	 */
	public function statement($query);

}