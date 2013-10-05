<?php
final class mMySQLi {
	private $mysqli;

	public function __construct($hostname, $username, $password, $database) {
		$this->mysqli = new mysqli($hostname, $username, $password, $database);

		if ($this->mysqli->connect_error) {
			trigger_error('Error: Could not make a database link (' . $this->mysqli->connect_errno . ') ' . $this->mysqli->connect_error);
		}

		$this->mysqli->set_charset('utf8');
	}

	public function query($sql) {
		$query = $this->mysqli->query($sql);

		if ($this->mysqli->errno) {
			trigger_error('Error: ' . $this->mysqli->error . '<br />Error No: ' . $this->mysqli->errno . '<br />' . $sql);
			exit();
		}

		if ($query) {
			if (isset($query->num_rows)) {
				$result = new stdClass();
				$data = array();

				while ($row = $query->fetch_assoc()) {
					$data[] = $row;
				}

				$result->row = isset($data[0]) ? $data[0] : array();
				$result->rows = $data;
				$result->num_rows = $query->num_rows;

				$query->close();

				unset($data);

				return $result;
			} else {
				// When MySQLi returns boolean true instead of a result object
				// http://www.php.net/manual/en/mysqli.query.php
				$result = new stdClass();
				$result->row = array();
				$result->rows = array();
				$result->num_rows = 0;

				return $result;
			}
		} else {
			trigger_error('Error: ' . $this->mysqli->error . '<br />Error No: ' . $this->mysqli->errno . '<br />' . $sql);
			exit();
		}
	}

	public function escape($value) {
		return $this->mysqli->real_escape_string($value);
	}

	public function countAffected() {
		return $this->mysqli->affected_rows;
	}

	public function getLastId() {
		return $this->mysqli->insert_id;
	}

	public function __destruct() {
		$this->mysqli->close();
	}
}
?>