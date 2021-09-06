<?php
require_once '../helpers/string.php';

abstract class BaseModel {
	protected static $sqlite;
	protected $table;
	protected $fields = [];
	protected $values = [];

	public function __construct($values=[])
	{
		$this->fill($values);
	}

	public function __get($field)
	{
		return $this->values[$field] ?? null;
	}

	public function __set($field, $value)
	{
		return $this->values[$field] = $value;
	}

	// create/retrieve singleton database instance
	private static function dbinst()
	{
		if (!self::$sqlite) {
			self::$sqlite = new SQLite3(__DIR__ . '/.source.db');
			self::$sqlite->busyTimeout(5000);
		}
		return self::$sqlite;
	}

	// get the table name based on the model's class name (class name camel case into table name snake case)
	public function getTable()
	{
		if ($this->table === null) {
			$this->table = camel2snake(get_class($this));
		}
		return $this->table;
	}

	// prepare and execute queries
	public static function exec($query, $bindings=[])
	{
		$stmt = self::dbinst()->prepare($query);
		foreach($bindings as $k=>$v) {
			$stmt->bindValue($k, $v);
		}
		return $stmt->execute();
	}

	// execute query and map it into list of models
	public static function query($query, $bindings=[])
	{
		$model = get_called_class();
		$rows = self::exec($query, $bindings);

		if (!$rows) {
			return false;
		}

		$results = [];
		while ($row = $rows->fetchArray(SQLITE3_ASSOC)) {
			$inst = new $model;
			$inst->fill($row);
			$results[] = $inst;
		}
	
		return $results;
	}

	// delete table
	public static function dropTable()
	{
		$model = get_called_class();
		self::exec('DROP TABLE ' . (new $model)->getTable());
	}

	// create table based on the model properties
	public static function createTable()
	{
		$model = get_called_class();
		$model = new $model;

		$fields = array_merge(['id' => 'INTEGER PRIMARY KEY AUTOINCREMENT'], $model->fields);
		$fields = join(
			array_map(
				function($k) use ($fields) { return $k . ' ' . $fields[$k]; }, 
				array_keys($fields)
			),
			', '
		);
		$query = 'CREATE TABLE ' . $model->getTable() . ' (' . $fields . ')';
		self::exec($query);
	}

	// retrieve all rows of this model's table
	public static function all()
	{
		$model = get_called_class();
		$query = 'SELECT * FROM ' . (new $model)->getTable();
		return self::query($query);
	}

	// update or insert, depending if id is already existing
	public static function upsert($values)
	{
		$model = get_called_class();
		$model = new $model;
		return $model->fill($values)->save();
	}

	// insert new row
	public static function create($values)
	{
		$values['id'] = null;
		self::upsert($values);
	}

	// fills a models properties based on the passed arguments
	public function fill($values)
	{
		$this->values = array_merge($this->values, $values);
		return $this;
	}

	// update the models properties based on the passed arguments
	public function update($values)
	{
		unset($values['id']);
		return $this->fill($values)->save();
	}

	// saves the models properties into the database
	public function save()
	{
		$table = $this->getTable();
		$fields = array_keys($this->values);
		$isNew = ($this->values['id'] ?? null) === null;
		if ($isNew) {
			$query = 
				'INSERT INTO ' . $table . 
				'(' . join($fields, ', ') . ') '.
				'VALUES'.
				'(' . join(array_map(function($i){ return ':x' . $i;}, array_keys($fields)), ', ') . ')';
		} else {
			$query = 
				'UPDATE ' . $table . 
				' SET ' . join(array_map(function($i)use($fields){return $fields[$i] . '=:x' . $i;}, array_keys($fields)), ', ') .
				' WHERE id=' . $this->values['id'];
		}
		$bindings = [];
		foreach (array_keys($fields) as $i) {
			$bindings[':x' . $i] = $this->values[$fields[$i]];
		}
		self::exec($query, $bindings);

		// if new entry is created, retrieve the last autoincrement id
		if ($isNew) {
			$this->id = self::dbinst()->lastInsertRowID();
		}
		return $this;
	}

	// retrieve the properties as an associative array
	public function toArray()
	{
		return $this->values;
	}

	// retrieve row information by id, and return the model
	public static function find($id)
	{
		$model = get_called_class();
		$query = 'SELECT * FROM ' . (new $model)->getTable() . ' WHERE id=:id';
		return self::query($query, [':id'=>$id])[0] ?? null;
	}

	// delete the model by its id value
	public function delete()
	{
		$query = 'DELETE FROM ' . $this->getTable() . ' WHERE id=:id';
		return self::exec($query, [':id'=>$this->id]);
	}
}
