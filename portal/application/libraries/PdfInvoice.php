<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once dirname(__FILE__) . '/tcpdf/tcpdf.php';

class PdfInvoice extends TCPDF {

    private $header_text1, $header_text2, $header_text3, $footer_text = '';

    function __construct() {
        parent::__construct(); //$this->original_lMargin=10;	
		
    }

    public function get_w() {
        return $this->w;
    }

    public function set_header_text1($header_text1) {
        $this->header_text1 = '<strong>' . $header_text1 . '</strong>';
    }

    public function set_header_logo($image_url) {
        $this->header_text1 = '<img src="' . $image_url . '" height="70" align="left" />';
        ;
    }

    public function set_header_text2($header_text2) {
        $this->header_text2 = $header_text2;
    }

    public function set_header_text3($header_text3) {
        $this->header_text3 = $header_text3;
    }

    public function set_footer_text($footer_text) {
        $this->footer_text = $footer_text;
    }

    public function Header() {


        if ($this->header_xobjid === false) {
            // start a new XObject Template
            $this->header_xobjid = $this->startTemplate($this->w, $this->tMargin);
            $headerfont = $this->getHeaderFont();
            $headerdata = $this->getHeaderData();
            $this->y = $this->header_margin;
            $this->x = $this->original_lMargin;
            $this->SetFont('helvetica', '', 9);

            $search_tbl = '<table cellspacing="0" cellpadding="05" border="0" width="100%" >';
            $search_tbl .= '<tr>
							<td width="43%">' . $this->header_text1 . '</td>
							<td width="50%" align="right">' . $this->header_text3 . '</td>							
							<td width="7%">&nbsp;</td></tr>';
            $search_tbl .= '</table>';


            $this->writeHTML($search_tbl, true, false, false, false, '');



            // print an ending header line
            //$this->SetLineStyle(array('width' => 0.85 / $this->k, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $headerdata['line_color']));
            $this->SetLineStyle(array('width' => 0.85 / $this->k, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(153, 5, 5)));
            $this->SetY((2.835 / $this->k) + max($imgy, $this->y));
            $this->SetX($this->original_lMargin);
            $this->Cell(($this->w - $this->original_lMargin - $this->original_rMargin), 0, '', 'T', 0, 'C');
            $this->endTemplate();
        }


        $x = 0;
        $dx = 0;
        if (!$this->header_xobj_autoreset AND $this->booklet AND ( ($this->page % 2) == 0)) {
            // adjust margins for booklet mode
            $dx = ($this->original_lMargin - $this->original_rMargin);
        }
        if ($this->rtl) {
            $x = $this->w + $dx;
        } else {
            $x = 0 + $dx;
        }
       // $this->printTemplate($this->header_xobjid, $x, 0, 0, 0, '', '', false);
        if ($this->header_xobj_autoreset) {
            // reset header xobject template at each page
            $this->header_xobjid = false;
        }
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        $this->SetFont('helvetica', '', 8);

        if ($this->footer_text != '') {
            $footer_text = '<div style="text-align:center;">' . $this->footer_text . '</div>';
            $this->writeHTML($footer_text, true, false, false, false, '');
        }

        // Page number
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
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
        $w = array_fill(0, $num_headers, 32); //array(40, 35, 40, 45);
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
