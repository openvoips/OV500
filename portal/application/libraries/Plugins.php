<?php
/**
* Codeigniter Plugin System
*
* A hook based plugin library for adding in Wordpress like plugin functionality.
*
* NOTICE OF LICENSE
*
* Licensed under the Open Software License version 3.0
*
* This source file is subject to the Open Software License (OSL 3.0) that is
* bundled with this package in the files license.txt / license.rst. It is
* also available through the world wide web at this URL:
* http://opensource.org/licenses/OSL-3.0
* If you did not receive a copy of the license and are unable to obtain it
* through the world wide web, please send an email to
* licensing@ellislab.com so we can send you a copy immediately.
*
* @package CI Plugin System
* @author Dwayne Charrington
* @copyright Copyright (c) 2012 - Dwayne Charrington
* @license http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
* @link http://ilikekillnerds.com
* @since Version 1.1
*/
  
class Plugins {
    
    // Codeigniter instance
    protected $_ci;
    
    // Instance of this class
    public static $instance;
    
    // Action statics
    public static $actions;
    public static $current_action;
    public static $run_actions;
    
    // Plugins
    public static $plugins_pool;
    public static $plugins_active;
    
    // Directory
    public $plugins_dir;
    
    // Error and Message Pools
    public static $errors;
    public static $messages;
    
