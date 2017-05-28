<?php
namespace BtTable;
use \Nette\Utils\Html;


class ColumnDate implements IElement
{
    const ELEMENT = "span";

    private $name;
    private $htmlElement;
    private $text;


    public function __construct($name)
    {
        $this->name = $name;
        $this->htmlElement = Html::el(self::ELEMENT);
    }


    public function render()
    {
        $html = "<span class='datetime'>" . $this->text . "</span>";
        $this->htmlElement->setHtml($html);

        return $this->htmlElement->getHtml();
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