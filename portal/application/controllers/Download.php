<?php

/*
 * Copyright (C) Openvoips Technologies - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential, Only allow to use with license certificate
 * OV500Pro Version 3.0.0
 * Written by Seema Anand <openvoips@gmail.com> , Jan 2026 
 * http://www.openvoips.com 
 */

class Download extends MY_Controller {

    function __construct() {
        parent::__construct();
    }

    public function sample($file) {
        if (!check_is_loggedin())
            redirect(base_url(), 'refresh');

        $file = param_decrypt($file);
        switch ($file) {
            case 'rate_incoming':
                $filename = 'rates_incoming.csv';

                $fullPath = 'uploads/sample/' . $filename;

                break;
            case 'diversion_number_sample':
                $filename = 'diversion_number_sample.csv';

                $fullPath = 'uploads/sample/' . $filename;

                break;
            case 'rate_outgoing':
                $filename = 'rates_outgoing.csv';

                $fullPath = 'uploads/sample/' . $filename;

                break;
            case 'did':
                $filename = 'did.csv';

                $fullPath = 'uploads/sample/' . $filename;

                break;

            default:

                $filename = $file;
                $fullPath = 'uploads/sample/' . $filename;

                if (file_exists($fullPath)) {
                    
                } else
                    show_404();
        }

        $this->load->helper('download');
        force_download($fullPath, NULL, true);
        exit;
    }

    public function cdr($account_id, $folder, $filename) {
        if (!check_is_loggedin())
            redirect(base_url(), 'refresh');
        $this->load->helper('download');

        $account_id = param_decrypt($account_id);
        $folder = param_decrypt($folder);
        $filename = param_decrypt($filename);

        $dir = CDR_DIRECTORY;

        $dir = str_replace('{{ACCOUNT_ID}}', $account_id, $dir);
        $dir = str_replace('{{FOLDER}}', $folder, $dir);

        $fullPath = $dir . $filename;

        if (!file_exists($fullPath))
            show_404();

        force_download($fullPath, NULL, true);

        /* if ($fd = fopen ($fullPath, "r")) {
          $fsize = filesize($fullPath);
          $path_parts = pathinfo($fullPath);
          $ext = strtolower($path_parts["extension"]);
          switch ($ext) {
          case "csv":
          header("Content-type: application/csv"); // add here more headers for diff. extensions
          header("Content-Disposition: attachment; filename=\"".$path_parts["basename"]."\""); // use 'attachment' to force a download
          break;


          case "pdf":
          header("Content-type: application/pdf"); // add here more headers for diff. extensions
          header("Content-Disposition: attachment; filename=\"".$path_parts["basename"]."\""); // use 'attachment' to force a download
          break;

          default;
          header("Content-type: application/octet-stream");
          header("Content-Disposition: filename=\"".$path_parts["basename"]."\"");
          }
          header("Content-length: $fsize");
          header("Cache-control: private"); //use this to open files directly
          while(!feof($fd)) {
          $buffer = fread($fd, 2048);
          echo $buffer;
          }
          }
          fclose ($fd); */
        exit;
    }

    public function ticket($account_id, $filename) {
        if (!check_is_loggedin())
            redirect(base_url(), 'refresh');

        $account_id = param_decrypt($account_id);
        $filename = param_decrypt($filename);

        $download_path = 'uploads/ticket/' . $account_id;
        $fullPath = $download_path . '/' . $filename;

        if (!file_exists($fullPath))
            show_404();


        $this->load->helper('download');
        force_download($fullPath, NULL);

        exit;
    }

    public function recording($filename) {
        if (!check_is_loggedin())
            redirect(base_url(), 'refresh');

        //$account_id = param_decrypt($account_id);
        $filename = param_decrypt($filename);

        $fullPath = $filename;

        if (!file_exists($fullPath))
            show_404();


        $this->load->helper('download');
        force_download($fullPath, NULL);

        exit;
    }

}
