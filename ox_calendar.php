<?php
/*
	Plugin Name: Oxford Calendar Widget (Ox Calendar Widget)
	Plugin Script: oxCalendar.php
	Plugin URI: to be done
	Description: Shows you upcoming events for a configurable iCalendar .ics file. You can also set a range of dates. 
	The Ox Calendar Widget is a fork of the iCalendar Events Widget (version 0.3.3) by programmschmie.de [http://programmschmie.de]
	Version: 0.0.3
	Author: Guido Klingbeil, Marko Jung, programmschmie.de
	Author URI: to be done
	Text Domain: icalevents
	Domain Path: /languages/

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
	Online: http://www.gnu.org/licenses/gpl.txt
*/

// global variable to store the locations of the ongoing events
$currentlyOccupied = array();

define('OX_CALENDAR_VERSION', '0.0.3');

class oxCalendar extends WP_Widget {
	private	/** @type {string} */ $widgetFilePath;
	private /** @type {string} */ $libPath;
	private /** @type {string} */ $templatePath;
	private /** @type {string} */ $languagePath;
	private /** @type {string} */ $imagePath;
	private /** @type {string} */ $cssPath;
	private /** @type {string} */ $javaScriptPath;
    
    
    function oxCalendar() {
		$this->widgetFilePath	= dirname(__FILE__);
		$this->libPath			= $this->widgetFilePath.'/lib/';
		$this->templatePath		= $this->widgetFilePath.'/templates/';
		$this->imagePath		= $this->widgetFilePath.'/images/';
		$this->cssPath			= basename(dirname(__FILE__)).'/css/';
		$this->javaScriptPath	= basename(dirname(__FILE__)).'/js/';
		$this->languagePath		= basename(dirname(__FILE__)).'/languages';
	    
    	load_plugin_textdomain('icalevents', 'false', $this->languagePath);

	    if (!file_exists( $this->libPath.'class.iCalReader.php' )) return false;
	    require_once( $this->libPath.'class.iCalReader.php' );
		
	    if (!file_exists( $this->libPath.'class.Template.php' )) return false;
	    require_once( $this->libPath.'class.Template.php' );

		// widgets own javascript files
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-core');

	    wp_register_script('jquery-ui-datepicker', plugins_url($this->javaScriptPath.'jquery.ui.datepicker.min.js'), false, ICALEVENTS_VERSION);
	    wp_enqueue_script('jquery-ui-datepicker');
		
	    wp_register_script('iCalendarJS', plugins_url($this->javaScriptPath.'icalendar.js'), false, OX_CALENDAR_VERSION);
	    wp_enqueue_script('iCalendarJS');
		
	    wp_register_script('DateJS', plugins_url($this->javaScriptPath.'date.js'), false, OX_CALENDAR_VERSION);
	    wp_enqueue_script('DateJS');

	    // widgets own css styles
	    wp_register_style('jquery-ui-theme', plugins_url($this->javaScriptPath.'custom-theme/jquery-ui-1.8.16.custom.css'), array(), OX_CALENDAR_VERSION, 'screen');
	    wp_enqueue_style('jquery-ui-theme');

	    wp_register_style('ui-datepicker', plugins_url($this->cssPath.'ui.datepicker.css'), array(), OX_CALENDAR_VERSION, 'screen');
	    wp_enqueue_style('ui-datepicker');

	    wp_register_style('iCalendarCSS', plugins_url($this->cssPath.'icalendar.css'), array(), OX_CALENDAR_VERSION, 'screen');
	    wp_enqueue_style('iCalendarCSS');

	    $widget_ops = array('classname' => __CLASS__, 'description' => __('Shows you upcoming events for a configurable iCalendar .ics file.', 'icalevents'));
	    parent::WP_Widget(__CLASS__, 'Oxford Calendar', $widget_ops);
    }

