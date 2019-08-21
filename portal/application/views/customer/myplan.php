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
<?php
//echo '<pre>';
$pbxplan = $data['result'][$_SESSION['session_current_customer_id']]['pbxplan'];
$balance = $data['result'][$_SESSION['session_current_customer_id']]['balance'];
$currency = $data['result'][$_SESSION['session_current_customer_id']]['currency'];
$tariff = $data['result'][$_SESSION['session_current_customer_id']]['tariff'];

//var_dump($tariff);
//echo '</pre>';
?>
<!-- Table row -->
<div class="">
    <div class="clearfix"></div>
    <div class="col-md-12 col-sm-12 col-xs-12 right">
        <div class="x_title">
            <h2>Your Tariff Plan (<?php echo $tariff['tariff_name'];?>) Price List (Prices list in <?php echo $currency['symbol']; ?>)</h2>
            <div class="clearfix"></div>
        </div>

    </div>

    <div class="col-md-12 col-sm-12 col-xs-12">       
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
                <div class="x_content">
                    <div class="col-xs-12 table">
                        <table class="table table-striped">
                            <thead>
                                <tr>                                   
                                    <th>Service</th>        
                                    <th>Detail</th>
                                    <th>Prices</th>
                                </tr>
                            </thead>
                            <tbody>                               
                                <tr>                                    
                                    <td>Plan Monthly Charges</td>
                                    <td></td>
                                    <td><?php echo $currency['symbol']." ". number_format($tariff['monthly_charges'], 4, '.', ''); ?></td>                
                                </tr>
                                <?php if ($tariff[bundle_option] == '1') { ?>
                                    <tr> 
                                        <td>Bundle (1) <?php echo $tariff['bundle1_type']; ?> </td>
                                        <td>Allowed Prefixes : <?php echo $tariff['bp1'];?> </td>
                                        <td><?php if ($tariff['bundle1_type'] == 'COST') echo $currency['symbol']. " "; echo number_format($tariff['bundle1_value'], 4, '.', '') . " Free " . $tariff['bundle1_type']; ?></td>              
                                    </tr>

                                    <tr> 
                                        <td>Bundle (2) <?php echo $tariff['bundle2_type']; ?> </td>
                                        <td>Allowed Prefixes : <?php echo $tariff['bp2'];?> </td>
                                        <td><?php if ($tariff['bundle1_type'] == 'COST') echo $currency['symbol']. " "; echo number_format($tariff['bundle2_value'], 4, '.', '') . " Free " . $tariff['bundle2_type']; ?></td>              
                                    </tr>

                                    <tr> 
                                        <td>Bundle (3) <?php echo $tariff['bundle3_type']; ?> </td>
                                        <td>Allowed Prefixes : <?php echo $tariff['bp3'];?> </td>
                                        <td><?php if ($tariff['bundle1_type'] == 'COST') echo $currency['symbol']. " "; echo number_format($tariff['bundle3_value'], 4, '.', '') . " Free " . $tariff['bundle3_type']; ?></td>              
                                    </tr>

                                <?php } ?>

                                <?php if ($tariff['pbx_option'] == '1') { ?>       

                                    <tr> 
                                        <td>Extension Setup Charge (each)</td>
                                        <td>  </td>
                                        <td><?php echo $currency['symbol']. " ". number_format($pbxplan['extension_setupcost'], 4, '.', ''); ?></td>      
                                    </tr>
                                    <tr> 
                                        <td>Extension Monthly Rental (each)</td>
                                        <td>  </td>
                                        <td><?php echo $currency['symbol']. " ". number_format($pbxplan['extension_cost'], 4, '.', ''); ?></td>            
                                    </tr>
                                    <tr> 
                                        <td>Free Extension in Plan</td>
                                        <td>  </td>
                                        <td><?php echo number_format($pbxplan['free_extensions'], 0, '.', ''); ?></td>
                                    </tr>

                                    <tr> 
                                        <td>VoiceMail Setup Charge (each)</td>
                                        <td>  </td>
                                        <td><?php echo $currency['symbol']. " ".number_format($pbxplan['voicemail_setupcost'], 4, '.', ''); ?></td>                                           
                                    </tr>
                                    <tr> 
                                        <td>VoiceMail Monthly Rental (each)</td>
                                        <td>  </td>
                                        <td><?php echo $currency['symbol']. " ". number_format($pbxplan['voicemail_cost'], 4, '.', ''); ?></td>                  
                                    </tr>
                                    <tr> 
                                        <td>Free VoiceMail Accounts in Plan</td>
                                        <td>  </td>
                                        <td><?php echo number_format($pbxplan['free_voicemail'], 0, '.', ''); ?></td>
                                    </tr>
                                    <tr> 
                                        <td>IVR Setup Charge (each)</td>
                                        <td>  </td>
                                        <td><?php echo $currency['symbol']. " ".number_format($pbxplan['ivr_setupcost'], 4, '.', ''); ?></td>                                           
                                    </tr>
                                    <tr> 
                                        <td>IVR Monthly Rental (each)</td>
                                        <td>  </td>
                                        <td><?php echo $currency['symbol']. " ".number_format($pbxplan['ivr_cost'], 4, '.', ''); ?></td>                  
                                    </tr>
                                    <tr> 
                                        <td>Free IVR in Plan</td>
                                        <td>  </td>
                                        <td><?php echo number_format($pbxplan['free_ivr'], 0, '.', ''); ?></td>
                                    </tr>

                                    <tr> 
                                        <td>RingGroup Setup Charge (each)</td>
                                        <td>  </td>
                                        <td><?php echo $currency['symbol']. " ".number_format($pbxplan['ringgroup_setupcost'], 4, '.', ''); ?></td>                                           
                                    </tr>
                                    <tr> 
                                        <td>RingGroup Monthly Rental (each)</td>
                                        <td>  </td>
                                        <td><?php echo $currency['symbol']. " ".number_format($pbxplan['ringgroup_cost'], 4, '.', ''); ?></td>                  
                                    </tr>
                                    <tr> 
                                        <td>Free RingGroup in Plan</td>
                                        <td>  </td>
                                        <td><?php echo number_format($pbxplan['free_ringgroup'], 0, '.', ''); ?></td>
                                    </tr>
                                    <tr> 
                                        <td>Queue Setup Charge (each)</td>
                                        <td>  </td>
                                        <td><?php echo $currency['symbol']. " ".number_format($pbxplan['callcenter_setupcost'], 4, '.', ''); ?></td>                                           
                                    </tr>
                                    <tr> 
                                        <td>Queue Monthly Rental (each)</td>
                                        <td>  </td>
                                        <td><?php echo $currency['symbol']. " ".number_format($pbxplan['callcenter_cost'], 4, '.', ''); ?></td>                  
                                    </tr>
                                    <tr> 
                                        <td>Free Queue in Plan</td>
                                        <td>  </td>
                                        <td><?php echo number_format($pbxplan['callcenter_ivr'], 0, '.', ''); ?></td>
                                    </tr>


                                    <tr> 
                                        <td>Per MB Recording Charges</td>
                                        <td>  </td>
                                        <td><?php echo $currency['symbol']. " ".number_format($pbxplan['recording_cost'], 4, '.', ''); ?></td>
                                    </tr>

                                    <tr> 
                                        <td>Free Recording(Size) in Plan</td>
                                        <td>  </td>
                                        <td><?php echo number_format($pbxplan['free_recording'], 0, '.', ''); ?></td>
                                    </tr>

                                <?php } ?>

                            </tbody>
                        </table>
                    </div>                  
                </div>
            </div>
        </div>
    </div>
</div>
