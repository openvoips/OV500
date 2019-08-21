<?php

class MY_Exceptions extends CI_Exceptions {

    public function show_404($page = '', $log_error = true) {//
        $CI = & get_instance();
        $data['sitesetup_data'] = $CI->sitesetup_mod->get_sitesetup_data();
        if (!check_is_loggedin()) {

            $CI->load->view('404');
        } else {//loggedin
            if ($page == '')
                $page = '404';
            $CI->load->view('basic/header', $data);
            $CI->load->view('basic/' . $page, $data);
            $CI->load->view('basic/footer', $data);
        }
        echo $CI->output->get_output();
        exit;
    }

}

?>