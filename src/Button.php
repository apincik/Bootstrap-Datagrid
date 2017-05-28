<?php

namespace BtTable;

use \Nette\Utils\Html;


class Button implements IElement
{
    const ELEMENT = "button";

    private $name;
    private $attrbutes = array();
    private $htmlElement;
    private $text;
    private $buttonType;
    private $actionUrl = "";

    public function __construct($name)
    {
        $this->name = $name;
        $this->htmlElement = Html::el(self::ELEMENT);
    }

    public function render()
    {
        $html = "<button class='btn btn-xs ". $this->buttonType ." btaction'>" . $this->text ."</button>";
        if(strlen($this->actionUrl)) {
            $html = "<a href='" . $this->actionUrl . "'>" . $html . "</a>";
        }

        $this->htmlElement->setHtml($html);
        return $this->htmlElement->getHtml();
    }


    public function setUrl($url)
    {
        $this->actionUrl = $url;
    }


    public function getUrl()
    {
        return $this->actionUrl;
    }


    public function setType($type)
    {
        if($type == "danger") {
            $this->buttonType = "btn-danger";
        }
        else {
            $this->buttonType = "btn-success";
        }
    }


    public function setText($text)
    {
        $this->text = $text;
    }


    public function getText()
    {
        return $this->text;
    }


    public function setAttribute($name, $value)
    {
        $this->htmlElement->__set($name, $value);
    }


    public function getAttribute($name)
    {
        $this->htmlElement->__get($name);
    }


    public function getHtmlElement()
    {
        return $this->htmlElement;
    }




}