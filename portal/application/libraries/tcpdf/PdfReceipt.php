<?php

//require_once dirname(__FILE__) . '/tcpdf/tcpdf.php';
class PdfReceipt extends TCPDF {

    function __construct() {
        parent::__construct();
    }

    public function get_w() {
        return $this->w;
    }

    public function Header() {


        if ($this->header_xobjid === false) {
            // start a new XObject Template
            $this->header_xobjid = $this->startTemplate($this->w, $this->tMargin);
            $headerfont = $this->getHeaderFont();
            $headerdata = $this->getHeaderData();
            $this->y = $this->header_margin;
            $this->x = $this->original_lMargin;

            //set logo

//            $image_file = base_url() . 'theme/default/images/logo.png';
//
//            $imgtype = TCPDF_IMAGES::getImageFileType($image_file);
//            $this->Image($image_file, $this->x, 5, '40', '', 'png', '', 'T', false, 300, '', false, false, 0, false, false, false);
//
//            $imgy = $this->getImageRBY();


            $cell_height = $this->getCellHeight($headerfont[2] / $this->k);
            // set starting margin for text data cell

            $header_x = $this->original_lMargin + ($headerdata['logo_width'] * 1.1);
            $cw = $this->w - $this->original_lMargin - $this->original_rMargin - 50;
            $this->SetTextColorArray($this->header_text_color);


            // header string
            $this->SetFont($headerfont[0], $headerfont[1], $headerfont[2]);
            $this->SetX($cw);

            //set company info

            if (SITE_SUBDOMAIN == 'INS')
                $contact_info = '<tr><td><strong>Phone:</strong> +913366236623</td></tr>
						<tr><td><strong>Email:</strong> billing@I-Solution.in</td></tr>
						<tr><td><strong>Web:</strong> http://www.I-Solution.in</td></tr>';
            else
                $contact_info = '<tr><td><strong>Web:</strong>' . base_url() . '</td></tr>';

            $search_tbl = '<table style="font-family:Arial, Helvetica;font-size: 12px">' . $contact_info . '</table>';

            $this->SetFont('Helvetica', '', 10);
            $this->writeHTML($search_tbl, true, false, false, false, '');



            // print an ending header line

            /* $this->SetLineStyle(array('width' => 0.85 / $this->k, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $headerdata['line_color']));
              $this->SetY((2.835 / $this->k) + max($imgy, $this->y));
              $this->SetX($this->original_lMargin);
              $this->Cell(($this->w - $this->original_lMargin - $this->original_rMargin), 0, '', 'T', 0, 'C');
              $this->endTemplate(); */
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
        $this->printTemplate($this->header_xobjid, $x, 0, 0, 0, '', '', false);
        if ($this->header_xobj_autoreset) {
            // reset header xobject template at each page
            $this->header_xobjid = false;
        }
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
     
            $address = '<p align="center">'. base_url().'</p>';
  


        $this->writeHTML($address, true, false, false, false, '');
    }

}
