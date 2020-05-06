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

class Export {

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
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A' . $row, "Search Data");

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

                    foreach ($search_array as $field_name => $field_value) {
                        $row++;
                        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A' . $row, $field_name)
                                ->setCellValue('B' . $row, $field_value);
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

    public function download_pdf($file_name, $report_data, $sdr_terms, $user_dp, $monthyear, $account_id) {
        $CI = & get_instance();
        require_once dirname(__FILE__) . '/tcpdf/tcpdf.php';
        require_once dirname(__FILE__) . '/tcpdf/PdfReceipt.php';



        $date_time = date('d-m-Y h:i:s A', time());
        $stmt_gen_date = date('d-m-Y');


        $openingbalance = $addbalance = $removebalance = $usage = 0;
        $debit_sum = $credit_sum = 0;
        $top_html_str = $last_html_str = '';
        $amount_format = "%." . $user_dp . "f";
        if (count($report_data['result']) > 0) {
            $recordCount = 0;
            foreach ($report_data['result'] as $sdr_data) {
                $debit = $credit = '';
                $display_text = '';
                $rule_type = $sdr_data['rule_type'];



                if (isset($sdr_terms[$rule_type])) {
                    $term_array = $sdr_terms[$rule_type];

                    $term_group = $term_array['term_group'];
                    $display_text = $term_array['display_text'];
                    $cost_calculation_formula = trim($term_array['cost_calculation_formula']);

                    //$total_cost = number_format($sdr_data['total_cost'],$user_dp,'.','');
                    $total_cost = round($sdr_data['total_cost'], $user_dp);



                    if ($term_group == 'opening') {
                        if ($cost_calculation_formula == '+') {
                            $openingbalance = $openingbalance + $total_cost;
                            $credit = $total_cost;
                        } elseif ($cost_calculation_formula == '-') {
                            $openingbalance = $openingbalance - $total_cost;
                            $debit = $total_cost;
                        }
                    } elseif ($term_group == 'balance') {
                        if ($cost_calculation_formula == '+') {
                            $addbalance = $addbalance + $total_cost;
                            $credit = $total_cost;
                        } elseif ($cost_calculation_formula == '-') {
                            $removebalance = $removebalance + $total_cost;
                            $debit = $total_cost;
                        }
                    } else {//usage
                        if ($cost_calculation_formula == '+') {
                            $usage = $usage + $total_cost;
                            $credit = $total_cost;
                        } elseif ($cost_calculation_formula == '-') {
                            $usage = $usage + $total_cost;
                            $debit = $total_cost;
                        }
                    }
                }

                if ($display_text == '')
                    $display_text = $sdr_data['rule_type'];

                if ($sdr_data['service_number'] != '')
                    $display_text .= ' (' . $sdr_data['service_number'] . ')';

                /////////////
                if ($sdr_data['service_startdate'] != '' && $sdr_data['service_stopdate'] != '') {
                    $start_date_timestamp = strtotime($sdr_data['service_startdate']);
                    $start_date_display = date(DATE_FORMAT_1, $start_date_timestamp);

                    $stop_date_timestamp = strtotime($sdr_data['service_stopdate']);
                    $stop_date_display = date(DATE_FORMAT_1, $stop_date_timestamp);

                    $display_text .= ' for the period ' . $start_date_display . ' to ' . $stop_date_display;
                }
                ////////////	


                $action_date_timestamp = strtotime($sdr_data['action_date']);
                $action_date_display = date(DATE_FORMAT_1, $action_date_timestamp);


                $debit_sum += $debit;
                $credit_sum += $credit;

                if ($debit != '')
                    $debit = sprintf($amount_format, $debit);
                if ($credit != '')
                    $credit = sprintf($amount_format, $credit);



                $tr_html = '<tr><td style="width: 15%; text-align: left; padding:5px;border:1px solid #ccc;font-size:11px;line-height:16px;">' . $action_date_display . '</td>
												<td style="width: 55%; text-align: left; padding:5px;border:1px solid #ccc;font-size:11px;line-height:16px;">' . $display_text . '</td>                            
												<td style="width: 15%; text-align: right; padding:5px;border:1px solid #ccc;font-size:11px;line-height:16px;">' . $debit . '</td>
												<td style="width: 15%; text-align: right; padding:5px;border:1px solid #ccc;font-size:11px;line-height:16px;">' . $credit . '</td>
										</tr>';


                if ($cost_calculation_formula == '')
                    continue;

                if ($term_group == 'opening') {
                    $top_html_str .= $tr_html;
                } else {
                    $last_html_str .= $tr_html;
                }
                $recordCount++;
            } // end of foreach


            $current_balance = $openingbalance + $addbalance - $removebalance - $usage;

            $total_debit = sprintf($amount_format, $debit_sum);
            $total_credit = sprintf($amount_format, $credit_sum);

            $total_payment = sprintf($amount_format, $addbalance);

            $total_refund = sprintf($amount_format, $removebalance);


            $total_charges = sprintf($amount_format, $usage);


            $total_avail_bal = sprintf($amount_format, $current_balance);
        }// end of if 

        $CI->load->library('Pdf');


        $pdf = new PdfReceipt(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);


        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('I-Solution');
        $pdf->SetTitle('Account Statements');
        $pdf->SetSubject('Account Statements');

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

        // ---------------------------------------------------------
        // set font
        $pdf->SetFont('helvetica', '', 10);

        // add a page
        $pdf->AddPage();



        $css_table = 'style="width: 100%;border-collapse: collapse;font-family:Arial, Helvetica, sans-serif;font-size:11px;" padding:0px;';
        $css_header = 'style="width: 100%; text-align: center; padding:8px;font-size:15px;font-family:Arial, Helvetica"';
        $css_box_header = 'style="text-align: left; padding:5px;border:1px solid #ccc;font-size:12px;background-color:#ccc;line-height:16px;"';
        $css_td_100 = 'style="width: 100%; text-align: left; padding:5px;font-size:11px;line-height:14px;"';
        $css_td_80 = 'style="width: 80%; text-align: left; padding:5px;border:1px solid #ccc;font-size:11px;line-height:16px;"';
        $css_td_60 = 'style="width: 60%; text-align: left; padding:5px;border:1px solid #ccc;font-size:11px;line-height:16px;"';
        $css_td_50 = 'style="width: 50%; text-align: left; padding:5px;border:1px solid #ccc;font-size:11px;line-height:16px;"';
        $css_td_50p = 'style="width: 50%; text-align: left;font-size:11px;line-height:16px;vertical-align:top;"';
        $css_td_40 = 'style="width: 40%; text-align: left; padding:5px;border:1px solid #ccc;font-size:11px;line-height:16px;"';
        $css_td_20 = 'style="width: 20%; text-align: left; padding:5px;border:1px solid #ccc;font-size:11px;line-height:16px;"';
        $css_td_30 = 'style="width: 30%; text-align: left; padding:5px;border:1px solid #ccc;font-size:11px;line-height:16px;"';
        $css_td_20s = 'style="width: 20%; text-align: left; padding:3px;border:1px solid #ccc;font-size:8px;line-height:10px;"';
        $css_td_15 = 'style="width: 15%; text-align: left; padding:5px;border:1px solid #ccc;font-size:11px;line-height:16px;"';
        $css_td_15s = 'style="width: 15%; text-align: left; padding:3px;border:1px solid #ccc;font-size:8px;line-height:10px;"';
        $css_td_15_price = 'style="width: 15%; text-align: right; padding:5px;border:1px solid #ccc;font-size:11px;line-height:16px;"';
        $css_td_10 = 'style="width: 10%; text-align: left; padding:5px;border:1px solid #ccc;font-size:11px;line-height:16px;"';
        $css_td_10s = 'style="width: 10%; text-align: left; padding:3px;border:1px solid #ccc;font-size:8px;line-height:10px;"';
        $css_td_5 = 'style="width: 5%; text-align: center; padding:5px;border:1px solid #ccc;font-size:11px;line-height:16px;"';
        $css_td_5s = 'style="width: 5%; text-align: center; padding:5px;border:1px solid #ccc;font-size:8px;line-height:10px;"';
        $css_td = 'style="text-align: left; padding:0px;font-size:11px;line-height:16px;"';

        $css_td_80_black = 'style="width: 80%; text-align: left; padding:5px;border:1px solid #ccc;font-size:11px;line-height:16px; background-color:#ccc;"';
        $css_td_5_black = 'style="width: 5%; text-align: center; padding:5px;border:1px solid #ccc;font-size:11px;line-height:16px;background-color:#ccc;"';
        $css_td_20_price_black = 'style="width: 20%; text-align: left; padding:5px;border:1px solid #ccc;font-size:11px;line-height:16px;background-color:#ccc;"';
        $css_td_30_black = 'style="width: 30%; text-align: left; padding:5px;border:1px solid #ccc;font-size:11px;line-height:16px; background-color:#ccc;"';
        $css_td_50_black = 'style="width: 50%; text-align: left; padding:5px;border:1px solid #ccc;font-size:11px;line-height:16px; background-color:#ccc;"';
        $css_td_100_right = 'style="width: 100%; text-align: right; padding:5px;font-size:11px;line-height:14px;"';

        $css_td_15_black = 'style="width: 15%; text-align: left; padding:5px;border:1px solid #ccc;font-size:11px;line-height:16px; background-color:#ccc;"';
        $css_td_50_black = 'style="width: 50%; text-align: left; padding:5px;border:1px solid #ccc;font-size:11px;line-height:16px; background-color:#ccc;"';

        $css_td_20 = 'style="width: 15%; text-align: left; padding:5px;border:1px solid #ccc;font-size:11px;line-height:16px;"';
        $css_td_50 = 'style="width: 50%; text-align: left; padding:5px;border:1px solid #ccc;font-size:11px;line-height:16px;"';



        $page_content .= '<table ' . $css_table . ' align="center">
			
						<tr>
							<td style="text-align: left;"> Account No : ' . $account_id . ' </td>
							<td style="text-align: right;">Generated Date : ' . $stmt_gen_date . ' </td>
						</tr>
						<tr>
							<td ' . $css_header . '><strong>Account Statements (' . $monthyear . ')</strong></td>
						</tr>
										
						</table>';


        $pdf->writeHTML($page_content, true, false, true, false, '');

        $page_content = '<table ' . $css_table . ' style="center">								
						
						  <tr>
							<td style="width: 15%; text-align: left; padding:5px;border:1px solid #ccc;font-size:11px;line-height:16px;background-color:#ccc;"><strong>Activity Date</strong></td>
							<td style="width: 55%; text-align: left; padding:5px;border:1px solid #ccc;font-size:11px;line-height:16px;background-color:#ccc;"><strong>Activity</strong></td>
							<td style="width: 15%; text-align: right; padding:5px;border:1px solid #ccc;font-size:11px;line-height:16px;background-color:#ccc;"><strong>Debit</strong></td>
							<td style="width: 15%; text-align: right; padding:5px;border:1px solid #ccc;font-size:11px;line-height:16px;background-color:#ccc;"><strong>Credit</strong></td>
						  </tr>	

							' . $top_html_str . '
							' . $last_html_str . '									

						  
						   <tr>
							<td ></td>
							<td ></td>
							<td ></td>
							<td ></td>
						  </tr>
						 
						   <tr>
								<td ' . $css_td_20 . '></td>
								<td style="width: 55%; text-align: right; padding:5px;border:1px solid #ccc;font-size:11px;line-height:16px;"><strong>Total</strong></td>
								<td ' . $css_td_15_price . '> <strong>' . $total_debit . '</strong> </td>
								<td ' . $css_td_15_price . '> <strong>' . $total_credit . '</strong> </td>
								
							</tr>
						
						 <tr>
							<td ></td>
							<td ></td>
							<td ></td>
							<td ></td>
						  </tr>
						
						<tr>
								<td ' . $css_td_20 . '></td>
								<td style="width: 55%; text-align: right; padding:5px;border:1px solid #ccc;font-size:11px;line-height:16px;"><strong>Total Payment</strong> </td>
								<td ' . $css_td_15_price . '> </td>
								<td ' . $css_td_15_price . '>' . $total_payment . '</td>
								
						</tr>
						<tr>
								<td ' . $css_td_20 . '></td>
								<td style="width: 55%; text-align: right; padding:5px;border:1px solid #ccc;font-size:11px;line-height:16px;"><strong>Total Refund</strong> </td>
								<td ' . $css_td_15_price . '> </td>
								<td ' . $css_td_15_price . '>' . $total_refund . '</td>
								
						</tr>
						<tr>
								<td ' . $css_td_20 . '></td>
								<td style="width: 55%; text-align: right; padding:5px;border:1px solid #ccc;font-size:11px;line-height:16px;"><strong>Total Charges</strong></td>
								<td ' . $css_td_15_price . '> </td>
								<td ' . $css_td_15_price . '>' . $total_charges . '</td>
								
						</tr>
						
						<tr>
								<td ' . $css_td_20 . '></td>
								<td style="width: 55%; text-align: right; padding:5px;border:1px solid #ccc;font-size:11px;line-height:16px;"><strong>Total Available Balance</strong>  </td>
								<td ' . $css_td_15_price . '></td>
								<td ' . $css_td_15_price . '>' . $total_avail_bal . '</td>
								
						</tr>
						
											  
					
					</table>';

//////////////////////////////////////////////////////////


        $pdf->writeHTML($page_content, true, false, true, false, '');


        $page_footer .= '<page_footer>';
        $page_footer .= '<table style="width: 100%;">
					<tr>
						<td style="text-align: left; width: 100%;font-family:Arial, Helvetica, sans-serif; font-size:10px;line-height:12px;">
							** This is System Generated Report based on ' . $date_time . ' available data.
						</td>
					</tr>
					<tr>
						<td ></td>
					</tr>
				</table>';
        $page_footer .= '</page_footer>';

        $page_footer .= '</page>';


        $pdf->writeHTML($page_footer, true, false, true, false, '');

        $pdf->lastPage();
        //Close and output PDF document			

        $pdf->Output($file_name . '.pdf', 'I');
    }

    public function download_excel($file_name, $report_data, $sdr_terms, $user_dp, $monthyear, $account_id, $format) {
        require_once dirname(__FILE__) . '/PHPExcel/Classes/PHPExcel.php';

        $date_time = date('d-m-Y h:i:s A', time());
        $stmt_gen_date = date('d-m-Y');


        $openingbalance = $addbalance = $removebalance = $usage = 0;
        $debit_sum = $credit_sum = 0;
        $top_html_str = $last_html_str = '';
        $amount_format = "%." . $user_dp . "f";
        if (count($report_data['result']) > 0) {
            $rowCount = 0;
            foreach ($report_data['result'] as $sdr_data) {
                $debit = $credit = '';
                $display_text = '';
                $rule_type = $sdr_data['rule_type'];



                if (isset($sdr_terms[$rule_type])) {
                    $term_array = $sdr_terms[$rule_type];

                    $term_group = $term_array['term_group'];
                    $display_text = $term_array['display_text'];
                    $cost_calculation_formula = trim($term_array['cost_calculation_formula']);

                    //$total_cost = number_format($sdr_data['total_cost'],$user_dp,'.','');
                    $total_cost = round($sdr_data['total_cost'], $user_dp);



                    if ($term_group == 'opening') {
                        if ($cost_calculation_formula == '+') {
                            $openingbalance = $openingbalance + $total_cost;
                            $credit = $total_cost;
                        } elseif ($cost_calculation_formula == '-') {
                            $openingbalance = $openingbalance - $total_cost;
                            $debit = $total_cost;
                        }
                    } elseif ($term_group == 'balance') {
                        if ($cost_calculation_formula == '+') {
                            $addbalance = $addbalance + $total_cost;
                            $credit = $total_cost;
                        } elseif ($cost_calculation_formula == '-') {
                            $removebalance = $removebalance + $total_cost;
                            $debit = $total_cost;
                        }
                    } else {//usage
                        if ($cost_calculation_formula == '+') {
                            $usage = $usage + $total_cost;
                            $credit = $total_cost;
                        } elseif ($cost_calculation_formula == '-') {
                            $usage = $usage + $total_cost;
                            $debit = $total_cost;
                        }
                    }
                }

                if ($display_text == '')
                    $display_text = $sdr_data['rule_type'];

                if ($sdr_data['service_number'] != '')
                    $display_text .= ' (' . $sdr_data['service_number'] . ')';

                /////////////
                if ($sdr_data['service_startdate'] != '' && $sdr_data['service_stopdate'] != '') {
                    $start_date_timestamp = strtotime($sdr_data['service_startdate']);
                    $start_date_display = date(DATE_FORMAT_1, $start_date_timestamp);

                    $stop_date_timestamp = strtotime($sdr_data['service_stopdate']);
                    $stop_date_display = date(DATE_FORMAT_1, $stop_date_timestamp);

                    $display_text .= ' for the period ' . $start_date_display . ' to ' . $stop_date_display;
                }
                ////////////	


                $action_date_timestamp = strtotime($sdr_data['action_date']);
                $action_date_display = date(DATE_FORMAT_1, $action_date_timestamp);

                //$sdr_data['service_startdate']							
                //$sdr_data['service_stopdate']

                $debit_sum += $debit;
                $credit_sum += $credit;

                if ($debit != '')
                    $debit = sprintf($amount_format, $debit);
                if ($credit != '')
                    $credit = sprintf($amount_format, $credit);



                $tr_html = '<tr><td style="width: 20%; text-align: left; padding:5px;border:1px solid #ccc;font-size:11px;line-height:16px;">' . $action_date_display . '</td>
												<td style="width: 50%; text-align: left; padding:5px;border:1px solid #ccc;font-size:11px;line-height:16px;">' . $display_text . '</td>                            
												<td style="width: 15%; text-align: right; padding:5px;border:1px solid #ccc;font-size:11px;line-height:16px;">' . $debit . '</td>
												<td style="width: 15%; text-align: right; padding:5px;border:1px solid #ccc;font-size:11px;line-height:16px;">' . $credit . '</td>
										</tr>';

                if ($cost_calculation_formula == '')
                    continue;

                if ($term_group == 'opening') {
                    $top_html_str .= $tr_html;
                } else {
                    $last_html_str .= $tr_html;
                }

                $rowCount++;
            } // end of foreach


            $current_balance = $openingbalance + $addbalance - $removebalance - $usage;

            $total_debit = sprintf($amount_format, $debit_sum);
            $total_credit = sprintf($amount_format, $credit_sum);

            $total_payment = sprintf($amount_format, $addbalance);

            $total_refund = sprintf($amount_format, $removebalance);


            $total_charges = sprintf($amount_format, $usage);


            $total_avail_bal = sprintf($amount_format, $current_balance);
        }// end of if 


        $com_address = '<tr><td colspan="4"></td></tr>
							<tr><td colspan="4"><strong>Web:</strong>' . base_url() . '</tr></td>';




        // ---------start excel html content------- //

        $excel_html = '';
        $excel_html .= '<table border="1">
						
						<tr>
							<td colspan="4" rowspan="4"></td>
						</tr>
						
						
						<tr>
							<td rowspan="2" colspan="4"></td>
						</tr>
						<tr>
							<td rowspan="2" colspan="4"></td>
						</tr>
						
						
						
						<tr >
							<td >
								  <tr><td colspan="4"><strong>Account No </strong> : ' . $account_id . '</td></tr>						 
								  <tr><td colspan="4"><strong>Generated Date </strong> : ' . $stmt_gen_date . '</td></tr> 
								<tr><td colspan="4"><strong>Account Statements (' . $monthyear . ')</strong></td></tr> 								  
							</td>
						</tr>
								
						
					</table>';
        $excel_html .= '<table>								
								
								  <tr>
									<td style="width: 20%; text-align: left; padding:5px;border:1px solid #ccc;font-size:11px;line-height:16px;background-color:#ccc;"><strong>Activity Date</strong></td>
									<td style="width: 50%; text-align: left; padding:5px;border:1px solid #ccc;font-size:11px;line-height:16px;background-color:#ccc;"><strong>Activity</strong></td>
									<td style="width: 15%; text-align: right; padding:5px;border:1px solid #ccc;font-size:11px;line-height:16px;background-color:#ccc;"><strong>Debit</strong></td>
									<td style="width: 15%; text-align: right; padding:5px;border:1px solid #ccc;font-size:11px;line-height:16px;background-color:#ccc;"><strong>Credit</strong></td>
								  </tr>	

									' . $top_html_str . '
									' . $last_html_str . '									

								  
								   <tr>
									<td ></td>
									<td ></td>
									<td ></td>
									<td ></td>
								  </tr>
								 
								   <tr>
										<td></td>
										<td align="right"><strong>Total</strong></td>
										<td style="text-align: right;"> <strong>' . $total_debit . '</strong> </td>
										<td style="text-align: right;"> <strong>' . $total_credit . '</strong> </td>
										
									</tr>
								
								 <tr>
									<td ></td>
									<td ></td>
									<td ></td>
									<td ></td>
								  </tr>
								
								<tr>
										<td></td>
										<td align="right"><strong>Total Payment</strong> </td>
										<td> </td>
										<td>' . $total_payment . '</td>
										
								</tr>
								<tr>
										<td></td>
										<td align="right"><strong>Total Refund</strong> </td>
										<td> </td>
										<td>' . $total_refund . '</td>
										
								</tr>
								<tr>
										<td ></td>
										<td align="right"><strong>Total Charges</strong></td>
										<td> </td>
										<td>' . $total_charges . '</td>
										
								</tr>
								
								<tr>
										<td></td>
										<td align="right"><strong>Total Available Balance</strong>  </td>
										<td></td>
										<td>' . $total_avail_bal . '</td>
										
								</tr>
								
								<tr>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										
								</tr>
								<tr>
									<tr><td align="center" colspan="4">** This is System Generated Report based on ' . $date_time . ' available data.</td></tr>
									' . $com_address . ' ' . $contact_info . '									
								</tr>
								
											
						</table>';

//////////////////////////////////////////////////////////


        $objPHPExcel = new PHPExcel();

        $gdImage = imagecreatefromjpeg(base_url() . '/theme/default/images/logo.png');

        $objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
        $objDrawing->setName('Logo Image');
        $objDrawing->setDescription('Logo Image');
        $objDrawing->setImageResource($gdImage);
        $objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
        $objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
        $objDrawing->setWidth(200);
        $objDrawing->setHeight(80);
        $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
        $objDrawing->setCoordinates('A1');

        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $styleArray = array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        );


        $total_row = ($rowCount + 18);

        $cell_index = 'A1:' . 'D' . $total_row;
        $objPHPExcel->getActiveSheet()->getStyle($cell_index)->applyFromArray($styleArray);

        unset($styleArray);


        $objPHPExcel->getActiveSheet()->getStyle("A5:A7")->getFont()->setBold(true)
                ->setName('Verdana')
                ->setSize(10);

        $objPHPExcel->getActiveSheet()->getStyle('A5:A7')->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $address_loc = ($total_row + 3);
        $cell_address = 'A' . $address_loc . ':' . 'A' . ($address_loc + 7);
        $objPHPExcel->getActiveSheet()->getStyle($cell_address)->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);




