<?php 
// BitBucket POST Service script
// @author Joshua Canfield <www.icodeclarity.com>
// @description Initiates GIT Deployment and Synchronization post-hooks on remote server
// @version 0.2.20120331-RC1

date_default_timezone_set('America/Denver');

class Deploy {
	
	// Callback function after Deployment
	public $post_deploy;
	// Log file location (Future Changes)
	private $_log = 'deployments.log';
	private $_date_format = 'Y-m-d H:i:sP';
	
	private $_branch = 'master';
	private $_remote = 'origin';
	
	// Construct Options for script
	public function __construct($directory, $options = array())
	  {
	      // Determine the directory path
	      $this->_directory = realpath($directory).DIRECTORY_SEPARATOR;

	      $available_options = array('log', 'date_format', 'branch', 'remote');

	      foreach ($options as $option => $value)
	      {
	          if (in_array($option, $available_options))
	          {
	              $this->{'_'.$option} = $value;
	          }
	      }

	      $this->log('Attempting deployment...');
	  }
	
	// Write to log file
	public function log($message, $type = 'INFO')
	  {
	      if ($this->_log)
	      {
	          // Set the name of the log file
	          $filename = $this->_log;

	          if ( ! file_exists($filename))
	          {
	              // Create the log file
	              file_put_contents($filename, '');

	              // Allow anyone to write to log files
	              chmod($filename, 0666);
	          }

	          // Write the message into the log file
	          // Format: time --- type: message
	          file_put_contents($filename, date($this->_date_format).' --- '.$type.': '.$message.PHP_EOL, FILE_APPEND);
	      }
	  }
	
	public function execute_deployment()
	  {
	      try
	      {
	          // Make sure we're in the right directory
	          exec('cd '.$this->_directory, $output);
	          $this->log('Changing working directory... '.implode(' ', $output));

	          // Discard any changes to tracked files since our last deploy
	          exec('git reset --hard HEAD', $output);
	          $this->log('Reseting repository... '.implode(' ', $output));

	          // Update the local repository
	          exec('git pull '.$this->_remote.' '.$this->_branch, $output);
	          $this->log('Pulling in changes... '.implode(' ', $output));

	          // Secure the .git directory
	          exec('chmod -R og-rx .git');
	          $this->log('Securing .git directory... ');

	          if (is_callable($this->post_deploy))
	          {
	              call_user_func($this->post_deploy, $this->_data);
	          }

	          $this->log('Deployment successful.');
	      }
	      catch (Exception $e)
	      {
	          $this->log($e, 'ERROR');
	      }
	  }
} // End of PHP Class

// Deployment Execution

$deploy = new Deploy('/var/www/foobar.com');

$deploy->post_deploy = function() use ($deploy) {
  // hit the wp-admin page to update any db changes
  exec('curl http://www.foobar.com/wp-admin/upgrade.php?step=upgrade_db');
  $deploy->log('Updating wordpress database... ');
};

$deploy->execute();


?>