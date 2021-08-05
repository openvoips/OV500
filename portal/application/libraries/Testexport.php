<?php
// ##############################################################################
// OV500 - Open Source SIP Switch & Pre-Paid & Post-Paid VoIP Billing Solution
//
// Copyright (C) 2019 Openvoips Technologies  
// Seema Anand <openvoips@gmail.com>
// Anand <kanand81@gmail.com>
// http://www.openvoips.com  http://www.openvoips.org
//
//
// OV500 Version 1.0
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

class Testexport {

    function __construct() {
        
    }

    // Load table data from file
    public function download($file_name, $format, $search_array, $result_header, $result_data) {
        // die("aaaaaaaaaaaaa");
        $CI = & get_instance();
        switch ($format) {
            case 'csv':
                $output_file_name = $file_name . '.csv';
                $out = fopen('php://output', 'w');
                $delimiter = ',';

                fputcsv($out, $result_header, $delimiter);

                foreach ($result_data as $line) {
                    fputcsv($out, $line, $delimiter);
                }

                /** modify header to be downloadable csv file * */
                header('Content-Type: application/csv');
                header('Content-Disposition: attachement; filename="' . $output_file_name . '";');
                /** Send file to browser for download */
                fpassthru($out);


                break;
            case 'txt':
                $output_file_name = $file_name . '.txt';
                $out = fopen('php://output', 'w');
                $delimiter = ',';

                foreach ($result_data as $line) {
                    fputcsv($out, $line, $delimiter);
                    fwrite($out, PHP_EOL);
                }


                header('Content-Type: text/plain');
                header('Content-Disposition: attachement; filename="' . $output_file_name . '";');
                /** Send file to browser for download */
                fpassthru($out);
                /* implode(',',$result_data);
                  $data = 'Here is some text!';
                  $name = $file_name.'.txt';
                  force_download($name, $data); */


                break;
            case 'pdf':

                require_once dirname(__FILE__) . '/tcpdf/tcpdf.php';
                $CI->load->library('Pdf');

                $pdf = new Pdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // set document information
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('I-Solution');
                $pdf->SetTitle('I-Solution Communications');
                $pdf->SetSubject('I-Solution Communications');
                $pdf->SetKeywords('I-Solution Communications');

                // set default header data
                $date = date('l jS \of F Y h:i:s A');
                $pdf->SetHeaderData('', PDF_HEADER_LOGO_WIDTH, SITE_NAME . ' (' . $date . ')', '');

                // set header and footer fonts
                $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
                $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

                // set some language-dependent strings (optional)
                if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
                    require_once(dirname(__FILE__) . '/lang/eng.php');
                    $pdf->setLanguageArray($l);
                }


                // add a page
                $pdf->AddPage();


                // search html table			
                if (count($search_array) > 0) {
                    $search_tbl = '<table cellspacing="0" cellpadding="1" border="1">
						<tr><th colspan="2" align="center"><strong>Search Data</strong></th></tr>';
                    foreach ($search_array as $key => $value) {
                        $search_tbl .= '<tr>
								<td><strong>' . $key . '</strong></td>
								<td>' . $value . '</td>
							</tr>';
                    }
                    $search_tbl .= '</table>';
                    $pdf->SetFont('helvetica', '', 10);
                    $pdf->writeHTML($search_tbl, true, false, false, false, '');
                }


                $result_tbl = '';
                $result_tbl = '<table cellspacing="0" cellpadding="1" border="1">';
                if (count($result_header) > 0) {

                    $result_tbl .= '<tr style="background-color:#CCCCCC;">';
                    foreach ($result_header as $key => $value) {
                        $result_tbl .= '<td><strong>' . $value . '</strong></td>';
                    }
                    $result_tbl .= '</tr>';
                }

                if (count($result_data) > 0) {
                    foreach ($result_data as $key => $result_array) {
                        $result_tbl .= '<tr>';
                        foreach ($result_array as $key => $value) {
                            $result_tbl .= '<td>' . $value . '</td>';
                        }
                        $result_tbl .= '</tr>';
                    }
                } else {
                    $result_tbl .= '<tr><td colspan="10">No Records Found</td></tr>';
                }
                $result_tbl .= '</table>';
                $pdf->SetFont('helvetica', '', 9);
                $pdf->writeHTML($result_tbl, true, false, false, false, '');

                // set font for data table		
                //$pdf->SetFont('helvetica', '', 9);	
                // print colored table
                //$pdf->ColoredTable($result_header, $result_data);
                // ---------------------------------------------------------
                // close and output PDF document
                $pdf->Output($file_name . '.pdf', 'D');




                break;

            case 'xls':
            case 'xlsx':

                require_once dirname(__FILE__) . '/PHPExcel/Classes/PHPExcel.php';
                $objPHPExcel = new PHPExcel();

                $column_count = count($result_header);

                $row = 1; //excel row number
                if (count($search_array) > 0) {
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A' . $row, "Test Search Data");

                    $objPHPExcel->getActiveSheet()->getStyle('A' . $row)
                            ->getFont()
                            ->setBold(true)
                            ->setSize(12)
                            ->getColor()->setRGB('405467');
                    //merge cells
                    $objPHPExcel->getActiveSheet()->mergeCells('A1:B1');
                    //align center
                    $style = array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        )
                    );
                    $objPHPExcel->getActiveSheet()->getStyle("A1:B1")->applyFromArray($style);

