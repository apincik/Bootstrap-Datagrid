<?php

namespace Table;

use \Nette\Application\UI\Control;
use \Nette\ComponentModel\IContainer;
use \Nette\Utils\Html;
use \Nette\Database\Table\Selection;
use \Nette\Application\UI\Link;


class BtGrid extends Control
{
    //"/admin/user/jstable/?do=loadUsers"
    const TABLE_ID = "table";
    const DATA_PAGINATION_SERVER = "server";
    const DATA_PAGINATION_CLIENT = "client";
    const ELEMENT_TABLE = "table";

    /** @var \Nette\Database\Table\Selection */
    private $dataSource;

    /**
     * Called when column update occures
     * @var function
     */
    public $updateCallback;

    /** @var  \Kdyby\Translation\Translator */
    public $translator;

    /** @var  Primary key database column name */
    private $primaryKey;

    /** @var string Default attribute for BT table field. */
    private $dataToggle = "table";

    /** @var null|string Url for loading data. */
    private $dataUrl = NULL;

    /** @var null|string Url for updating data. */
    //private $updateUrl = NULL;

    /** @var null|string(client,server) Set pagination side. */
    private $dataSidePagination = NULL;

    /** @var null|boolean Set pagination on/off. */
    private $dataPagination = NULL;

    /** @var null|bool Show or hide pagination list. */
    private $dataPageList = NULL;

    /** @var null|bool Show or hide search box in BT Table. */
    private $dataSearch = NULL;

    /** @var null|bool Show or hide export button. */
    private $dataShowExports = NULL;

    /** @var null|bool Show or hide control for fitering column. */
    private $dataFilterControl = NULL;

    /** @var null|bool If table is responsive. */
    private $dataMobile = NULL;

    /** @var array Registered table attributes. */
    private $tableAttributes = array();

    /** @var array Columns in BT Table. */
    private $columns = array();

    /** @var array Action Columns in BT Table */
    private $actionColumns = array();

    /**
     * @var null
     * DB connection
     */
    private $dataConnection = NULL;

    /**
     * @var null
     * Destination table
     */
    private $dataTable = NULL;


    /**
     * @param IContainer|NULL $container
     * @param null $name
     */
    public function __construct(IContainer $container = NULL, $name = NULL)
    {
        parent::__construct($container, $name);
    }


    /**
     * Populate tableAttributes array with class property values.
     * @throws \Exception
     */
    protected function loadTableAttributes()
    {
        if($this->dataUrl == NULL) {
            $link = new Link($this, "loadData", array());
            $this->dataUrl = $link->__toString();       //link to BtGrid->loadData function
        }

        $this->tableAttributes["data-toggle"] = $this->dataToggle;
        $this->tableAttributes["data-url"] = $this->dataUrl;
        $this->tableAttributes["data-side-pagination"] = $this->dataSidePagination;
        $this->tableAttributes["data-pagination"] = $this->dataPagination;
        $this->tableAttributes["data-page-list"] = $this->dataPageList;
        $this->tableAttributes["data-search"] = $this->dataSearch;
        $this->tableAttributes["data-show-exports"] = $this->dataShowExports;
        $this->tableAttributes["data-filter-control"] = $this->dataFilterControl;
        $this->tableAttributes["data-mobile-responsive"] = $this->dataMobile;
        $this->tableAttributes["id"] = self::TABLE_ID;
        //$this->tableAttributes["data-toolbar"] = "#toolbar";
    }


    public function loadColumns()
    {
        $schemaTable = explode(".", $this->dataTable);
        $schema = $schemaTable[0];
        $table = $schemaTable[1];

        $columnQuery = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE 
                        TABLE_SCHEMA='$schema' AND TABLE_NAME='$table'";

        $columns = $this->dataConnection->query($columnQuery)->fetchAll();
        foreach($columns as $col)
        {
            $name = $col["COLUMN_NAME"];
            $btColumn = new Column($name, $name, $name);
            $btColumn->setFilterType(Column::FILTER_INPUT);

            $this->addColumn($btColumn);
        }
    }


    /**
     * @param $database \Nette\Database\Connection
     */
    public function setDataConnection($database)
    {
        $this->dataConnection = $database;
    }


    /**
     * @param $table string
     */
    public function setDataTable($table)
    {
        $this->dataTable = $table;
    }


    /**
     * @return null|string
     */
    public function getDataTable()
    {
        return $this->dataTable;
    }


    /**
     * Prepare template variables before component render.
     */
    protected function beforeRender()
    {
        $this->loadTableAttributes();

        $table = Html::el(self::ELEMENT_TABLE);
        foreach ($this->tableAttributes as $attribute => $value) {
            if ($value != NULL) {
                $table->__set($attribute, $value);
            }
        }

        $updateLink = new Link($this, "updateData", array());
        $this->template->updateUrl = $updateLink->__toString();       //link to BtGrid->updateData

        if($this->primaryKey == NULL) {
            throw new \Exception("No primary key has been set.");
        }

        $this->template->primaryKey = $this->primaryKey;
        $this->template->table = $table;
        $this->template->columns = $this->columns;
        $this->template->actionColumns = $this->actionColumns;

    }


    /**
     * Render component
     */
    public function render()
    {
        $this->template->setFile(__DIR__ . '/btGrid.latte');
        $this->beforeRender();
        $this->template->render();
    }


