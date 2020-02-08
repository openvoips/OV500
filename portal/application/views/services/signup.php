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
//echo '<pre>';
//print_r($pagination);
//
//print_r($listing_data);
//echo '</pre>';

$status_name_array = array(
    '1' => array('name' => 'Active', 'class' => 'label-success'),
    '0' => array('name' => 'Inactive', 'class' => 'label-danger'),  
);

?>


<div class="col-md-12 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Signup Configuration</h2>
            <ul class="nav navbar-right panel_toolbox">

                <li><a href="<?php echo base_url() ?>sysconfig/AddSignup"><input type="button" value="Add Signup Config" name="add_link" class="btn btn-primary"></a></li>
            </ul>
            <div class="clearfix"></div>
        </div>
        <div class="x_content"> 
            <form class="block-content form-horizontal " id="search_form" name="search_form"  method="post" action="<?php echo base_url(); ?>sysconfig/signupConfig/">
                <input type="hidden" name="search_action" value="search" />
           <input type="hidden" name="no_of_rows" id="no_of_rows" value="" />
                <div class="form-group">
                    <label class="control-label col-md-2 col-sm-4 col-xs-12">Plan Name</label>
                    <div class="col-md-2 col-sm-5 col-xs-12">
                        <input type="text"  name="signup_plan" id="signup_plan"  value="<?php echo $_SESSION['search_signup_data']['s_signup_plan']; ?>" class="form-control data-search-field" placeholder="Signup plan">
                    </div>

                    <label class="control-label col-md-1 col-sm-4 col-xs-12">Key</label>
                    <div class="col-md-2 col-sm-5 col-xs-12">
                        <input type="text"  name="signupkey" id="signupkey"  value="<?php echo $_SESSION['search_signup_data']['s_signupkey']; ?>" class="form-control data-search-field" placeholder="Signup Key">
                    </div>
                    <label class="control-label col-md-1 col-sm-4 col-xs-12">Status</label>                    
                    <div class="col-md-2 col-sm-5 col-xs-12">
                        <select name="status_id" id="status_id" class="form-control data-search-field">
                            <option value="">Select</option>
                            <option value="1" <?php if($_SESSION['search_signup_data']['s_status_id']=='1') echo 'selected="selected"'; ?>  >Active</option>
                            <option value="0" <?php if($_SESSION['search_signup_data']['s_status_id']=='0') echo 'selected="selected"'; ?> >Inactive</option>                                                    
                        </select>
                    </div>

                    <div class="searchBar ">
                        <input type="submit" value="Search" name="OkFilter" id="OkFilter" class="btn btn-primary">
                        <input type="button" value="Reset" name="search_reset" id="search_reset" class="btn btn-info">           
                    </div>
                </div>

            </form> 

            <div class="clearfix"></div>
            <div class="ln_solid"></div>               

            <div class="row">  
                <?php
                dispay_pagination_row($total_records, $_SESSION['search_signup_data']['s_no_of_rows'], $pagination);
                ?>    
            </div>     
            <div class="table-responsive">
                <table class="table table-striped jambo_table bulk_action table-bordered">
                    <thead>

                        <tr class="headings thc">                               
                            <th class="column-title">Plan Name</th>                               
                            <th class="column-title">Signup Key</th>                               
                            <th class="column-title">Tariff ID</th>                               
                            <th class="column-title">Dialplan ID</th>                               
                            <th class="column-title">Business Holder</th>
                            <th class="column-title">Business Holder ID</th>
                            <th class="column-title">Plan Status</th>
                            <th class="column-title">Action</th>

                        </tr>
                    </thead>		
                    <tbody>
                        <?php
                        if (count($listing_data) > 0) {
                            foreach ($listing_data as $listing_row) {
                                
                                $singup_status = $listing_row['status_id'];
                                if (isset($status_name_array[$singup_status])) {
                                    $status_name = $status_name_array[$singup_status]['name'];
                                    $class = $status_name_array[$singup_status]['class'];
                                } else {
                                    $status_name = '';
                                    $class = '';
                                }
                                $status = '<span class="label ' . $class . '">' . $status_name . '</span>';
                                
                                ?>

                                <tr>
                                    <td width="100"><?php echo $listing_row['signup_plan']; ?></td> 
                                    <td width="100"><?php echo $listing_row['signupkey']; ?></td> 
                                    <td width="100"><?php echo $listing_row['tariff_id']; ?></td> 
                                    <td width="100"><?php echo $listing_row['dialplan_id']; ?></td> 
                                    <td width="100"><?php echo $listing_row['business_holder']; ?></td> 
                                    <td width="100"><?php echo $listing_row['business_holder_account_id']; ?></td> 
                                    <td width="100"><?php echo $status; ?></td> 
                                    <td width="100">
                                         <a href="<?php echo base_url(); ?>sysconfig/eSignupConfig/<?php echo param_encrypt($listing_row['signupkey']); ?>" title="Edit"><i class="fa fa-pencil-square-o"></i></a>
                               
                                           
                               
                                    <a href="javascript:void(0);"  onclick=doConfirmDelete('<?php echo $listing_row['id']; ?>') title="Delete"><i class="fa fa-trash"></i></a>
                               
                                        
                                    </td> 
                                      
                                </tr>
                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="7" align="center"><strong>No Record Found</strong></td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>                    
            <?php
            echo '<div class="btn-toolbar" role="toolbar"> <div class="btn-group col-md-5 col-sm-12 col-xs-12">';

            echo '</div>  <div class="btn-group pull-right navigation-bar col-md-6 col-sm-12 col-xs-12 text-right">  ' . $pagination . ' </div></div>';
            ?>

        </div>
    </div>
</div>
<?php
//print_r($listing_data);
?>

<script>
    $(document).ready(function () {
        $('#OkFilter').click(function () {
            var no_of_records = $('#no_of_records').val();
            $('#no_of_rows').val(no_of_records);
            
        });
    });
</script>  