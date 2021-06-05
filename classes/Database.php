<?

class Database
{
	protected static $connect;
	
	public function __construct()
	{	
		if (isset(self::$connect))
		{
			return self::$connect;
		}
		
		$connect = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD);
		
		if ($connect === false)
		{
			throw new DBConnectExc();
			return false;
		}
		
		if (mysqli_select_db($connect, DB_DATABASE_NAME) === false)
		{
			throw new DBSelectDBExc();
			return false;
		}
		
		mysqli_set_charset($connect, 'utf8');
		self::$connect = $connect;
		
		return self::$connect;
	}
	
	public function escape($value) {
		$value = mysqli_real_escape_string(self::$connect, trim($value));
		return $value;
	}

	public function last_id() {
		return mysqli_insert_id(self::$connect);
	}
	
	public function query($query)
	{
		$numArgs = func_num_args();
		
		if ($numArgs > 1)
		{
			$query = preg_replace(array('/\%s/','/\?/'), array('%%s', '%s'), $query);
			$args = func_get_args();
			unset($args[0]);
			for ($i = 1; $i < $numArgs; $i++)
			{
				$args[$i] = mysqli_real_escape_string( self::$connect, stripslashes($args[$i]));
			}
			$query = vsprintf($query, $args);
		}

		// SQL QUERY LOG
		// file_put_contents(__DIR__.'/../query_log.log', date('H:i:s d.m.Y')."\t".$query.PHP_EOL, FILE_APPEND);
		
		$result = mysqli_query(self::$connect, $query);

		return $result;
	}
	
	public function getOne($query)
	{
		$args = func_get_args();
		
		$result = call_user_func_array(array('Database', 'query'), $args);
		$result = mysqli_fetch_assoc($result);
		
		return $result;
	}

	public function getNum($query)
	{
		$result = mysqli_query(self::$connect, $query);
		$result = mysqli_num_rows($result);
		
		return $result;
	}

	public function getAll($query)
	{
		$args = func_get_args();
		$result = call_user_func_array(array('Database', 'query'), $args);
		if ($result) {
			while ($row = mysqli_fetch_assoc($result))
			{
				$return[] = $row;
			}
		}
		
		return isset($return) ? $return : array();
	}

	
	public function getColumn($query)
	{
		$args = func_get_args();
		
		$result = call_user_func_array(array('Database', 'query'), $args);
		
		
		while ($row = mysqli_fetch_row($result))
		{
			$return[] = $row[0];
		}
		return isset($return) ? $return : array();
	}
	public function getError()
	{
		return mysqli_error(self::$connect);
	}
}