        $objPHPExcel->getActiveSheet()->getStyle("A9")->getFont()->setBold(true)
                ->setName('Verdana')
                ->setSize(10);



        $objPHPExcel->getActiveSheet()->getStyle("B9")->getFont()->setBold(true)
                ->setName('Verdana')
                ->setSize(10);



        $objPHPExcel->getActiveSheet()->getStyle("C9")->getFont()->setBold(true)
                ->setName('Verdana')
                ->setSize(10);
        $objPHPExcel->getActiveSheet()->getStyle("C9")->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

        $objPHPExcel->getActiveSheet()->getStyle("D9")->getFont()->setBold(true)
                ->setName('Verdana')
                ->setSize(10);
        $objPHPExcel->getActiveSheet()->getStyle("D9")->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

        // Total Debit and Credit customize          
        $total = ($total_row - 7);
        $cell_total = 'B' . $total;
        $cell_dt_val = 'C' . $total;
        $cell_cr_val = 'D' . $total;
        $objPHPExcel->getActiveSheet()->getStyle($cell_total)->getFont()->setBold(true)
                ->setName('Verdana')
                ->setSize(10);
        $objPHPExcel->getActiveSheet()->getStyle($cell_dt_val)->getFont()->setBold(true)
                ->setName('Verdana')
                ->setSize(10);