    /**
     * @TODO DELETE
     * testing purpose
     */
    public function handleLoadTestData()
    {
        Response::sendJsonResponse(array(
           'total' => 1,
            'rows' => array(
                array(
                    'id' => "1",
                    'login' => 'andrej',
                    'email' => 'test@gmail.com',
                ),
            ),
        ));
    }


    /**
     * Default server side load data.
     * Load data from dataSource, apply filters and send response.
     */
    public function handleLoadData()
    {
        $btData = new Data($this->dataSource, $this->columns);
        $request = new Request($this->columns);
        $request->handleRequest();

        if($request->hasSearch) {
            $btData->searchByColumns($request->search);
        }

        if($request->hasFilter) {
            foreach($request->filters as $dbColumnName => $value) {
                $btData->searchByColumn($dbColumnName, $value);
            }
        }

        if($request->hasLimit) {
            $btData->limit($request->limit, $request->offset);
        }

        if($request->hasSort) {
            foreach($request->sorts as $dbColumnName => $order) {
                $btData->order($dbColumnName, $order);        //check if sort column exists
            }
        }

        $response = new Response($btData->getDataSource(), $this->columns, $btData->getRowCount());
        $response->sendLoadResponse();
    }


    /**
     * Export data to CSV
     */
    public function exportToCsv()
    {
        $btData = new Data($this->dataSource, $this->columns);
        $response = new Response($btData->getDataSource(), $this->columns, $btData->getRowCount());
        $export = new CsvExport($this->columns, $response->getResponseData());

        $export->export();
    }


    /**
     * Update column value ex. in database.
     * Check if column is editable, set a new value.
     * Run validation on column and call updateCallback to handle save process.
     * @throws \Exception
     */
    public function handleUpdateData()
    {
        $id = $_POST["id"];
        $column = $_POST["column"];
        $value = $_POST["newValue"];

        if(is_callable($this->updateCallback) == FALSE) {
            throw new \Exception("Update callback is not callable.");
        }

        $active_column = NULL;
        foreach($this->columns as & $col)
        {
            if($col->getName() == $column && $col->getEditable() == TRUE)
            {
                $col->setValue($value);    //update column object text value
                $active_column = & $col;
                break;
            }
        }

        if($active_column != NULL)
        {
            if($active_column->isValid() == FALSE)      //control column is valid
            {
                $errors = $active_column->getErrors();
                Response::sendJsonResponse(["result" => "error", "errors" => $errors]);
            }

            $result = $this->updateCallback($id, $active_column->getDbColumnName(), $value);
        }
        else
        {
            $result = FALSE;
        }

        if($result == FALSE) {
            Response::sendJsonResponse(["result" => "error"]);
        }

        Response::sendJsonResponse(["result" => "success"]);
    }


    ///////////////GETTERS & SETTERS////////////////////////////////////////////////////////////////////////////////////


    public function setPrimaryKey($string)
    {
        $this->primaryKey = $string;
    }


    public function getPrimaryKey($string)
    {
        return $this->primaryKey;
    }


    public function addColumn(Column & $column)
    {
        array_push($this->columns, $column);
    }


    public function getColumns()
    {
        return $this->columns;
    }


    public function addActionColumn(ActionColumn & $column)
    {
        array_push($this->actionColumns, $column);
    }


    public function getActionColumns()
    {
        return $this->actionColumns;
    }


    /*public function getUpdateUrl()
    {
        return $this->updateUrl;
    }*/


    public function setDataSource(Selection & $data)
    {
        $this->dataSource = $data;
    }


    public function getDataSource()
    {
        return $this->dataSource;
    }


    public function setSearch($bool)
    {
        $this->dataSearch = $bool == TRUE ? "true" : NULL;
    }


    public function getSearch()
    {
        return $this->dataSearch;
    }


    public function setShowExports($bool)
    {
        $this->dataShowExports = $bool == TRUE ? "true" : NULL;
    }


    public function getShowExports()
    {
        return $this->dataShowExports;
    }


    public function setFilterControls($bool)
    {
        $this->dataFilterControl = $bool == TRUE ? "true" : NULL;
    }


    public function getFilterControls()
    {
        return $this->dataFilterControl;
    }


    public function setPageList(array $values)
    {
        $this->dataPageList = $values;
    }


    public function getPageList()
    {
        return $this->dataPageList;
    }


    public function setPagination($bool)
    {
        $this->dataPagination = $bool == TRUE ? "true" : NULL;
    }


    public function getPagination()
    {
        return $this->dataPagination;
    }


    public function setMobile($bool)
    {
        $this->dataMobile = $bool == TRUE ? "true" : NULL;
    }


    public function getMobile()
    {
        return $this->dataMobile;
    }


    public function setSidePagination($val)
    {
        if ($val != self::DATA_PAGINATION_CLIENT && $val != self::DATA_PAGINATION_SERVER)
            throw new \Exception("Unknow data side pagination value.");

        $this->dataSidePagination = $val;
    }


    public function getSidePagination()
    {
        return $this->dataSidePagination;
    }


    public function setUrl($url)
    {
        $this->dataUrl = $url;
    }


    public function getUrl()
    {
        return $this->dataUrl;
    }


    public function setTranslator(\Kdyby\Translation\Translator $translator)
    {
        $this->translator = $translator;
    }


    public function getTranslator()
    {
        return $this->translator;
    }
}
