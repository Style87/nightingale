<?php

interface NIGHTINGALE_Adapter_Interface
{

    /**
     * Connects to the database
     * @throws Nightingale_Exception
     */
    public function connect(
        $host = false, $port = false, $username = false, $password = false, $database_name = false
    );

    /**
     * Runs an SQL query
     * @throws Nightingale_Exception
     */
    public function query($sql);

    /**
     * Must return an array() that contains all the schema object names in the database
     * @example return array('articles', 'comments', 'posts')
     * @throws Nightingale_Exception
     * @return array()
     */
    public function getSchema();

    /**
     * Given a schema object name, returns the SQL query that will create
     * that schema object on any machine running the DBMS of choice.
     * @example CREATE TABLE / CREATE PROCEDURE queries in MySQL
     * @throws Nightingale_Exception
     */
    public function getSchemaObject($name);

}