        $objPHPExcel->getActiveSheet()->getStyle($cell_cr_val)->getFont()->setBold(true)
                ->setName('Verdana')
                ->setSize(10);

        $objPHPExcel->getActiveSheet()->getStyle($cell_total)->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);


// total payment customize
        $total_payment = ($total_row - 5);
        $cell_total_payment = 'B' . $total_payment;
        $cell_total_payment_val = 'D' . $total_payment;
        $objPHPExcel->getActiveSheet()->getStyle($cell_total_payment)->getFont()->setBold(true)
                ->setName('Verdana')
                ->setSize(10);
        $objPHPExcel->getActiveSheet()->getStyle($cell_total_payment_val)->getFont()->setBold(true)
                ->setName('Verdana')
                ->setSize(10);



        $objPHPExcel->getActiveSheet()->getStyle($cell_total_payment)->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
// total refund customize
        $total_refund = ($total_row - 4);
        $cell_total_refund_txt = 'B' . $total_refund;
        $cell_total_refund_val = 'D' . $total_refund;
        $objPHPExcel->getActiveSheet()->getStyle($cell_total_refund_txt)->getFont()->setBold(true)
                ->setName('Verdana')
                ->setSize(10);
        $objPHPExcel->getActiveSheet()->getStyle($cell_total_refund_val)->getFont()->setBold(true)
                ->setName('Verdana')
                ->setSize(10);

        $objPHPExcel->getActiveSheet()->getStyle($cell_total_refund_txt)->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

