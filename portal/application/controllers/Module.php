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

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Module extends MY_Controller {

    //private $url ='http://openvoips.org/ci_modules/';
    private $url = 'http://localhost/ov500updated/ci_modules/';
    private $module_path = APPPATH . 'modules' . DIRECTORY_SEPARATOR;

    function __construct() {
        parent::__construct();
        $this->load->library('pagination');
        $this->form_validation->set_error_delimiters('', '');
        if (!check_is_loggedin())
            redirect(site_url(), 'refresh');
        if (!check_logged_user_type(array('ADMIN', 'SUBADMIN')))
            show_404('403');
        // $this->output->enable_profiler(ENABLE_PROFILE);
        $this->load->library('plugins');
    }

    public function index() {
        $page_name = "module_installed";
        $data['page_name'] = $page_name;
        /* if (!check_account_permission('????', 'view'))
          show_404('403'); */
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        $data['plugins_installed'] = $this->plugins->get_plugins_pool();
        //$data['plugins_active'] = $this->plugins->get_plugins_active();

        $this->load->view('basic/header', $data);
        $this->load->view('module/modules', $data);
        $this->load->view('basic/footer', $data);
    }

    public function view($plugin_name = -1) {
        $page_name = "module_installed_view";
        $data['page_name'] = $page_name;
        if ($plugin_name == -1)
            show_404();
        /* if (!check_account_permission('???', 'view'))
          show_404('403'); */

        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();
        $plugins_installed = $data['plugins_installed'] = $this->plugins->get_plugins_pool();
        $data['plugins_active'] = $this->plugins->get_plugins_active();

        $data['plugin_name'] = $plugin_name;
        $data['plugin_header'] = $this->plugins->fetch_plugin_headers($plugin_name);

        if (!isset($plugins_installed[$plugin_name]))
            show_404();
        $this->load->view('basic/header', $data);
        $this->load->view('module/view', $data);
        $this->load->view('basic/footer', $data);
    }

    public function status($plugin_system_name, $action) {

        /* if (!check_account_permission('???', 'view'))
          show_404('403'); */

        $result = $this->plugins->change_status($plugin_system_name);
        if ($result === true) {
            $this->session->set_flashdata('suc_msgs', 'Module Activated Successfully');
        } else {
            $this->session->set_flashdata('suc_msgs', $result);
        }

        redirect(site_url('module'), 'location', '301');
    }

    public function install() {

        try {
            $source_directory = FCPATH . 'uploads/';
            $destination_directory = APPPATH . '/modules/';


            $error_message = '';

            $this->load->helper('download');

            $ftp_server = 'ftp.chinnamasale.com';
            $ftp_user_name = 'ftpdeveloper@openvoips.org';
            $ftp_user_pass = 'X^3*p$zTkAh7';

            /* require_once dirname(__FILE__) . '/../libraries/phpseclib/Net/SFTP.php';
              set_include_path(dirname(__FILE__) . '/../libraries/phpseclib/');
              $sftp = new Net_SFTP($ftp_server);
              if (!$sftp->login($ftp_user_name, $ftp_user_pass)) {
              $error_message = 'Problam on connection';
              throw new Exception($error_message);
              } */



            $ftp_conn = ftp_connect($ftp_server) or die("Could not connect to $ftp_server");
            $login = ftp_login($ftp_conn, $ftp_user_name, $ftp_user_pass);

            if ((!$ftp_conn) || (!$login)) {
                throw new Exception('FTP connection has failed! Attempted to connect to ' . $host . ' for user ' . $user . '.');
            }
            /* else{
              echo 'FTP connection was a success.';
              $directory = ftp_nlist($ftp_conn,'');
              echo ''.print_r($directory,true).'';
              } */
            $zip_file_name = 'test1.zip';
            $local_file = $source_directory . $zip_file_name;
            $server_file = '/openvoips.org/ci_modules/module_zip/' . $zip_file_name;
            if (ftp_get($ftp_conn, $local_file, $server_file, FTP_BINARY)) {
                echo "Successfully written to $local_file\n";
            } else {
                throw new Exception("There was a problem getting file\n");
            }


            ftp_close($ftp_conn);


            //$source_file = $local_file;






            /*
              if(file_exists($source_file))
              echo 'file exists: '.$source_file.'<br>';
              else
              echo 'file NOOOOOOO: '.$source_file.'<br>';
             */

            $zip = new ZipArchive;
            $res = $zip->open($local_file);
            if ($res === TRUE) {
                $zip->extractTo($destination_directory);
                $zip->close();
                echo 'woot!';
            } else {
                echo 'doh!';
                var_dump($res);
            }
            echo 'end';
        } catch (Exception $e) {
            echo 'Exception: ' . $e->getMessage();
            die;
            $this->session->set_flashdata('err_msgs', $e->getMessage());
            // redirect(base_url(), 'refresh');
        }
    }

    public function available() {
        $page_name = "module_available";
        $data['page_name'] = $page_name;
        /* if (!check_account_permission('????', 'view'))
          show_404('403'); */
        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        if (isset($_POST['action']) && $_POST['action'] == 'OkInstall') {
            
        }




        $data['plugins_available'] = $this->get_available_plugins();

        $this->load->view('basic/header', $data);
        $this->load->view('module/availables', $data);
        $this->load->view('basic/footer', $data);
    }

    public function download($plugin_code = -1) {
        $page_name = "module_download";
        $data['page_name'] = $page_name;
        if ($plugin_code == -1)
            show_404();
        /* if (!check_account_permission('???', 'view'))
          show_404('403'); */

        $data['sitesetup_data'] = $this->sitesetup_mod->get_sitesetup_data();

        $filter_data = array('plugin_code' => $plugin_code, 'request' => 'download');
        $plugins_available = $this->get_available_plugins($filter_data);

        $plugin_info = current($plugins_available['plugins']);

        $module_directory_path = $this->module_path . $plugin_info['module_directory'];

        if (file_exists($module_directory_path)) {
            //directory already exists
        }

        //echo $module_directory_path ;
        //die;
        if (isset($plugins_available['status']) && $plugins_available['status'] == 1 && $plugins_available['download_link'] != '') {
            $url = $plugins_available['download_link'];
            $path = FCPATH . 'uploads/fast.zip';
            if (file_exists($path)) {
                unlink($path);
            }

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $data = curl_exec($ch);
            curl_close($ch);
            $r = file_put_contents($path, $data);
            if ($r === false) {
                //failed
            }
            /////
            $zip = new ZipArchive;
            if ($zip->open($path) === TRUE) {
                $extract_path = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . rand(1000, 9999);
                mkdir($extract_path, 0755);
                $zip->extractTo($extract_path);
                $zip->close();
                //echo 'ok';
                ///////now copy//////
                $source = $extract_path;
                $dest = $module_directory_path;

                mkdir($dest, 0755);
                foreach (
                $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $item
                ) {
                    if ($item->isDir()) {
                        mkdir($dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
                    } else {
                        copy($item, $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
                    }
                }



                //////////////
            } else {
                //echo 'failed';
            }


            ////
        } elseif (isset($plugins_available['message']) && $plugins_available['message'] != '') {
            
        } else {
            
        }
        die;




        $this->load->view('basic/header', $data);
        $this->load->view('module/downloading', $data);
        $this->load->view('basic/footer', $data);
    }

    public function get_available_plugins($filter_data = array()) {

        $license_key = $this->get_license_key();
        ;
        $machine_key = get_machine_key(); //$this->get_machine_key();

        $url = $this->url . 'moduleapi.php?p=' . rand(1, 1000);
        $curl = curl_init();

        $data_credntial = array('license_key' => $license_key, 'machine_key' => $machine_key);

        $data = array_merge($data_credntial, $filter_data);
        print_r($data);
        $url .='&';
        $url = sprintf("%s%s", $url, http_build_query($data));
        //echo $url;
        //curl_setopt($curl, CURLOPT_POST, 1);
        //	curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        /* 	case "POST":
          curl_setopt($curl, CURLOPT_POST, 1);
          if ($data)
          curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
          break;
          case "PUT":
          curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
          if ($data)
          curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
          break;
          default:
          if ($data)
          $url = sprintf("%s?%s", $url, http_build_query($data)); */



        //echo $url.'<br><br><br>';
        // OPTIONS:
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'APIKEY: SWITCH_KEY',
            'Content-Type: application/json',
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        // EXECUTE:
        $result_raw = $result = curl_exec($curl);
        if (curl_errno($curl)) {
            $error_msg = curl_error($curl);
        }

        if (!$result) {
            $return_array = array('error' => 0, 'message' => $error_msg);
            $result = json_encode($return_array);
            //die("Connection Failure");
        }
        curl_close($curl);


        $return = json_decode($result, true);
        echo '<pre>';
        print_r($return);
        echo '</pre>';
        return $return;
    }

    private function get_license_key() {
        return 'L';
    }

    private function delete_get_machine_key($salt = "") {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $temp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "diskpartscript.txt";
            if (!file_exists($temp) && !is_file($temp))
                file_put_contents($temp, "select disk 0\ndetail disk");
            $output = shell_exec("diskpart /s " . $temp);
            $lines = explode("\n", $output);
            $result = array_filter($lines, function($line) {
                return stripos($line, "ID:") !== false;
            });
            if (count($result) > 0) {
                //$result = array_shift(array_values($result));
                $k = array_values($result);
                $result = array_shift($k);
                $result = explode(":", $result);
                $result = trim(end($result));
            } else
                $result = $output;
        } else {
            $result = shell_exec("blkid -o value -s UUID");
            if (stripos($result, "blkid") !== false) {
                $result = $_SERVER['HTTP_HOST'];
            }
        }
        return md5($salt . md5($result));
    }

}
