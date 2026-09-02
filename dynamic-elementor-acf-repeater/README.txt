=== Dynamic ACF Repeater for Elementor ===
Contributors: wplunadev
Tags: elementor, loop grid, repeater fields, acf repeater, dynamic tags
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 2.6.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Renders ACF or Secure Custom Fields repeater values in Elementor loop items and loop grids via Dynamic Tags.

== Requirements ==

This plugin requires the following:

* WordPress 6.0 or higher
* PHP 7.4 or higher
* Elementor 3.8 or higher
* Elementor Pro 3.8 or higher for Loop Grid/Loop Carousel features
* Secure Custom Fields or Advanced Custom Fields Pro with Repeater/Flexible Content field support

Please ensure you have these plugins installed and activated before using Dynamic ACF Repeater for Elementor.

= Compatibility boundary =

Version 2.6.0 is verified with classic Elementor Loop Grid and Loop Carousel widgets. Atomic Elements inside Loop Grids remain an upstream Elementor limitation and are not claimed as supported.

Automatic context resolves Elementor's configured preview post, queried term or author objects, the current post, and the ACF Options page fallback. Free users can also select Current Post, Queried Object, or Options directly. Pro adds Current User and explicit post, user, taxonomy-term, or Options object IDs.

The free package boots safely without premium field types, but a compatible Repeater field provider is required before row fields can be created or rendered. Version 2.6.0 was validated with Elementor 4.2.3, Elementor Pro 4.2.2, and Secure Custom Fields 6.9.5. Premium source and premium REST routes are excluded from the free package.

== Description ==

Dynamic ACF Repeater for Elementor is a powerful plugin that bridges the gap between Advanced Custom Fields (ACF) repeater fields and Elementor's dynamic content capabilities. It allows you to use ACF repeater field values directly in Elementor Loop Items and Loop Grids through Dynamic Tags.

Dynamic ACF Repeater for Elementor is an independent third-party plugin by WP Luna. It is not affiliated with or endorsed by Elementor.

== Usage Guide ==

