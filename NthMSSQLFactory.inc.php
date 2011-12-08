<?php
	require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'phpconf' . DIRECTORY_SEPARATOR . 'config.inc.php');
	require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'NthMSSQLDatabase.inc.php');
	
	class NthMSSQLFactory extends NthMSSQLDatabase {
		
		private static $masterInstance; //An instance of the master database connection.
		
		private static $slaveInstance;	//An instance of the slave database connection.
		
		/**
		* A singleton method for connecting to a single instance of the master database.
		* You should connect to the master when you need to insert, update, or delete
		* records.
		* 
		*/
		public static function connectMaster() {
			//Reference the $masterdb configuration array from config.inc.php.
			global $masterMSSQLDB;	
			
			//If the instance of the master database is not yet set, create the instance.
			if(!isset(self::$masterInstance)) {
				self::$masterInstance = new NthMSSQLFactory($masterMSSQLDB);
				
				//In the MySQL Factory, this is where we make the client
				//use UTF8 encoding. We might eventually do that in MSSQL,
				//but currently everything is encoded in Latin.
			}
			
			//Return the master database instance.
			return self::$masterInstance;
		} 
		
		/**
		* A singleton method for connecting to a single instance of the slave database.
		* You should connect to the slave when you simply need to read records.
		* Please note that there is no guarantee as to how "fresh" the slave database
		* is. Records may be only a few milliseconds old, or they may be several hours
		* old.
		* 
		*/
		public static function connectSlave() {
			//Reference the $slavedb configuration array from config.inc.php.
			global $masterMSSQLDB;
			
			//If the instance of the slave database is not yet set, create the instance.
			if(!isset(self::$slaveInstance)) {
				self::$slaveInstance = new NthMSSQLFactory($masterMSSQLDB);
				
				//In the MySQL Factory, this is where we make the client
				//use UTF8 encoding. We might eventually do that in MSSQL,
				//but currently everything is encoded in Latin.
			}
			
			//Return the master database instance.
			return self::$slaveInstance;
		} 
	}
?>