    /**
    * Constructor
    * 
    * @param mixed $params
    * @return Plugins
    */
	public function __construct($params = array())
    {
        // Codeigniter instance
        $this->_ci =& get_instance();
        
        $this->_ci->load->database();
        $this->_ci->load->helper('directory');
        $this->_ci->load->helper('file');
        $this->_ci->load->helper('url');
        
        // Set the plugins directory if passed via paramater
        if (array_key_exists('plugins_dir', $params))
        {
        	$this->set_plugin_dir($params['plugins_dir']);
        }
        else // else set to default value
        {
            $basepath = str_replace("\\", "/", FCPATH);
        	$this->set_plugin_dir($basepath . "application/modules/");
        }
        
        // Remove index.php string on the plugins directory if any
        $this->plugins_dir = str_replace("index.php", "", $this->plugins_dir);  
		
		//echo $this->plugins_dir;    
                
        // Find all plugins
        $this->find_plugins();
		
		//print_r(self:: $plugins_pool);
        
        // Get all activated plugins
        $this->get_activated_plugins();
        
        // Include plugins
        $this->include_plugins();       
        
        self::$messages = ""; // Clear messages
        self::$errors   = ""; // Clear errors                      
    }
	
	
	
	
	public function  get_plugins_pool()
	{
		return self::$plugins_pool;
	}
	public function  get_plugins_active()
	{
		return self::$plugins_active;
	}
	
	
	 public function fetch_plugin_headers($plugin)
    {
        if (self::$plugins_pool !== false AND !empty(self::$plugins_pool))
        {     
            $plugin = strtolower(trim($plugin)); // Lowercase and trim the plugin name
            
            $plugin_data = read_file($this->plugins_dir.$plugin."/".$plugin.".php"); // Load the plugin we want
                   
            preg_match ('|Plugin Name:(.*)$|mi', $plugin_data, $name);
            preg_match ('|Plugin URI:(.*)$|mi', $plugin_data, $uri);
            preg_match ('|Version:(.*)|i', $plugin_data, $version);
            preg_match ('|Description:(.*)$|mi', $plugin_data, $description);
            preg_match ('|Author:(.*)$|mi', $plugin_data, $author_name);
            preg_match ('|Author URI:(.*)$|mi', $plugin_data, $author_uri);
                
            if (isset($name[1]))
            {
                $arr['plugin_name'] = trim($name[1]);
            }
            
            if (isset($uri[1]))
            {

                $arr['plugin_uri'] = trim($uri[1]);
            }
            
            if (isset($version[1]))
            {
                $arr['plugin_version'] = trim($version[1]);
            }
            
            if (isset($description[1]))
            {
                $arr['plugin_description'] = trim($description[1]);
            }
            
            if (isset($author_name[1]))
            {
                $arr['plugin_author'] = trim($author_name[1]);
            }
            
            if (isset($author_uri[1]))
            {
                $arr['plugin_author_uri'] = trim($author_uri[1]);
            }
			
			$arr['plugin_system_name'] = $plugin;
			return $arr;
            
            
        } 
    }
	
	
	public function change_status($plugin_system_name)
	{
		try {
		
			 $this->_ci->db->trans_begin();
			$sql = "SELECT plugin_system_name FROM ".$this->_ci->db->dbprefix('plugins')." WHERE  plugin_system_name ='".$plugin_system_name."'";
			$query = $this->_ci->db->query($sql);
			$row = $query->row();
			if(isset($row)) 
			{
				$result = $this->_ci->db->delete('plugins', array('plugin_system_name' => $plugin_system_name));	
				if(!$result){
					$error_array = $this->db->error();
					throw new Exception($error_array['message']);
				}
				$function_name = 'plugin_'.$plugin_system_name.'_uninstall';
			}
			else
			{
				$header_info = $this->fetch_plugin_headers($plugin_system_name);
				if(!isset($header_info['plugin_name']) ||  $header_info['plugin_name']=='')
				{
					return 'Problem On Plugin';
				}			
				
				$plugins_array =$header_info;
				$plugins_array['plugin_system_name'] = $plugin_system_name;			
				
				$str = $this->_ci->db->insert_string('plugins', $plugins_array);
				$result = $this->_ci->db->query($str);
				if (!$result) {
					$error_array = $this->db->error();
					throw new Exception($error_array['message']);
				}
				$function_name = 'plugin_'.$plugin_system_name.'_install';
			}
			
			 $file_path = $this->plugins_dir.$plugin_system_name."/".$plugin_system_name.".php";			 
			 include_once($file_path);
			 if(function_exists($function_name))
			 {
			 	$return = $function_name();
				if ($return !== true) {
					throw new Exception($return);
				}
			 }
		
			
			if($this->_ci->db->trans_status() === FALSE) {
                $error_array = $this->_ci->db->error();
                $this->_ci->db->trans_rollback();
                return $error_array['message'];
            } else {
               $this->_ci->db->trans_commit();
                return true;
            }
		
		} catch (Exception $e) {
            $this->_ci->db->trans_rollback();
            return $e->getMessage();
        }
	
	
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
   
    
    /**
    * Set Plugin Dir
    * Set the location of where all of the plugins are located
    * 
    * @param mixed $directory
    */
    public function set_plugin_dir($directory)
    {
        if (!empty($directory))
        {
            $this->plugins_dir = trim($directory);
        }    
    }
    
    /**
    * Instance
    * The instance of this plugin class
    * 
    */
    public static function instance()
    {
        if (!self::$instance)
        {
            self::$instance = new Plugins();
        }

        return self::$instance;
    }
    
    
    /**
    * Find Plugins
    * 
    * Find plugins in the plugins directory. 
    * 
    */
    public function find_plugins()
    {        
        $plugins = directory_map($this->plugins_dir, 1); // Find plugins
		//print_r($plugins);die;
       
        if ($plugins != false)
        {        
            foreach ($plugins AS $key => $name)
            {                 
                $name = strtolower(trim($name));
				$name =str_replace('\\','',$name );
				$name =str_replace('/','',$name );
                      
                // If the plugin hasn't already been added and isn't a file
                if (!isset(self::$plugins_pool[$name]) AND !stripos($name, "."))
                {              
                    // Make sure a valid plugin file by the same name as the folder exists
                    if (file_exists($this->plugins_dir.$name."/".$name.".php"))				
                    {
                        // Register the plugin
                        self::$plugins_pool[$name]['plugin'] = $name; 
                    }
                    else
                    {
                     //   self::$errors[$name][] = "Plugin file ".$name.".php does not exist.";
                    }
                }
            }
        }
		// echo '<pre>';print_r(self::$plugins_pool);die;
    }
    
    
    /**
    * Get Activated Plugins
    * Get all activated plugins from the database
    * 
    */
    public function get_activated_plugins()
    {
        // Only plugins in the database are active ones
        $plugins = $this->_ci->db->get('plugins');
	   
        // If we have activated plugins
        if ($plugins->num_rows() > 0)
        {
            // For every plugin, store it
            foreach ($plugins->result_array() AS $plugin)
            {
                $this->get_plugin_headers($plugin['plugin_system_name']);
                self::$plugins_active[$plugin['plugin_system_name']] = $plugin['plugin_system_name'];
            }
        }
        else
        {
            return true;
        }    
    }
    
    /**
    * Include Plugins
    * Include all active plugins that are in the database
    *  
    */
	public function include_plugins()
    {//echo '11';
		//echo '<pre>';print_r(self::$plugins_pool);echo '</pre>';
		//$plugins_pool
        if(self::$plugins_active AND !empty(self::$plugins_pool))
        {
            // Validate and include our found plugins
            foreach (self::$plugins_active AS $name => $value)
            {
                // The plugin information being added to the database
                /*
				$data = array(
                    "plugin_system_name" => $name,
                    "plugin_name"        => trim(self::$plugins_pool[$name]['plugin_info']['plugin_name']),
                    "plugin_uri"         => trim(self::$plugins_pool[$name]['plugin_info']['plugin_uri']),
                    "plugin_version"     => trim(self::$plugins_pool[$name]['plugin_info']['plugin_version']),
                    "plugin_description" => trim(self::$plugins_pool[$name]['plugin_info']['plugin_description']),
                    "plugin_author"      => trim(self::$plugins_pool[$name]['plugin_info']['plugin_author']),
                    "plugin_author_uri"  => trim(self::$plugins_pool[$name]['plugin_info']['plugin_author_uri'])
                );
				*/
				//echo $name.'<br>';
				$action_page = $this->plugins_dir.'/'.$name.'/action.php';
               
            	if(is_file($action_page) && file_exists($action_page))
				{
                // If the file was included
                include_once $action_page;				
            
                // Run the install action for this plugin
                self::do_action('install_' . $name);
				} 
            }   
        }
    }
	
    public function old_include_plugins()
    {
        if(self::$plugins_active AND !empty(self::$plugins_active))
        {
            // Validate and include our found plugins
            foreach (self::$plugins_active AS $name => $value)
            {
                // The plugin information being added to the database
                $data = array(
                    "plugin_system_name" => $name,
                    "plugin_name"        => trim(self::$plugins_pool[$name]['plugin_info']['plugin_name']),
                    "plugin_uri"         => trim(self::$plugins_pool[$name]['plugin_info']['plugin_uri']),
                    "plugin_version"     => trim(self::$plugins_pool[$name]['plugin_info']['plugin_version']),
                    "plugin_description" => trim(self::$plugins_pool[$name]['plugin_info']['plugin_description']),
                    "plugin_author"      => trim(self::$plugins_pool[$name]['plugin_info']['plugin_author']),
                    "plugin_author_uri"  => trim(self::$plugins_pool[$name]['plugin_info']['plugin_author_uri'])
                );
                $this->_ci->db->where('plugin_system_name', $name)->update('plugins', $data);
            
                // If the file was included
                include_once $this->plugins_dir.$name."/".$name.".php";				
            
                // Run the install action for this plugin
                self::do_action('install_' . $name); 
            }   
        }
    }
    
    
    /**
    * Get Plugin Headers
    *
    * Get the header information from all plugins in
    * the plugins pool for use later on.
    * 
    * @param mixed $plugin
    */
    public function get_plugin_headers($plugin)
    {
        if (self::$plugins_pool !== false AND !empty(self::$plugins_pool))
        {     
            $plugin = strtolower(trim($plugin)); // Lowercase and trim the plugin name
            
            $plugin_data = read_file($this->plugins_dir.$plugin."/".$plugin.".php"); // Load the plugin we want
                   
            preg_match ('|Plugin Name:(.*)$|mi', $plugin_data, $name);
            preg_match ('|Plugin URI:(.*)$|mi', $plugin_data, $uri);
            preg_match ('|Version:(.*)|i', $plugin_data, $version);
            preg_match ('|Description:(.*)$|mi', $plugin_data, $description);
            preg_match ('|Author:(.*)$|mi', $plugin_data, $author_name);
            preg_match ('|Author URI:(.*)$|mi', $plugin_data, $author_uri);
                
            if (isset($name[1]))
            {
                $arr['plugin_name'] = trim($name[1]);
            }
            
            if (isset($uri[1]))
            {

                $arr['plugin_uri'] = trim($uri[1]);
            }
            
            if (isset($version[1]))
            {
                $arr['plugin_version'] = trim($version[1]);
            }
            
            if (isset($description[1]))
            {
                $arr['plugin_description'] = trim($description[1]);
            }
            
            if (isset($author_name[1]))
            {
                $arr['plugin_author'] = trim($author_name[1]);
            }
            
            if (isset($author_uri[1]))
            {
                $arr['plugin_author_uri'] = trim($author_uri[1]);
            }
            
            // For every plugin header item
			if(isset($arr) && count($arr) >0)
			{
            foreach ($arr AS $k => $v)
            {
                // If the key doesn't exist or the value is not the same, update the array
                if (!isset(self::$plugins_pool[$plugin]['plugin_info'][$k]) OR self::$plugins_pool[$plugin]['plugin_info'][$k] != $v)
                {
                    self::$plugins_pool[$plugin]['plugin_info'][$k] = trim($v);
                }
                else
                {
                    return true;
                }
            }
			}
        } 
    }
    
    /**
    * Activate Plugin
    *
    * Activates a plugin only if it exists in the
    * plugins_pool. After activating, reload page
    * to get the newly activated plugin
    * 
    * @param mixed $name
    */
    public function activate_plugin($name)
    {
        $name = strtolower(trim($name)); // Make sure the name is lowercase and no spaces
        
        // Okay the plugin exists, push it to the activated array
        if (isset(self::$plugins_pool[$name]) AND !isset(self::$plugins_active[$name]))
        {            
            $db = $this->_ci->db->select('plugin_system_name')->where('plugin_system_name', $name)->get('plugins', 1);
            
            if ($db->num_rows() == 0)
            {
                $this->_ci->db->insert('plugins', array('plugin_system_name' => $name));   
            }
            
            // Run the activate hook
            self::do_action('activate_' . $name);
        }
    }
    
    /**
    * Deactivate Plugin
    *
    * Deactivates a plugin
    * 
    * @param string $name
    */
    public function deactivate_plugin($name)
    {
        $name = strtolower(trim($name)); // Make sure the name is lowercase and no spaces
        
        // Okay the plugin exists
        if (isset(self::$plugins_active[$name]))
        {
            $this->_ci->db->where('plugin_system_name', $name)->delete('plugins');
            self::$messages[] = "Plugin ".self::$plugins_pool[$name]['plugin_info']['plugin_name']." has been deactivated!";
            
            // Deactivate hook
            self::do_action('deactivate_' . $name);
        }        
    }
    
    
    /**
    * Plugin Info
    *
    * Get information about a specific plugin
    * 
    * @param mixed $name
    */
    public function plugin_info($name)
    {
        if (isset(self::$plugins_pool[$name]))
        {
            return self::$plugins_pool[$name]['plugin_info'];
        }
        else
        {
            return true;
        }
    }
    
    
    /**
    * Print Plugins
    *
    * This plugin returns the array of all plugins found
    * 
    */
    public function print_plugins()
    {
        return self::$plugins_pool;
    }
    
    
    /**
    * Add Action
    *
    * Add a new hook trigger action
    * 
    * @param mixed $name
    * @param mixed $function
    * @param mixed $priority
    */
    public function add_action($name, $function, $priority=10)
    {
        // If we have already registered this action return true
        if (isset(self::$actions[$name][$priority][$function]))
        {
            return true;
        }
        
        /**
        * Allows us to iterate through multiple action hooks.
        */
        if (is_array($name))
        {
            foreach ($name AS $name)
            {
                // Store the action hook in the $hooks array
                self::$actions[$name][$priority][$function] = array("function" => $function);
            }
        }
        else
        {
            // Store the action hook in the $hooks array
            self::$actions[$name][$priority][$function] = array("function" => $function);
        }
        
        return true;
    }
    
    
    /**
    * Do Action
    *
    * Trigger an action for a particular action hook
    * 
    * @param mixed $name
    * @param mixed $arguments
    * @return mixed
    */
    public function do_action($name, $arguments = "")
    {
        // Oh, no you didn't. Are you trying to run an action hook that doesn't exist?
        if (!isset(self::$actions[$name]))
        {
            return $arguments;
        }
        
        // Set the current running hook to this
        self::$current_action = $name;
        
        // Key sort our action hooks
        ksort(self::$actions[$name]);
        
        foreach(self::$actions[$name] AS $priority => $names)
        {
            if (is_array($names))
            {
                foreach($names AS $name)
                {
                    // This line runs our function and stores the result in a variable                    
                    $returnargs = call_user_func_array($name['function'], array(&$arguments));
                    
                    if ($returnargs)
                    {
                        $arguments = $returnargs;
                    }
                    
                    // Store our run hooks in the hooks history array
                    self::$run_actions[$name][$priority];
                }
            }
        }
        
        // No hook is running any more
        self::$current_action = '';
        
        return $arguments;
    }
	
	
      
    
    /**
    * Remove Action
    *
    * Remove an action hook. No more needs to be said.
    * 
    * @param mixed $name
    * @param mixed $function
    * @param mixed $priority
    */
    public function remove_action($name, $function, $priority=10)
    {
        // If the action hook doesn't, just return true
        if (!isset(self::$actions[$name][$priority][$function]))
        {
            return true;
        }
        
        // Remove the action hook from our hooks array
        unset(self::$actions[$name][$priority][$function]);
    }
    
    
    /**
    * Current Action
    *
    * Get the currently running action hook
    * 
    */
    public function current_action()
    {
        return self::$current_action;
    }
    
    
    /**
    * Has Run
    *
    * Check if a particular hook has been run
    * 
    * @param mixed $hook
    * @param mixed $priority
    */
    public function has_run($action, $priority = 10)
    {
        if (isset(self::$actions[$action][$priority]))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    
    /**
    * Action Exists
    *
    * Does a particular action hook even exist?
    * 
    * @param mixed $name
    */
    public function action_exists($name)
    {
        if (isset(self::$actions[$name]))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    
    /**
    * Will print our information about all plugins and actions
    * neatly presented to the user.
    * 
    */
    public static function debug_class()
    {
        if (isset(self::$plugins_pool))
        {
            echo "<h2>Found plugins</h2>";
            echo "<p>All plugins found in the plugins directory.</p>";
            echo "<pre>";
            print_r(self::$plugins_pool);
            echo "</pre>";
            echo "<br />";
            echo "<br />";
        }
        
        if (isset(self::$plugins_active))
        {
            echo "<h2>Activated plugins</h2>";
            echo "<p>Activated plugins that have already been included and are usable.</p>";
            echo "<pre>";
            print_r(self::$plugins_active);
            echo "</pre>";
            echo "<br />";
            echo "<br />";
        }
        
        if (isset(self::$actions))
        {
            echo "<h2>Register action hooks</h2>";
            echo "<p>Action hooks that have been registered by the application and can be called via plugin files.</p>";
            echo "<pre>";
            print_r(self::$actions);
            echo "</pre>";
            echo "<br />";
            echo "<br />";
        }        
        
        if (isset(self::$run_actions))
        {
            echo "<h2>Previously run action hooks</h2>";
            echo "<p>Hooks that have been called previously.</p>";
            echo "<pre>";
            print_r(self::$run_actions);
            echo "</pre>";
            echo "<br />";
            echo "<br />";
        }       
    }   
	
	/**
	* fetch cron actions
	*/
	public function fetch_cron_action()
    {
		$name='cron';
    
		$cron_array = array();
    
		if(isset(self::$actions[$name]))
		{
			foreach(self::$actions[$name] AS $priority => $names)
			{
				if(is_array($names))
				{
					foreach($names AS $name)
					{              
                        //echo '<br>Calling $name["function"]';
						$returnargs = call_user_func_array($name['function'],array(&$arguments));
						//$cron_array[] = $returnargs; 
					}
				}
			}     
		}
        return $cron_array;
    }
}


function get_license_key()
{


}
function get_machine_key($salt = "")
{

		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			$temp = sys_get_temp_dir().DIRECTORY_SEPARATOR."diskpartscript.txt";
			if(!file_exists($temp) && !is_file($temp)) file_put_contents($temp, "select disk 0\ndetail disk");
			$output = shell_exec("diskpart /s ".$temp);
			$lines = explode("\n",$output);
			$result = array_filter($lines,function($line) {
				return stripos($line,"ID:")!==false;
			});
			if(count($result)>0) {
				//$result = array_shift(array_values($result));
				$k=array_values($result);
				$result=array_shift($k);
				$result = explode(":",$result);
				$result = trim(end($result));       
			} else $result = $output;       
		} else {
			$result = shell_exec("blkid -o value -s UUID");  
			if(stripos($result,"blkid")!==false) {
				$result = $_SERVER['HTTP_HOST'];
			}
		}   
		return md5($salt.md5($result));
	
}

/**
* Add a new action hook
* 
* @param mixed $name
* @param mixed $function
* @param mixed $priority
*/
function add_action($name, $function, $priority=10)
{
    return Plugins::instance()->add_action($name, $function, $priority);
}

/**
* Run an action
* 
* @param mixed $name
* @param mixed $arguments
* @return mixed
*/
function do_action($name, $arguments = "")
{
    return Plugins::instance()->do_action($name, $arguments);
}

/**
* Remove an action
* 
* @param mixed $name
* @param mixed $function
* @param mixed $priority
*/
function remove_action($name, $function, $priority=10)
{
    return Plugins::instance()->remove_action($name, $function, $priority);
}

/**
* Check if an action actually exists
* 
* @param mixed $name
*/
function action_exists($name)
{
    return Plugins::instance()->action_exists($name);
}

/**
* Set the location of where our plugins are located
* 
* @param mixed $directory
*/
function set_plugin_dir($directory)
{
    Plugins::instance()->set_plugin_dir($directory);
}

/**
* Activate a specific plugin
* 
* @param mixed $name
*/
function activate($name)
{
    return Plugins::instance()->activate_plugin($name);
}

/**
* Deactivate a specific plugin
* 
* @param mixed $name
*/
function deactivate($name)
{
    return Plugins::instance()->deactivate_plugin($name);
}

/**
* Print Plugins
* Returns the list of plugins
* 
*/
function print_plugins()
{
    return Plugins::instance()->print_plugins();
}

/**
* Return the number of plugins found
* 
*/
function count_found_plugins()
{
    return count(Plugins::$plugins_pool);
}

/**
* Return number of plugins activated
* 
*/
function count_activated_plugins()
{
    return count(Plugins::$plugins_active);
}

/**
* Debug function will return all plugins registered and hooks
* 
*/
function debug_class()
{
    Plugins::debug_class();
}

/**
* Return all errors
*
*/
function plugin_errors()
{
    if (is_array(Plugins::$errors))
    {
        foreach (Plugins::$errors AS $k => $error)
        {
            echo $error."\n\r";   
        }
    }
    else
    {
        return true;
    }
}

/**
* Return all messages
*
*/
function plugin_messages()
{
    if (is_array(Plugins::$messages))
    {
        foreach (Plugins::$messages AS $k => $message)
        {
            echo $message."\n\r";   
        }
    }
    else
    {
        return true;
    }
}