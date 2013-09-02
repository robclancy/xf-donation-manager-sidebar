<?php namespace Robbo\SchemaBuilder;

abstract class Grammar {

	/**
	 * The keyword identifier wrapper format.
	 *
	 * @var string
	 */
	protected $wrapper = '"%s"';

	/**
	 * The grammar table prefix.
	 *
	 * @var string
	 */
	protected $tablePrefix = '';

	/**
	 * Wrap an array of values.
	 *
	 * @param  array  $values
	 * @return array
	 */
	public function wrapArray(array $values)
	{
		return array_map(array($this, 'wrap'), $values);
	}

	/**
	 * Wrap a table in keyword identifiers.
	 *
	 * @param  string  $table
	 * @return string
	 */
	public function wrapTable($table)
	{
		if ($table instanceof Blueprint) $table = $table->getTable();

		if ($this->isExpression($table)) return $this->getValue($table);

		return $this->wrap($this->tablePrefix.$table);
	}

	/**
	 * Wrap a value in keyword identifiers.
	 *
	 * @param  string  $value
	 * @return string
	 */
	public function wrap($value)
	{
		if ($value instanceof Fluent) $value = $value->name;

		if ($this->isExpression($value)) return $this->getValue($value);

		// If the value being wrapped has a column alias we will need to separate out
		// the pieces so we can wrap each of the segments of the expression on it
		// own, and then joins them both back together with the "as" connector.
		if (strpos(strtolower($value), ' as ') !== false)
		{
			$segments = explode(' ', $value);

			return $this->wrap($segments[0]).' as '.$this->wrap($segments[2]);
		}

		$wrapped = array();

		$segments = explode('.', $value);

		// If the value is not an aliased table expression, we'll just wrap it like
		// normal, so if there is more than one segment, we will wrap the first
		// segments as if it was a table and the rest as just regular values.
		foreach ($segments as $key => $segment)
		{
			if ($key == 0 and count($segments) > 1)
			{
				$wrapped[] = $this->wrapTable($segment);
			}
			else
			{
				$wrapped[] = $this->wrapValue($segment);
			}
		}

		return implode('.', $wrapped);
	}

	/**
	 * Wrap a single string in keyword identifiers.
	 *
	 * @param  string  $value
	 * @return string
	 */
	protected function wrapValue($value)
	{
		return $value !== '*' ? sprintf($this->wrapper, $value) : $value;
	}

	/**
	 * Convert an array of column names into a delimited string.
	 *
	 * @param  array   $columns
	 * @return string
	 */
	public function columnize(array $columns)
	{
		return implode(', ', array_map(array($this, 'wrap'), $columns));
	}

	/**
	 * Create query parameter place-holders for an array.
	 *
	 * @param  array   $values
	 * @return string
	 */
	public function parameterize(array $values)
	{
		return implode(', ', array_map(array($this, 'parameter'), $values));
	}

	/**
	 * Get the appropriate query parameter place-holder for a value.
	 *
	 * @param  mixed   $value
	 * @return string
	 */
	public function parameter($value)
	{
		return $this->isExpression($value) ? $this->getValue($value) : '?';
	}

	/**
	 * Get the value of a raw expression.
	 *
	 * @param  \Illuminate\Database\Query\Expression  $expression
	 * @return string
	 */
	public function getValue($expression)
	{
		return $expression->getValue();
	}

	/**
	 * Determine if the given value is a raw expression.
	 *
	 * @param  mixed  $value
	 * @return bool
	 */
	public function isExpression($value)
	{
		return $value instanceof Expression;
	}

	/**
	 * Get the format for database stored dates.
	 *
	 * @return string
	 */
	public function getDateFormat()
	{
		return 'Y-m-d H:i:s';
	}

	/**
	 * Get the grammar's table prefix.
	 *
	 * @return string
	 */
	public function getTablePrefix()
	{
		return $this->tablePrefix;
	}

