<?php

    class DB
    {

        protected $db;
        private $sql = '';
        private $sqls = [];
        private $params = array();
        private $stmt;
        public $error;

        public function __construct($host, $dbname, $user, $passw, $charset = 'utf8')
        {
            try {
                $this -> db = new PDO('mysql:host=' . $host . ';dbname=' . $dbname . ';charset=' . $charset, $user, $passw);
            } catch(PDOException $t) {
                $this -> showErrors($t -> errorInfo);
            }
        }

        public function select($column = '*')
        {
            $this -> sql = 'SELECT ' . $column;

            return $this;
        }

        public function from($table)
        {
            if ($this -> sql === '') $this -> select('*');

            $this -> sql .= ' FROM ' . $table; 

            return $this;
        }

        public function join($table, $joinStr, $type = 'INNER')
        {
            switch(strtolower($type)) {
                case 'inner':
                    $type = 'INNER';
                    break;
                case 'left':
                    $type = 'LEFT';
                    break;
                case 'right':
                    $type = 'RIGHT';
                    break;
                default: 
                    $type = 'INNER';
            };

            $this -> sql .= ' ' . $type . ' JOIN ' . $table . ' ON ' . $joinStr;

            return $this;
        }

        public function delete($table)
        {
            $this -> sql = 'DELETE FROM ' . $table;

            return $this;
        }

        public function update($table, $data = array())
        {
            $this -> sql = 'UPDATE ' . $table . ' SET ';

            foreach ($data as $key => $param) {
                $this -> sql .= $key . ' = :' . $key . ',';

                array_push($this -> params, [
                    'column' => $key,
                    'match' => $param,
                ]);
            }

            $this -> deleteLastComma($this -> sql);

            return $this;
        }

        public function insert($table, $data = array())
        {
            $this -> sql = 'INSERT INTO ' . $table . ' SET ';

            foreach ($data as $key => $param) {
                $this -> sql .= $key . ' = :' . $key . ',';

                array_push($this -> params, [
                    'column' => $key,
                    'match' => $param,
                ]);
            }

            $this -> deleteLastComma($this -> sql);

            return $this;
        }

        public function where($column, $match, $mark = '=')
        {
            if (!strpos(strtoupper($this -> sql), 'WHERE')) $this -> sql .= ' WHERE'; 

            $this -> sql .= ' ' . $column . ' = :' . $column;

            array_push($this -> params, [
                'column' => $column,
                'match' => $match,
                'mark' => $mark
            ]);

            return $this;
        }

        private function deleteLastComma($str)
        {
            if (substr($this -> sql, strlen($this -> sql) - 1) === ',') $this -> sql = substr($this -> sql, 0, -1);
        }

        private function reset()
        {
            $this -> params = [];
            $this -> sql = '';
        }

        public function and($column, $match, $mark = '=')
        {
            $this -> sql .= ' AND ' . $column . ' = :' . $column;

            array_push($this -> params, [
                'column' => $column,
                'match' => $match,
                'mark' => $mark
            ]);

            return $this;
        }

        public function or($column, $match, $mark = '=')
        {
            $this -> sql .= ' OR ' . $column . ' ' . $mark . ' :' . $column;

            array_push($this -> params, [
                'column' => $column,
                'match' => $match,
                'mark' => $mark
            ]);

            return $this;
        }

        public function get()
        {
            if ($this -> run()) return $this -> stmt -> fetch(PDO::FETCH_OBJ);
        }
        
        public function getAll()
        {
            if ($this -> run()) return $this -> stmt -> fetchAll(PDO::FETCH_OBJ);
        }

        public function paramType($param)
        {
            switch(gettype($param)) {
                case 'integer':
                    return PDO::PARAM_INT;
                case 'string':
                    return PDO::PARAM_STR;
                case 'boolean':
                    return PDO::PARAM_BOOL;
            };
        }

        public function run()
        {
            $this -> stmt = $this -> db -> prepare($this -> sql);

            $this -> sqls[] = $this -> sql;

            if (!$this -> stmt) $this -> showErrors($this -> db -> errorInfo());

            else {

                foreach ($this -> params as $param) {
                    $this -> stmt -> bindParam(':' . $param['column'], $param['match'], $this -> paramType($param['match']));
                }

                $this -> reset();

                $this -> stmt -> execute();

                if ($this -> stmt -> rowCount() > 0) return true;

                return false;

            }

            $this -> reset();

            return false;
        }

        public function getStmts()
        {
            return $this -> sqls;
        }

        public function showErrors($error)
        {
            $code = $error[1];
            $fail = $error[2];

            $html = '<div style="background: #AA4A44; color: #fff; font-size: 20px;padding: 20px; font-family="Arial"">Hata Kodu : <b>' . $code . '</b>, <br>Hata : ' . $fail . '</div>';

            echo $html;
            exit;
        }

    }