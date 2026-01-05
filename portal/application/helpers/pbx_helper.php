<?php

function get_queue_strategy() {
    $queue_strategy_array = array('ring-all' => 'Ring All', 'longest-idle-agent' => 'Longest Idle Agent', 'round-robin' => 'Round Robin', 'top-down' => 'Top Down', 'agent-with-least-talk-time' => 'Agent With Least Talk Time', 'agent-with-fewest-calls' => 'Agent With Fewest Calls', 'sequentially-by-agent-order' => 'Sequentially By Agent Order', 'random' => 'Random');
    return $queue_strategy_array;
}

function start_page($title, $links_array) {

    $str = '<div class="col-md-12 col-sm-6 col-xs-12">
			<div class="x_panel">';
    $str .= '			<div class="x_title">
					<h2>' . $title . '</h2>';

    if (count($links_array) > 0) {
        $str .= '<ul class="nav navbar-right panel_toolbox">';
        foreach ($links_array as $back_array) {
            $back_page_link = $back_array['link'];
            $back_page_text = $back_array['text'];
            if (isset($back_array['class']))
                $back_page_class = $back_array['class'];
            else
                $back_page_class = 'btn-primary';
            //$str .='<li><a href="'.$back_page_link.'"  class="btn '.$back_page_class.'" role="button">'.$back_page_text.'</a></li>'; 	
            $str .= '<li><a href="' . $back_page_link . '"><input type="button" value="' . $back_page_text . '" name="add_link" class="btn ' . $back_page_class . '"></a></li>';
        }
        $str .= '<li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li></ul>';
    }

    $str .= '<div class="clearfix"></div>
				</div>';

    echo $str;
}

function end_page($title = '', $links_array = array()) {
    $str = '</div>
</div>';
    echo $str;
}

///incoming number timeconditions
function get_weeks() {
    $week_array = array('0' => 'Monday', '1' => 'Tuesday', '2' => 'Wednesday', '3' => 'Thursday', '4' => 'Friday', '5' => 'Saturday', '6' => 'Sunday');
    return $week_array;
}

function get_destination_array($is_reversed = false, $exclude_array = array()) {
    $destination_array = array(
        'HANGUP' => 'Hang Up',
        'IPDEVICE' => 'IP Device',
        'SIPDEVICE' => 'SIP DEVICE',
        'SIPURI' => 'SIP URI',
        'PSTN' => 'PSTN Number',
        'EXTEN' => 'Extension',
        'IVR' => 'IVR',
        'VOICEMESSAGE' => 'Voice Message',
        'ANNOUNCEMENT' => 'Announcement',
        'TIMEROUTE' => 'Time Route',
        'RINGGROUP' => 'Ring Group',
        'QUEUE' => 'Queue',
        'CONFERENCE' => 'Conference',
        'VOICEMAIL' => 'Voice mail',
		'SIPURI' => 'SIP URI',        
        'DNC' => 'DNC',
    );

    if ($is_reversed) {
        $destination_array = array_flip($destination_array);
    }

    if (count($exclude_array) > 0) {
        foreach ($exclude_array as $exclude_id) {
            if (isset($destination_array[$exclude_id]))
                unset($destination_array[$exclude_id]);
        }
    }

    return $destination_array;
}

function get_destination_options($selected_id = '', $exclude_array = array(), $include_array = array()) {
    $destination_type_id_array = get_destination_array();

    $option_html = '';
    $option_array = array();

    $destination_type_id_array = $destination_type_id_array + $include_array - $exclude_array;

    foreach ($destination_type_id_array as $key => $deisplay_value) {
        if (in_array($key, $exclude_array))
            continue;


        $selected = '';
        if ($selected_id == $key || $selected_id == $deisplay_value)
            $selected = 'selected="selected"';


        $option_html .= '<option value="' . $key . '" ' . $selected . '>' . $deisplay_value . '</option>';
    }

    return $option_html;
}

function get_destination_type($type) {
    $type = strtolower($type);
    $return_type = '';

    $destination_type_id_array = get_destination_array();
    $destination_type_array = get_destination_array(true);

    if ($type == '') {
        
    } elseif (isset($destination_type_array[$type])) {
        $return_type = $destination_type_array[$type];
    } elseif (isset($destination_type_id_array[$type])) {
        $return_type = $destination_type_id_array[$type];
    }

    return $return_type;
}

function check_delete($endpoint_value) {
    $CI = & get_instance();
    $error_messages = array();
   

    $sql = "SELECT ivr_id, ivr_name FROM " . $CI->db->dbprefix('ivrs') . " WHERE timeout_route_endpoint='$endpoint_value'";
    $query = $CI->db->query($sql);
    $row = $query->row();
    if (isset($row)) {
        $error_messages[] = "Remove Link From IVR: " . $row->ivr_name;
    }

    $sql = "SELECT ivr_name FROM " . $CI->db->dbprefix('ivroptions') . " io INNER JOIN " . $CI->db->dbprefix('ivrs') . " i ON io.ivr_id=i.ivr_id  WHERE dtmf_route_endpoint='$endpoint_value'";
    $query = $CI->db->query($sql);
    $row = $query->row();
    if (isset($row)) {
        $error_messages[] = "Remove Link From IVR Mapping : " . $row->ivr_id;
    }

    $sql = "SELECT announcement_id, annumcement_name FROM " . $CI->db->dbprefix('announcement') . " WHERE route_endpoint='$endpoint_value'";
    $query = $CI->db->query($sql);
    $row = $query->row();
    if (isset($row)) {
        $error_messages[] = "Remove Link From Announcement: " . $row->annumcement_name;
    }

    $sql = "SELECT timeconditions_id, timeconditions_name FROM " . $CI->db->dbprefix('timeconditions') . " WHERE route_endpoint='$endpoint_value'";
    $query = $CI->db->query($sql);
    $row = $query->row();
    if (isset($row)) {
        $error_messages[] = "Remove Link From Timecondition: " . $row->timeconditions_name;
    }

    $sql = "SELECT timeconditions_name FROM " . $CI->db->dbprefix('timeconditions_rules') . " tr INNER JOIN " . $CI->db->dbprefix('timeconditions') . " t ON tr.timeconditions_id =t.timeconditions_id WHERE tr.route_endpoint='$endpoint_value'";
    $query = $CI->db->query($sql);
    $row = $query->row();
    if (isset($row)) {
        $error_messages[] = "Remove Link From Timecondition: " . $row->timeconditions_name;
    }

    if (count($error_messages) > 0) {
        $str = implode('<br>', $error_messages);
        return $str;
    } else
        return true;
}

/* find duplicates within an array */

function check_array_duplicates($array_check) {
    $array_check_unique = array_unique($array_check);

    if (count($array_check) == count($array_check_unique)) {
        return array();
    }
    $array_diff = array_diff_assoc($array_check, $array_check_unique);
    return $array_diff;
}