                    foreach ($search_array as $field_name => $field_value) { //$field_value=$field_value.'T';
                        $row++;
                        $objPHPExcel->setActiveSheetIndex(0)
                                ->setCellValue('A' . $row, $field_name)
                                ->setCellValueExplicit('B' . $row, $field_value, PHPExcel_Cell_DataType::TYPE_STRING);
                        //	->setCellValue('B'.$row, $field_value);
                    }

                    //set border
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    $objPHPExcel->getActiveSheet()->getStyle('A1:B' . $row)->applyFromArray($styleArray);

                    $row++;
                    $row++; //give one extra space after search				
                }

                if (count($result_header) > 0) {
                    $k = 0;
                    $char_index = 65; //'A'	
                    while ($k < $column_count) {
                        if ($char_index >= 65 + 26) {
                            $char_index_temp = $char_index;
                            $a_to_z_loop_counter = -1;
                            while ($char_index_temp >= 65 + 26) {
                                $char_index_temp = $char_index_temp - 26;
                                $a_to_z_loop_counter++;
                            }

                            $char_prefix = chr(65 + $a_to_z_loop_counter);
                            $char = $char_prefix . chr($char_index_temp);
                            //	echo $a_to_z_loop_counter;
                            //echo '--'.$char;die;
                        } else {
                            $char = chr($char_index);
                        }
                        //echo $char.$row.'<br>';
                        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($char . $row, $result_header[$k]);
                        $char_index++;
                        $k++;
                    }

                    $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':' . $char . $row)
                            ->getFont()
                            ->setBold(true)
                            ->setSize(12)
                            ->getColor()->setRGB('405467');
                }

                if (count($result_data) > 0) {
                    foreach ($result_data as $rec) {
                        $row++;
                        $k = 0;
                        $char_index = 65;
                        while ($k < $column_count) {
                            if ($char_index >= 65 + 26) {
                                $char_index_temp = $char_index;
                                $a_to_z_loop_counter = -1;
                                while ($char_index_temp >= 65 + 26) {
                                    $char_index_temp = $char_index_temp - 26;
                                    $a_to_z_loop_counter++;
                                }

                                $char_prefix = chr(65 + $a_to_z_loop_counter);
                                $char = $char_prefix . chr($char_index_temp);
                            } else {
                                $char = chr($char_index);
                            }


                            //$objPHPExcel->setActiveSheetIndex(0)->setCellValue($char.$row, $rec[$k]);	
//$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($char.$row, $rec[$k],PHPExcel_Cell_DataType::TYPE_STRING);						
                            if ($file_name == 'cdr_report_connected_calls' && in_array($result_header[$k], array('SRC-DST', 'SRC-CLI', 'USER-CLI', 'User-DST', 'C-CLI', 'C-DST'))) {

                                $objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($char . $row, $rec[$k], PHPExcel_Cell_DataType::TYPE_STRING);
                            } else {
                                $objPHPExcel->setActiveSheetIndex(0)->setCellValue($char . $row, $rec[$k]);
                            }





                            $char_index++;
                            $k++;
                        }
                    }
                }

                /////set auto width/
                foreach (range('A', $objPHPExcel->getActiveSheet()->getHighestDataColumn()) as $col) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                //////

                header('Content-Disposition: attachment;filename="' . $file_name . '.' . $format . '"');
                header('Cache-Control: max-age=0');
                header('Cache-Control: max-age=1');
                header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
                header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                header('Pragma: public'); // HTTP/1.0

                if ($format == 'xls') {
                    header('Content-Type: application/vnd.ms-excel');
                    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
                } else {
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
                }


                $objWriter->save('php://output');


                break;


            default:

                return 'Format not supported';
        }//switch
    }

}
