<?php

namespace Nmc9\Uploader\Kfir;

class ReplaceGenerator
{

    public static function make(){
        return new self();
    }

    /**
     * Generates a QueryObject with the SQL query and the bindings.
     *
     * @param       $table
     * @param       $rows
     * @param array $exclude
     *
     * @return QueryObject
     */
    public function generate($table, $rows)
    {
        if(empty($rows)){
            return null;
        }
        $columns = array_keys($rows[0]);
        $columnsString = implode('`,`', $columns);
        $values = $this->buildSQLValuesStringFrom($rows);

        $query = vsprintf('REPLACE INTO `%s` (`%s`) VALUES %s;', [
            $table, $columnsString, $values
        ]);

        return new QueryObject($query, $this->extractBindingsFrom($rows));
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
            return $values . '(' . rtrim(str_repeat('?,', count($row)), ',') . '),';
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
            return array_merge($result, array_values($item));
        }, []);
    }
}
