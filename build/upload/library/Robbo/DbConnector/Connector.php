<?php namespace Robbo\DbConnector;

use PDO;

class Connector {

	/**
	 * The default PDO connection options.
	 *
	 * @var array
	 */
	protected $options = array(
			PDO::ATTR_CASE => PDO::CASE_NATURAL,
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
			PDO::ATTR_STRINGIFY_FETCHES => false,
			PDO::ATTR_EMULATE_PREPARES => false,
	);

	/**
	 * Get the PDO options based on the configuration.
	 *
	 * @param  array  $config
	 * @return array
	 */
	public function getOptions(array $config)
	{
		if ( ! isset($config['options']))
		{
			$config['options'] = array();
		}

		return array_diff_key($this->options, $config['options']) + $config['options'];
	}

	/**
	 * Create a new PDO connection.
	 *
	 * @param  string  $dsn
	 * @param  array   $config
	 * @param  array   $options
	 * @return PDO
	 */
	public function createConnection($dsn, array $config, array $options)
	{
		return new PDO($dsn, $config['username'], $config['password'], $options);
	}

	/**
	 * Get the default PDO connection options.
	 *
	 * @return array
	 */
	public function getDefaultOptions()
	{
		return $this->options;
	}

	/**
	 * Set the default PDO connection options.
	 *
	 * @param  array  $options
	 * @return void
	 */
	public function setDefaultOptions(array $options)
	{
		$this->options = $options;
	}

	/**
	 * Create a connector instance based on the configuration.
	 *
	 * @param  array  $config
	 * @param  bool   $connect
	 * @return \Robbo\DbConnector\ConnectorInterface
	 */
	public static function create(array $config, $connect = false)
	{
		if ( ! isset($config['driver']))
		{
			throw new \InvalidArgumentException("A driver must be specified.");
		}

		switch ($config['driver'])
		{
			case 'mysql': 	$connector = new MySqlConnector; 	 break;

			case 'pgsql': 	$connector = new PostgresConnector;  break;

			case 'sqlite': 	$connector = new SQLiteConnector; 	 break;

			case 'sqlsrv': 	$connector = new SqlServerConnector; break;

			default: throw new \InvalidArgumentException("Unsupported driver [{$config['driver']}");
		}

		return $connect ? $connector->connect($config) : $connector;
	}
}