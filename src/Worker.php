<?php

class Worker {

    private $db;
    private $queueTable;
    private $eventTable;

    /**
     * @throws PDOException
     * @throws Exception
     */
    public function __construct() {
        $this->db = $this->openDbConnection();
        $this->queueTable = $this->getEnv('DB_QUEUE_TABLE');
        $this->eventTable = $this->getEnv('DB_EVENT_TABLE');
        $this->logPath = $this->resolveLogPath();
    }

    private function openDbConnection() {
        $host = $this->getEnv('DB_HOST');
        $name = $this->getEnv('DB_NAME');
        $user = $this->getEnv('DB_USER');
        $pass = $this->getEnv('DB_PASS');

        $driver = "mysql:host=$host;dbname=$name";
        return new PDO($driver, $user, $pass);
    }

    public function doWork() {
        while (true) {
            $work = $this->getWork();
            if (!$work) {
                echo "No work!" . PHP_EOL;
                sleep(1);
            } else {
                $result = $this->execute($work);
                $this->markWorkExecuted($work['id'], $result);
            }
        }
    }

    private function getWork() {
        $sql = "SELECT q.*, e.cmd, e.dataAsCliParams "
             . "FROM {$this->queueTable} as q, {$this->eventTable} as e "
             . "WHERE q.event = e.event "
             . "  AND q.executed = 0 "
             . "ORDER BY q.id ASC "
             . "LIMIT 1 ";
        $query = $this->db->prepare($sql);
        if (!$query->execute()) {
            throw new Exception("Failed to execute select on queue database! " . print_r($query->errorInfo(), TRUE));
        }
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    private function markWorkExecuted($id, $exitCode) {
        $sql = "UPDATE {$this->queueTable} SET executed = :ex, executedAt = :exAt, exitCode = :code WHERE id = :id";
        $data = array(
            'code' => intval($exitCode), 
            'id' => $id,
            'ex' => 1,
            'exAt' => date('Y-m-d H:i:s')
        );
        $query = $this->db->prepare($sql);
        if (!$query->execute($data)) {
            throw new Exception("Failed to execute update on queue database! " . print_r($query->errorInfo(), TRUE));
        }
    }

    private function getEnv($key) {
        $val = getenv($key);
        if ($val === FALSE) throw new Exception("Environment variable $key does not exist");
        return $val;
    }

    private function execute($work) {
        $params = ($work['dataAsCliParams'] == 1) ? $work['data'] : $work['id'];
        $log = $this->logPath . '/' . $work['event'] . '.log';
        $cmd = $work['cmd'] . ' ' . $params;
        $logCmd = ' | tee -a ' . $log . '; ( exit ${PIPESTATUS} )';
        passthru("/bin/bash -c '" . escapeshellcmd($cmd) . $logCmd . "'", $exitCode);
        return $exitCode;
    }

    private function resolveLogPath() {
        $path = $this->getenv('LOG_DIR');
        if (substr($path, 0, 1) === '/') {
            return $path;
        } else {
            return realpath(__DIR__ . '/../' . $path);
        }
    }

}