=== CleanPrint ===
Contributors: johncadams, lucascolin
Donate link: http://www.formatdynamics.com/contact-us
Tags: pdf, print, printer, printable, printing, widget, email, save, optimize, output, edit, editing, eco-friendly, environmental, sustainable, reader, iPad, tablet, saving, ecological, eco, ink, social, output, plugin, saver, box, box.net, box.com, kindle, dropbox, rtf, printer friendly, readlater, instapaper, cloud, google docs, google drive, google cloud print, box, box.net, box.com
Requires at least: 2.0.2
Tested up to: 4.2
Stable tag: 3.4.6
  
CleanPrint - Eco-friendly content output to print, PDF, email, Kindle, Box, Google Drive and Dropbox


== Description ==
The world's best and most used eco-friendly print tool is now available on WordPress. Join top sites like NBC News, CNN, Disney and Fox Sports and offer your users an economically and ecologically friendly printing and saving experience that saves paper, ink, and money while saving trees.

<h4>How CleanPrint Works</h4>

CleanPrint is an eco-friendly tool that saves paper, ink and money when printing from the internet. Users can easily edit content before printing or saving to PDF, Kindle, Dropbox, Google Drive or Box. CleanPrint includes sharing tools and a pagination/paper saving counter that shows visitors how eco-friendly your site is and helps them avoid that dreaded extra sheet of paper!

1. User activates CleanPrint by hitting print button
2. Print preview appears including pagination and editing tools for optimization
3. User selects desired output:
   * PDF - Saves content as a PDF document
   * Text - Saves content as a rich text formated file
   * Dropbox - Saves a PDF or text file to your Dropbox account
   * Box - Saves a PDF or text file to your Box account
   * Google Drive - Saves a PDF or text file to your Google Drive account
   * Kindle - Saves content directly to your Kindle
   * Google Cloud Print - prints to a remote printer
   * Print - Sends content to your printer
   * Email - Sends content via email
4. Share article link to Facebook, Twitter, LinkedIn, and Google+

<h4>Features and Benefits</h4>

1. Use the CleanPrint button set or point your own buttons or text to CleanPrint.
2. Lightbox keeps users on your page within their original browser window.
3. Green ticker shows how many pages CleanPrint has saved.
4. Pagination/paper saving counter helps users avoid that dreaded extra sheet of paper.
5. Control - Users are in control of font size, images, gray scale of text, and eliminating any unwanted content before outputting to print, PDF, Kindle, Dropbox, Google Drive and Box.
6. Button Set - Users feel good about hitting eco-friendly content output buttons next to your content.
7. Branded output - Your brand/URL are printed on the page or saved so that people can always get back to your site.


== Installation ==

1. Log into your WordPress installation as an administrator.
2. On the navigation on the left hand side, click 'Plugins', then 'Add New' from the menu.
3. Enable the CleanPrint plugin.
4. Visit the CleanPrint Settings page, select the appropriate options and click 'Save Changes'.

<h4>Using Your Own Buttons</h4>
If you prefer to use your own text links or buttons you may do so but it does
require a deeper understanding of WordPress administration and HTML.  This information can be found in
WordPress documentation found elsewhere:

1. Hide the buttons under Button Styles in the CleanPrint Settings page.
2. Insert a hyperlink into your page as per the example below:
   <pre>
      &lt;a href='.' onClick='WpCpCleanPrintPrintHtml(); return false' title='Print page'&gt;Print&lt;/a&gt;
      &lt;a href='.' onClick='WpCpCleanPrintSendEmail(); return false' title='Email page'&gt;Email&lt;/a&gt;
      &lt;a href='.' onClick='WpCpCleanPrintGeneratePdf(); return false' title='PDF page'  &gt;PDF&lt;/a&gt;
   </pre>


<h4>Using Shortcode Buttons</h4>
1. Hide the buttons under Button Styles in the CleanPrint Settings page.
2. Add the shortcode [cleanprint_button] to your theme's functions.php file (usually at/near the bottom):
   <pre>add_shortcode('cleanprint_button', 'cleanprint_add_button');</pre>
3. Activate the button(s) in your HTML content, for example to add all 3 buttons:
   <pre>[cleanprint_button print='true' pdf='true' email='true']</pre>



== Frequently Asked Questions ==

