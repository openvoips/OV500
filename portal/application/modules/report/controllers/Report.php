<?php
 
defined('BASEPATH') OR exit('No direct script access allowed');

class Report extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->library('pagination');
        $this->form_validation->set_error_delimiters('', '');
        $this->load->model('reports_mod');

        if (!check_is_loggedin())
            redirect(base_url(), 'refresh');
    }

    function index() {

        $page_name = "report_dashboard";
        $search_session_key = 'search_' . $page_name;
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        if (!check_logged_user_group(array('SYSTEM', 'RESELLER'))) {
            redirect(site_url('report/myinvoice'));
        }

        $this->load->view('basic/header', $data);
        $this->load->view('report/dashboard', $data);
        $this->load->view('basic/footer', $data);
    }
	
	

    function clientprofitdetails() {
        $this->load->model('detail_mod');
        $data = array();
        $page_name = "clientprofitdetails_list";
        $search_session_key = 'search_' . $page_name;
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (!check_logged_user_group(array('SYSTEM', 'RESELLER'))) {
            show_404('403');
        }

        $search_parameters = array('clienttime', 'company_name', 'account_id', 'no_of_rows');

        if (isset($_POST['search_action'])) {
            set_post_to_session($search_session_key, $search_parameters);
        } else {
            set_session_to_session($search_session_key, $search_parameters);
        }

        if ($_SESSION[$search_session_key]['clienttime'] == '') {
            $yesterday_timestamp = strtotime("yesterday");
            $yesterday = date('Y-m-d', $yesterday_timestamp);
            $time_range = $yesterday . ' 00:00:00 - ' . $yesterday . ' 23:59:59';
            $_SESSION[$search_session_key]['clienttime'] = $time_range;
        }


        $search_data = array(
            'clienttime' => $_SESSION[$search_session_key]['clienttime'],
            'account_id' => $_SESSION[$search_session_key]['account_id'],
            'company_name' => $_SESSION[$search_session_key]['company_name'],
            'logged_customer_type' => get_logged_account_type(),
            'logged_customer_level' => get_logged_account_level(),
            'logged_customer_account_id' => get_logged_account_id(),
        );
        $is_file_downloaded = false;
        if ($is_file_downloaded === false) {
            $pagination_uri_segment = 3;
            list($per_page, $segment) = get_pagination_param($pagination_uri_segment, $search_session_key);

            $response = $this->detail_mod->get_client_profitloss_data('', $per_page, $segment, $search_data);

            $total_count = $this->detail_mod->get_data_total_count();
            $data['pagination'] = setup_pagination_option($total_count, 'report/clientprofitdetails', $per_page, $pagination_uri_segment, $this->pagination);

            $data['listing_data'] = $response;
            $data['total_records'] = $total_count;
            $data['search_session_key'] = $search_session_key;

            $this->load->view('basic/header', $data);
            $this->load->view('report/clientprofitdetails', $data);
            $this->load->view('basic/footer', $data);
        }
    }

    function resellerprofitdetails() {
        $this->load->model('detail_mod');
        $data = Array();
        $page_name = "resellerprofitdetails_list";
        $search_session_key = 'search_' . $page_name;
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (!check_logged_user_group(array('SYSTEM', 'RESELLER'))) {
            show_404('403');
        }

        $search_parameters = array('resellertime', 'company_name', 'account_id', 'no_of_rows');

        if (isset($_POST['search_action'])) {
            set_post_to_session($search_session_key, $search_parameters);
        } else {
            set_session_to_session($search_session_key, $search_parameters);
        }

        if ($_SESSION[$search_session_key]['resellertime'] == '') {
            $yesterday_timestamp = strtotime("yesterday");
            $yesterday = date('Y-m-d', $yesterday_timestamp);
            $time_range = $yesterday . ' 00:00:00 - ' . $yesterday . ' 23:59:59';
            $_SESSION[$search_session_key]['resellertime'] = $time_range;
        }

        $search_data = array(
            'resellertime' => $_SESSION[$search_session_key]['resellertime'],
            'account_id' => $_SESSION[$search_session_key]['account_id'],
            'company_name' => $_SESSION[$search_session_key]['company_name'],
            'logged_customer_type' => get_logged_account_type(),
            'logged_customer_level' => get_logged_account_level(),
            'logged_customer_account_id' => get_logged_account_id(),
        );
        $is_file_downloaded = false;
        if ($is_file_downloaded === false) {
            $pagination_uri_segment = 3;
            list($per_page, $segment) = get_pagination_param($pagination_uri_segment, $search_session_key);

            $response = $this->detail_mod->get_reseller_profitloss_data('', $per_page, $segment, $search_data);

            $total_count = $this->detail_mod->get_data_total_count();
            $data['pagination'] = setup_pagination_option($total_count, 'report/resellerprofitdetails', $per_page, $pagination_uri_segment, $this->pagination);

            $data['listing_data'] = $response;
            $data['total_records'] = $total_count;
            $data['search_session_key'] = $search_session_key;

            $this->load->view('basic/header', $data);
            $this->load->view('report/resellerprofitdetails', $data);
            $this->load->view('basic/footer', $data);
        }
    }

    //working
    function servicedetails() {
        $this->load->model('detail_mod');
        $data = array();
        $page_name = "servicedetails_list";
        $search_session_key = 'search_' . $page_name;
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (!check_logged_user_group(array('SYSTEM', 'RESELLER'))) {
            show_404('403');
        }

        $search_parameters = array('account_id', 'service_name', 'no_of_rows');

        if (isset($_POST['search_action'])) {
            set_post_to_session($search_session_key, $search_parameters);
        } else {
            set_session_to_session($search_session_key, $search_parameters);
        }
		
		 

        $search_data = array(
            'account_id' => trim($_SESSION[$search_session_key]['account_id']),
            'service_name' => trim($_SESSION[$search_session_key]['service_name']),
            'logged_customer_type' => get_logged_account_type(),
            'logged_customer_level' => get_logged_account_level(),
            'logged_customer_account_id' => get_logged_account_id(),
        );
        $is_file_downloaded = false;
        if ($is_file_downloaded === false) {
            $pagination_uri_segment = 3;
            list($per_page, $segment) = get_pagination_param($pagination_uri_segment, $search_session_key);

            $response = $this->detail_mod->get_service_data('', $per_page, $segment, $search_data);

            $total_count = $this->detail_mod->get_data_total_count();
            $data['pagination'] = setup_pagination_option($total_count, 'report/servicedetails', $per_page, $pagination_uri_segment, $this->pagination);

            $data['listing_data'] = $response;
            $data['total_records'] = $total_count;
            $data['search_session_key'] = $search_session_key;

            $this->load->view('basic/header', $data);
            $this->load->view('report/servicedetails', $data);
            $this->load->view('basic/footer', $data);
        }
    }

    function providerprofitdetails() {
        $this->load->model('detail_mod');
        $data = array();
        $page_name = "providerprofitdetails_list";
        $search_session_key = 'search_' . $page_name;
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (!check_logged_user_group(array('SYSTEM'))) {
            show_404('403');
        }
        $search_parameters = array('providertime', 'carrier_name', 'carrier_id', 'no_of_rows');

        if (isset($_POST['search_action'])) {
            set_post_to_session($search_session_key, $search_parameters);
        } else {
            set_session_to_session($search_session_key, $search_parameters);
        }
		
		if ($_SESSION[$search_session_key]['providertime'] == '') {
            $yesterday_timestamp = strtotime("yesterday");
            $yesterday = date('Y-m-d', $yesterday_timestamp);
            $time_range = $yesterday . ' 00:00:00 - ' . $yesterday . ' 23:59:59';
            $_SESSION[$search_session_key]['providertime'] = $time_range;
        }

        $search_data = array(
            'providertime' => $_SESSION[$search_session_key]['providertime'],
            'carrier_id' => $_SESSION[$search_session_key]['carrier_id'],
            'carrier_name' => $_SESSION[$search_session_key]['carrier_name'],
        );
        $is_file_downloaded = false;
        if ($is_file_downloaded === false) {
            $pagination_uri_segment = 3;
            list($per_page, $segment) = get_pagination_param($pagination_uri_segment, $search_session_key);

            $response = $this->detail_mod->get_provider_profitloss_data('', $per_page, $segment, $search_data);

            $total_count = $this->detail_mod->get_data_total_count();
            $data['pagination'] = setup_pagination_option($total_count, 'report/providerprofitdetails', $per_page, $pagination_uri_segment, $this->pagination);

            $data['listing_data'] = $response;
            $data['total_records'] = $total_count;
            $data['search_session_key'] = $search_session_key;

            $this->load->view('basic/header', $data);
            $this->load->view('report/providerprofitdetails', $data);
            $this->load->view('basic/footer', $data);
        }
    }
    function ajax_clientprofit() {

        if ($_POST['search_action'] == 'searchcustomer') {
            $search_data = array(
                'clienttime' => $_POST['clienttime'],
                'logged_customer_type' => get_logged_account_type(),
                'logged_customer_level' => get_logged_account_level(),
                'logged_customer_account_id' => get_logged_account_id(),
            );
            $client_data = $this->reports_mod->get_client_profitloss_data($search_data);
            $str = '';
            if (isset($client_data['result']) && count($client_data['result']) > 0) {
                foreach ($client_data['result'] as $row) {
                    $str .= '<tr><td>' . $row['cname'] . '</td><td>' . $row['total_customer'] . '</td><td class="text-right">' . number_format($row['profit'], 2, '.', '') . '</td></tr>';
                }
            } else {
                $str .= '<tr><td colspan="3" align="center"><strong>No Record</strong></td></tr>';
            }
            //$str .='<tr><td colspan="3" align="center">'.$this->reports_mod->sql.'</td></tr>';
            echo $str;
        }
    }

    /////
    function ajax_resellerprofit() {
        if ($_POST['search_action'] == 'searchreseller') {
            $search_data = array(
                'resellertime' => $_POST['resellertime'],
                'logged_customer_type' => get_logged_account_type(),
                'logged_customer_level' => get_logged_account_level(),
                'logged_customer_account_id' => get_logged_account_id(),
            );
            $client_data = $this->reports_mod->get_reseller_profitloss_data($search_data);
            $str = '';
            if (isset($client_data['result']) && count($client_data['result']) > 0) {
                foreach ($client_data['result'] as $row) {
                    $str .= '<tr><td>' . $row['cname'] . '</td><td>' . $row['total_customer'] . '</td><td class="text-right">' . number_format($row['profit'], 2, '.', '') . '</td></tr>';
                }
            } else {
                $str .= '<tr><td colspan="3" align="center"><strong>No Record</strong></td></tr>';
            }
            //$str .='<tr><td colspan="3" align="center">'.$this->reports_mod->reseller_sql.'</td></tr>';
            echo $str;
        }
    }

    function ajax_providerprofit() {

        if ($_POST['search_action'] == 'searchprovider') {
            $search_data = array(
                'providertime' => $_POST['providertime'],
                'logged_customer_type' => get_logged_account_type(),
                'logged_customer_level' => get_logged_account_level(),
                'logged_customer_account_id' => get_logged_account_id(),
            );
            $client_data = $this->reports_mod->get_provider_profitloss_data($search_data);
            $str = '';
            if (isset($client_data['result']) && count($client_data['result']) > 0) {
                foreach ($client_data['result'] as $row) {
                    $str .= '<tr><td>' . $row['cname'] . '</td><td>' . $row['total_customer'] . '</td><td class="text-right">' . number_format($row['total'], 2, '.', '') . '</td></tr>';
                }
            } else {
                $str .= '<tr><td colspan="3" align="center"><strong>No Record</strong></td></tr>';
            }
            //$str .='<tr><td colspan="3" align="center">'.$this->reports_mod->provider_sql.'</td></tr>';
            echo $str;
        }
    }

    function ajax_activeservices() {
        $page_name = "report_dashboard";
        $search_session_key = 'search_' . $page_name;
        $data['page_name'] = $page_name;
        if ($_POST['search_action'] == 'search') {
            $search_data = array(
                'logged_customer_type' => get_logged_account_type(),
                'logged_customer_level' => get_logged_account_level(),
                'logged_customer_account_id' => get_logged_account_id(),
            );
            $services_data = $this->reports_mod->get_active_services_data($search_data);
            $str = '';
            $str .= $services_data['serices_count'];
            echo $str;
            //echo '<br>'.$this->reports_mod->service_sql;
            // echo json_encode($services_data);
        }
    }

    function paymenthistory() {
        $this->load->model('detail_mod');
        $page_name = "report_paymenthistory";
        $search_session_key = 'search_' . $page_name;

        $this->load->model('payment_mod');
        $this->load->library('pagination'); // pagination class		
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (!check_logged_user_group(array('SYSTEM', 'RESELLER'))) {
            show_404('403');
        }

        $logged_account_id = get_logged_account_id();

        $search_parameters = array('pay_date', 'company_name', 'account_id', 'payment_type', 'no_of_rows');

        if (isset($_POST['search_action'])) {
            set_post_to_session($search_session_key, $search_parameters);
        } else {
            set_session_to_session($search_session_key, $search_parameters);
        }

        if ($_SESSION[$search_session_key]['pay_date'] == '') {
            $today_timestamp = strtotime("today");
            $today = date('Y-m-d', $today_timestamp);
            $time_range = $today . ' 00:00 - ' . $today . ' 23:59';
            $_SESSION[$search_session_key]['pay_date'] = $time_range;
        }

        $search_data = array(
            'time_range' => $_SESSION[$search_session_key]['pay_date'],
            'account_id' => $_SESSION[$search_session_key]['account_id'],
            'company_name' => $_SESSION[$search_session_key]['company_name'],
            'payment_type' => $_SESSION[$search_session_key]['payment_type'],
            'logged_customer_type' => get_logged_account_type(),
            'logged_customer_level' => get_logged_account_level(),
            'logged_customer_account_id' => get_logged_account_id(),);

        if (check_logged_user_type(array('ACCOUNTMANAGER')))
            $search_data['account_manager'] = $logged_account_id;
        elseif (check_logged_user_type(array('SALESMANAGER')))
            $search_data['sales_manager'] = $logged_account_id;
        elseif (check_logged_user_type(array('RESELLER'))) {
            // $report_search_data['parent_account_id'] = $logged_account_id;    
        } {
            $pagination_uri_segment = 3;
            list($per_page, $segment) = get_pagination_param($pagination_uri_segment, $search_session_key);

            $response = $this->detail_mod->paymenthistory('', $per_page, $segment, $search_data);

            $total_count = $this->detail_mod->get_data_total_count();
            $data['pagination'] = setup_pagination_option($total_count, 'report/paymenthistory', $per_page, $pagination_uri_segment, $this->pagination);

            $data['listing_data'] = $response;
            $data['total_records'] = $total_count;
            $data['search_session_key'] = $search_session_key;

            $this->load->view('basic/header', $data);
            $this->load->view('report/paymenthistory', $data);
            $this->load->view('basic/footer', $data);
        }
    }

    function salessummary($arg1 = '') {
        $this->load->model('detail_mod');
        $data = array();
        $page_name = "sales_summary";
        $search_session_key = 'search_' . $page_name;
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (!check_logged_user_group(array('SYSTEM', 'RESELLER'))) {
            show_404('403');
        }

        $search_parameters = array('clienttime', 'company_name', 'account_id', 'no_of_rows');

        if (isset($_POST['search_action'])) {
            set_post_to_session($search_session_key, $search_parameters);
        } else {
            set_session_to_session($search_session_key, $search_parameters);
        }

        if ($_SESSION[$search_session_key]['clienttime'] == '') {
            $yesterday_timestamp = strtotime("first day of this month");
            $yesterday = date('Y-m-d', $yesterday_timestamp);
            $today = date('Y-m-d');
            $time_range = $yesterday . ' - ' . $today; // . ' 23:59:59';
            $_SESSION[$search_session_key]['clienttime'] = $time_range;
        }


        $search_data = array(
            'clienttime' => $_SESSION[$search_session_key]['clienttime'],
            'account_id' => $_SESSION[$search_session_key]['account_id'],
            'company_name' => $_SESSION[$search_session_key]['company_name'],
            'logged_customer_type' => get_logged_account_type(),
            'logged_customer_level' => get_logged_account_level(),
            'logged_customer_account_id' => get_logged_account_id(),
        );
        $is_file_downloaded = false;
        if ($arg1 == 'export') {

            $listing_data = $this->detail_mod->get_sales_summary_data('', '', '', $search_data);


            require_once APPPATH . 'libraries/PHPExcel/Classes/PHPExcel.php'; //die("SS");
            $objPHPExcel = new PHPExcel();

            /////header row
            $row = 1;
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A' . $row, "Consolidated Report (" . $_SESSION[$search_session_key]['clienttime'] . ")");

            $objPHPExcel->getActiveSheet()->getStyle('A' . $row)
                    ->getFont()
                    ->setBold(true)
                    ->setSize(14)
                    ->getColor()->setRGB('405467');
            //merge cells
            $objPHPExcel->getActiveSheet()->mergeCells('A1:E1');
            //align center
            $style = array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                )
            );
            $objPHPExcel->getActiveSheet()->getStyle("A1:B1")->applyFromArray($style);


            $row++;
            $row++;
            ///////table header row
            $table_header_row = array('Client Name', 'Total Cost excl.', 'Total Sell excl.', 'Profit excl.', 'Currency');
            $total_sum_array = array();
            $cost_sum = $sell_cost = $profit_sum = 0;


            $objPHPExcel->getActiveSheet()->fromArray($table_header_row, NULL, 'A' . $row);
            $objPHPExcel->getActiveSheet()->getStyle("A{$row}:E{$row}")->getFont()->setBold(true);


            if ($listing_data['result'] > 0) {
                foreach ($listing_data['result'] as $listing_row) {

                    $acc = '';
                    if (!empty($listing_row['account_id'])) {
                        if (!empty($listing_row['company_name'])) {
                            $acc = $listing_row['company_name'] . ' (' . $listing_row['account_id'] . ')';
                        }
                    }
                    $buy_cost = number_format($listing_row['buy_cost'], 2, '.', '');
                    $total_cost = number_format($listing_row['total_cost'], 2, '.', '');
                    $profit = number_format($listing_row['profit'], 2, '.', '');
                    $currency = $listing_row['cname'];
                    $result_row = array($acc, $buy_cost, $total_cost, $profit, $currency);

                    $row++;
                    $objPHPExcel->getActiveSheet()->fromArray($result_row, NULL, 'A' . $row);



                    /* if(!isset($total_sum_array[$currency]))
                      {
                      $total_sum_array[$currency] = array('Grand Totals excl.', 0.00, 0.00, 0.00, $currency);
                      }

                      $total_sum_array[$currency][1] +=$listing_row['buy_cost'];
                      $total_sum_array[$currency][2] +=$listing_row['total_cost'];
                      $total_sum_array[$currency][3] +=$listing_row['profit']; */
                }
            }



            $row++;
            $row++;
            foreach ($listing_data['sum_result'] as $total_row_temp) {
                $total_row = array('Grand Totals excl.',
                    number_format($total_row_temp['buy_cost'], 2, '.', ''),
                    number_format($total_row_temp['total_cost'], 2, '.', ''),
                    number_format($total_row_temp['profit'], 2, '.', ''),
                    $total_row_temp['cname']);
                $objPHPExcel->getActiveSheet()->fromArray($total_row, NULL, 'A' . $row);
                $objPHPExcel->getActiveSheet()->getStyle("A{$row}:E{$row}")->getFont()->setBold(true);
                $row++;
            }
            /* foreach($total_sum_array as $total_row_temp)
              {
              $total_row = array($total_row_temp[0],
              number_format($total_row_temp[1],2,'.',''),
              number_format($total_row_temp[2],2,'.',''),
              number_format($total_row_temp[3],2,'.',''),
              $total_row_temp[4]);
              $objPHPExcel->getActiveSheet()->fromArray($total_row, NULL, 'A'. $row);
              $objPHPExcel->getActiveSheet()->getStyle("A{$row}:E{$row}")->getFont()->setBold( true );
              $row++;
              } */

            //$objPHPExcel->getActiveSheet()->getStyle ('G')->getNumberFormat()->setFormatCode ("0.00");
            //PExcel->getActiveSheet()->SetCellValue($letter2.$row, number_format($input['amount'],2,'.',''));
            //////
            for ($i = 1; $i <= $row; $i ++) {
                $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(25);
            }
            /////set auto width/
            foreach (range('A', $objPHPExcel->getActiveSheet()->getHighestDataColumn()) as $col) {
                $objPHPExcel->getActiveSheet()
                        ->getColumnDimension($col)
                        ->setAutoSize(true);
            }
            //////
            $rand = rand(1, 1000);
            $path = FCPATH . 'uploads/rand' . $rand . '.xlsx';

            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
            $objWriter->save($path);

            $file = $path;
            ob_end_clean(); // this is solution
            header('Content-Description: File Transfer');
            header('Content-Type: ' . $mime);
            header("Content-Transfer-Encoding: Binary");
            header("Content-disposition: attachment; filename=\"" . basename($file) . "\"");
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            readfile($file);






            $is_file_downloaded = true;
        }


        if ($is_file_downloaded === false) {
            $pagination_uri_segment = 3;
            list($per_page, $segment) = get_pagination_param($pagination_uri_segment, $search_session_key);

            $response = $this->detail_mod->get_sales_summary_data('', $per_page, $segment, $search_data);

            $total_count = $this->detail_mod->get_data_total_count();
            $data['pagination'] = setup_pagination_option($total_count, 'report/salessummary', $per_page, $pagination_uri_segment, $this->pagination);

            $data['listing_data'] = $response;
            $data['total_records'] = $total_count;
            $data['search_session_key'] = $search_session_key;

            $this->load->view('basic/header', $data);
            $this->load->view('report/sales_summary', $data);
            $this->load->view('basic/footer', $data);
        }
    }

    function salesdetails($arg1 = '') {
        $this->load->model('detail_mod');
        $data = array();
        $page_name = "sales_details";
        $search_session_key = 'search_' . $page_name;
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (!check_logged_user_group(array('SYSTEM', 'RESELLER'))) {
            show_404('403');
        }

        $search_parameters = array('clienttime', 'company_name', 'account_id', 'no_of_rows');

        if (isset($_POST['search_action'])) {
            set_post_to_session($search_session_key, $search_parameters);
        } else {
            set_session_to_session($search_session_key, $search_parameters);
        }

        if ($_SESSION[$search_session_key]['clienttime'] == '') {
            $yesterday_timestamp = strtotime("first day of this month");
            $yesterday = date('Y-m-d', $yesterday_timestamp);
            $today = date('Y-m-d');
            $time_range = $yesterday . ' - ' . $today; // . ' 23:59:59';
            $_SESSION[$search_session_key]['clienttime'] = $time_range;
        }

        $search_data = array(
            'clienttime' => $_SESSION[$search_session_key]['clienttime'],
            'account_id' => $_SESSION[$search_session_key]['account_id'],
            'company_name' => $_SESSION[$search_session_key]['company_name'],
            'logged_customer_type' => get_logged_account_type(),
            'logged_customer_level' => get_logged_account_level(),
            'logged_customer_account_id' => get_logged_account_id(),
        );
        $is_file_downloaded = false;
        if ($arg1 == 'export') {

            $listing_data = $this->detail_mod->get_sales_details_data('', '', '', $search_data);


            require_once APPPATH . 'libraries/PHPExcel/Classes/PHPExcel.php'; //die("SS");
            $objPHPExcel = new PHPExcel();

            /////header row
            $row = 1;
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A' . $row, "Sales Details Report(" . $_SESSION[$search_session_key]['clienttime'] . ")");

            $objPHPExcel->getActiveSheet()->getStyle('A' . $row)
                    ->getFont()
                    ->setBold(true)
                    ->setSize(14)->getColor()->setRGB('405467');
            //merge cells
            $objPHPExcel->getActiveSheet()->mergeCells('A1:E1');
            //align center
            $style = array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                )
            );
            $objPHPExcel->getActiveSheet()->getStyle("A1:B1")->applyFromArray($style);


            $row++;
            $row++;
            ///////table header row
            $table_header_row = array('', 'Item Name', 'Units', 'Total Cost excl.', 'Total Sell excl.', 'Profit excl.', 'Currency');
            $total_sum_array = array();
            $cost_sum = $sell_cost = $profit_sum = 0;





            if ($listing_data['result'] > 0) {
                $previous_account_id = '';
                $buy_cost_sum = $total_cost_sum = $profit_sum = 0;
                $k = 0;
                foreach ($listing_data['result'] as $listing_row) {
                    $current_account_id = $listing_row['account_id'];

                    $acc = '';
                    if (!empty($listing_row['account_id'])) {
                        if (!empty($listing_row['company_name'])) {
                            $acc = $listing_row['company_name'] . ' (' . $listing_row['account_id'] . ')';
                        }
                    }
                    $buy_cost = $listing_row['buy_cost'];
                    $total_cost = $listing_row['total_cost'];
                    $profit = $listing_row['profit'];

                    if ($previous_account_id != $current_account_id) {
                        if (isset($sum_array[$previous_account_id])) {//display total row
                            $sum_row = array($sum_array[$previous_account_id]['company_name'] . ' Total:', '', '', number_format($sum_array[$previous_account_id]['buy_cost_sum'], 2, '.', ''), number_format($sum_array[$previous_account_id]['total_cost_sum'], 2, '.', ''), number_format($sum_array[$previous_account_id]['profit_sum'], 2, '.', ''), $sum_array[$previous_account_id]['cname']);
                            $row++;
                            $objPHPExcel->getActiveSheet()->fromArray($sum_row, NULL, 'A' . $row);
                            $objPHPExcel->getActiveSheet()->getStyle("A{$row}:G{$row}")->getFont()->setBold(true)->setSize(12);
                            $row++;
                        }

                        $row++; //$row++;	
                        //display company name starting
                        $objPHPExcel->getActiveSheet()->fromArray(array($acc), NULL, 'A' . $row);
                        $objPHPExcel->getActiveSheet()->getStyle("A{$row}:G{$row}")->getFont()->setBold(true)->setSize(13);
                        $objPHPExcel->getActiveSheet()->mergeCells("A{$row}:G{$row}");
                        $row++;
                        //display column header
                        $objPHPExcel->getActiveSheet()->fromArray($table_header_row, NULL, 'A' . $row);
                        $objPHPExcel->getActiveSheet()->getStyle("A{$row}:G{$row}")->getFont()->setBold(true);
                        //	$row++;
                    } {
                        if (!isset($sum_array[$current_account_id]['buy_cost_sum'])) {
                            $sum_array[$current_account_id]['buy_cost_sum'] = 0;
                            $sum_array[$current_account_id]['total_cost_sum'] = 0;
                            $sum_array[$current_account_id]['profit_sum'] = 0;
                        }

                        $sum_array[$current_account_id]['buy_cost_sum'] += $buy_cost;
                        $sum_array[$current_account_id]['total_cost_sum'] += $total_cost;
                        $sum_array[$current_account_id]['profit_sum'] += $profit;
                        $sum_array[$current_account_id]['cname'] = $listing_row['cname'];
                        $sum_array[$current_account_id]['company_name'] = $listing_row['company_name'];


                        //display detail breakup row
                        $detail_row = array('', $listing_row['display_text'], $listing_row['quantity'], number_format($listing_row['buy_cost'], 2, '.', ''), number_format($listing_row['total_cost'], 2, '.', ''), number_format($listing_row['profit'], 2, '.', ''));

                        $row++;
                        $objPHPExcel->getActiveSheet()->fromArray($detail_row, NULL, 'A' . $row);
                    }




                    $previous_account_id = $current_account_id;
                    $k++;
                }

                if (isset($sum_array[$previous_account_id])) {//display total last total row
                    $sum_row = array($sum_array[$previous_account_id]['company_name'] . ' Total:', '', '', number_format($sum_array[$previous_account_id]['buy_cost_sum'], 2, '.', ''), number_format($sum_array[$previous_account_id]['total_cost_sum'], 2, '.', ''), number_format($sum_array[$previous_account_id]['profit_sum'], 2, '.', ''), $sum_array[$previous_account_id]['cname']);
                    $row++;
                    $objPHPExcel->getActiveSheet()->fromArray($sum_row, NULL, 'A' . $row);
                    $objPHPExcel->getActiveSheet()->getStyle("A{$row}:G{$row}")->getFont()->setBold(true)->setSize(12);
                    $row++;
                }
            }



            $row++;
            $row++;

            //$objPHPExcel->getActiveSheet()->getStyle ('G')->getNumberFormat()->setFormatCode ("0.00");
            //PExcel->getActiveSheet()->SetCellValue($letter2.$row, number_format($input['amount'],2,'.',''));
            //////
            for ($i = 1; $i <= $row; $i ++) {
                $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(25);
            }
            /////set auto width/
            foreach (range('A', $objPHPExcel->getActiveSheet()->getHighestDataColumn()) as $col) {
                $objPHPExcel->getActiveSheet()
                        ->getColumnDimension($col)
                        ->setAutoSize(true);
            }
            //////
            $rand = rand(1, 1000);
            $path = FCPATH . 'uploads/rand' . $rand . '.xlsx';

            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
            $objWriter->save($path);

            $file = $path;
            ob_end_clean(); // this is solution
            header('Content-Description: File Transfer');
            header('Content-Type: ' . $mime);
            header("Content-Transfer-Encoding: Binary");
            header("Content-disposition: attachment; filename=\"" . basename($file) . "\"");
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            readfile($file);






            $is_file_downloaded = true;
        }


        if ($is_file_downloaded === false) {
            $pagination_uri_segment = 3;
            list($per_page, $segment) = get_pagination_param($pagination_uri_segment, $search_session_key);

            $response = $this->detail_mod->get_sales_details_data('', '', '', $search_data);

            $total_count = $this->detail_mod->get_data_total_count();
            $data['pagination'] = setup_pagination_option($total_count, 'report/sales_details', $per_page, $pagination_uri_segment, $this->pagination);

            $data['listing_data'] = $response;
            $data['total_records'] = $total_count;
            $data['search_session_key'] = $search_session_key;

            $this->load->view('basic/header', $data);
            $this->load->view('report/sales_details', $data);
            $this->load->view('basic/footer', $data);
        }
    }

    

