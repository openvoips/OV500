<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require_once dirname(__FILE__) . '/tcpdf/tcpdf.php';

class Pdf extends TCPDF {

    function __construct() {
        parent::__construct();
    }

    // Load table data from file
    public function LoadData($file) {
        // Read file lines
        $lines = file($file);
        $data = array();
        foreach ($lines as $line) {
            $data[] = explode(';', chop($line));
        }
        return $data;
    }

    // Colored table
    public function ColoredTable($header, $data) {//print_r($data);	
        // Colors, line width and bold font
        $this->SetFillColor(42, 63, 84); //2A3F54
        $this->SetTextColor(255);
        $this->SetDrawColor(42, 63, 84);
        $this->SetLineWidth(0.3);
        $this->SetFont('', 'B');
        // Header

        $num_headers = count($header);
        $w = array_fill(0, $num_headers, 36); //array(40, 35, 40, 45);
        for ($i = 0; $i < $num_headers; ++$i) {
            $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C', 1);
        }
        $this->Ln();
        // Color and font restoration
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        $this->SetFont('');
        // Data
        $fill = 0;
        foreach ($data as $row) {
            $num_row = count($row);
            for ($i = 0; $i < $num_row; ++$i) {
                $this->Cell($w[$i], 6, $row[$i], 'LR', 0, 'L', $fill);
            }
            /* $this->Cell($w[0], 6, $row[0], 'LR', 0, 'L', $fill);
              $this->Cell($w[1], 6, $row[1], 'LR', 0, 'L', $fill);
              $this->Cell($w[2], 6, number_format($row[2]), 'LR', 0, 'R', $fill);
              $this->Cell($w[3], 6, number_format($row[3]), 'LR', 0, 'R', $fill); */
            $this->Ln();
            $fill = !$fill;
        }
        $this->Cell(array_sum($w), 0, '', 'T');
    }

}
