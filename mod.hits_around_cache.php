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

    // the the opportunity to run a summary (one extra query if nothing to be done)
    $this->_summarize_hits();

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

    $start_date = $this->_get_start_date();

    // count unsummarized hits
    $q = ee()->db->select('COUNT(hit_id) count')
            ->from('hits_ac')
            ->where('entry_id', $entry_id)
            ->where('timestamp >=', $start_date." 00:00:00")
            ->get();
    $count = $q->row('count');

    // count summarized hits
    $q = ee()->db->select('COUNT(id) count')
            ->from('hits_ac_summary')
            ->where('entry_id', $entry_id)
            ->where('date >=', $start_date)
            ->get();
    $count += $q->row('count');

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
    $start_date = $this->_get_start_date();

    // count the unsummarized
    $q = ee()->db->select('entry_id, COUNT(hit_id) count')
            ->from('hits_ac')
            ->where('timestamp >=', $start_date.' 00:00:00')
            ->where('entry_id !=', 0)
            ->group_by('entry_id')
            ->get();
    $results = $q->result_array();
    $counts = array();
    foreach ($results as $row) {
      $counts[$row['entry_id']] = $row['count'];
    }

    // count summarized
    $q = ee()->db->select('entry_id, SUM(hits) count')
            ->from('hits_ac_summary')
            ->where('date >=', $start_date)
            ->where('entry_id !=', 0)
            ->group_by('entry_id')
            ->get();

    $results = $q->result_array();
    foreach ($results as $row) {
      if (!array_key_exists($row['entry_id'], $counts)) {
        $counts[$row['entry_id']] = 0;
      }
      $counts[$row['entry_id']] += $row['count'];
    }

    // select the entrys with $limit top counts
    arsort($counts);
    $entry_ids = array_keys(array_slice($counts, 0, $limit, true));

    return $this->return_data = implode('|', $entry_ids);
  }

  /**
   * Helper: make the previous parameter into a MYSQL timestamp
   *
   */ 
  function _get_start_date() {
    $previous = ee()->TMPL->fetch_param('previous');
    $previous = ($previous === false)? "-1 month":$previous;

    return date('Y-m-d', strtotime($previous));
  }

  /**
   * Helper: summarize any hits on previous days
   *
   */
  function _summarize_hits() {
    // select any hits from before today
    // we're summarizing with a count per entry_id per day
    $q = ee()->db->select("entry_id, COUNT(hit_id) hits, DATE_FORMAT(timestamp, '%Y-%m-%d') date", false)
            ->from('hits_ac')
            ->where('timestamp <', date('Y-m-d').' 00:00:00')
            ->where('entry_id !=', '0')
            ->group_by('date, entry_id')
            ->get();

    if ($q->num_rows() == 0) return false;

    // insert those counts
    $results = $q->result_array();
    foreach ($results as $row) {
      // already an entry for this date/entry_id?
      $summary_q = ee()->db->select('id, hits')
        ->from('hits_ac_summary')
        ->where('date', $row['date'])
        ->where('entry_id', $row['entry_id'])
        ->get();

      if ($summary_q->num_rows()) {
        $summary_row = $summary_q->row_array();
        // add the pre-existing counts
        $row['hits'] += $summary_row['hits'];
        // update
        ee()->db->where('id', $summary_row['id'])
          ->update('hits_ac_summary', $row);
      } else {
        // insert a new row
        ee()->db->insert('hits_ac_summary', $row);
      }
    }

    // clear out the unsummarized hit counts
    // it's a little dangerous to do this as a separate transaction but should be OK
    ee()->db->delete('hits_ac', array('timestamp <' => date('Y-m-d').' 00:00:00'));

    return true;
  }
  
}
/* End of file mod.hits_around_cache.php */