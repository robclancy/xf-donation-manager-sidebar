<?php namespace Robbo\SchemaBuilder;

use PDO;
use Closure;
use DateTime;

class Connection implements ConnectionInterface {

	/**
	 * The active PDO connection.
	 *
	 * @var PDO
	 */
	protected $pdo;

	/**
	 * The schema grammar implementation.
	 *
	 * @var \Illuminate\Database\Schema\Grammars\Grammar
	 */
	protected $schemaGrammar;

	/**
	 * All of the queries run against the connection.
	 *
	 * @var array
	 */
	protected $queryLog = array();

	/**
	 * Indicates whether queries are being logged.
	 *
	 * @var bool
	 */
	protected $loggingQueries = true;

	/**
	 * Indicates if the connection is in a "dry run".
	 *
	 * @var bool
	 */
	protected $pretending = false;

	/**
	 * The name of the connected database.
	 *
	 * @var string
	 */
	protected $database;

	/**
	 * The table prefix for the connection.
	 *
	 * @var string
	 */
	protected $tablePrefix = '';

	/**
	 * The database connection configuration options.
	 *
	 * @var string
	 */
	protected $config = array();

	/**
	 * Create a new database connection instance.
	 *
	 * @param  PDO     $pdo
	 * @param  string  $database
	 * @param  string  $tablePrefix
	 * @param  array   $config
	 * @return void
	 */
	public function __construct(PDO $pdo, $database = '', $tablePrefix = '', array $config = array())
	{
		$this->pdo = $pdo;

		$this->database = $database;

		$this->tablePrefix = $tablePrefix;

		$this->config = $config;
	}

	/**
	 * Set the schema grammar to the default implementation.
	 *
	 * @return void
	 */
	public function useDefaultSchemaGrammar()
	{
		$this->schemaGrammar = $this->getDefaultSchemaGrammar();
	}

	/**
	 * Get the default schema grammar instance.
	 *
	 * @return \Illuminate\Database\Schema\Grammars\Grammar
	 */
	protected function getDefaultSchemaGrammar() 
	{
		return new Grammar;
	}

	/**
	 * Get a schema builder instance for the connection.
	 *
	 * @return \Illuminate\Database\Schema\Builder
	 */
	public function getSchemaBuilder()
	{
		if (is_null($this->schemaGrammar)) { $this->useDefaultSchemaGrammar(); }

		return new Builder($this);
	}

	/**
	 * Execute an SQL statement and return the boolean result.
	 *
	 * @param  string  $query
	 * @return bool
	 */
	public function statement($query)
	{
		return $this->run($query, function($me, $query)
		{
			if ($me->pretending()) return true;

			return $me->getPdo()->prepare($query)->execute();
		});
	}

	/**
	 * Run a raw, unprepared query against the PDO connection.
	 *
	 * @param  string  $query
	 * @return bool
	 */
	public function unprepared($query)
	{
		return $this->run($query, array(), function($me, $query)
		{
			if ($me->pretending()) return true;

			return (bool) $me->getPdo()->exec($query);
		});
	}

	/**
	 * Execute the given callback in "dry run" mode.
	 *
	 * @param  Closure  $callback
	 * @return array
	 */
	public function pretend(Closure $callback)
	{
		$this->pretending = true;

		$this->queryLog = array();

		// Basically to make the database connection "pretend", we will just return
		// the default values for all the query methods, then we will return an
		// array of queries that were "executed" within the Closure callback.
		$callback($this);

		$this->pretending = false;

		return $this->queryLog;
	}

	/**
	 * Run a SQL statement and log its execution context.
	 *
	 * @param  string   $query
	 * @param  Closure  $callback
	 * @return mixed
	 */
	protected function run($query, Closure $callback)
	{
		$start = microtime(true);

		// To execute the statement, we'll simply call the callback, which will actually
		// run the SQL against the PDO connection. Then we can calculate the time it
		// took to execute and log the query SQL and time in our memory.
		try
		{
			$result = $callback($this, $query);
		}

		// If an exception occurs when attempting to run a query, we'll format the error
		// message which will make this exception a lot more helpful to the developer 
		// instead of just the database's errors.
		catch (\Exception $e)
		{
			$this->handleQueryException($e, $query);
		}

		// Once we have run the query we will calculate the time that it took to run and
		// then log the query and execution time so we will report them on
		// the event that the developer needs them. We'll log time in milliseconds.
		$time = $this->getElapsedTime($start);

		$this->logQuery($query, $time);

		return $result;
	}

	/**
	 * Handle an exception that occurred during a query.
	 *
	 * @param  Exception  $e
	 * @param  string     $query
	 * @return void
	 */
	protected function handleQueryException(\Exception $e, $query)
	{
		$message = $e->getMessage()." (SQL: {$query})";

		throw new \Exception($message, 0);	
	}