    function form( $instance ) {
	    
        // setting up stored values for all widget options
        if ( $instance ) {
			$title					= esc_attr( $instance[ 'title' ] );
			$iCalURI				= esc_attr( $instance[ 'iCalURI' ] );
			$showNrOfEvents			= esc_attr( $instance[ 'showNrOfEvents' ] );
			$showEventDateStart		= esc_attr( $instance[ 'showEventDateStart' ] );
			$showEventDateEnd		= esc_attr( $instance[ 'showEventDateEnd' ] );
			$showEventTimeStart		= esc_attr( $instance[ 'showEventTimeStart' ] );
			$showEventTimeEnd		= esc_attr( $instance[ 'showEventTimeEnd' ] );
			$showEventSummary		= esc_attr( $instance[ 'showEventSummary' ] );
			$showEventDescription	= esc_attr( $instance[ 'showEventDescription' ] );
			$showEventLocation		= esc_attr( $instance[ 'showEventLocation' ] );
			$showEventURL			= esc_attr( $instance[ 'showEventURL' ] );
			$showEventRangeDateFrom	= esc_attr( $instance[ 'showEventRangeDateFrom' ] );
			$showEventRangeDateTo	= esc_attr( $instance[ 'showEventRangeDateTo' ] );
			
			// additional widget options required by the BSG
			$showTitle		= esc_attr( $instance[ 'showTitle' ] );
			$showAllDayEvents	= esc_attr( $instance[ 'showAllDayEvents' ] );
			$showMultiDayEvents	= esc_attr( $instance[ 'showMultiDayEvents' ] );
			
		// setting up default values for all widget options
		} else {
			$title					= __( 'Oxford Calendar', 'icalevents' );
			$iCalURI				= '';
			$showNrOfEvents			= '5';			// show the next 5 events by default
			$showEventDateStart		= true;			// 0/false = no, 1/true = yes
			$showEventDateEnd		= false;		// 0/false = no, 1/true = yes
			$showEventTimeStart		= false;		// 0/false = no, 1/true = yes
			$showEventTimeEnd		= false;		// 0/false = no, 1/true = yes
			$showEventSummary		= true;			// 0/false = no, 1/true = yes
			$showEventDescription	= true;			// 0/false = no, 1/true = yes
			$showEventLocation		= true;			// 0/false = no, 1/true = yes
			$showEventURL			= false;		// 0/false = no, 1/true = yes
			$showEventRangeDateFrom	= '';
			$showEventRangeDateTo	= '';
			
			// additional widget option required by the BSG
			$showTitle = false;
			$showAllDayEvents = '';
			$showMultiDayEvents = '';
			
		}
		
		// build an asoc array with all of our options as items
		$widgetOptions = array(

			// ---------------------------------------------------------------------
			// list of textfields
			// ---------------------------------------------------------------------
			array(
				'field_id'		=> 'title',
				'field_name'	=> 'title',
				'field_label'	=> __( 'Title', 'icalevents' ),
				'field_title'	=> __( 'If you leave this setting empty, this widget will show the calendar name in your sidebar box title', 'icalevents' ),
				'field_type'	=> 'textfield',
				'field_css'		=> 'widefat',
				'field_value'	=> $title			),
			array(
				'field_id'		=> 'iCalURI',
				'field_name'	=> 'iCalURI',
				'field_label'	=> __( 'iCalendar Subscription URL', 'icalevents' ),
				'field_title'	=> __( 'Enter a full qualified URL to a iCalendar subscription. It *MUST* start with \'http://\' or \'webcal://\'. ', 'icalevents' ),
				'field_type'	=> 'textfield',
				'field_css'		=> 'widefat',
				'field_value'	=> $iCalURI,
			),
			array(
				'field_id'		=> 'showNrOfEvents',
				'field_name'	=> 'showNrOfEvents',
				'field_label'	=> __( 'Show # of upcoming events', 'icalevents' ),
				'field_title'	=> __( 'Enter a numeric value here. It represents the number of the events that will be shown in your sidebar box.', 'icalevents' ),
				'field_type'	=> 'textfield',
				'field_css'		=> 'widefat',
				'field_value'	=> $showNrOfEvents
			),
			array(
				'field_type'	=> 'separator'
			),
			array(
				'field_type'	=> 'note',
				'field_value'	=> __( 'Select a date range here. The format of the given date must be "YYYY/MM/DD".', 'icalevents' )
			),
			array(
				'field_id'		=> 'showEventRangeDateFrom',
				'field_name'	=> 'showEventRangeDateFrom',
				'field_label'	=> __( 'Show Events from', 'icalevents' ),
				'field_title'	=> __( 'If you leave this setting empty, the current date will be used.', 'icalevents' ),
				'field_type'	=> 'datepicker',
				'field_css'		=> 'rangeDateFrom',
				'field_value'	=> $showEventRangeDateFrom
			),
			array(
				'field_id'		=> 'showEventRangeDateTo',
				'field_name'	=> 'showEventRangeDateTo',
				'field_label'	=> __( 'Show Events to', 'icalevents' ),
				'field_title'	=> __( 'If you leave this setting empty, the 2038/12/31 will be used as the maximum date.', 'icalevents' ),
				'field_type'	=> 'datepicker',
				'field_css'		=> 'rangeDateTo',
				'field_value'	=> $showEventRangeDateTo
			),
			array(
				'field_type'	=> 'separator'
			),
			
			// ---------------------------------------------------------------------
			// list of checkboxes
			// ---------------------------------------------------------------------
			array(
				'field_id'		=> 'showTitle',
				'field_name'	=> 'showTitle',
				'field_label'	=> __( 'Show calendar title', 'icalevents' ),
				'field_title'	=> __( '', 'icalevents' ),
				'field_type'	=> 'checkbox',
				'field_css'		=> '',
				'field_value'	=> $showTitle
			),
			array(
				'field_id'		=> 'showEventSummary',
				'field_name'	=> 'showEventSummary',
				'field_label'	=> __( 'Show event summary', 'icalevents' ),
				'field_title'	=> __( '', 'icalevents' ),
				'field_type'	=> 'checkbox',
				'field_css'		=> '',
				'field_value'	=> $showEventSummary
			),
			array(
				'field_id'		=> 'showEventDateStart',
				'field_name'	=> 'showEventDateStart',
				'field_label'	=> __( 'Show start date', 'icalevents' ),
				'field_title'	=> __( '', 'icalevents' ),
				'field_type'	=> 'checkbox',
				'field_css'		=> '',
				'field_value'	=> $showEventDateStart
			),
			array(
				'field_id'		=> 'showEventTimeStart',
				'field_name'	=> 'showEventTimeStart',
				'field_label'	=> __( 'Show start time', 'icalevents' ),
				'field_title'	=> __( '', 'icalevents' ),
				'field_type'	=> 'checkbox',
				'field_css'		=> '',
				'field_value'	=> $showEventTimeStart
			),
			array(
				'field_id'		=> 'showEventDateEnd',
				'field_name'	=> 'showEventDateEnd',
				'field_label'	=> __( 'Show end date', 'icalevents' ),
				'field_title'	=> __( '', 'icalevents' ),
				'field_type'	=> 'checkbox',
				'field_css'		=> '',
				'field_value'	=> $showEventDateEnd
			),
			array(
				'field_id'		=> 'showEventTimeEnd',
				'field_name'	=> 'showEventTimeEnd',
				'field_label'	=> __( 'Show end time', 'icalevents' ),
				'field_title'	=> __( '', 'icalevents' ),
				'field_type'	=> 'checkbox',
				'field_css'		=> '',
				'field_value'	=> $showEventTimeEnd
			),
			array(
				'field_id'		=> 'showEventDescription',
				'field_name'	=> 'showEventDescription',
				'field_label'	=> __( 'Show description', 'icalevents' ),
				'field_title'	=> __( '', 'icalevents' ),
				'field_type'	=> 'checkbox',
				'field_css'		=> '',
				'field_value'	=> $showEventDescription
			),
			array(
				'field_id'		=> 'showEventLocation',
				'field_name'	=> 'showEventLocation',
				'field_label'	=> __( 'Show location', 'icalevents' ),
				'field_title'	=> __( '', 'icalevents' ),
				'field_type'	=> 'checkbox',
				'field_css'		=> '',
				'field_value'	=> $showEventLocation
			),
			array(
				'field_id'		=> 'showEventURL',
				'field_name'	=> 'showEventURL',
				'field_label'	=> __( 'Show a given URL', 'icalevents' ),
				'field_title'	=> __( '', 'icalevents' ),
				'field_type'	=> 'checkbox',
				'field_css'		=> '',
				'field_value'	=> $showEventURL
			),
			array(
				'field_id'		=> 'showAllDayEvents',
				'field_name'	=> 'showAllDayEvents',
				'field_label'	=> __( 'Show events that last an entire day', 'icalevents' ),
				'field_title'	=> __( '', 'icalevents' ),
				'field_type'	=> 'checkbox',
				'field_css'		=> '',
				'field_value'	=> $showAllDayEvents
			),
			array(
				'field_id'		=> 'showMultiDayEvents',
				'field_name'	=> 'showMultiDayEvents',
				'field_label'	=> __( 'Show multi day events', 'icalevents' ),
				'field_title'	=> __( '', 'icalevents' ),
				'field_type'	=> 'checkbox',
				'field_css'		=> '',
				'field_value'	=> $showMultiDayEvents
			),
		);
		
		$closePTag = true;
		foreach($widgetOptions as $option) {
			switch ($option['field_type']) {
				case 'hidden':
					$hiddenFieldTemplate = new TemplateFromFile( $this->templatePath.'admin_option_hiddenfield.tpl' );
					$hiddenFieldTemplate->replaceTokenByContent( 'OPTION_ITEM_ID',			$option['field_id'] );
					$hiddenFieldTemplate->replaceTokenByContent( 'OPTION_ITEM_NAME',		$option['field_name'] );
					$hiddenFieldTemplate->replaceTokenByContent( 'OPTION_ITEM_VALUE',		$option['field_value'] );
					$hiddenFieldTemplate->replaceTokenByContent( 'OPTION_ITEM_FIELD_TYPE',	$option['field_type'] );
					$hiddenFieldTemplate->show();
				break;
				
				case 'separator':
					print('<hr class="horizontalRule"/>');
				break;
				
				case 'note':
					print('<div class="icaleventsNote">'.$option['field_value'].'</div>');
				break;

				case 'checkbox':
					$checkBoxTemplate = new TemplateFromFile( $this->templatePath.'admin_option_checkbox.tpl' );
					$checkBoxTemplate->replaceTokenByContent( 'OPTION_ITEM_ID',				$this->get_field_id( $option['field_id'] ) );
					$checkBoxTemplate->replaceTokenByContent( 'OPTION_ITEM_NAME',			$this->get_field_name( $option['field_name'] ) );
					$checkBoxTemplate->replaceTokenByContent( 'OPTION_ITEM_VALUE',			$option['field_value'] );
					$checkBoxTemplate->replaceTokenByContent( 'OPTION_ITEM_LABEL',			$option['field_label'] );
					$checkBoxTemplate->replaceTokenByContent( 'OPTION_ITEM_TITLE',			$option['field_title'] );
					$checkBoxTemplate->replaceTokenByContent( 'OPTION_ITEM_CSS_CLASS',		$option['field_css'] );
					$checkBoxTemplate->replaceTokenByContent( 'OPTION_ITEM_CHECKED_FLAG',	($option['field_value'] ? 'checked="checked"' : '') );

					if ($closePTag) print('<p>');
					$checkBoxTemplate->show();
					$closePTag = false;
				break;
				
				case 'textfield':
				case 'datepicker':
				default:
					$textFieldTemplate = new TemplateFromFile( $this->templatePath.'admin_option_'.$option['field_type'].'.tpl' );
					$textFieldTemplate->replaceTokenByContent( 'OPTION_ITEM_ID',			$this->get_field_id( $option['field_id'] ) );
					$textFieldTemplate->replaceTokenByContent( 'OPTION_ITEM_NAME',			$this->get_field_name( $option['field_name'] ) );
					$textFieldTemplate->replaceTokenByContent( 'OPTION_ITEM_VALUE',			$option['field_value'] );
					$textFieldTemplate->replaceTokenByContent( 'OPTION_ITEM_LABEL',			$option['field_label'] );
					$textFieldTemplate->replaceTokenByContent( 'OPTION_ITEM_TITLE',			$option['field_title'] );
					$textFieldTemplate->replaceTokenByContent( 'OPTION_ITEM_CSS_CLASS',		$option['field_css'] );
					$textFieldTemplate->replaceTokenByContent( 'OPTION_ITEM_FIELD_TYPE',	'text' );

					if (!$closePTag) print('</p>');		// switch from checkbox to textfield
					print('<p>');
					
					// if we have a datepicker
					// there are a few more things to do
					if ($option['field_type'] == 'datepicker') {
						
						// get a message template if we need it for an error
						$errorMessageTemplate = new TemplateFromFile( $this->templatePath.'admin_option_message.tpl' );
						$errorMessageTemplate->replaceTokenByContent( 'OPTION_ITEM_ID', $this->get_field_id( $option['field_id'] ).'_errorMessage' );
						$errorMessageTemplate->replaceTokenByContent( 'OPTION_ITEM_CSS_CLASS', 'icaleventsErrorMessage' );
						$errorMessageTemplate->replaceTokenByContent( 'OPTION_ITEM_ERRORMESSAGE',__( 'This is NOT a valid date format!', 'icalevents' ) );
						
						print('
			<script>
				jQuery(document).ready(function(){
					jQuery("#'.$this->get_field_id( $option['field_id'] ).'")
					.datepicker({
						dateFormat: "yy-mm-dd",
						showWeek: true,
						firstDay: 1,
						showOn: "button",
						buttonImage: "'.plugins_url(basename(dirname(__FILE__))).'/images/calendar_icon.gif",
						buttonImageOnly: true
					})
					.change(function() {
						var parsedDate = Date.parse( jQuery(this).val() );
						if ( parsedDate == null && jQuery(this).val() != "" ) {
							jQuery("#'.$this->get_field_id( $option['field_id'] ).'_errorMessage").fadeIn().delay(3000);
							jQuery(this).focus();
							jQuery("#'.$this->get_field_id( $option['field_id'] ).'_errorMessage").fadeOut();
						} else {
							jQuery("#'.$this->get_field_id( $option['field_id'] ).'_errorMessage").fadeOut();
						}
					});
				});
			</script>
						');
						$errorMessageTemplate->show();
					}
					$textFieldTemplate->show();
					$closePTag = true;
				break;
			}
			if ($closePTag) print('</p>');				// if the last option in loop was a textfield
		}
		if (!$closePTag) print('</p>');					// if the last option was a checkbox field
    }


    function update( $new_instance, $old_instance ) {
        // processes widget options to be saved
        $instance = $old_instance;
		$instance['widgetIsParkedState']	= strip_tags($new_instance['widgetIsParkedState']);
		$instance['title']					= strip_tags($new_instance['title']);
		$instance['iCalURI']				= strip_tags($new_instance['iCalURI']);
		$instance['showNrOfEvents']			= strip_tags($new_instance['showNrOfEvents']);
		$instance['showEventRangeDateFrom']	= strip_tags($new_instance['showEventRangeDateFrom']);
		$instance['showEventRangeDateTo']	= strip_tags($new_instance['showEventRangeDateTo']);
                $instance['showTitle']			= strip_tags($new_instance['showTitle']);
		$instance['showEventDateStart']		= strip_tags($new_instance['showEventDateStart']);
		$instance['showEventDateEnd']		= strip_tags($new_instance['showEventDateEnd']);
		$instance['showEventTimeStart']		= strip_tags($new_instance['showEventTimeStart']);
		$instance['showEventTimeEnd']		= strip_tags($new_instance['showEventTimeEnd']);
		$instance['showEventSummary']		= strip_tags($new_instance['showEventSummary']);
		$instance['showEventDescription']	= strip_tags($new_instance['showEventDescription']);
		$instance['showEventLocation']		= strip_tags($new_instance['showEventLocation']);
		$instance['showEventURL']		= strip_tags($new_instance['showEventURL']);
		$instance['showAllDayEvents']		= strip_tags($new_instance['showAllDayEvents']);
		$instance['showMultiDayEvents']		= strip_tags($new_instance['showMultiDayEvents']);
		return $instance;
    }


    function widget( $args, $instance ) {

        // outputs the content of the widget
        // ADD YOUR FRONT-END FORM HERE
        extract( $args );
		$title				= trim(apply_filters( 'widget_title', $instance['title'] ));
		$iCalURI			= trim($instance['iCalURI']);
		$showNrOfEvents			= $instance['showNrOfEvents'];
		$showEventRangeDateFrom		= $instance['showEventRangeDateFrom'];
		$showEventRangeDateTo		= $instance['showEventRangeDateTo'];
		$showTitle			= stripslashes_deep($instance['showTitle']);
		$showEventDateStart		= $instance['showEventDateStart'];
		$showEventDateEnd		= $instance['showEventDateEnd'];
		$showEventTimeStart		= $instance['showEventTimeStart'];
		$showEventTimeEnd		= $instance['showEventTimeEnd'];
		$showEventSummary		= stripslashes_deep($instance['showEventSummary']);
		$showEventDescription		= stripslashes_deep($instance['showEventDescription']);
		$showEventLocation		= stripslashes_deep($instance['showEventLocation']);
		$showEventURL			= stripslashes_deep($instance['showEventURL']);
		$showAllDayEvents		= stripslashes_deep($instance['showAllDayEvents']);
		$showMultiDayEvents		= stripslashes_deep($instance['showMultiDayEvents']);
		
		// prepare the URI for use with the "ical" class
		$iCalURI = str_ireplace ( 'webcal:' , 'http:' , $instance['iCalURI'] );

		$iCal = new ical($iCalURI);
		$iCalEvents = $iCal->eventsFromRange( $showEventRangeDateFrom, $showEventRangeDateTo );

		if (!$iCalEvents) {
			print_r($currentlyOccupied);
			print("Error: in iCal, events empty:".$iCalEvents."\n");
			return false;
		}

		if (!$showEventRangeDateFrom)
			$showEventRangeDateFrom = new DateTime();
		else
			$showEventRangeDateFrom = new DateTime($showEventRangeDateFrom);
			
		if (!$showEventRangeDateTo)
			$showEventRangeDateTo = new DateTime('2038/12/31');
		else
			$showEventRangeDateTo = new DateTime($showEventRangeDateTo);
			

		// if the title for this event list is not given
		// by the options, so set the widget title to iCal name
		$title = ( $title == '' ? $iCal->cal['VCALENDAR']['X-WR-CALNAME'] : $title);

		// -----------------------------------------------------------------------------------------------
		// show the widget
		// -----------------------------------------------------------------------------------------------
		
		print( $before_widget );
		if ( $showTitle & $title )
			print( $before_title . $title . $after_title );
		
		// there are NO event items
		if (count($iCalEvents) == 0) {
			$eventListEmptyTpl = new TemplateFromFile( $this->templatePath.'event_list_empty.tpl' );
			$eventListEmptyTpl->replaceTokenByContent( 'EVENTLIST_EMPTY_MESSAGE', __( 'There are no events available.', 'iCalEvents' ) );
			$eventListEmptyTpl->show();
		
		        
		// there are event items available
		} else {
			$eventCounter = 0;
			$eventListItems = '';

                        $heute = date('Ymd');

                        // In cooperation with the BSG theme this widget implements a very simpy mechanism
                        // to show a message if a room or location is currently occupied by an event.
                        // An example might be a digital sign outside a lecture room displaying:
                        // "Lecture ongoing. Please do not disturb! Thank you."
                        // The locations of the currently ongoing events are stored as strings in a global array.
                        
                        // Empty the global array of currently occupied locations
                        global $currentlyOccupied;
                        unset($currentlyOccupied);

			// iterate through the events, extract the $showNrOfEvents events
			// and put into a list
		        $currentday = 0;
                        $firstheader = 1;
			foreach( $iCalEvents as $anEvent) {
                                print( "<!-- currentday: $currentday, heute: $heute -->");

				// get a new event list item template
				$eventListItemTpl = new TemplateFromFile( $this->templatePath.'event_list_item.tpl' );

                                // handle all day and multi day events
                                // retrieve the start / end date and time and set two flags accordingly
                                $tmpStartTime = date('H:i', $iCal->iCalDateToUnixTimestamp($anEvent['DTSTART']));
                                $tmpEndTime = date('H:i', $iCal->iCalDateToUnixTimestamp($anEvent['DTEND']));
                                $tmpStartDate = date('Ymd', $iCal->iCalDateToUnixTimestamp($anEvent['DTSTART']));
                                $tmpEndDate = date('Ymd', $iCal->iCalDateToUnixTimestamp($anEvent['DTEND']));
                                
                                $isAllDay = 0;
                                $isMultiDay = 0;
                                if( strcmp($tmpStartTime, '00:00') == 0 & strcmp($tmpEndTime, '00:00') == 0 ) $isAllDay = 1;
                                if( strcmp($tmpStartDate, $tmpEndDate) != 0 ) $isMultiDay = 1; 
                                
                                // if the option showAllDayEvents is set show events lasting an entire day
                                if(!$showAllDayEvents & $isAllDay) continue;
                                
                                // if the option showMultDayEvents is set display multi day events
                                if(!$showMultiDayEvents & $isMultiDay) continue;

                                // check if this is a currently ongoing event
                                // and add its location to the global list of currently occupied once
                                // if the location string is empty, it is not appended
                                $now = date('H:i');
                                if( strcmp($tmpStartDate, $heute) <= 0 & strcmp($tmpStartTime, $now) <= 0 & strcmp($tmpEndTime, $now ) > 0 & !empty( $anEvent['LOCATION'] ) ) {
                                    // the event is ongoing - add its location
                                    global $currentlyOccupied;
                                    $currentlyOccupied[] = stripslashes_deep($anEvent['LOCATION']);
                                }

				// ---------------------------------------------------------------------------------------
				// event start date
				// ---------------------------------------------------------------------------------------
				if ($showEventDateStart) {
                                        $datestart = date('Ymd', $iCal->iCalDateToUnixTimestamp($anEvent['DTSTART']));
                                        if ( intval($datestart) > $currentday ) {
                                                $currentday = $datestart;
                                                print( "<!-- updated currentday to: $currentday -->");
                                                $spanclassextra = "";
                                                if ( $firstheader ) {
                                                    $spanclassextra = " first";
                                                } 
                                                $eventDateStartTpl = new TemplateFromString( '<span class="eventListItemDateStart' . $spanclassextra . '">{EVENTLIST_ITEM_DATE_START}</span>' );
					        $eventDateStartTpl->replaceTokenByContent( 'EVENTLIST_ITEM_DATE_START', 
		                                    ( strcmp($datestart, $heute) == 0 ) ? "Today" : date('j F', $iCal->iCalDateToUnixTimestamp($anEvent['DTSTART']) ) );
					        $eventListItemTpl->replaceTokenByContent( 'EVENTLIST_ITEM_DATE_START_TPL', $eventDateStartTpl->get() );
                                                $firstheader = 0;
                                        } else {
					        $eventListItemTpl->deleteToken( 'EVENTLIST_ITEM_DATE_START_TPL' );
                                        }
				} else {
					$eventListItemTpl->deleteToken( 'EVENTLIST_ITEM_DATE_START_TPL' );
				}

                                // delete the event tokens from template if this is an all day event
                                if( $isAllDay ) {
                                    // this is an all day event
                                    $eventListItemTpl->deleteToken( 'EVENTLIST_ITEM_TIME_START_TPL' ); 
                                    $eventListItemTpl->deleteToken( 'EVENTLIST_ITEM_TIME_END_TPL' );
                                }
				
                                if( $isAllDay ) {
                                  $eventListItemTpl->replaceTokenByContent( 'EVENTLIST_ITEM_CSSCLASS_TPL', 'iCalAllDayItem' );
                                } elseif( $isMultiDay ) {
                                  $eventListItemTpl->replaceTokenByContent( 'EVENTLIST_ITEM_CSSCLASS_TPL', 'iCalmultiDayItem' );
                                } else {
                                  $eventListItemTpl->replaceTokenByContent( 'EVENTLIST_ITEM_CSSCLASS_TPL', 'iCalEventsItem' );
                                }

				// ---------------------------------------------------------------------------------------
				// event start time
				// ---------------------------------------------------------------------------------------
				if ($showEventTimeStart) {
					$eventTimeStartTpl = new TemplateFromString( '<span class="eventListItemTimeStart">{EVENTLIST_ITEM_TIME_START}</span>' );
					$eventTimeStartTpl->replaceTokenByContent( 'EVENTLIST_ITEM_TIME_START', date('H:i', $iCal->iCalDateToUnixTimestamp($anEvent['DTSTART'])) );
					$eventListItemTpl->replaceTokenByContent( 'EVENTLIST_ITEM_TIME_START_TPL', $eventTimeStartTpl->get() );
				} else {
					$eventListItemTpl->deleteToken( 'EVENTLIST_ITEM_TIME_START_TPL' );
				}

				// ---------------------------------------------------------------------------------------
				// event end date
				// ---------------------------------------------------------------------------------------
				if ($showEventDateEnd) {
					$eventDateEndTpl = new TemplateFromString( '<span class="eventListItemDateEnd">{EVENTLIST_ITEM_DATE_END}</span>' );
					$eventDateEndTpl->replaceTokenByContent( 'EVENTLIST_ITEM_DATE_END', date('d.m.Y', $iCal->iCalDateToUnixTimestamp($anEvent['DTEND'])) );
					$eventListItemTpl->replaceTokenByContent( 'EVENTLIST_ITEM_DATE_END_TPL', $eventDateEndTpl->get() );
				} else {
					$eventListItemTpl->deleteToken( 'EVENTLIST_ITEM_DATE_END_TPL' );
				}

				// ---------------------------------------------------------------------------------------
				// event end time
				// ---------------------------------------------------------------------------------------
				if ($showEventTimeEnd) {
					$eventTimeEndTpl = new TemplateFromString( '<span class="eventListItemTimeEnd">&nbsp;-&nbsp;{EVENTLIST_ITEM_TIME_END}</span>' );
					$eventTimeEndTpl->replaceTokenByContent( 'EVENTLIST_ITEM_TIME_END', date('H:i', $iCal->iCalDateToUnixTimestamp($anEvent['DTEND'])) );
					$eventListItemTpl->replaceTokenByContent( 'EVENTLIST_ITEM_TIME_END_TPL', $eventTimeEndTpl->get() );
				}
				else						$eventListItemTpl->deleteToken( 'EVENTLIST_ITEM_TIME_END_TPL' );

                                
				// ---------------------------------------------------------------------------------------
				// event summary
				// ---------------------------------------------------------------------------------------
				if (array_key_exists( 'SUMMARY', $anEvent )) {
					if ($showEventSummary) {
						$eventSummaryTpl = new TemplateFromString( '<span class="eventListItemSummary">{EVENTLIST_ITEM_SUMMARY}</span>' );
						$eventSummaryTpl->replaceTokenByContent( 'EVENTLIST_ITEM_SUMMARY', stripslashes_deep($anEvent['SUMMARY']) );
						$eventListItemTpl->replaceTokenByContent( 'EVENTLIST_ITEM_SUMMARY_TPL', $eventSummaryTpl->get() );
					} else {
						$eventListItemTpl->deleteToken( 'EVENTLIST_ITEM_SUMMARY_TPL' );
					}
				} else {
					$eventListItemTpl->deleteToken( 'EVENTLIST_ITEM_SUMMARY_TPL' );
				}

				// ---------------------------------------------------------------------------------------
				// event description
				// ---------------------------------------------------------------------------------------
				if (array_key_exists( 'DESCRIPTION', $anEvent )) {
					if ($showEventDescription) {
						$eventDescriptionTpl = new TemplateFromString( '<span class="eventListItemDescription">{EVENTLIST_ITEM_DESCRIPTION}</span>' );
						$eventDescriptionTpl->replaceTokenByContent( 'EVENTLIST_ITEM_DESCRIPTION', stripslashes_deep($anEvent['DESCRIPTION']) );
						$eventListItemTpl->replaceTokenByContent( 'EVENTLIST_ITEM_DESCRIPTION_TPL', $eventDescriptionTpl->get() );
					} else {
						$eventListItemTpl->deleteToken( 'EVENTLIST_ITEM_DESCRIPTION_TPL' );
					}
				} else {
					$eventListItemTpl->deleteToken( 'EVENTLIST_ITEM_DESCRIPTION_TPL' );
				}

				// ---------------------------------------------------------------------------------------
				// event location
				// ---------------------------------------------------------------------------------------
				if (array_key_exists( 'LOCATION', $anEvent )) {
					if ($showEventLocation) {
						$eventLocationTpl = new TemplateFromString( '<span class="eventListItemLocation">{EVENTLIST_ITEM_LOCATION}</span>' );
						$eventLocationTpl->replaceTokenByContent( 'EVENTLIST_ITEM_LOCATION', stripslashes_deep($anEvent['LOCATION']) );
						$eventListItemTpl->replaceTokenByContent( 'EVENTLIST_ITEM_LOCATION_TPL', $eventLocationTpl->get() );
					} else {
						$eventListItemTpl->deleteToken( 'EVENTLIST_ITEM_LOCATION_TPL' );
					}
				} else {
					$eventListItemTpl->deleteToken( 'EVENTLIST_ITEM_LOCATION_TPL' );
				}

				// ---------------------------------------------------------------------------------------
				// event url
				// ---------------------------------------------------------------------------------------
				if (array_key_exists( 'URL;VALUE=URI', $anEvent )) {
					if ($showEventURL) {
						$eventURLTpl = new TemplateFromString( '<span class="eventListItemURL"><a href="{EVENTLIST_ITEM_URL}" target="_blank">{EVENTLIST_ITEM_URL}</a></span>' );
						$eventURLTpl->replaceTokenByContent( 'EVENTLIST_ITEM_URL', $anEvent['URL;VALUE=URI'] );
						$eventListItemTpl->replaceTokenByContent( 'EVENTLIST_ITEM_URL_TPL', $eventURLTpl->get() );
					} else {
						$eventListItemTpl->deleteToken( 'EVENTLIST_ITEM_URL_TPL' );
					}
				} else {
					$eventListItemTpl->deleteToken( 'EVENTLIST_ITEM_URL_TPL' );
				}

				// append the string of the current item to the list of items
				$eventListItems .= $eventListItemTpl->get();
				$eventListItemTpl = NULL;
				
				$eventCounter++;
				if ($eventCounter >= (int)$showNrOfEvents)
					break;
			}
			
			// replace the event list item token by real content
			$eventListTpl = new TemplateFromFile( $this->templatePath.'event_list.tpl' );
			$eventListTpl->replaceTokenByContent( 'EVENTLIST_ITEMS',  $eventListItems);
			$eventListTpl->show();
			$eventListItems = '';
		}
		print( $after_widget );
    }
}
add_action('widgets_init', create_function('', "register_widget('oxCalendar');"));

?>
