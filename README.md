=== Ox Calendar Widget ===
Contributors: Guido Klingbeil, Marko Jung, phranck
Donate link: http://programmschmie.de/
Tags: icalendar, ical, plugin, widget, events, rfc2445, iCalEvents
Requires at least: 3.1
Tested up to: 3.5.1
Stable tag: 1.0

This is a fork of the iCalendar Events Widget by [programmschie.de] for the
Oxfod Blavatnik School of Government.

This widget shows you upcoming events for a configurable iCalendar .ics file.


== Description ==

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
* Switch on/off if multiday events should be shown

Note: if an event lasts an entire day, the start and end time of such events is not shown.
An event lasts en entire day if the start and end times are equal but the dates are not.

= Translations =
* English
* German
* French


== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the complete 'ox-calendar-widget/' directory to '/wp-content/plugins/' directory of your active theme
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to the 'Widgets' menue in WordPress and put it on the right place of your sidebar


== Frequently Asked Questions ==

n/a


== Screenshots ==

1. Widget options in your WordPress Admin
2. The active Datepicker for selecting a date range
3. Widget in action in your sidebar


== Changelog ==

= 0.0.2 =
* bug fixes,
* in cooperation with the BSG theme ability to display a message if a location is currently
  occupied by an event.

= 0.0.1 =
* initial fork of the iCalendar Events Widget version 0.3.3

== Upgrade Notice ==

n/a


== Notes ==

This widget makes (in some topics partially) use of:

- the [ics-parser](http://code.google.com/p/ics-parser/) class by [Martin Thoma](http://martin-thoma.de)
- the [jQuery UI-Datepicker plugin](http://jqueryui.com/demos/datepicker/)
- the [DateJS - Javascript Date Library](http://www.datejs.com/)

= Good to know =
Normally you will select a "range date-to" greater than the "range date-from". But, if you set the "range date-to" lesser than the "range date-from" 
(or lesser than the current date, if "range date-from" is empty), then the list of events to show will be given in reverse order (aka descending).


== Customization ==


Please check the **icalendar.css** file for style customization and the templates in the templates folder for styling options.