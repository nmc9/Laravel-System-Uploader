<?php

namespace Nmc9\Uploader\Kfir;

class OnDuplicateGenerator
{

    private $updated_at = [];
    private $created_at = [];
    private $created_at_key = null;


    public static function make(){
        return new self();
    }

    /**
     * Generates a QueryObject with the SQL query and the bindings.
     *
     * @param       $table
     * @param array $rows
     * @param array $exclude
     *
     * @return QueryObject
     */
    public function generate($table,array $records, array $exclude = [])
    {
        return $this->generateRaw($table,array_map(function($row){
            return $row->get();
        },$records),$exclude);
    }

    /**
     * Generates a QueryObject with the SQL query and the bindings.
     *
     * @param       $table
     * @param array $rows
     * @param array $exclude
     *
     * @return QueryObject
     */
    public function generateRaw($table,array $rows, array $exclude = []){
        if(empty($rows)){
            return null;
        }
        $columns = $this->buildColumns($rows);
        $columnsString = implode('`,`', $columns);
        $values = $this->buildSQLValuesStringFrom($rows);
        $updates = $this->buildSQLUpdatesStringFrom($columns, $exclude);

        $query = vsprintf('INSERT INTO `%s` (`%s`) VALUES %s ON DUPLICATE KEY UPDATE %s;', [
            $table, $columnsString, $values, $updates,
        ]);
        return new QueryObject($query, $this->extractBindingsFrom($rows));
    }

    /**
     * Sets the timestamp on this Generator Object
     *
     * @param       $now
     * @param       $updated_at
     * @param       $created_at
     *
     * @return this
     */
    public function setTimestamps($now,$updated_at, $created_at){
        if($updated_at != null){
            $this->updated_at[$updated_at] = $now;
        }
        if($created_at != null){
            $this->created_at_key = $created_at;
            $this->created_at[$created_at] = $now;
        }
        return $this;
    }

    /**
     * Build the SQL "values()" string.
     *
     * @param $rows
     *
     * @return string
     */
    protected function buildSQLValuesStringFrom($rows)
    {
        return rtrim(array_reduce($rows, function ($values, $row) {
            return $values . '(' . rtrim(str_repeat('?,', count($this->mergeWithTimestamps($row))), ',') . '),';
        }, ''), ',');
    }

    /**
     * Build the column list
     *
     * @param $rows
     *
     * @return array
     */
    protected function buildColumns($rows){
        return array_keys(
            $this->mergeWithTimestamps($rows[0])
        );
    }

    /**
     * Build the SQL "on duplicate key update" string.
     *
     * @param $rows
     * @param $exclude
     *
     * @return string
     */
    protected function buildSQLUpdatesStringFrom($rows, $exclude)
    {
        $exclude = array_merge($exclude,[$this->created_at_key]);
        return trim(array_reduce(array_filter($rows, function ($column) use ($exclude) {
            return ! in_array($column, $exclude);
        }), function ($updates, $column) {
            return $updates . "`{$column}`=VALUES(`{$column}`),";
        }, ''), ',');
    }

    /**
     * Flatten the given array one level deep to extract the bindings.
     *
     * @param $rows
     *
     * @return mixed
     */
    protected function extractBindingsFrom($rows)
    {
        return array_reduce($rows, function ($result, $item) {
            return array_merge($result, array_values($this->mergeWithTimestamps($item)));
        }, []);
    }


    private function mergeWithTimestamps($row){
        return array_merge($row,$this->updated_at,$this->created_at);
    }
}
