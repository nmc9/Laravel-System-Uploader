<?php

namespace Nmc9\Uploader\Kfir;

class ShowKeysGenerator
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
    public function generate($table, $fields)
    {
        $fields = !empty($fields) ? $fields : ["id"];
        $values = $this->buildSQLValuesStringFrom($fields);

        $query = vsprintf('SHOW KEYS FROM `%s` WHERE `Column_name` IN %s;', [
            $table, $values
        ]);

        return new QueryObject($query, $fields);
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
        return "(" . rtrim(
            array_reduce($rows,function ($x,$y) {
                return $x . "?,";
            }),','
        ) . ")";
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
        return $rows;
    }
}