[Usage Guide and Documentation Here](https://calculabs.github.io/elementor-acf-repeater-docs/usage-guide/)

== Free vs Pro Features ==

Dynamic ACF Repeater for Elementor comes in two versions: Free and Pro. Here's a breakdown of what each version offers:

= Free Version =

The free version of Dynamic ACF Repeater for Elementor provides essential functionality for integrating ACF repeater fields with Elementor:

* Basic integration with Elementor Pro and Secure Custom Fields or ACF Pro
* Support for image and text repeater fields in Elementor dynamic tags for loop items and loop grid
* Repeater images can be used as Loop Item background images
* ACF Repeater Text, Image, and Original Post Title dynamic tags
* Loop Grid widget integration
* Support for repeater fields on ACF Options page
* Automatic, current-post, queried-object, and Options context selection
* Optional editor-only Context Inspector for resolved provider, object context, row source, row count, and representative-row metadata

= Pro Version =

The Pro version includes everything in the free version, plus a host of advanced features for power users:

* File, gallery, link, relationship, taxonomy, color, icon, and other supported ACF values
* ACF Repeater File, Gallery, URL, Color, Icon, Link Title, and Link Target dynamic tags
* Multiple independently filtered Loop Grids on the same page, post, or template
* Opt-in integration with Elementor Pro's Taxonomy Filter widget, including multi-select filtering and its existing pagination/Load More behavior
* Lightbox functionality on the loop grid widget
* Optional previous/next lightbox navigation
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
* Stable Relationship/Post Object field-key paths through Groups, Flexible Content layouts, nested Repeaters, and mixed container combinations
* Row-scoped Relationship/Post Object queries inside mapped Repeater and Flexible Content Loop Items, including repeated layouts and empty rows
* Loop Carousel support
* Current-user and explicit ACF object context selection
* True nested Repeater row sources through Repeater, Flexible Content, and Group field paths
* Row-scoped nested Repeater loops: a Loop Grid or Loop Carousel inside a Repeater or Flexible Content Loop Item renders only the current row's nested rows
* Allow nested Loop widgets: an opt-in Loop Item setting that puts Elementor's Loop Grid and Loop Carousel back in that template's widget panel
* Treat rows as one accordion: a Loop Grid whose Loop Item is built around Elementor's Accordion widget renders its Repeater or Flexible Content rows as one accordion, with row-unique item IDs, a single open item, arrow-key navigation across rows, and one FAQ schema block
* Flexible Content layout-to-Loop-template mapping for Loop Grid and Loop Carousel
* Per-layout row schemas and representative values while editing mapped Loop Item templates
* Opt-in row search across selected scalar fields, with tokenized matching before pagination
* Stable text, number, and date sorting with deterministic missing-value handling
* Number and date range filters plus Flexible Content layout filtering
* Isolated, optional URL state for multiple independently controlled grids or carousels
* Expanded Context Inspector details for stable field paths, Flexible Content mappings, Relationship/Post Object counts, row-query state, filtering, pagination, and signed refresh security
* Element Display Conditions: ACF Repeater Field condition (show/hide by repeater sub‑field)
* New Dynamic Tags (PRO): ACF Repeater Link Title, ACF Repeater Link Target, ACF Repeater Color, ACF Repeater Icon
* Enhanced URL support (PRO): ACF Link field now maps to URL/Title/Target
* ACF Repeater Form Field: Create dynamic form fields in Elementor Forms populated from ACF repeater data (dropdown/radio/checkbox)

= Upgrade to Pro =

Unlock the full potential of Dynamic ACF Repeater for Elementor and take your dynamic content creation to the next level!

[Start Your Free Trial](https://checkout.freemius.com/mode/dialog/plugin/16334/plan/27245/?trial=paid)

The Pro version comes with a 7-day free trial. You can cancel anytime before the trial ends to avoid being charged. We'll send you an email reminder before the trial expires.

== Frequently Asked Questions ==

= Which ACF field types are supported? =

The free tags support text/textarea, image, URL-compatible values, and original post titles. The premium tags add file/media, gallery, URL/link, color, icon, link title, and link target output. Relationship and taxonomy values are formatted according to their ACF field type; multiple values use deterministic ordering.

= How do I map Flexible Content layouts to different Loop templates? =

In each Loop Item's Page Settings, choose the ACF Row Schema that the template represents. In the Loop Grid or Loop Carousel, enable ACF Rows, select the Flexible Content row source, choose the ACF Flexible Content template type, and assign a Loop Item to each discovered layout. Unmapped layouts use the normal Loop template unless Skip Row is selected.

= Can a Repeater row render its own nested Repeater rows? =

Yes, in Pro. Build the outer Loop Grid or Loop Carousel over the parent Repeater or Flexible Content source. In the parent row's Loop Item, open Page Settings › ACF Repeater Loop Settings and turn on Allow nested Loop widgets (Elementor hides Loop Grid and Loop Carousel inside Loop Items by default), then add a Loop Grid or Loop Carousel and select the nested Repeater row source (for example [Nested Repeater] Athlete Rows › Athletes). Each rendered parent row shows only its own nested rows, in ACF order, and a parent row without nested rows renders an empty inner loop. The same nested source rendered outside a parent row keeps its flattened, whole-object behavior. Editing the parent Loop Item on its own previews the nested rows of the same representative row that its Repeater dynamic tags preview.

= Can Repeater or Flexible Content rows render as one Accordion? =

Yes, in Pro. Build a Loop Item around Elementor's Accordion widget with a single item: bind the item title to an ACF Repeater Text tag, and place a Text Editor bound to another ACF Repeater Text tag inside the item's content container. In the Loop Grid, enable ACF Rows, select the Repeater or Flexible Content row source, then open Accordion Rows and turn on Treat rows as one accordion. Each row still renders its own Accordion widget, but the list behaves as one: every item ID is unique to its row, only the first item starts open, opening an item closes the open item in any other row (when the widget allows one open item), arrow keys, Home, and End move through every row, and the widget's FAQ schema is printed once for all rows on the page. With Flexible Content, only the layouts mapped to an accordion Loop Item take part; other layouts render normally in between. Use a one-column layout.

= Can a mapped Flexible Content row render its own Relationship posts? =

Yes, in Pro. Add a Loop Grid or Loop Carousel inside the mapped Loop Item, enable ACF Rows and Query Relationship Posts, then select the Relationship/Post Object field path under that Flexible Content layout. Each repeated layout row uses only its own selected posts, preserves their configured order, and returns an empty result when that row has no selections. Outside an active row, the same field-path query keeps its aggregate source-object behavior.

= Is there documentation available? =

Yes, you can find the [usage guide here](https://calculabs.github.io/elementor-acf-repeater-docs/usage-guide/).


== Screenshots ==

1. Choose your repeater for loop item - Set up your ACF Repeater field in the Loop Item Settings panel to begin using dynamic content.
2. Map your repeater text - Use ACF Repeater dynamic tags to display your repeater field content in Elementor widgets.
3. Et Voila! - See your dynamic ACF Repeater content beautifully displayed in an Elementor Loop Grid.


== Changelog ==

= 2.6.0 =

* Accordion rows as one accordion (Pro)
  - Added Treat rows as one accordion to a Loop Grid's new Accordion Rows section (shown when ACF Rows is on, default off). Each row's Elementor Accordion widget keeps rendering on its own, and the plugin links the rows as they render.
  - Made every generated accordion item ID unique to its row, together with the aria-controls and aria-labelledby pairs that reference it; custom CSS IDs on items are left as authored.
  - Kept the widget's default state only for the list's first accordion, so First expanded opens one item for the whole list and the list has one tab stop.
  - Removed the per-row FAQ schema blocks and printed one FAQPage block for all rows after the Loop Grid; background renders (editor previews, AJAX and REST refreshes) rewrite rows but print no schema.
  - Added a frontend script that closes the open item in other rows when an item opens (unless the widget allows multiple), moves focus through every row with the arrow keys, Home, and End, keeps a single roving tab stop, and folds rows added by Load More or filters into the list closed.
  - Supported Flexible Content row sources: only layouts mapped to an accordion Loop Item take part, the first accordion row starts open even when another layout precedes it, and every row keeps its own layout template.
  - Kept a Loop Grid nested inside a grouped row as its own list, and gave an accordion nested inside a row's accordion item unique IDs without changing its state.
  - Showed the switch disabled with an upgrade notice in the free build. The mode is offered on Loop Grid only.
* ACF Repeater Text tag
  - Listed Post Object and Relationship sub-fields in the tag's Repeater Field dropdown. A post value resolves to its title, so one Post Object sub-field can drive a linked title with the Text tag and its permalink with the Url tag. A sub-field returning Post ID still renders the numeric ID; prefer the Post Object return format.
* Compatibility
  - Verified with Elementor 4.2.3, Elementor Pro 4.2.2, and Secure Custom Fields 6.9.5. The legacy Accordion widget (hidden from the panel since Elementor 3.15) has no dynamic content field and is not part of this mode.
* Validation
  - Added unit coverage for ID and aria rewriting, custom IDs, collapse and tab-stop handling, schema extraction and aggregation, Flexible Content rows without an accordion, nested rows and multi-source rows, nested loops, nested accordions, background renders, and untouched grids.
  - Added Playwright coverage for cross-row closing, Enter and Space activation, multiple-open widgets, keyboard navigation, nested loops, appended and replaced rows, and unmarked containers, plus a marked LocalWP fixture with a grouped Repeater grid, a grouped Flexible Content grid mixing FAQ and Note layouts, and an ungrouped control grid.

= 2.5.0 =

* Row-scoped nested Repeater loops
  - Scoped a nested Repeater row source rendered by a Loop Grid or Loop Carousel inside a Repeater or Flexible Content Loop Item to the parent row that owns those nested rows.
  - Preserved ACF row order and the complete row index path for every scoped row so deeper nesting, row-value tags, and row-scoped Relationship queries keep working inside the inner loop.
  - Kept a parent row without nested rows authoritative so its inner loop renders empty instead of showing another row's children.
  - Matched parent rows produced by the classic top-level Repeater source (including Repeaters inside Groups) as well as registry-based nested and Flexible Content sources.
* Loop Item editing
  - Added the opt-in Allow nested Loop widgets setting to a Loop Item's ACF Repeater Loop Settings; it puts Elementor's Loop Grid and Loop Carousel back in that template's widget panel immediately, and only for that template.
  - Previewed the nested rows of the representative parent row when a Loop Item that contains a nested Loop widget is edited on its own, matching the row its Repeater dynamic tags preview.
* Row-scoped Relationship queries
  - Extended active-row scoping to parent rows produced by the classic top-level Repeater source, so a Relationship Loop Grid inside a Repeater Loop Item now follows its own row instead of aggregating every row.
* Nested Elementor loops
  - Fixed one virtual row per request rendering as the site's front page (or another template preview post) when Elementor's preview manager resolved the row's virtual ID to a Theme Builder template with a configured preview. Every such switch is now unwound, not only a switch that landed on the same post number.
  - Rebound the outer Loop Grid or Loop Carousel to Elementor's shared skin after a nested Loop widget of the same type renders, so later outer rows keep their own Loop Item and settings instead of the inner widget's.
  - Rendered a Loop widget empty when its Loop Item is already rendering one of that widget's own rows, preventing self-referencing Loop Items from recursing without end.
  - Reported a current-object query without rows as empty so Elementor's Nothing Found message renders instead of an empty loop container.
* Compatibility
  - A nested source rendered outside a matching parent row, at the same depth as the parent row, or from another ACF object keeps its flattened whole-object behavior.
  - The inner loop must resolve the same ACF object as the outer loop; Automatic context does this on the object that owns the fields, and explicit contexts must match.
  - No saved Elementor settings are renamed or migrated, and no custom PHP is required for the nested row workflow.
* Validation
  - Added unit coverage for row-scoped nested sources, classic and group-prefixed parent paths, layout isolation, empty parent rows, standalone Loop Item preview rows, shared-skin rebinding, and the self-referencing render guard.
  - Added a marked LocalWP fixture with three parent rows of two, one, and zero nested rows plus real WordPress frontend, Loop Item editor, desktop, and mobile browser coverage.

= 2.4.9 =

* Row-scoped Relationship/Post Object queries
  - Scoped nested Loop Grid and Loop Carousel Relationship queries to the active Repeater or Flexible Content row that owns the selected field path.
  - Preserved the order configured in ACF or Secure Custom Fields for every row, including repeated instances of the same Flexible Content layout.
  - Kept an empty active row authoritative so it cannot inherit posts selected by another row.
* Nested Elementor loops
  - Preserved real related-post context while a Relationship Loop Grid renders inside a virtual row Loop Item.
  - Pinned Flexible Content skin settings through nested Loop Grid rendering so later rows retain their own layout-to-template mappings.
* Compatibility
  - Top-level and out-of-row Relationship queries keep their existing source-object aggregation behavior.
  - No saved Elementor settings are renamed or migrated, and no custom PHP is required for the nested row workflow.
* Validation
  - Added unit coverage for repeated layouts, layout isolation, empty rows, top-level fallback, and nested real-post context.
  - Added a marked LocalWP fixture plus real WordPress, Elementor editor, desktop, and mobile browser coverage.

= 2.4.8 =

* Elementor dynamic-tag controls
  - Restored the ACF Repeater Text Advanced section when the plugin prebuilds Elementor's dynamic-tag control stack.
  - Before, After, and Fallback now appear in the actual Elementor editor registration path.
* Options-page Loop Item previews
  - Repeated a saved row-schema lookup against the ACF Options page when the selected Elementor preview post has no rows.
  - Kept post-specific preview rows authoritative whenever the preview object contains data.

= 2.4.7 =

* ACF Repeater Text output controls
  - Added native Before, After, and Fallback controls under the dynamic tag's Advanced section.
  - Applied Before and After only when the selected field has a value, and returned Fallback by itself when it is empty.
  - Preserved zero as a populated value and retained the existing output when all three controls are blank.
* Compatibility
  - Kept ACF Repeater Text as a plain Elementor Data Tag and left image, URL, gallery, file, and other structured-value tags unchanged.

= 2.4.6 =

* Fragment and relative Repeater URLs
  - Fixed ACF Link subfields using the Link URL return format dropping fragment-only links such as `#information-we-collect`.
  - Preserved root-relative URLs while continuing to sanitize all scalar URL values through WordPress.
* Compatibility
  - Absolute URLs, email links, media fields, relationships, taxonomy links, Loop Item editor previews, and frontend row context behavior remain unchanged.

= 2.4.5 =

* Frontend context isolation
  - Fixed Automatic context using a Loop Item's saved Preview Post on normal frontend requests instead of the actual queried post.
  - Limited saved Elementor Preview Post resolution to Elementor edit and preview modes.
* Compatibility
  - Loop Item editor previews continue to use their selected sample post, while frontend Loop Grids and Loop Carousels use each viewed post's own rows.

= 2.4.4 =

* Loop Item autosave preview
  - Fixed Repeater dynamic tags rendering empty values when the selected Preview Post existed only in the current editor user's Elementor autosave.
  - Resolved the active Loop Item through Elementor's autosave-aware document API while preserving the published-document fallback.
* Compatibility
  - The override remains limited to authenticated Elementor AJAX requests for this plugin's Repeater tags; frontend rendering and unrelated dynamic tags are unchanged.

= 2.4.3 =

* Loop Item editor preview
  - Fixed Repeater dynamic tags rendering empty values when Elementor's editor-side tag request used the Loop Item template instead of its selected preview post.
  - Persisted explicit ACF row-schema changes before reloading the editor preview, including changes restored to the control default.
  - Invalidated stale dynamic-tag preview cache entries after a schema change without altering non-Repeater dynamic-tag settings.
* Compatibility
  - Frontend Loop Grid and Loop Carousel rendering is unchanged, no saved settings are renamed or removed, and no migrations run.

= 2.4.2 =

* Multiple same-object Loop Grids
  - Fixed trailing Repeater Loop Grids rendering empty dynamic values when Elementor normalized a negative virtual row ID to an existing positive Theme Document ID.
  - Preserved each virtual row and the surrounding WordPress query through Elementor's Loop Item preview lifecycle without leaking row context into later grids.
* Compatibility
  - No saved Elementor settings are renamed or removed, and no migrations run.

= 2.4.1 =

* Trial and pricing alignment
  - Updated the paid Pro trial from 3 days to 7 days to match the current Freemius checkout configuration.
  - Kept the payment-method requirement and existing plan, license, and entitlement behavior unchanged.
* Compatibility
  - No Loop Grid, Loop Carousel, field-provider, editor-control, saved-setting, or frontend-rendering behavior changed.

= 2.4.0 =

* Editor-only Context Inspector
  - Added an opt-in Troubleshooting panel to Elementor Loop Grid and Loop Carousel widgets that use ACF or Secure Custom Fields rows.
  - Reports the resolved field provider, object context, row source, row count, and representative-row metadata without exposing row values.
  - Pro also reports stable source paths, Flexible Content template mappings, Relationship/Post Object paths and counts, row search/sort state, filtering, pagination, and public-refresh security.
* Privacy, security, and compatibility
  - Keeps the inspector disabled by default and sends no request until it is enabled on the current widget.
  - Requires an authenticated nonce plus permission to edit the active Elementor document and accepts only a strict settings allowlist.
  - Adds no inspector markup, buttons, styles, or visual changes to the preview canvas or frontend.
  - Adds no setting renames, removals, automatic migrations, or behavior changes to existing widgets.
* Validation
  - Added metadata-leakage, provider, context, Options fallback, all-posts, Flexible Content, row-query, and Relationship/Post Object unit coverage.
  - Added real Elementor Loop Grid and Loop Carousel browser coverage for default-off behavior, context resolution, AJAX results, toggle cycling, visual quality, and zero canvas/frontend output.

= 2.3.0 =

* Row discovery controls (Pro)
  - Added opt-in search across selected scalar Repeater or Flexible Content row fields.
  - Added stable text, number, and date sort options with configurable labels and default direction.
  - Added number/date minimum and maximum filters plus Flexible Content layout filtering.
  - Added isolated, optional URL state and support for multiple independently controlled Loop Grids or Loop Carousels.
* Compatibility and behavior
  - Applied taxonomy filtering, row search, ranges, and sorting before one final pagination step.
  - Kept every new control disabled by default; no existing widget settings or Loop Item markup are changed.
  - Added Elementor Style controls for the opt-in public control group.
* Validation
  - Added unit coverage for field discovery, Group paths, search, stable sorting, ranges, invalid bounds, layout filtering, widget isolation, and taxonomy-filter composition.
  - Added isolated browser coverage for state transport, stale-request cancellation, URL isolation, and 500-row response performance.

= 2.2.1 =

* Elementor editor compatibility (Pro)
  - Fixed an HTTP 500 while Elementor generated its global Loop Grid and Loop Carousel control configuration.
  - Stopped reading instance settings while Elementor is still constructing the widget control schema.
  - Kept legacy Relationship/Post Object selectors editable through clearly labelled compatibility options, with no saved-setting migration.
* Validation
  - Added unit coverage for prototype-safe Relationship control options.
  - Added real Elementor editor coverage for a six-row Repeater Loop Carousel.

= 2.2.0 =

* Product name
  - Renamed the customer-facing product to Dynamic ACF Repeater for Elementor, following Elementor's trademark guidance.
  - Preserved the existing plugin folder, main file, WordPress.org slug, text domain, Freemius identity, saved settings, and internal identifiers so updates remain backward-compatible.
* Deep Relationship/Post Object queries (Pro)
  - Added stable field-key paths through Groups, Flexible Content layouts, nested Repeaters, and mixed container combinations.
  - Flattened posts selected across multiple nested rows, removed duplicates, and preserved the first configured occurrence and field order.
  - Added Relationship query controls to Loop Carousel as well as Loop Grid.
* Backward compatibility
  - Preserved existing top-level field-name and `repeater:subfield` settings without automatic migrations.
  - Kept legacy saved selectors visible while editing existing widgets without offering name-based paths to new widgets.
  - Added no frontend controls, panels, or visual markup.
* Validation
  - Added unit coverage for top-level, Group, Flexible Content, Repeater, mixed-container, layout-isolation, empty, duplicate, ID, object, and legacy-selector cases.
  - Added a polished LocalWP Relationship showcase plus frontend, editor, desktop, and mobile browser coverage.

= 2.1.0 =

* Elementor Pro Taxonomy Filter integration (Pro)
  - Added an opt-in bridge from Elementor Pro's Taxonomy Filter widget to taxonomy fields stored in Repeater rows.
  - Preserved Elementor Pro's Taxonomy Filter markup, styling, URL state, multi-select behavior, and pagination/Load More controls.
  - Limited Taxonomy Filter term choices to terms used by the configured current-object rows when empty terms are hidden.
  - Applied row filtering before pagination so matches from later pages remain available.
* Backward compatibility
  - Kept the existing built-in custom filter, saved controls, URL parameters, and frontend markup unchanged when the Elementor Pro integration is not enabled.
  - Made the Elementor Pro integration explicitly opt-in and mutually exclusive with the existing custom filter for each Loop Grid.
  - Added no setting renames, removals, or automatic migrations.
* Validation
  - Added unit coverage for query isolation, multi-term OR, AND, NOT IN, child terms, and filter-before-pagination behavior.
  - Added real Elementor browser coverage for Taxonomy Filter term discovery, REST refresh, Load More, multi-select, empty results, and mobile viewport fit.

= 2.0.1 =

* Compatibility
  - Restored the established field-provider behavior: Secure Custom Fields and Advanced Custom Fields Pro are both supported through their shared runtime API.
  - Kept Elementor as the only hard WordPress.org plugin dependency because the dependency header cannot express alternative providers.

= 2.0.0 =

* Relationship queries (Pro)
  - Preserved Elementor posts-per-page and pagination when resolving Relationship or Post Object fields from the current ACF object.
  - Kept selected posts in field order without routing them through Repeater virtual-row pagination.
* Nested row sources (Pro)
  - Added stable field-key paths for Repeaters nested inside Repeaters, Flexible Content layouts, and Group fields.
  - Flattened matching nested rows in source order while preserving their complete row/index path and formatted ACF values.
  - Kept existing top-level Repeater widget settings and saved Loop Items backward-compatible.
* Flexible Content templates (Pro)
  - Added the ACF Flexible Content Loop skin for Loop Grid and Loop Carousel.
  - Added one opt-in Loop Item mapping control per discovered Flexible Content layout.
  - Added explicit fallback-to-default and skip-unmapped behavior without inserting frontend buttons, panels, or other visual markup.
  - Disabled Elementor's position-based Alternate Templates only while the data-driven Flexible Content skin renders, then restored the saved setting.
* Elementor editor
  - Added row-schema choices for nested Repeaters and individual Flexible Content layouts in Loop Item Page Settings.
  - Limited dynamic-tag field choices to the selected layout schema.
  - Rendered representative nested/layout values in the Loop Item editor preview through the same resolver used on the frontend.
* Validation
  - Added PHPUnit coverage for discovery, stable selectors, nested path flattening, layout schemas, and virtual-row values.
  - Added real WordPress browser coverage for per-layout output, nested rows, mapping controls, and Loop Item preview data.

= 1.2.1 =

* Lightbox visual compatibility
  - Removed plugin-generated buttons from Loop items and removed the editor diagnostic overlay.
  - Restored the lightbox to a dim overlay containing the cloned Loop item, with no generated title, white panel, toolbar, or footer.
  - Kept nested links and form controls interactive while non-interactive card clicks open the lightbox.
  - Prevented site-wide button styles from adding backgrounds, borders, or rounded shapes to close and previous/next controls.
* Elementor editor
  - Applied width, height, padding, background, navigation, and close-control changes to an already-open lightbox preview.
  - Restored Content Height sizing on the cloned Loop item and its background-bearing Elementor root instead of sizing only the modal wrapper.
  - Preserved Loop Carousel item deduplication and editor document-handle access without adding template markup.
* Packaging
  - Kept the Freemius source header neutral while labeling licensed premium installs as PRO without duplicating an existing suffix.

= 1.2.0 =

* Context controls
  - Added one normalized ACF context resolver for Loop Grid, Loop Carousel, relationship queries, filters, and signed filter refreshes.
  - Added Automatic, Current Post, Queried Object, and Options selectors to Free; Pro also supports Current User and explicit post, user, taxonomy-term, or Options IDs.
  - Added editor context diagnostics; these were removed in 1.2.1 because they imposed visible UI on the canvas.
* Elementor editor
  - Restored lightbox opening inside AJAX-rendered Loop Grid and Loop Carousel previews.
  - Added per-item lightbox trigger buttons; these were removed in 1.2.1 in favor of the existing card click surface.
  - Restored all Pro lightbox Style controls for both Loop Grid and Loop Carousel.
* Reliability
  - Preserved non-post ACF object IDs through virtual rows and signed filter requests.
  - Avoided enqueueing frontend dependencies inside editor AJAX and REST render responses.

= 1.1.0 =

* Security
  - Replaced arbitrary public document rendering with signed, expiring render contexts bound to published content and an owned Loop Grid/Carousel widget.
  - Blocked anonymous private, draft, password-protected, malformed, and cross-document render requests with controlled REST errors.
  - Changed editor REST permissions from generic edit_posts checks to edit_post checks for the requested document.
* Correctness
  - Removed the site-wide SQL rewrite that stripped post__not_in exclusions from unrelated queries.
  - Added request-local, collision-safe virtual row IDs and row-level pagination for current-object repeaters.
  - Fixed empty relationship queries, preserved ACF relationship order, and corrected multi-term OR filtering.
  - Corrected media, URL, relationship, taxonomy, and multi-value dynamic-tag formatting.
* Premium
  - Scoped multiple filters to their owning widget, localized the REST URL, and prevented stale responses from replacing newer results.
  - Consolidated feature registration behind the active Freemius entitlement and updated Freemius SDK to 2.13.4.
  - Reworked the lightbox to preserve interactive content and provide buttons, accessible names, focus management, keyboard controls, and reduced-motion support.
* Compatibility and maintenance
  - Raised the Elementor/Elementor Pro minimum to 3.8, added Requires Plugins headers, dependency notices, scoped asset loading, uninstall cleanup, and graceful missing-SDK behavior.
  - Added PHPUnit, WordPress runtime, package-boundary, and Playwright regression suites for free and premium builds.

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
* Initial release of Dynamic ACF Repeater for Elementor
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
