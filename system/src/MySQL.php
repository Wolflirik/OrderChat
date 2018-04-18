<?php

namespace LightphotosChat;

final class MySQL {
	private $mysqli_handler;
	
	public function __construct($hostname, $username, $password, $database) {
		$this->mysqli_handler = mysqli_connect($hostname, $username, $password, $database);
		
		if ($this->mysqli_handler->connect_error) {
      		trigger_error('Error: Could not make a database link (' . $this->mysqli_handler->connect_errno . ') ' . $this->mysqli_handler->connect_error);
		}
		
		$this->mysqli_handler->query("SET NAMES 'utf8'");
		$this->mysqli_handler->query("SET CHARACTER SET utf8");
		$this->mysqli_handler->query("SET CHARACTER_SET_CONNECTION=utf8");
		$this->mysqli_handler->query("SET SQL_MODE = ''");
  }
		
  public function query($sql) {
		$result = $this->mysqli_handler->query($sql, MYSQLI_STORE_RESULT);
		if ($result !== FALSE) {
			if (is_object($result)) {
				$i = 0;
    	
				$data = array();
		
				while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
					$data[$i] = $row;
    	
					$i++;
				}

				$result->close();
				
				$query = new \stdClass();
				$query->row = isset($data[0]) ? $data[0] : array();
				$query->rows = $data;
				$query->num_rows = count($data);
				
				unset($data);
				
				
				
				
				return $query;	
    		 
			
			}
			else {
				return true;
			}
		}
		else {
			trigger_error('Error: ' . $this->mysqli_handler->error . '<br />Error No: ' . $this->mysqli_handler->errno . '<br />' . $sql);
			exit();
		}
  }
	
	public function escape($value) {
		return $this->mysqli_handler->real_escape_string($value);
	}
	
	public function countAffected() {
		return $this->mysqli_handler->affected_rows;
	}

	public function getLastId() {
		return $this->mysqli_handler->insert_id;
	}	
	
	public function __destruct() {
		$this->mysqli_handler->close();
	}
}
?>