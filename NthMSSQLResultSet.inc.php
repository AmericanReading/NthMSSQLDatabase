<?php

    class NthMSSQLResultSet implements Iterator {

        private $result;		//Stores the MSSQL result resource.
        private $position;		//The currently selected row in the result set.
        private $numResults;	//How many results were returned?
        private $checkSum;		//An MD5 checksum of the SQL query used to generate this result set.

        /**
        * Instantiate a new NthMSSQLResultSet given an MSSQL result resource.
        * 
        * @param mixed $result The result resource from MSSQL
        * @param string $sqlQuery The SQL query used to generate the result set.
        * @return NthMSSQLResultSet
        */
        public function __construct(&$result, $sqlQuery) {
            $this->result =& $result;
            $this->numResults = mssql_num_rows($this->result);
            $this->position = 0;
            $this->checkSum = md5($sqlQuery);
        }

        /**
        * When the NthMSSQLResultSet object is destroyed, make sure to free the result resource.
        * 
        */
        public function __destruct() {
            if(is_resource($this->result)) {
                mssql_free_result($this->result);
            }
        }

        /**
        * The current result position.
        * Supports the Iterator interface.
        * 
        */
        public function key() {
            return $this->position;
        }

        /**
        * The row stored at the current position, expressed as an associative array.
        * Supports the Iterator interface.
        * 
        */
        public function current() {
            mssql_data_seek($this->result, $this->position);
            return mssql_fetch_assoc($this->result);
        }

        /**
        * Checks if the current position is valid.
        * Supports the Iterator interface.
        * 
        */
        public function valid() {
            return ($this->position < $this->numResults);
        }

        /**
        * Increments the current position.
        * Supports the Iterator interface.
        * 
        */
        public function next() {
            ++$this->position;
        }

        /**
        * Moves the position back to the start of the record set.
        * Supports the Iterator interface.
        * 
        */
        public function rewind() {
            $this->position = 0;
        }

        /**
        * Returns the number of records in this result set.
        * 
        */
        public function numResults() {
            return $this->numResults;
        }

        /**
        * Returns the value of the first field of the first row of this record set.
        * 
        */
        public function firstValue() {
            return mssql_result($this->result, 0, 0);
        }

        /**
        * Returns the entire record set as an associative array.
        * This can consume a lot of memory if the record set is large.
        * 
        */
        public function assocArray() {
            //Rewind to the beginning of the result set.
            mssql_data_seek($this->result, 0);
            
            $a = array();
            
            while($r = mssql_fetch_assoc($this->result)) { $a[] = $r; }
            
            return $a;
        }

        /**
        * Return the first row of the result set as an associative array.
        *
        */
        public function assocArraySingle() {
            //Rewind to the beginning of the result set.
            mssql_data_seek($this->result, 0);
            
            $a = array();
            
            $r = mssql_fetch_assoc($this->result);
            
            return $r;
        }

    }

?>
