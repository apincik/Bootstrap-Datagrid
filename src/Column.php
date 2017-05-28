<?php

namespace BtTable;

use \Nette\Application\UI\Control;
use \Nette\ComponentModel\IContainer;
use \Nette\Utils\Html;


class Column implements IColumn
{
    const MIN_LENGTH = "isMinLength";
    const MAX_LENGTH = "isMaxLength";
    const EQUAL = "isEqual";

    const FILTER_INPUT = "input";
    const FILTER_SELECT = "select";
    const ELEMENT_COLUMN = "th";

    public $onValidate = NULL;
    private $rules = array();
    private $errors = array();

    private $filterType;
    private $name;
    private $isSortable;
    private $isEditable;
    private $formatter;
    private $id;
    private $text;
    private $dbColumnName;
    private $value;

    private $attributes = array();


    public function __construct($name, $db_column_name, $text = NULL)
    {
        $this->name = $name;
        $this->text = $text;
        $this->dbColumnName = $db_column_name;
    }


    protected function loadAttributes()
    {
        $this->attributes["data-filter-control"] = $this->filterType;
        $this->attributes["data-sortable"] = $this->isSortable;
        $this->attributes["data-editable"] = $this->isEditable;
        $this->attributes["data-field"] = $this->name;
        $this->attributes["id"] = $this->id;
        //$this->attributes["data-formatter"] = $this->formatter;
    }


    protected function makeElement()
    {
        $this->loadAttributes();

        $el = Html::el(self::ELEMENT_COLUMN);
        foreach($this->attributes as $attribute => $value) {
            if($value != NULL) {
                $el->__set($attribute, $value);
            }
        }

        $text = $this->text != NULL ? $this->text : $this->name;
        $el->setText($text);

        return $el;
    }


    public function render()
    {
        $el = $this->makeElement();
        return $el->render();
    }


    ////////////////VALIDATORS//////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Store rule in rules array
     * @param $rule
     * @param $message
     * @param $value
     * @throws \Exception
     */
    public function setRule($rule, $message, $value)
    {
        if($rule != self::MAX_LENGTH && $rule != self::MIN_LENGTH && $rule != self::EQUAL) {
            throw new \Exception("Uknown validation rule type.");
        }

        $this->rules[$rule] = array(
                            'message' => $message,
                            'value' => $value
        );
    }


    /**
     * Run validate function and return if errors exist
     * @return bool
     */
    public function isValid()
    {
        $this->validate();
        if(count($this->errors) > 0) {
            return FALSE;
        }

        return TRUE;
    }


    /**
     * Check default rules and validate callbacks
     */
    private function validate()
    {
        //iterate default rules
        foreach($this->rules as $rule => $array)
        {
            $validation_result = call_user_func("BtTable::$rule", [$this->value, $array["value"]]); //$this->{$rule}($this->value, $array["value"]);
            if($validation_result == FALSE) {
                $array["message"] = str_replace("%d", $array["value"], $array["message"]);
                $this->addError($array["message"]);       //@TODO call preg_replace on "%d" in message.
            }
        }

        if(is_callable($this->onValidate)) {
            $result_column = call_user_func_array($this->onValidate, array($this, $this->value));
            if ($result_column instanceof Column == FALSE) {
                throw new \Exception("Invalid return type from onValidate closure.");
            }
        }

    }


    public function addError($message)
    {
        array_push($this->errors, $message);
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    public function setFilterType($type)
    {
        if($type != self::FILTER_INPUT && $type != self::FILTER_SELECT)
            throw new \Exception("Unknow filter type for column.");

        $this->filterType = $type;
    }


    public function setName($name)
    {
        $this->name = $name;
    }


    public function setSortable($val = TRUE)
    {
        $this->isSortable = $val == TRUE ? "true" : "false";
    }


    public function getSortable()
    {
        return $this->isSortable;
    }


    public function setEditable($val = TRUE)
    {
        $this->isEditable = $val == TRUE ? "true" : "false";
    }


    public function getEditable()
    {
        return $this->isEditable;
    }


    public function setFormatter($string)
    {
        $this->formatter = $string;
    }


    public function getFormatter()
    {
        return $this->formatter;
    }


    public function setId($string)
    {
        $this->id = $string;
    }


    public function getId()
    {
        return $this->id;
    }


    public function getText()
    {
        return $this->text;
    }

    public function getName()
    {
        return $this->name;
    }


    public function getDbColumnName()
    {
        return $this->dbColumnName;
    }


    public function getValue()
    {
        return $this->value;
    }


    public function setValue($value)
    {
        $this->value = $value;
    }


    public function getErrors()
    {
        return $this->errors;
    }


}