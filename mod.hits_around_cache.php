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
 * Hits Around Cache Module Front End File
 *
 * @package   ExpressionEngine
 * @subpackage  Addons
 * @category  Module
 * @author    Andy Hebrank
 * @link    
 */

class Hits_around_cache {
  
  public $return_data;
  
  /**
   * Constructor
   */
  public function __construct() {
  }
  
   /**
   * Register a hit from the front end
   *
   * @return  boolean   TRUE
   */ 
  function frontend_hit() {
    // record the URL title
    // hopefully the timestamping is automatic via mysql
    $url_title = ee()->input->get('url_title', $xss_clean = true);
    ee()->db->insert('hits_ac', array('url_title' => $url_title));

    return true;
  }

  /**
   * Count hits to a URL for a date range
   *
   * @return  int   number of hits
   */ 
  function hit_count() {
    // two tag parameters:
    //    url_title : defines the URL
    //    previous : how far back to go, in PHP strtotime form
    //               default to '-1 month'
    if (($url_title = ee()->TMPL->fetch_param('url_title')) === false) return;
    $previous = ee()->TMPL->fetch_param('previous');
    $previous = ($previous === false)? "-1 month":$previous;

    $start_time = date('Y-m-d H:i:s', strtotime($previous));
    $q = ee()->db->select('COUNT(hit_it) count')
            ->from('hits_ac')
            ->where('url_title', $url_title)
            ->where('timestamp >=', $start_time)
            ->get();
    return $q->row('count');
  }
  
  
}
/* End of file mod.hits_around_cache.php */