	/**
	 * Set the grammar's table prefix.
	 *
	 * @param  string  $prefix
	 * @return \Illuminate\Database\Grammar
	 */
	public function setTablePrefix($prefix)
	{
		$this->tablePrefix = $prefix;

		return $this;
	}

	/**
	 * Compile a foreign key command.
	 *
	 * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
	 * @param  \Illuminate\Support\Fluent  $command
	 * @return string
	 */
	public function compileForeign(Blueprint $blueprint, Fluent $command)
	{
		$table = $this->wrapTable($blueprint);

		$on = $this->wrapTable($command->on);

		// We need to prepare several of the elements of the foreign key definition
		// before we can create the SQL, such as wrapping the tables and convert
		// an array of columns to comma-delimited strings for the SQL queries.
		$columns = $this->columnize($command->columns);

		$onColumns = $this->columnize((array) $command->references);

		$sql = "alter table {$table} add constraint {$command->index} ";

		$sql .= "foreign key ({$columns}) references {$on} ({$onColumns})";

		// Once we have the basic foreign key creation statement constructed we can
		// build out the syntax for what should happen on an update or delete of
		// the affected columns, which will get something like "cascade", etc.
		if ( ! is_null($command->onDelete))
		{
			$sql .= " on delete {$command->onDelete}";
		}

		if ( ! is_null($command->onUpdate))
		{
			$sql .= " on update {$command->onUpdate}";
		}

		return $sql;
	}

	/**
	 * Compile the blueprint's column definitions.
	 *
	 * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
	 * @return array
	 */
	protected function getColumns(Blueprint $blueprint)
	{
		$columns = array();

		foreach ($blueprint->getColumns() as $column)
		{
			// Each of the column types have their own compiler functions which are
			// responsible for turning the column definition into its SQL format
			// for the platform. Then column modifiers are compiled and added.
			$sql = $this->wrap($column).' '.$this->getType($column);

			$columns[] = $this->addModifiers($sql, $blueprint, $column);
		}

		return $columns;
	}

	/**
	 * Add the column modifiers to the definition.
	 *
	 * @param  string  $sql
	 * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
	 * @param  \Illuminate\Support\Fluent  $column
	 * @return string
	 */
	protected function addModifiers($sql, Blueprint $blueprint, Fluent $column)
	{
		foreach ($this->modifiers as $modifier)
		{
			if (method_exists($this, $method = "modify{$modifier}"))
			{
				$sql .= $this->{$method}($blueprint, $column);
			}
		}

		return $sql;
	}

	/**
	 * Get the primary key command if it exists on the blueprint.
	 *
	 * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
	 * @return \Illuminate\Support\Fluent|null
	 */
	protected function getCommandByName(Blueprint $blueprint, $name)
	{
		$commands = $this->getCommandsByName($blueprint, $name);

		if (count($commands) > 0)
		{
			return reset($commands);
		}
	}

	/**
	 * Get all of the commands with a given name.
	 *
	 * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
	 * @param  string  $name
	 * @return array
	 */
	protected function getCommandsByName(Blueprint $blueprint, $name)
	{
		return array_filter($blueprint->getCommands(), function($value) use ($name)
		{
			return $value->name == $name;
		});
	}

	/**
	 * Get the SQL for the column data type.
	 *
	 * @param  \Illuminate\Support\Fluent  $column
	 * @return string
	 */
	protected function getType(Fluent $column)
	{
		return $this->{"type".ucfirst($column->type)}($column);
	}

	/**
	 * Add a prefix to an array of values.
	 *
	 * @param  string  $prefix
	 * @param  array   $values
	 * @return array
	 */
	public function prefixArray($prefix, array $values)
	{
		return array_map(function($value) use ($prefix)
		{
			return $prefix.' '.$value;

		}, $values);
	}

	/**
	 * Format a value so that it can be used in "default" clauses.
	 *
	 * @param  mixed   $value
	 * @return string
	 */
	protected function getDefaultValue($value)
	{
		if ($value instanceof Expression) return $value;

		if (is_bool($value)) return "'".intval($value)."'";

		return "'".strval($value)."'";
	}
}