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
   * Register a hit from the front end
   *
   * @return  boolean   TRUE
   */ 
  function frontend_hit() {
    // record the URL title
    // hopefully the timestamping is automatic via mysql
    $url_title = ee()->input->post('url_title', $xss_clean = true);
    ee()->db->insert('hits_ac', array('url_title' => $url_title));

    return true;
  }

   /**
   * Tag: insert the JS snippet that does the POSTing
   *
   */ 
   function frontend_js() {
    // pass a URL title
    if (($url_title = ee()->TMPL->fetch_param('url_title')) === false) {
      return $this->return_data = "Need a url_title";
    }
    $params = "url_title=".$url_title;
    // find the action id
    $action_id = ee()->functions->fetch_action_id('Hits_around_cache', 'frontend_hit');
    $url = ee()->functions->fetch_site_index(false, false).QUERY_MARKER."ACT=".$action_id;

    $script = '<script type="text/javascript">
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.open("POST", "'.$url.'",false);
  xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xmlhttp.send("'.$params.'");
</script>';
    
    return $this->return_data = $script;
  }

  /**
   * Tag: count hits to a URL for a date range
   *
   */ 
  function hit_count() {
    // two tag parameters:
    //    url_title : defines the URL
    //    previous : how far back to go, in PHP strtotime form
    //               default to '-1 month'
    if (($url_title = ee()->TMPL->fetch_param('url_title')) === false) {
      return $this->return_data = "Need a url_title";
    }
    $previous = ee()->TMPL->fetch_param('previous');
    $previous = ($previous === false)? "-1 month":$previous;

    $start_time = date('Y-m-d H:i:s', strtotime($previous));
    $q = ee()->db->select('COUNT(hit_id) count')
            ->from('hits_ac')
            ->where('url_title', $url_title)
            ->where('timestamp >=', $start_time)
            ->get();
    $count = $q->row('count');
    return $this->return_data = $count;
  }
  
  
}
/* End of file mod.hits_around_cache.php */