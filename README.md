# ox-calendar-widget  
## The University of Oxford Calendar Widget
Copyright: [University of Oxford IT Services](http://www.it.ox.ac.uk)  
Contributors: [Guido Klingbeil](http://www.gklingbeil.net), [Marko Jung](http://mjung.net), phranck  
Tags: icalendar, ical, plugin, widget, events, rfc2445, iCalEvents  
Requires at least: 3.1  
Tested up to: 3.5.1  
Stable tag: trunk  
License: GPLv3 or later  
License URI: http://www.gnu.org/licenses/gpl-3.0.html    
GitHub URI: https://github.com/ox-it/ox-calendar-widget  


## Short Description

This widget shows you upcoming events for a configurable iCalendar .ics file.


## Description

This widget shows you upcoming events for a configurable iCalendar .ics file. There are a few options you can set like:

* Title of the widget when it's visible in your sidebar
* Subscription URL to your iCalendar .ics file
* Number of events to show
* Switch on/off event summary
* Switch on/off event start/end date
* Switch on/off event start/end time
* Switch on/off event description
* Switch on/off event location
* Switch on/off event URL
* Setting up a range of dates that events will be shown
* Switch on/off the event list title
* Switch on/off if events that last an entire day should be shown
* Switch on/off if multi-day events should be shown

Note: if an event lasts an entire day, the start and end time of such events is not shown. An event lasts en entire day if the start and end times are equal but the dates are not.

The plug-in contains translations for English, German, and French.

This is a fork of the *iCalendar Events Widget* by programmschie.de for the
Blavatnik School of Government at the University of Oxford. It contains patches to satisfy to the School's distinct calendar display requirements. Please consider using the original WordPress extension and donating to the original author.


## Installation

This plugin is not published in the official WordPress plugin catalogue yet. If you wish to manually install it:

1. Download the plugin from [GitHub](https://github.com/ox-it/ox-calendar-widget),
1. Upload the entire `ox-digital-signage` directory to your WordPress plugins folder, 
1. Activate the plugin through the 'Plugins' menu in WordPress


## Usage

Go to the *Widgets* menu in WordPress and place it on in one of your widget areas (i.e. your sidebar). The widget offers several configuration options as described above.


## Frequently Asked Questions 

We are happy answer any questions to the best of our knowledge.

#### Can I display events in a reverse order?
Normally you will select a "range date-to" greater than the "range date-from". But, if you set the "range date-to" lesser than the "range date-from" (or lesser than the current date, if "range date-from" is empty), then the list of events to show will be given in reverse order (aka descending).

#### How do I customise the output of the widget?
Please check the **icalendar.css** file for style customisation and the templates in the templates folder for styling options.


## Changelog 

* 0.2 
  * bug fixes,
  * in cooperation with the BSG theme ability to display a message if a location is currently occupied by an event.
* 0.1
  * initial fork of the iCalendar Events Widget version 0.3.3


## Acknowledgements 

This widget makes (in some topics partially) use of:

* the [ics-parser](http://code.google.com/p/ics-parser/) class by [Martin Thoma](http://martin-thoma.de)
* the [jQuery UI-Datepicker plugin](http://jqueryui.com/demos/datepicker/)
* the [DateJS - Javascript Date Library](http://www.datejs.com/)
