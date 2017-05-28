<?php
namespace BtTable;

use \Nette\Application\UI\Control;
use \Nette\ComponentModel\IContainer;
use \Nette\Utils\Html;
use \Nette\Database\Table\Selection;
use \Nette\Application\UI\Link;



class Data
{
    /**
     * @var \Nette\Database\Table\Selection
     */
    private $dataSource;

    /**
     * Number of rows in selection.
     * @var int
     */
    private $rowCount;

    /**
     * Grid columns.
     * @var BtColumn array
     */
    private $columns;


    public function __construct(Selection & $dataSource, & $columns)
    {
        $this->dataSource = $dataSource;
        $this->rowCount = count($dataSource);
        $this->columns = $columns;
    }


    /**
     * Do search in every column.
     * @param $search_query string
     */
    public function searchByColumns($value)
    {
        $query = "";
        $params = array();

        foreach($this->columns as $col) {
            $dbColumnName = $col->getDbColumnName();
            $name = $dbColumnName == NULL ? $col->getName() : $dbColumnName;
            $query .= $name . " LIKE ? OR ";
            array_push($params, "%$value%");
        }

        $query = rtrim($query, " OR "); //slice last OR from query string
        $this->dataSource->where($query, $params);
        $this->rowCount = count($this->dataSource);
    }


    /**
     * Search by column.
     * @param $column
     * @param $value
     */
    public function searchByColumn($column, $value)
    {
        $query = $column . " LIKE ?";
        $this->dataSource->where($query, "%$value%");
    }


    /**
     * Apply limit on datasource.
     * @param $limit int|mixed
     * @param $offset int|mixed
     */
    public function limit($limit, $offset)
    {
        $this->dataSource->limit(intval($limit), intval($offset));
    }


    /**
     * Order by column.
     * @param $column
     * @param $order
     */
    public function order($column, $order)
    {
        $this->dataSource->order($column . " " . $order);
    }


    /**
     * @return Selection
     */
    public function getDataSource()
    {
        return $this->dataSource;
    }


    /**
     * @return int
     */
    public function getRowCount()
    {
        return $this->rowCount;
    }


}