	/**
	 * Log a query in the connection's query log.
	 *
	 * @param  string  $query
	 * @param  $time
	 * @return void
	 */
	public function logQuery($query, $time = null)
	{
		if ( ! $this->loggingQueries) return;

		$this->queryLog[] = compact('query', 'time');
	}

	/**
	 * Get the elapsed time since a given starting point.
	 *
	 * @param  int    $start
	 * @return float
	 */
	protected function getElapsedTime($start)
	{
		return number_format((microtime(true) - $start) * 1000, 2);
	}

	/**
	 * Get the currently used PDO connection.
	 *
	 * @return PDO
	 */
	public function getPdo()
	{
		return $this->pdo;
	}

	/**
	 * Get the database connection name.
	 *
	 * @return string|null
	 */
	public function getName()
	{
		return $this->getConfig('name');
	}

	/**
	 * Get an option from the configuration options.
	 *
	 * @param  string  $option
	 * @return mixed
	 */
	public function getConfig($option)
	{
		if (isset($this->config[$option]))
		{
			return $this->config[$option];
		}

		return null;
	}

	/**
	 * Get the PDO driver name.
	 *
	 * @return string
	 */
	public function getDriverName()
	{
		return $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
	}

	/**
	 * Get the schema grammar used by the connection.
	 *
	 * @return \Illuminate\Database\Query\Grammars\Grammar
	 */
	public function getSchemaGrammar()
	{
		return $this->schemaGrammar;
	}

	/**
	 * Set the schema grammar used by the connection.
	 *
	 * @param  \Illuminate\Database\Schema\Grammars\Grammar
	 * @return void
	 */
	public function setSchemaGrammar(Grammar $grammar)
	{
		$this->schemaGrammar = $grammar;
	}

	/**
	 * Determine if the connection in a "dry run".
	 *
	 * @return bool
	 */
	public function pretending()
	{
		return $this->pretending === true;
	}

	/**
	 * Get the connection query log.
	 *
	 * @return array
	 */
	public function getQueryLog()
	{
		return $this->queryLog;
	}

	/**
	 * Clear the query log.
	 *
	 * @return void
	 */
	public function flushQueryLog()
	{
		$this->queryLog = array();
	}

	/**
	 * Enable the query log on the connection.
	 *
	 * @return void
	 */
	public function enableQueryLog()
	{
		$this->loggingQueries = true;
	}

	/**
	 * Disable the query log on the connection.
	 *
	 * @return void
	 */
	public function disableQueryLog()
	{
		$this->loggingQueries = false;
	}

	/**
	 * Get the name of the connected database.
	 *
	 * @return string
	 */
	public function getDatabaseName()
	{
		return $this->database;
	}

	/**
	 * Set the name of the connected database.
	 *
	 * @param  string  $database
	 * @return string
	 */
	public function setDatabaseName($database)
	{
		$this->database = $database;
	}

	/**
	 * Get the table prefix for the connection.
	 *
	 * @return string
	 */
	public function getTablePrefix()
	{
		return $this->tablePrefix;
	}

	/**
	 * Set the table prefix in use by the connection.
	 *
	 * @param  string  $prefix
	 * @return void
	 */
	public function setTablePrefix($prefix)
	{
		$this->tablePrefix = $prefix;
	}

	/**
	 * Set the table prefix and return the grammar.
	 *
	 * @param  \Illuminate\Database\Grammar  $grammar
	 * @return \Illuminate\Database\Grammar
	 */
	public function withTablePrefix(Grammar $grammar)
	{
		$grammar->setTablePrefix($this->tablePrefix);

		return $grammar;
	}

	/**
	 * Create a new connection instance.
	 *
	 * @param  string  $driver
	 * @param  PDO     $connection
	 * @param  string  $database
	 * @param  string  $tablePrefix
	 * @param  string  $config
	 * @return \Illuminate\Database\Connection
	 */
	public static function create($driver, PDO $connection, $database, $tablePrefix = '', array $config = array())
	{
		switch ($driver)
		{
			case 'mysql':
				return new Connection\MySqlConnection($connection, $database, $tablePrefix, $config);

			case 'pgsql':
				return new Connection\PostgresConnection($connection, $database, $tablePrefix, $config);

			case 'sqlite':
				return new Connection\SQLiteConnection($connection, $database, $tablePrefix, $config);

			case 'sqlsrv':
				return new Connection\SqlServerConnection($connection, $database, $tablePrefix, $config);
		}

		throw new \InvalidArgumentException("Unsupported driver [$driver]");
	}
}