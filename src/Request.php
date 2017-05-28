<?php

namespace BtTable;

use \Nette\Application\UI\Control;
use \Nette\ComponentModel\IContainer;
use \Nette\Utils\Html;
use \Nette\Database\Table\Selection;
use \Nette\Application\UI\Link;


class Request
{
    const SEARCH = "search";
    const FILTER = "filter";
    const LIMIT = "limit";
    const OFFSET = "offset";
    const SORT = "sort";
    const ORDER = "order";

    const ORDER_ASC = "asc";
    const ORDER_DESC = "desc";

    /**
     * @var Column array
     */
    private $columns;

    /** @string */
    public $search;

    /** @var array */
    public $filters;

    /** @var array */
    public $sorts;

    /** @var int|string */
    public $limit;

    /** @var int|string */
    public $offset;

    /** @var int|string */
    public $sort;

    /** @var int|string */
    public $order;

    /** @var  boolean */
    public $hasLimit;

    /** @var  boolean */
    public $hasFilter;

    /** @var boolean */
    public $hasSort;

    /** @var boolean */
    public $hasSearch;


    public function __construct(array $columns)
    {
        $this->columns = $columns;
    }


    /**
     * Read request GET data for filtering
     * Setup properties to identify filter actions
     * @throws \Exception
     */
    public function handleRequest()
    {
        if(isset($_GET[self::SEARCH])) {
            $this->search = $_GET["search"];
            $this->hasSearch = TRUE;
        }

        if(isset($_GET[self::FILTER])) {
            $this->filters = $this->getFilters();
            $this->hasFilter = TRUE;
        }

        if(isset($_GET[self::LIMIT]) && isset($_GET[self::OFFSET]) && $_GET[self::LIMIT] != NULL && $_GET[self::OFFSET] != NULL) {
            $this->limit = $_GET[self::LIMIT];
            $this->offset = $_GET[self::OFFSET];
            $this->hasLimit = TRUE;
        }

        if(isset($_GET[self::SORT]) && isset($_GET[self::ORDER])) {
            $sort = $_GET[self::SORT];          //column name
            $order = $_GET[self::ORDER];        //order type

            if($order != self::ORDER_ASC && $order != self::ORDER_DESC)
                throw new \Exception("Unknow parameter for order field.");

            $order = strtoupper($order);
            $this->sorts = $this->getSorts($sort, $order);
            $this->hasSort = TRUE;
        }
    }


    /**
     *  @TODO check if column has filter ON
     * Parse filters from query string and check if exists in grid
     * Store its db_column_name and value to array.
     * @return array
     */
    private function getFilters()
    {
        $filters = json_decode($_GET[self::FILTER], TRUE);
        $selected_filters = array();

        foreach($filters as $filter => $value)
        {
            foreach($this->columns as $col)
            {
                if($filter == $col->getName()) {
                    $selected_filters[$col->getDbColumnName()] =  $value;        //check if filter parameter exists and save
                    break;
                }
            }
        }

        return $selected_filters;
    }


    /**
     * @param $sort grid column name
     * @param $order order type
     */
    private function getSorts($sort, $order)
    {
        $sorts = array();

        foreach($this->columns as $col)
        {
            if($sort == $col->getName()) {
                $sorts[$col->getDbColumnName()] = $order;        //check if sort column exists
                break;
            }
        }

        return $sorts;
    }


}
