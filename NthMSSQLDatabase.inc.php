<?php

    require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'NthMSSQLResultSet.inc.php');
    
    class NthMSSQLConnectException extends Exception {
    	public function errorMessage() {
    		$e = "There was an error connecting to the database: " . $this->getMessage();
    		return $e;
		}	
	}

	class NthMSSQLDatabase {
		
		protected $databaseErrors;		//An array of database errors since this connection was opened.
										//array( 0 => 'databaseError', ...)
										
		protected $link;				//A connection link to the MSSQL server.

		/**
		* Takes a dbConnectionArray and connects to an MySQL database.
		* 
		* dbConnectionArray = array(
		* 	"host" => "localhost",
		* 	"username" => "someuser",
		* 	"password" => "somepassword",
		* 	"database" => "somedatabasename"
		* );
		* 
		* @param mixed $dbConnectionArray
		* @return NthMSSQLDatabase
		*/
		public function __construct($dbConnectionArray) {
			
			if ($this->link = mssql_connect($dbConnectionArray["host"], $dbConnectionArray["username"], $dbConnectionArray["password"], true)) {
				//Select the specified database.
				mssql_select_db($dbConnectionArray["database"], $this->link);
			} else {
				//Otherwise, there was an error. Throw an exception with the error message.
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
		*/
		public function databaseErrors() {
			return $this->databaseErrors;	
		}
		
		/**
		* How many database errors were logged?
		* 
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
			//If the query failed, log the error.
            if (($result=mssql_query($sqlQuery, $this->link))===false) {
            	$this->addDatabaseError($sqlQuery, mssql_get_last_message());
            	return false;	
			} else {
				return new NthMSSQLResultSet($result, $sqlQuery);	
			}
        }

        /**
        * Executes a SQL query that will produce no output.
        * 
        * @param string $sqlQuery
        * @return mixed
        */
        public function exec($sqlQuery) {
            //If the query failed, log the error.
            if (mssql_query($sqlQuery, $this->link)===false) {
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
        */
		public function lastInsertId() {
            $lastInsertId = 0;
            $q = "SELECT @@IDENTITY as lastInsertId;";
            if($r = $this->query($q)) { $lastInsertId = $r->firstValue(); }
            return $lastInsertId;
        }

	}

?>
