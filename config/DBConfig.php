<?php

require_once 'env.php';

(new DotEnv('../.env'))->load();

class Database {

		private $conn = NULL;
		private $result = NULL;

		public function connect() {
				try {
						$this->conn = new PDO(getenv('DB_DNS'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'), [
								PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
								PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
						]);

						return $this->conn;
				} catch (PDOException $e) {
						echo $e->getMessage();
    		}
		}

		public function update($table, $key, $value, $params) {
				$sql = "UPDATE $table SET ";
				foreach ($params as $paramKey => $paramValue) {
						$sql .= "$paramKey = '$paramValue',";
				}
				$sql = preg_replace('/\,$/', "WHERE $key = '$value'", $sql);

				$query = $this->conn->prepare($sql);
				$query->execute();

				return $query->rowCount();
		}

		public function findBy($table, $key, $value) {
			$sql = "SELECT * FROM $table WHERE $key = '$value' LIMIT 1";
			
			return $this->conn->query($sql)->fetch();
		}
}
