<?php
    
    /**
     * @package NthMSSQLDatabase
     */
    
    require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'NthMSSQLResultSet.inc.php');

    class NthMSSQLConnectException extends Exception {
        public function errorMessage() {
            $e = 'There was an error connecting to the database: ' . $this->getMessage();
            return $e;
        }   
    }

    class NthMSSQLDatabase {
        
        /**
         * An array of database errors since this connection was opened.
         * array( 0 => 'databaseError', ...)
         * @var array
         */
        protected $databaseErrors;
        
        /**
         * A connection link to the MSSQL server.
         * @var object
         */
        protected $link;


        /**
         * Creates and returns a new connection to a MSSQL database and returns
         * a new NthMSSQLDatabase
         *
         * @param string $servername
         * @param string $username
         * @param string $password
         * @param bool $new_link
         * @param string $database
         * @return NthMSSQLDatabase
         */
        public function __construct($servername, $username=null, $password=null,
                $new_link=null, $database=null) {
            
            if ($this->link = mssql_connect($servername, $username, $password,
                     $new_link)) {
                // Select the specified database.
                mssql_select_db($database, $this->link);
            } else {
                // Otherwise, there was an error.
                // Throw an exception with the error message.
                throw new NthMSSQLConnectException(mssql_get_last_message());
            }
            
            $this->databaseErrors = array();
        }
        
        /**
         * Add an error message to the array of database errors.
         * 
         * @param string $sqlQuery The SQL query that resulted in an error.
         * @param string $errorMessage The resulting error message.
         */
        protected function addDatabaseError($sqlQuery, $errorMessage) {
            $this->databaseErrors[] = $errorMessage;
        }
        
        /**
         * Return the array of database error messages.
         * 
         * @return array
         */
        public function databaseErrors() {
            return $this->databaseErrors;   
        }
        
        /**
         * How many database errors were logged?
         * 
         * @return int
         */
        public function numDatabaseErrors() {
            return count($this->databaseErrors);    
        }

        /**
         * Performs the SQL query and returns an NthMSSQLResultSet.
         *
         * @param string $sqlQuery
         * @return NthMSSQLResultSet
         */
        public function query($sqlQuery) {
            if (($result = mssql_query($sqlQuery, $this->link)) === false) {
                // If the query failed, log the error.
                $this->addDatabaseError($sqlQuery, mssql_get_last_message());
                return false;   
            } else {
                return $this->makeResultSet($result, $sqlQuery);   
            }
        }

        /**
         * Executes a SQL query that will produce no output.
         * 
         * @param string $sqlQuery
         * @return mixed
         */
        public function exec($sqlQuery) {
            if (mssql_query($sqlQuery, $this->link) === false) {
                // If the query failed, log the error.
                $this->addDatabaseError($sqlQuery, mssql_get_last_message());
                return false;   
            } else {
                return true;    
            }
        }

        /**
         * Returns the value of the autoincrement column last affected by
         * an INSERT statement.
         * 
         * @return int
         */
        public function lastInsertId() {
            
            $lastInsertId = 0;
            $q = 'SELECT @@IDENTITY as lastInsertId;';
            
            if ($r = $this->query($q)) {
                $lastInsertId = (int) $r->fetchFirstValue();
            }
            
            return $lastInsertId;
            
        }

        /**
         * Return a wrapped instance of the result.
         *
         * @param object $result  An MSSQL_Result
         * @param string $sqlQuery  The query used to obtain $result
         * @return mixed  The wrapped result
         */ 
        protected function makeResultSet($result, $sqlQuery) {
            return new NthMSSQLResultSet($result, $sqlQuery);	
        }

    }

?>
