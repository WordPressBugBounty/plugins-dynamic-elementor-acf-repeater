=== Dynamic Elementor ACF Repeater ===
Contributors: wplunadev
Tags: elementor, loop grid, repeater fields, acf repeater, dynamic tags
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.91
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Allows ACF repeater field values to be rendered in Elementor loop items and loop grids via Dynamic Tags.

== Requirements ==

This plugin requires the following:

* WordPress 6.0 or higher
* PHP 7.4 or higher
* Elementor Pro
* Advanced Custom Fields Pro (ACF Pro)

Please ensure you have these plugins installed and activated before using Dynamic Elementor ACF Repeater.

== Description ==

Dynamic Elementor ACF Repeater is a powerful plugin that bridges the gap between Advanced Custom Fields (ACF) repeater fields and Elementor's dynamic content capabilities. It allows you to use ACF repeater field values directly in Elementor Loop Items and Loop Grids through Dynamic Tags.

== Usage Guide ==

[Usage Guide and Documentation Here](https://calculabs.github.io/elementor-acf-repeater-docs/usage-guide/)

== Free vs Pro Features ==

Dynamic Elementor ACF Repeater comes in two versions: Free and Pro. Here's a breakdown of what each version offers:

= Free Version =

The free version of Dynamic Elementor ACF Repeater provides essential functionality for integrating ACF repeater fields with Elementor:

* Basic integration with Elementor Pro and ACF Pro
* Support for image and text repeater fields in Elementor dynamic tags for loop items and loop grid
* **Support for adding repeater images to background image in loop item! Game changer!**
* ACF Repeater Text, ACF Repeater Image dynamic tags
* Loop Grid widget integration
* Support for repeater fields on ACF Options page

= Pro Version =

The Pro version includes everything in the free version, plus a host of advanced features for power users:

* Support for more ACF field types within repeaters (file, gallery, relationship, more coming soon...)
* Advanced dynamic tags (e.g., ACF Repeater File, ACF Repeater Gallery, ACF Repeater Relationship)
* Multiple Loop Grids with ACF Repeater fields in the same page, post, or template
* Lightbox functionality on the loop grid widget
* Swiper integration when loop grid lightbox is enabled
* Advanced filtering capabilities for Loop Grid items with customizable URL parameters
* Drag-and-drop term ordering for loop filters (set order per widget)
* Optional deeplinking toggle to update the URL when filtering
* Show/hide empty taxonomy terms in filters
* Enhanced support for ACF Options page data in filters and virtual posts
* Default filter term selection for pre-selecting filters on page load
* Visual dividers between filter items with full styling controls
* Taxonomy term descriptions with animations and positioning options
* Enhanced responsive filter styling controls
* Lightbox visibility control for individual elements (show or hide individual items in the lightbox vs the grid)
* ACF Relationship field support for dynamic content associations across posts
* Nested Relationship/Post Object fields: Support for relationship fields inside repeaters (select via repeater:subfield)
* NEW - Support for Loop Carousel 
* Element Display Conditions: ACF Repeater Field condition (show/hide by repeater sub‑field)
* New Dynamic Tags (PRO): ACF Repeater Link Title, ACF Repeater Link Target, ACF Repeater Color, ACF Repeater Icon
* Enhanced URL support (PRO): ACF Link field now maps to URL/Title/Target
* ACF Repeater Form Field: Create dynamic form fields in Elementor Forms populated from ACF repeater data (dropdown/radio/checkbox)

= Upgrade to Pro =

Unlock the full potential of Dynamic Elementor ACF Repeater and take your dynamic content creation to the next level!

[Start Your Free Trial](https://checkout.freemius.com/mode/dialog/plugin/16334/plan/27245/?trial=paid)

The Pro version comes with a 3-day free trial. You can cancel anytime before the trial ends to avoid being charged. We'll send you an email reminder before the trial expires.

== Frequently Asked Questions ==

= Which ACF field types are supported? =

The plugin supports various field types within repeaters, including text, textarea, image, file, and more. Check the plugin's documentation for a full list of supported field types.

= Is there documentation available? =

Yes, you can find the [usage guide here](https://calculabs.github.io/elementor-acf-repeater-docs/usage-guide/).


== Screenshots ==

1. Choose your repeater for loop item - Set up your ACF Repeater field in the Loop Item Settings panel to begin using dynamic content.
2. Map your repeater text - Use ACF Repeater dynamic tags to display your repeater field content in Elementor widgets.
3. Et Voila! - See your dynamic ACF Repeater content beautifully displayed in an Elementor Loop Grid.


== Changelog ==

= 1.0.91 =

* Free and PRO
  - Fix visual overflow regression

= 1.0.9 =
* Free version:
  - Bug fixes and code improvements.

* Pro Version:
  - Bug fixes: Fixed registration for new form repeater options field.

= 1.0.8 =
* New Features (PRO ONLY):
  - ACF Repeater Form Field: New form field type for Elementor Forms that populates options from ACF repeater subfields
    - Display as dropdown, radio buttons, or checkboxes
    - Dynamically pulls options from any ACF repeater field subfield
    - Perfect for creating dynamic selection lists in forms
* Improvements (PRO ONLY):
  - ACF Relationship: supports nested Relationship/Post Object fields inside Repeaters (select via repeater:subfield)
* Fixes:
  - Relationship query now collects IDs from nested repeater rows and normalizes to post IDs

= 1.0.7 =
* Improvements (PRO ONLY):
  - Added ACF Link field support in repeater dynamic tags (URL, Title, Target)
  - New Dynamic Tags: ACF Repeater Link Title, ACF Repeater Link Target, ACF Repeater Color, ACF Repeater Icon
* Fixes:
  - ACF Repeater Gallery now accepts IDs, URLs, arrays, or objects and resolves URLs to attachment IDs
  - Minor stability and compatibility updates for dynamic tags loader

= 1.0.6 =
* Improvements (PRO ONLY):
  - New Filter Feature - drag and drop to reorder terms used in filter directly in widget
  - Filter updates instantly without page reload (faster UX)
  - "Use Deeplinking" toggle to control if the URL updates
  - New Display Condition - ACF Repeater Field (show/hide by repeater sub‑field value)
* Fixes (PRO ONLY):
  - Default Filter term now shows active style
  - Fixed custom filter parameter name in URL
  - Support for ACF Number Field

= 1.0.5 =
* Fixes: 
  - PRO ONLY - Lightbox Repeater Visibility Control: Fixed a flickering bug by replacing the visibility dropdown with toggles, so you can choose where a repeater item shows (in the loop item, the lightbox, or both.

= 1.0.4 =
* Fixes (PRO ONLY):
  - Filter now works when using repeater taxonomy from options pages
  - Improved handling of array term values in ACF taxonomy fields within repeaters
  
* Features (PRO ONLY):
  - Added input for custom URL filter parameter name (allows customization of filter query string)
  - Added support for showing empty/unused terms in filters (new "Show Empty Terms" control)
  - Default filter term selection - pre-select a filter on initial page load
  - Visual dividers for horizontal filters with customizable style, width, height, and color
  - Taxonomy term descriptions display with multiple positioning options and animations
  - Enhanced filter styling with responsive width and alignment controls

= 1.0.3 =
* Free Version:
  - Fixed bug where only the first 10 rows of repeater fields were displayed in Loop Grid
  - Added support for ACF Options page as fallback when no repeater data exists on current post
  
* Pro Version:
  - Added support for Loop Carousel widget with ACF Repeater fields (Pro only feature)

= 1.0.2 =
* Free Version:
  - Fixed bug that prevented multiple Loop Item widgets from accessing their selected repeater field in the Loop Item settings
  - Improved repeater value retrieval logic

* Pro Version:
  - Added support for multiple Loop Grids with ACF Repeater fields in the same post

= 1.0.1 =
* Free Version:
  - Added new "Query Current Post Only" control to restrict repeater items to the current post
  - Improved handling of current post ID in Elementor previews

* Pro Version:
  - Completely refactored taxonomy filtering system with new state-based architecture
  - Added three distinct filter states to improve handling of different filtering scenarios:
    + Current Post Only Mode: For filtering a single post's repeater items from chosen post type
    + All Posts Repeater Mode: For filtering repeater content across multiple posts of chosen post type
    + Standard Taxonomy Mode: For traditional WordPress taxonomy filtering of terms from chosen post type
  - Fixed issue with taxonomy filtering by ensuring repeater fields always use current post data
  - Better code organization with dedicated classes for filter states and UI components


= 1.0.0 =
* Initial release of Dynamic Elementor ACF Repeater
* Basic integration with Elementor Pro and ACF Pro
* Support for image and text repeater fields in Elementor dynamic tags for loop items and loop grid
* ACF Repeater Text and ACF Repeater Image dynamic tags
* Loop Grid widget integration
* Support for adding repeater images to background image in loop items
* Pro features:
  - Support for additional ACF field types within repeaters (file, gallery, relationship)
  - Advanced dynamic tags (ACF Repeater File, ACF Repeater Gallery, ACF Repeater Relationship)
  - Lightbox functionality on the loop grid widget
  - Swiper integration for lightbox
  - Advanced filtering capabilities for Loop Grid items
  - Lightbox visibility control for individual elements
  - ACF Relationship field support for dynamic content associations across posts

== Upgrade Notice ==

= 1.0.91 =
FREE and PRO: Fixed overflow visual regression.

= 1.0.9 =
PRO ONLY: Fixed registration hook for new for new form repeater options.

= 1.0.8 =
PRO ONLY: New Repeater Field for Elementor Forms - select/radio/checkbox repeater options. Nested repeater relationship sub‑fields.

= 1.0.7 =
PRO ONLY: Adds ACF Link field support (URL/Title/Target) and new Color/Icon tags; Gallery field fix, minor stability updates

= 1.0.6 =
PRO ONLY: Drag & drop term ordering, instant no‑reload filter updates, optional deeplinked URL, and new ACF Repeater Display Condition. Fixes repeater number support and default term active state and custom URL param name.

= 1.0.5 = 
Fixed Lightbox Repeater Visibility Control bug by using toggle buttons instead of dropdown.

= 1.0.4 =
Fixed filter compatibility with ACF Options pages. Adds default filter terms, visual dividers, term descriptions, and enhanced styling controls.

= 1.0.3 =
Fixed issue where only first 10 repeater rows were shown. Pro version adds Loop Carousel support.

= 1.0.2 =
Pro version now supports multiple Loop Grids with ACF Repeater in the same post. Free version fixes bug with accessing repeater fields in Loop Item settings.

= 1.0.1 =
Fixed taxonomy filtering issues by ensuring repeater fields always use current post data.


= 1.0.0 =
Initial release of Dynamic Elementor ACF Repeater. Enjoy the features and functionality!