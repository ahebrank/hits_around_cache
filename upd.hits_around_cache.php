<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// define the old-style EE object
if (!function_exists('ee')) {
    function ee()
    {
        static $EE;
        if (! $EE) {
          $EE = get_instance();
        }
        return $EE;
    }
}

/**
 * ExpressionEngine - by EllisLab
 *
 * @package   ExpressionEngine
 * @author    ExpressionEngine Dev Team
 * @copyright Copyright (c) 2003 - 2011, EllisLab, Inc.
 * @license   http://expressionengine.com/user_guide/license.html
 * @link    http://expressionengine.com
 * @since   Version 2.0
 * @filesource
 */
 
// ------------------------------------------------------------------------

/**
 * Hits Around Cache Module Install/Update File
 *
 * @package   ExpressionEngine
 * @subpackage  Addons
 * @category  Module
 * @author    Andy Hebrank
 * @link    
 */

class Hits_around_cache_upd {
  
  public $version = '0.1';
  
  /**
   * Constructor
   */
  public function __construct() {
  }
  
  // ----------------------------------------------------------------
  
  /**
   * Installation Method
   *
   * @return  boolean   TRUE
   */
  public function install() {
    $mod_data = array(
      'module_name'     => 'Hits_around_cache',
      'module_version'    => $this->version,
      'has_cp_backend'    => 'n',
      'has_publish_fields'  => 'n'
    );
    ee()->db->insert('modules', $mod_data);

    // register the front end hit action
    $action_data = array(
      'class' => 'Hits_around_cache',
      'method' => 'frontend_hit');
    ee()->db->insert('actions', $action_data);

    // make a table to store hits
    ee()->load->dbforge();
    $fields = array(
      'hit_id' => array('type' => 'int', 'constraint' => '10', 'unsigned' => true, 'auto_incremenet' => true),
      'url_title' => array('type' => 'varchar', 'constraint' => '256'),
      'timestamp' => array('type' => 'timestamp')
      );
    ee()->dbforge->add_field($fields);
    ee()->dbforge->add_key('hit_id', true);
    ee()->dbforge->create_table('hits_ac');

    return true;
  }

  // ----------------------------------------------------------------
  
  /**
   * Uninstall
   *
   * @return  boolean   TRUE
   */ 
  public function uninstall()
  {
    $mod_id = ee()->db->select('module_id')
                ->get_where('modules', array(
                  'module_name' => 'Hits_around_cache'
                ))->row('module_id');
    
    ee()->db->where('module_id', $mod_id)
           ->delete('module_member_groups');
    
    ee()->db->where('module_name', 'Hits_around_cache')
           ->delete('modules');
    
    ee()->db->where('class', 'Hits_around_cache')
            ->delete('actions');

    ee()->load->dbforge();
    ee()->dbforge->delete_table('hits_ac');
    
    return true;
  }
  
  // ----------------------------------------------------------------
  
  /**
   * Module Updater
   *
   * @return  boolean   TRUE
   */ 
  public function update($current = '')
  {
    // If you have updates, drop 'em in here.
    return true;
  }
  
}
/* End of file upd.hits_around_cache.php */