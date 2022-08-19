<!--
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
-->

<div class="col-md-12 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Module Details</h2>            
            <div class="clearfix"></div>
        </div>

        <div class="clearfix"></div>

        <div class="table-responsive">
            <table class="table   table-bordered" >
                <thead>
                    <tr class=" ">                     
                        <th class="thc" style="color:#FFFFFF; width:30%;">Module Name </th>  <td><?php echo $plugin_header['plugin_name']; ?></td></tr> 
                <th class="thc" style="color:#FFFFFF; width:30%;">Description</th>  <td><?php echo $plugin_header['plugin_description']; ?></td></tr> 
                <th class="thc" style="color:#FFFFFF; width:30%;">Version </th>  <td><?php echo $plugin_header['plugin_version']; ?></td></tr> 
                <th class="thc" style="color:#FFFFFF; width:30%;">Plugin URI </th>  <td><?php echo $plugin_header['plugin_uri']; ?></td></tr>                         
                <th class="thc" style="color:#FFFFFF; width:30%;">Author</th>  <td><?php echo $plugin_header['plugin_author']; ?></td></tr> 
                <th class="thc" style="color:#FFFFFF; width:30%;">Author URI</th>  <td><?php echo $plugin_header['plugin_author_uri']; ?></td></tr>  

                </thead>
                <tbody>
                    <tr><td colspan="2" align="right">
                            <?php
                            if (isset($plugins_active[$plugin_name])) {
                                $link = site_url('module/status/' . $plugin_header['plugin_system_name'] . '/inactivate');
                                $link_html = '<a href="' . $link . '" title="Deactivate"><button type="button" id="btnSave" class="btn btn-danger" >Deactivate Module</button></a>';
                            } else {
                                $link = site_url('module/status/' . $plugin_header['plugin_system_name'] . '/activate');
                                $link_html = '<a href="' . $link . '" title="Activate"><button type="button" id="btnSave" class="btn btn-success" >Activate Module</button></a>';
                            }
                            echo $link_html;
                            ?>
                        </td></tr>
                </tbody>


            </table>
        </div>             


    </div>

</div>            