// Total Charges customize
        $total_charges = ($total_row - 3);
        $cell_total_charges_txt = 'B' . $total_charges;
        $cell_total_charges_val = 'D' . $total_charges;
        $objPHPExcel->getActiveSheet()->getStyle($cell_total_charges_txt)->getFont()->setBold(true)
                ->setName('Verdana')
                ->setSize(10);
        $objPHPExcel->getActiveSheet()->getStyle($cell_total_charges_val)->getFont()->setBold(true)
                ->setName('Verdana')
                ->setSize(10);

        $objPHPExcel->getActiveSheet()->getStyle($cell_total_charges_txt)->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
// Total Available Balance customize
        $total_available_balance = ($total_row - 2);
        $cell_total_available_balance_txt = 'B' . $total_available_balance;
        $cell_total_available_balance_val = 'D' . $total_available_balance;
        $objPHPExcel->getActiveSheet()->getStyle($cell_total_available_balance_txt)->getFont()->setBold(true)
                ->setName('Verdana')
                ->setSize(10);
        $objPHPExcel->getActiveSheet()->getStyle($cell_total_available_balance_val)->getFont()->setBold(true)
                ->setName('Verdana')
                ->setSize(10);

        $objPHPExcel->getActiveSheet()->getStyle($cell_total_available_balance_txt)->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

        //die($excel_html);
        $tmpfile = tempnam(sys_get_temp_dir(), 'html');
        file_put_contents($tmpfile, $excel_html);

        $excelHTMLReader = PHPExcel_IOFactory::createReader('HTML');
        $excelHTMLReader->loadIntoExisting($tmpfile, $objPHPExcel);

        $objPHPExcel->getActiveSheet()->setTitle('Account Statements'); // Change sheet's title if you want
        unlink($tmpfile); // delete temporary file because it isn't needed anymore
        /////set auto width/
        foreach (range('A', $objPHPExcel->getActiveSheet()->getHighestDataColumn()) as $col) {
            $objPHPExcel->getActiveSheet()
                    ->getColumnDimension($col)
                    ->setAutoSize(true);
        }
        //header('Content-type: application/excel');
        header('Content-Disposition: attachment;filename="' . $file_name . '.' . $format . '"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        if ($format == 'xlsx') {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        }


        $objWriter->save('php://output');
        die;
    }

}
