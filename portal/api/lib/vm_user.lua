-- Title : vm_user.lua
-- Version : 1.0.0
-- Author : OpenVoIPs { Anand K Dayal - KANAND81@GMAIL.COM }
-- web: www.openvoips.com
-- Created Date : 01 March 2017
-- Compatible with OV500-1.0.0
-- gen_dir_user_xml.lua
-- example script for generating user directory XML
--  Don't terminate the code when hangup
--        INCLUDE FUNCTIONS
--
-- require"config";

local req_domain = params:getHeader("domain")
local req_key    = params:getHeader("key")
local req_user   = params:getHeader("user")
local req_ip   = params:getHeader("ip")
local company_extension_no = params:getHeader("company_extension_no");

--freeswitch.consoleLog("notice", "Debug from gen_dir_user_xml.lua, provided params:\n" .. params:serialize() .. "\n")

if req_user == nil then

 XML_STRING =

  [[<?xml version="1.0" encoding="UTF-8" standalone="no"?>
                <document type="freeswitch/xml">
                <section name="directory">
 </section>
                </document>]]

 return;
end


--local req_sip_auth_method = params:getHeader("sip_auth_method")

--assert (req_domain and req_key and req_user, "This example script only supports generating directory xml for a single user !\n")
 
local dbh = freeswitch.Dbh("odbc://freeswitch:ovswitch:ovswitch123")
if dbh:connected() == false then
  freeswitch.consoleLog("notice", "vm_user.lua cannot connect to database" .. dsn .. "\n")
  return
end


function split(s, delimiter)
    result = {};
    for match in (s..delimiter):gmatch("(.-)"..delimiter) do
        table.insert(result, match);
    end
    return result;
end

message_len = 0 ;

vmcount = 0;
FSHOST=req_domain

local my_query = string.format("select vm_password as vm_password,   vm_password as passwd, vm_name as pbx_extension_name, mailbox, '1' as vm_enabled, '' as callgroup, 'no' as nat, 'no' as  ip_restricted, ''  as ip_address, vm_name as extname, switch_pbx_voicemail.vm_no  as company_extension_no, account_id pbx_id, mailbox as id ,'default' as 'user_context',  '".. FSHOST.."' as 'domain' , switch_pbx_voicemail.email_address as email, switch_pbx_voicemail.email_attach_file vm_email_attachment, no_of_vm as  max_no_of_vm, no_of_vm_len as  vm_max_message_length  from switch_pbx_voicemail where mailbox = '"..req_user.."'  limit 1")

local my_query = string.format("select '1234' as vm_password,   '123456' as passwd, display_name as pbx_extension_name, username mailbox, '1' as vm_enabled, '' as callgroup, 'no' as nat, 'no' as  ip_restricted, ''  as ip_address, username as extname, username as company_extension_no,  username as pbx_id, username as id ,'default' as 'user_context',   '".. FSHOST.."' as 'domain' , voicemail_email as email, '1'as vm_email_attachment, 20 as  max_no_of_vm, 60 as  vm_max_message_length  from customer_sip_account where username = '"..req_user.."'  limit 1")

freeswitch.consoleLog("notice", "vm_user.lua User Query "..my_query .. "\n")
 
