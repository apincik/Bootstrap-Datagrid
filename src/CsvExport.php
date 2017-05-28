<?php

namespace BtTable;


class CsvExport
{

    private $columns = array();
    private $data = array();


    public function __construct($columns, $data)
    {
        $this->columns = $columns;
        $this->data = $data;

    }


    function export()
    {
        if (count($this->data) == 0) {
            return null;
        }

        ob_get_clean();
        ob_start();

        $df = fopen("php://output", 'w');
        fputcsv($df, array_keys($this->data["rows"][0]));   //header
        foreach ($this->data["rows"] as $row) {
            $array = array();
            foreach($row as $key => $rowData) {
                array_push($array, $rowData);
            }
            fputcsv($df, $array);
        }
        fclose($df);

        return ob_get_clean();
    }


}