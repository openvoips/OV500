<div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Live Calls</h2>
            <ul class="nav navbar-right panel_toolbox">
                <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                </li>
            </ul>
            <div class="clearfix"></div>
        </div>
        <div class="x_content">
            <div class="row top_tiles">
                <div class="text-center col-lg-3 col-md-3 col-sm-12 col-xs-12">
                    <div class="tile tile-stats">
                        <h2 id="totCall">0000</h2>
                        <span>Total Calls</span>
                    </div>
                </div>
                <div class="text-center col-lg-3 col-md-3 col-sm-12 col-xs-12">
                    <div class="tile tile-stats">
                        <h2 id="ansCall">0000</h2>
                        <span>Total Answered Calls</span>
                    </div>
                </div>
                <div class="text-center col-lg-3 col-md-3 col-sm-12 col-xs-12">
                    <div class="tile tile-stats">
                        <h2 id="ringCall">0000</h2>
                        <span>Total Ringing Calls</span>
                    </div>
                </div>
                <div class="text-center col-lg-3 col-md-3 col-sm-12 col-xs-12">
                    <div class="tile tile-stats">
                        <h2 id="proCall">0000</h2>
                        <span>Total Progress Calls</span>
                    </div>
                </div>
            </div>
            <br />
            <br />
            <table id="datatable-buttons" class="table table-striped table-bordered jambo_table" width="100%">
                <thead>
                    <tr>
                        <th></th>
                        <th>Account</th>
                        <th>Destination</th>
                        <th>Carrier</th>
                        <th>Caller</th>
                        <th>Callee</th>
                        <th width="250">Start Time</th>
                        <th width="250">Ans. Time</th>
                        <th>Duration</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <!--<tfoot>
                    <tr>
                        <th></th>
                        <th>Account</th>
                        <th>Destination</th>
                        <th>Carrier</th>
                                        <th>Caller</th>
                                        <th>Callee</th>
                                        <th>Start Time</th>
                                        <th>Ans. Time</th>
                                        <th>Duration</th>
                                        <th>Status</th>
                    </tr>
                </tfoot>-->
            </table>		
        </div>
    </div>
</div>
<style>
    #datatable-buttons td:hover { border:0px;}
    #datatable-buttons td.details-control {
        background: url('<?php echo base_url() ?>theme/default/images/details_open.png') no-repeat center center;
        cursor: pointer;
        padding:0 15px !important;
    }
    #datatable-buttons tr.shown td.details-control {
        background: url('<?php echo base_url() ?>theme/default/images/details_close.png') no-repeat center center;
        padding:0 15px !important;
    }
</style>
<script>
    $(document).ready(function () {
        function format(d) {
            // `d` is the original data object for the row
            return '<table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;" width="100%">' +
                    '<tr>' +
                    '<td width="25%"><strong>Account:</strong></td>' +
                    '<td width="25%">' + d.user_account_id + '</td>' +
                    '<td width="25%"><strong>Carrier ID:</strong></td>' +
                    '<td width="25%">' + d.carrier_carrier_id_name + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<td><strong>User Source IP:</strong></td>' +
                    '<td>' + d.user_src_ip + '</td>' +
                    '<td><strong>Gateway IP:</strong></td>' +
                    '<td>' + d.carrier_gateway_ipaddress + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<td><strong>Source Caller:</strong></td>' +
                    '<td>' + d.user_src_caller + '</td>' +
                    '<td><strong>Gateway Name:</strong></td>' +
                    '<td>' + d.carrier_gateway_ipaddress_name + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<td><strong>Source Callee:</strong></td>' +
                    '<td>' + d.user_src_callee + '</td>' +
                    '<td><strong>Dialplan:</strong></td>' +
                    '<td>' + d.carrier_dialplan_id_name + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<td><strong>Source Callee:</strong></td>' +
                    '<td>' + d.user_src_callee + '</td>' +
                    '<td><strong>Node:</strong></td>' +
                    '<td>' + d.fs_host + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<td><strong>Notes:</strong></td>' +
                    '<td colspan="3">' + d.notes + '</td>' +
                    '</tr>' +
                    '</table>';
        }

        var table = $('#datatable-buttons').DataTable({
            //ajax: '../data.txt',
            ajax: {
                url: '<?php echo base_url() ?>reports/api_livecall',
                dataSrc: function (json) {
                    console.log(json.data);
                    //$("#totCall").html(json.allCalls);
                    $("#totCall").html(json.data.length);
                    var countAnswer = countRinging = countProgress = 0;
                    $.each(json.data, function (key, obj) {
                        $.each(obj, function (k, value) {
                            if (k == 'callstatus') {
                                if (value == 'answer')
                                    countAnswer++;
                                else if (value == 'ring')
                                    countRinging++;
                                else if (value == 'progress')
                                    countProgress++;
                            }
                        });
                    });
                    $("#ansCall").html(countAnswer);
                    $("#ringCall").html(countRinging);
                    $("#proCall").html(countProgress);
                    return json.data;
                }
            },
            columns: [
                {
                    "className": 'details-control',
                    "orderable": false,
                    "data": null,
                    "defaultContent": ''
                },
                {"data": "user_account_id"},
                {"data": "user_destination"},
                {"data": "carrier_carrier_id_name"},
                {"data": "user_src_caller"},
                {"data": "user_src_callee"},
                {"data": "start_time"},
                {"data": "answer_time"},
                {"data": "duration"},
                {"data": "callstatus"}
            ],
            order: [[1, 'asc']],
            stateSave: true,
            aLengthMenu: [[5, 10, 25, 50, 100, 200, -1], [5, 10, 25, 50, 100, 200, "All"]],
            iDisplayLength: 50,
            fnRowCallback: function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                if (aData.callstatus == "ring")
                    $('td', nRow).css({'background-color': '#F2A2A4', 'color': '#990000'});
                else if (aData.callstatus == "answer")
                    $('td', nRow).css({'background-color': '#AEFF93', 'color': '#006600'});
            },
            sDom: 'l<"toolbox">ftip',
            searching: true,
            paging: false,
            bInfo: false,
            scrollY: 400,
            scrollX: true
        });

        $("div.toolbox").html('<label> <b>Freeze Flow</b> <input id="checkbox" type="checkbox"> </label>');

        $('#datatable-buttons tbody').on('click', 'td.details-control', function () {
            var tr = $(this).closest('tr');
            var row = table.row(tr);

            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
            } else {
                row.child(format(row.data())).show();
                tr.addClass('shown');
            }
        });

        setInterval(function () {
            var checked = $('#checkbox').is(':checked');
            if (checked) {
                console.log('Not loading');
            } else {
                console.log('Loading');
                table.ajax.reload();
            }
        }, 5000);
    });
</script>