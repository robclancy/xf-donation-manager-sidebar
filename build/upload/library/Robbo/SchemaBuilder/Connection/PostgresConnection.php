<?php namespace Robbo\SchemaBuilder\Connection;

use Robbo\SchemaBuilder\Connection;
use Robbo\SchemaBuilder\Grammar\PostgresGrammar;

class PostgresConnection extends Connection {

	/**
	 * Get the default schema grammar instance.
	 *
	 * @return \Illuminate\Database\Schema\Grammars\Grammar
	 */
	protected function getDefaultSchemaGrammar()
	{
		return $this->withTablePrefix(new PostgresGrammar);
	}

}