<?php

namespace BtTable;

use \Nette\Application\UI\Control;
use \Nette\ComponentModel\IContainer;
use \Nette\Utils\Html;

class ActionColumn implements IColumn
{
    const ELEMENT_COLUMN = "th";

    private $text;
    private $name;
    private $formatter;
    private $format;
    public $actionCallback;

    private $attributes = array();
    private $button;


    public function __construct($name)
    {
        $this->name = $name;
        //$this->format = "<button class='btn btn-xs btn-success btaction'>Delete</button>";        //@TODO vytvori vlastnu triedu pre button
        $this->formatter = "buttonDeleteFormatter";

    }


    protected function loadAttributes()
    {
        $this->attributes["data-field"] = $this->name;
        $this->attributes["data-formatter"] = $this->formatter;//$this->formatter;
        $this->attribute["format"] = $this->format;//$this->format;
    }


    protected function makeElement()
    {
        $this->format = $this->button->render();
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



    public function getName()
    {
        return $this->name;
    }


    public function setFormatter($string)
    {
        $this->formatter = $string;
    }


    public function getFormatter()
    {
        return $this->formatter;
    }


    public function setFormat($string)
    {
        $this->format = $string;
    }


    public function getFormat()
    {
        return $this->format;
    }


    public function setButton($button)
    {
        $this->button = $button;
    }


    public function getButton()
    {
        return $this->button;
    }





}