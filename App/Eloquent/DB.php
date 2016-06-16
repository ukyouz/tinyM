<?php

namespace App\Eloquent;

Class DB
{
	// The database connection
    protected static $connection;

    // mysql query
    private static $select = "*";
    private static $table;
    private static $where;
    private static $orderBy;
    private static $offset;
    private static $take;

    /**
     * Connect to the database
     * 
     * @return bool false on failure / mysqli MySQLi object instance on success
     */
    public static function connect() {    
        // Try and connect to the database
        if(!isset(self::$connection)) {
            self::$connection = mysqli_connect('localhost', 'root', '', 'apply_system');
            mysqli_query(self::$connection, "SET NAMES 'utf8'");
        }

        // If connection was not successful, handle the error
        if(self::$connection === false) {
            // Handle error - notify administrator, log to a file, show an error screen, etc.
            return false;
        }
        return self::$connection;
    }

    private static function initQuery() {
        static::$where = null;
        static::$orderBy = null;
        static::$offset = 0;
        static::$take = null;
    }

    /**
     * Set the using table name in database
     *
     * @param String $table The query string
     */
    public static function table($table) {
        self::initQuery();
        static::$table = self::quote($table);
        return new self;
    }

    /**
     * Set the using table name in database
     *
     * @param String $table The query string
     * @return mixed The result of the mysqli::query() function
     */
    public static function select() {
        $args = array_map(function($value){
            return self::quote($value);
        }, func_get_args());

        static::$select = join(", ", $args);
        return new self;
        // print_r($args);
    }

    public static function where() {
        $args = func_get_args();
        switch (count($args)) {
            case 1:
                # code...
                break;
            case 2:
                static::$where = static::quote($args[0]) . "=" . static::quote($args[1], "'");
                break;
            case 3:
                static::$where = static::quote($args[0]) . $args[1] . static::quote($args[2], "'");
                break;
        }
        // print_r(static::$where);
        return new self;
        // print_r($args);
    }

    public static function andWhere() {
        $args = func_get_args();
        switch (count($args)) {
            case 2:
                $where .= " AND ";
                static::$where .= static::quote($args[0]) . "=" . static::quote($args[1], "'");
                break;
            case 3:
                $where .= " AND ";
                static::$where .= static::quote($args[0]) . $args[1] . static::quote($args[2], "'");
                break;
        }
        // print_r(static::$where);
        return new self;
    }

    public function orderBy($field, $sort = 'desc') {
        $sort = strtolower($sort);
        if ($sort!='desc' or $sort!='asc')
            $sort = "";

        static::$orderBy = self::quote($field). " ". $sort;
        return new self;
    }

    public function take($num) {
        static::$take = (int)$num;
        return new self;
    }

    public function skip($num) {
        static::$offset = (int)$num;
        return new self;
    }

    public static function get() {
        // Connect to the database
        $connection = self::connect();

        $query = "SELECT " . static::$select . " FROM " . static::$table;
        if(static::$where != null)
            $query.= " WHERE ". static::$where;
        if(static::$orderBy != null)
            $query.= " ORDER BY ". static::$orderBy;
        if(static::$take != null)
            $query.= " LIMIT ". static::$take;
        if(static::$offset != null)
            $query.= " OFFSET ". static::$offset;
        // $result = self::query($query);

        $result = $connection->query($query);

        if ($result === false) {
            // self::error();
            return false;
        }

        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        return $rows;
    }

    public static function insert($dataArr) {
        $connection = self::connect();

        $cols = [];
        $data = [];
        foreach ($dataArr as $key => $value) {
            $cols[] = self::quote($key);
            $data[] = self::quote($value, "'");
        }

        $query = "INSERT INTO ". static::$table. " (".join(',', $cols).") VALUE (".join(',', $data).")";
        // echo $query."\n";
        $result = $connection->query($query);

        if($result === false) {
            // self::error();
            return false;
        }

        return $connection->insert_id;
    }

    public static function update($dataArr) {
        $connection = self::connect();

        $sets = [];
        foreach ($dataArr as $k => $v) {
            $sets[] = $k."=".self::quote($v, "'");
        }

        $query = "UPDATE ". static::$table. " SET ". join(",", $sets). " WHERE ". static::$where;

        $result = $connection->query($query);

        if($result === false) {
            // $ self::error();
            return false;
        }

        return true;
    }

    public static function delete() {
        $connection = self::connect();

        $query = "DELETE FROM ". static::$table;
        if(static::$where != null)
            $query.= " WHERE ". static::$where;
        if(static::$orderBy != null)
            $query.= " ORDER BY ". static::$orderBy;
        if(static::$take != null)
            $query.= " LIMIT ". static::$take;

        $result = $connection->query($query);

        if($result === false)
            return false;

        return true;
    }

    /**
     * Fetch the last error from the database
     * 
     * @return string Database error message
     */
    public static function error() {
        $connection = static::connect();
        return mysqli_error($connection);
    }

    /**
     * Quote and escape value for use in a database query
     *
     * @param string $value The value to be quoted and escaped
     * @return string The quoted and escaped string
     */
    public static function quote($value, $quote = null) {
        $connection = self::connect();
        if ($quote != null)
            return $quote . mysqli_real_escape_string($connection, $value) . $quote;

        return "`" . mysqli_real_escape_string($connection, $value) . "`";
    }
}