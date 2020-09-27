<?php
// ##############################################################################
// OV500 - Open Source SIP Switch & Pre-Paid & Post-Paid VoIP Billing Solution
//
// Copyright (C) 2019 Chinna Technologies  
// Seema Anand <openvoips@gmail.com>
// Anand <kanand81@gmail.com>
// http://www.openvoips.com  http://www.openvoips.org
//
//
//OV500 Version 1.0.3
// License https://www.gnu.org/licenses/agpl-3.0.html
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU Affero General Public License for more details.
//
// You should have received a copy of the GNU Affero General Public License
// along with this program. If not, see <http://www.gnu.org/licenses/>.
// ##############################################################################

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