assert (dbh:query(my_query, function(row_subs) -- there will be only 0 or 1 iteration (limit 1)
		
		vmattachfile = '';		
		if row_subs.vm_email_enabled == 'Y' then		
			vmnotif = 	"<param name=\"vm-email-all-messages\" value=\"true\"/> <param name=\"vm-mailfrom\" value=\"test@openvoips.org\"/>   <param name=\"vm-mailto\" value=\""..row_subs.email.."\"/>";
			if row_subs.vm_email_attachment == 'Y' then
				vmattachfile = "<param name=\"vm-attach-file\" value=\"true\" />"
			else
				vmattachfile = "<param name=\"vm-attach-file\" value=\"false\" />"
			end
		else
			vmnotif = '';
		end
		
		
		if row_subs.nat == 'Y' then
                        nat = "<variable name=\"sip-force-contact\" value=\"NDLB-connectile-dysfunction\"/>"
                else
                        nat = ""
                end
		message_leng = 60;
		if row_subs.vm_max_message_length ~= nil then
			if vmcount > 0  then 
				message_length = vmcount * tonumber(row_subs.vm_max_message_length);
				allowmsg_leng = (tonumber(row_subs.max_no_of_vm) + 2) * tonumber(row_subs.vm_max_message_length);
				if allowmsg_leng > message_length then
					message_leng = message_len + tonumber(row_subs.vm_max_message_length);
				end
			else
				message_leng = tonumber(row_subs.vm_max_message_length);
			end
		end	
		vmmaillen = "<param name=\"vm-disk-quota\" value=\""..message_leng.."\"/>";
		cidrlist = 1;
		if req_ip ~= nil  then
			if row_subs.ip_restricted == 'Y' then
				if row_subs.ip_address ~= nil then 
					cidrlist = 0;
					iplist =	split(row_subs.ip_address,',');		
					for _,line in ipairs(iplist) do
					   if line == req_ip or line == '0.0.0.0' then 
						cidrlist = 1;
					   end 
					end
				end
			end
		end
cidrlist = 1;
-- <user id="]] .. row_subs.mailbox .. [[" mailbox="]] .. row_subs.mailbox .. [[">
-- 	<param name="vm-alternate-greet-id" value="]]..company_extension_no..[["/>	
if cidrlist == 1 then 
	XML_STRING =
  
  [[<?xml version="1.0" encoding="UTF-8" standalone="no"?>
                <document type="freeswitch/xml">
                <section name="directory">
                <domain name="]]..row_subs.domain..[["> 


<user id="]] .. row_subs.mailbox .. [[" mailbox="]] .. row_subs.mailbox .. [["> 
                <params>
 <param name="dial-string" value="{^^:sip_invite_domain=${dialed_domain}:presence_id=${dialed_user}@${dialed_domain}}${sofia_contact(*/${dialed_user}@${dialed_domain})},${verto_contact(${dialed_user}@${dialed_domain})}"/>
  <param name="jsonrpc-allowed-methods" value="verto"/>
                <param name="password" value="]] .. row_subs.passwd .. [["/>
                <param name="vm-password" value="]]..row_subs.vm_password..[["/>
            
		<param name="manage-shared-appearance" value="true"/>
		<param name="http-allowed-api" value="voicemail"/>
	

	 <param name="vm-storage-dir" value="/usr/share/nginx/html/vm/]]..row_subs.pbx_id..[[/]]..req_user..[["/>
                ]]..vmattachfile..[[
                ]]..vmmaillen..[[
				]]..vmnotif..[[
</params>
                <variables>
                        <variable name="user_context" value="default"/>
                       <variable name="accountcode" value="]]..row_subs.pbx_extension_name..[["/>
                        <variable name="effective_caller_id_name" value="]]..row_subs.extname..[["/>
                        <variable name="caller_id_name" value="]]..row_subs.extname..[["/>
                        <variable name="effective_caller_id_number" value="]]..row_subs.company_extension_no..[["/> 
                        <variable name="caller_id_number" value="]]..row_subs.company_extension_no..[["/>  
                        <variable name="sip-force-expires" value="300"/>

]]..nat..[[
        </variables>
                </user>
                </domain>
                </section>
                </document>]]
  -- comment the following line for production:
else
  XML_STRING =

  [[<?xml version="1.0" encoding="UTF-8" standalone="no"?>
                <document type="freeswitch/xml">
                <section name="directory">
 </section>
                </document>]]

end 
freeswitch.consoleLog("notice", "Debug from gen_dir_user_xml.lua, generated XML:\n" .. XML_STRING .. "\n")
end))

