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
    // record the hit to an entry
    // hopefully the timestamping is automatic via mysql
    $entry_id = ee()->input->post('entry_id', $xss_clean = true);
    ee()->db->insert('hits_ac', array('entry_id' => $entry_id));

    return true;
  }

   /**
   * Tag: insert the JS snippet that does the POSTing
   *
   */ 
   function frontend_js() {
    // pass a URL title
    if (($entry_id = ee()->TMPL->fetch_param('entry_id')) === false) {
      return $this->return_data = "Need an entry ID";
    }
    $params = "entry_id=".$entry_id;
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
    //    entry_id : defines the URL
    //    previous : how far back to go, in PHP strtotime form
    //               default to '-1 month'
    if (($entry_id = ee()->TMPL->fetch_param('entry_id')) === false) {
      return $this->return_data = "Need an entry_id";
    }

    $start_time = $this->_get_start_time();
    $q = ee()->db->select('COUNT(hit_id) count')
            ->from('hits_ac')
            ->where('entry_id', $entry_id)
            ->where('timestamp >=', $start_time)
            ->get();
    $count = $q->row('count');
    return $this->return_data = $count;
  }

  /**
   * Tag: loop top entries
   *
   */ 
  function top_hits() {
    // tag parameters
    //    previous : how far back to go, in PHP strtotime form
    //               default to '-1 month'
    //    limit : how many to return (defaults to 6)
    // 
    // returns a pipe-delimited list of entry ids
    $limit = ee()->TMPL->fetch_param('limit');
    $limit = ($limit === false)? 6:$limit;
    $start_time = $this->_get_start_time();

    // get the top $limit
    $q = ee()->db->select('entry_id, COUNT(hit_id) count')
            ->from('hits_ac')
            ->where('timestamp >=', $start_time)
            ->group_by('entry_id')
            ->limit($limit)
            ->order_by('count DESC')
            ->get();
    $results = $q->result_array();
    $get_entry_id = function($row) {
      return $row['entry_id'];
    };
    $entry_ids = array_map($get_entry_id, $results);
    return $this->return_data = implode('|', $entry_ids);
  }

  /**
   * Helper: make the previous parameter into a MYSQL timestamp
   *
   */ 
  function _get_start_time() {
    $previous = ee()->TMPL->fetch_param('previous');
    $previous = ($previous === false)? "-1 month":$previous;

    return date('Y-m-d H:i:s', strtotime($previous));
  }
  
}
/* End of file mod.hits_around_cache.php */