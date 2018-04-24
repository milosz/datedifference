<?php
/**
 * Plugin datedifference: Display date difference
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Milosz Galazka <milosz@sleeplessbeastie.eu>
 */
 
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
 
/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_datedifference extends DokuWiki_Syntax_Plugin {
 
   /**
    * Get the type of syntax this plugin defines.
    *
    */
    function getType(){
        return 'substition';
    }
 
   /**
    * Define how this plugin is handled regarding paragraphs.
    *
    */
    function getPType(){
        return 'normal';
    }
 
   /**
    * Where to sort in?
    *
    * Doku_Parser_Mode_html	190
    *  --> execute here <--
    * Doku_Parser_Mode_code	200
    */
    function getSort(){
        return 199;
    }
 
   /**
    * Connect lookup pattern to lexer.
    *
    */
    function connectTo($mode) {
      $this->Lexer->addSpecialPattern('{{datedifference.*?}}',$mode,'plugin_datedifference');
    }
 
   /**
    * Handler to prepare matched data for the rendering process.
    *
    */
    function handle($match, $state, $pos, &$handler){
	$data = array();

	// default
        $data['from']   = 'now';
        $data['to']     = 'now + 1 minute';
	$data['finmsg'] = '';
	$data['arrow']  = 1;
	$data['debug']  = 0;

	// parse params
	$provided_data = substr($match, 17, -2);
	$arguments = explode (',', $provided_data);
	foreach ($arguments as $argument) {
		list($key, $value) = explode('=', $argument);
		switch($key) {
			case 'from':
				$data['from'] = $value;
				break;
			case 'to':
				$data['to'] = $value;
				break;
			case 'finmsg':
				$data['finmsg'] = $value;
				break;
			case 'arrow':
				switch(strtolower($value)) {
					case 'yes':
						$data['arrow'] = 1;
						break;
					default:
						$data['arrow'] = 0;
						break;
				}
				break;
			case 'debug':
				switch(strtolower($value)) {
					case 'yes':
						$data['debug'] = 1;
						break;
					default:
						$data['debug'] = 0;
						break;
				}
				break;
		}
	}
        return $data;
    }
 
   /**
    * Handle the actual output creation.
    *
    */
    function render($mode, &$renderer, $data) {
        if ($mode == 'xhtml'){
            $renderer->doc .= $this->calculate_difference($data);
            return true;
        }
        return false;
    }

   /**
    * Calculate date and time difference 
    *
    */
    function calculate_difference($data) {
	// set params
	$date_from   = new DateTime($data['from']);
	$date_to     = new DateTime($data['to']);
	$date_finmsg = $data['finmsg'];
	$date_arrow  = $data['arrow'];
	$date_debug  = $data['debug'];

	// use UTC internally
	$date_from->setTimezone(new DateTimeZone('UTC'));
	$date_to->setTimezone(new DateTimeZone('UTC'));

	// debug information
	if($date_debug == 1)
		$debug  = "(" . $date_from->format(DateTime::RFC3339) . " &rarr; " . $date_to->format(DateTime::RFC3339) . ")";
	else
		$debug = "";

	// arrows 
	// up   - target date is in the future
	// down - target date is in the past
	if($date_arrow == 1) 
		if($date_from < $date_to)
			$arrow = "&uarr;";
		else
			$arrow = "&darr;";
	else
		$arrow = "";

	// calculate date difference
	if($date_to <= $date_from && !empty($date_finmsg)) {
		$result = $date_finmsg;
		$arrow = "";
	} else {
		// calculate interval
		$interval = date_diff($date_from, $date_to);

		// get details
		$years    = $interval->format('%y');
		$months   = $interval->format('%m');
		$days     = $interval->format('%d');
		$hours    = $interval->format('%h');
		$minutes  = $interval->format('%i');
 
		$result = ""; 
		if($years > 1) 
			$result .= "$years years ";
		elseif($years == 1)
			$result .= "$years year ";

		if($months > 1) 
			$result .= "$months months ";
		elseif($months == 1)
			$result .= "$months month ";

		if($days > 1) 
			$result .= "$days days ";
		elseif($days == 1)
			$result .= "$days day ";

		if($hours > 1) 
			$result .= "$hours hours ";
		elseif($hours == 1)
			$result .= "$hours hour ";

		if($minutes > 1) 
			$result .= "$minutes minutes ";
		elseif($minutes == 1)
			$result .= "$minutes minute ";
	}

	// concatenate result, debug information and up/down arrow
	$html = "<span class=\"datedifference\">$result $debug $arrow</span>";

        return $html;
    }
}
 
//Setup VIM: ex: et ts=4 enc=utf-8 :
?>
