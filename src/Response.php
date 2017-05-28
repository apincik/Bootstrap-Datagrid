<?php

namespace BtTable;

use \Nette\Application\UI\Control;
use \Nette\ComponentModel\IContainer;
use \Nette\Utils\Html;
use \Nette\Database\Table\Selection;
use \Nette\Application\UI\Link;
use \Latte\Runtime\Filters;


class Response
{
    /**
     * @var \Nette\Database\Table\Selection
     */
    private $dataSource;

    /**
     * @var int
     */
    private $dataCount;

    /**
     * @var Column array
     */
    private $columns;

    private $responseData = NULL;


    public function __construct(Selection $dataSource, $columns, $dataCount)
    {
        $this->dataSource = $dataSource;
        $this->dataCount = $dataCount;
        $this->columns = $columns;
    }


    private function prepareResponseData()
    {
        $result["total"] = $this->dataCount;
        $result["rows"] = array();

        foreach($this->dataSource as $data)
        {
            $array = array();
            foreach($this->columns as $col) {
                $array[$col->getName()] = NULL;
                if($col instanceof Column) {
                    $dbColumnName = $col->getDbColumnName();

                    //if defined dot notation, look for reference
                    if (strpos($dbColumnName, '.') !== false) {
                        $nameParts = explode(".", $dbColumnName);
                        $related = $data->ref($nameParts[0], $nameParts[1]);
                        $colValue = $related->{$nameParts[2]};
                    }
                    else {
                        $colValue = $data->{$dbColumnName};
                    }

                    $array[$col->getName()] = Filters::escapeHtml($colValue, ENT_NOQUOTES); //escape data from DB
                }
            }

            array_push($result["rows"], $array);
        }

        $this->responseData = $result;
    }


    public function getResponseData()
    {
        if($this->responseData == NULL) {
            $this->prepareResponseData();
        }

        $result = $this->responseData;
        $this->responseData = NULL;

        return $result;
    }


    /**
     * Send loaded response
     */
    public function sendLoadResponse()
    {
        if($this->responseData == NULL) {
            $this->prepareResponseData();
        }

        self::sendJsonResponse($this->responseData);
    }


    /**
     * Send JSON response
     * @param array $array
     */
    public static function sendJsonResponse(array $array)
    {
        ob_get_clean();
        header('Content-Type: application/json');
        echo json_encode($array);
        die;
    }
}