= Can I personalize CleanPrint for my site? =

Yes, you can add your own logo in the CleanPrint Settings page.  The logo should be no more than 200px wide and 40px tall.

= Can I remove ads from CleanPrint? =

Not at the present time. Ads help us pay the bills. CleanPrint is primarily supported by advertising which allows us to cover costs while offering you content output tools that save paper, ink, money and the environment.

= How do remove the Email and PDF buttons leaving only the Print button? =

In the CleanPrint Settings page you may choose from a wide variety of button styles.  You may also elect to turn on/off any button.

= Can I add the CleanPrint button via a shortcode? =

Yes, see the Installation instructions.

= How do I remove the Print button from my home page? =

Change the Home Page setting from "Include" to "Exclude" in the CleanPrint Settings page.

= How do I move the buttons from the upper right corner to the lower left? =

Change the Page Location setting from "Top Right" to "Bottom Left" in the CleanPrint Settings page.

= How do I set CleanPrint to pre-remove content so the user doesn't have to? =

This can be tricky depending upon your WordPress knowledge and requires you to set certain class names on the element in question.  Visit http://www.formatdynamics.com/cpconfig for details.

= Where can I see CleanPrint in action? =

You have two options:
<ol>
   <li>Visit our website and try it out:
      <ul>
         <li>http://www.formatdynamics.com/cleanprint-4-0</li>      
      </ul>
   </li>
   <li>Install our free browser tool and try it anywhere yourself.
   <ul>
      <li>http://www.formatdynamics.com/bookmarklets.</li>
   </ul>
   </li>
</ol>

= Where can I learn more about CleanPrint? =

Visit us at:
<ul>
   <li><a href="http://www.formatdynamics.com/cleanprint-4-0">FormatDynamics.com</a></li>
   <li><a href="http://www.facebook.com/pages/CleanPrint/131304880322920?sk=app_162498273831267">Facebook</a></li>
</ul>


== Screenshots ==

1. CleanPrint allows you to insert Print, Email and PDF buttons into your content anywhere you like from a large number of button styles.
2. CleanPrint reformats your article content in order to make it easier to read and saves both paper and ink.  Users can edit article content further to get the output they desire.
3. Users can then print the article, email it to their friends or save it as a PDF or text document to their computer, Kindle, Google Drive, Dropbox or Box account.


== Changelog ==

= 3.4.6 =
* Improved visual accessibility  

= 3.4.5 =
* Corrected issue with function names 

= 3.4.4 =
* Non-standard page excludes

= 3.4.3 =
* Improved ID exclusion
* Simplifying shortcode config
* Adding taxonomies

= 3.4.2 =
* Improved page-load performance
* Added Page excludes

= 3.4.1 =
* HTTPS host changes

= 3.4.0 =
* HTTPS support

= 3.3.0 =
* Kindle support
* Minor bug fixes 

= 3.2.4 =
* New shortcode behavior 

= 3.2.3 =
* Added no-ad option

= 3.2.2 =
* Minor bug fixes 

= 3.2.1 =
* Minor bug fixes 

= 3.2.0 =
* Added support for Google Cloud Print
* Added support for RTF
* Improved button identifiers
* Improved script injection
* Added shortcode support

= 3.1.3 =
* Minor bug fixes

= 3.1.2 =
* Minor bug fixes

= 3.1.1 =
* Minor bug fixes

= 3.1.0 =
* Added support for tag page type
* Added support for excluding specific page IDs
* Added support for multiple print buttons per page
* Workaround for Google Analytics for WordPress plug-in defect

= 3.0.0 =
* Support for turning on/off the buttons per-page
* Additional button styles including chiclets
* Remove the class="button" to avoid conflicts with other styles
* Minor bug fixes

= 2.0 =
* Adds support for Email and PDF buttons
* Adds customizable logo

= 1.5.0 =
* Uses the new CleanPrint engine<br>
* Additional print button styles<br>
* Ability to place the button in different locations<br>
* Ability to use Google Analytics

= 1.0.1 =
* Minor bug fixes

= 1.0.0 =
* Re-factored for WordPress VIP installations

== Upgrade Notice ==

All CleanPrint 0.9.5b, 1.0.0 and 1.0.1 installations must upgrade to the latest version before 3/31/2012, if you have any questions please email tech@formatdynamics.com.