function carrierreport(){
       $this->load->model('detail_mod');
        $data = array();
        $page_name = "carrierreport_list";
        $search_session_key = 'search_' . $page_name;
        $data['page_name'] = $page_name;
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        if (!check_logged_user_group(array('SYSTEM'))) {
            show_404('403');
        }
        $search_parameters = array('providertime', 'carrier_name', 'carrier_id', 'no_of_rows');

        if (isset($_POST['search_action'])) {
            set_post_to_session($search_session_key, $search_parameters);
        } else {
            set_session_to_session($search_session_key, $search_parameters);
        }
		
		if ($_SESSION[$search_session_key]['providertime'] == '') {
            $yesterday_timestamp = strtotime("yesterday");
            $yesterday = date('Y-m-d', $yesterday_timestamp);
            $time_range = $yesterday . ' 00:00:00 - ' . $yesterday . ' 23:59:59';
            $_SESSION[$search_session_key]['providertime'] = $time_range;
        }

        $search_data = array(
            'providertime' => $_SESSION[$search_session_key]['providertime'],
            'carrier_id' => $_SESSION[$search_session_key]['carrier_id'],
            'carrier_name' => $_SESSION[$search_session_key]['carrier_name'],
        );
        $is_file_downloaded = false;
        if ($is_file_downloaded === false) {
            $pagination_uri_segment = 3;
            list($per_page, $segment) = get_pagination_param($pagination_uri_segment, $search_session_key);

           // $response = $this->detail_mod->get_provider_profitloss_data('', $per_page, $segment, $search_data);

            $response = $this->detail_mod->get_carrierreport('', $per_page, $segment, $search_data);
            $total_count = $this->detail_mod->get_data_total_count();
            $data['pagination'] = setup_pagination_option($total_count, 'report/carrierreport', $per_page, $pagination_uri_segment, $this->pagination);

            $data['listing_data'] = $response;
            $data['total_records'] = $total_count;
            $data['search_session_key'] = $search_session_key;

            $this->load->view('basic/header', $data);
            $this->load->view('report/carrierreport', $data);
            $this->load->view('basic/footer', $data);
        }
}
   
}
