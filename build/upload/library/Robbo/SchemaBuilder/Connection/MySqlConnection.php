<?php namespace Robbo\SchemaBuilder\Connection;

use Robbo\SchemaBuilder\Connection;
use Robbo\SchemaBuilder\MySqlBuilder;
use Robbo\SchemaBuilder\Grammar\MySqlGrammar;

class MySqlConnection extends Connection {

	/**
	 * Get a schema builder instance for the connection.
	 *
	 * @return \Illuminate\Database\Schema\Builder
	 */
	public function getSchemaBuilder()
	{
		if (is_null($this->schemaGrammar)) { $this->useDefaultSchemaGrammar(); }

		return new MySqlBuilder($this);
	}

	/**
	 * Get the default schema grammar instance.
	 *
	 * @return \Illuminate\Database\Schema\Grammars\Grammar
	 */
	protected function getDefaultSchemaGrammar()
	{
		return $this->withTablePrefix(new MySqlGrammar);
	}

}