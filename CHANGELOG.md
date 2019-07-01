# Career Pathways Web Tool Release Log
*(Since September 17, 2015)*

## 4.5.0 (June 30, 2019)
### Config
* None

### Release Notes
* Upgrade TinyMCE to version 5.


## 4.4.0 (June 25, 2019)
### Config
* None

### Release Notes
* Allow users to resize and move the content editor.


## 4.3.7 (May 25, 2018)
### Config
* None

### Release Notes
* DB update function will now include all values passed to it.


## 4.3.6 (May 21, 2018)
### Config
* None

### Release Notes
* When saving organization, prevent empty fields from causing an SQL error.


## 4.3.2 (August 18, 2017)
### Config
* None

### Release Notes
* Add CHANGELOG.md to project.
* Include Yahoo YUI files within core.
* Update licensing.


## 4.3.1 (June 7, 2017)
### Config
* None

### Release Notes
* Update Readme
* Do not auto-expand embedded drawing container height if height is defined.


## 4.3.0 (May 9, 2017)
### Config
* Execute `(core)/scripts/sql/updates/*_4.3.0.sql`

### Release Notes
* Update PDF and ADA Accessible links in top-right of drawings.
* Properly case words when importing program titles via APN.
* OLMIS Links titles are now acquired from drawing content, no longer depend on Quality Info website.
* Optionally remove roadmap title.
* Break chview.js and chadmin.js into separate files to make debugging easier.
* Fix bug where background color is lost on innerRectangle re-paint.
* Add Approved Program Name report.
* Add totals to APN report.
* Fix vertical alignment of content when hiding roadmap content box title.
* Fix PDF/IE rendering.
* Update verbiage on OLMIS links and add id.
* Fix roadmaps are not displaying at their full height when embedded.
* Make roadmaps canvas twice as tall.
* Add icons, formatting, and small html tweaks for PDF and text only links.
* Adds unit tests.
* Exclusion/exception lists for roadmap drawings now work with the DB.
* The report "Provide a quick breakdown on the types of POST Views" now has subtotals for HS, CC and other org.
* Update "POST report" page for an easier time navigating content.
* Update embed code for roadmap drawings so there is no scrollbar and the iframe takes the height of the drawing.
* Fix composer should not install php unit in non-dev environments.
* PDF exports should not ever contain the links to the pdf and text only versions of drawings.
* Fix PDF link in POST drawings.
* Fix Image library not loading buckets.
* Org admins of other orgs should be able to create post views.
* Anyone with access to a post view should be able to get the embed code.
* Other org users should see the new OLMIS text.
* Help desk email reply-to is now the form-filler's email address when available.
* Remove test embed code for drawings.
* Fixes regex bug affecting alt text entries.
* Adds font choice to Roadmap and POST Drawings.
* Fixes Schools information not displaying correctly on create school page.

## 4.2.x (August 24, 2016)
### Config
* Execute `(core)/scripts/sql/updates/d20160621_add_show_updated_column.sql`
* Execute `(core)/scripts/sql/updates/d20160623_add_alt_column_to_assets_table.sql`
* Execute `(core)/scripts/sql/updates/d20160706_4.2.0.sql`

### Release Notes
* Adds ability to turn "Updated" stamp on/off for published Roadmap and POST Drawings.
* Adds variable connection and line thickness to Roadmap drawings. Light, Medium and Heavy are now available.
* Editors can choose dashed lines as a connection type when editing Roadmap Drawings.
* Site admins can create abbreviations for Degree Types, and Views will utilize them for new tab names.
* Admins can see all of the image info in Image Library.
* Adds cache-invalidation for Image Library files.
* Users can now  enter custom alt text for images.
* Fix Image library was showing the school the asset belonged to. It now shows the school that the asset's creator belongs to.


## 4.1.5 (May 19, 2016)
### Config
* None

### Release Notes
* Updates image library to have a grey background for image assets, so white images with transparency can be seen in all steps.
* Updates wording during image replacement.
* Fixes image replacement in image library across different buckets. Improves Move behavior as well.
* Changed permissions for webmaster to match those of OrgAdmin.

## 4.1.4a (April 19, 2016)
### Config
* None

### Release Notes
* Moves the Image Library “move” feature up above images, so it’s not too far off the screen when used.
* Updates permissions model for the Image Library. “If you uploaded it, you can modify it.”
* Updated help sidebar links styles to be consistent in Roadmap and POST edit screens.

## 4.1.4 (March 24, 2016)
### Config
* t192 - It might be necessary to tweak css on a per-project basis, for this to take effect. I don't think so but be aware.

### Release Notes
* Adds ability to move images to different buckets in the Image Library. [LL WA t72]
* Fixes Approved Program Name not showing up when adding a drawing to a view. [LL OR t193]
* Makes tools sidebar link styles consistent with other sidebar links. [LL OR t195]
* Updates default image library bucket to be user's school, except state admins (who default to the site-wide bucket). [LL WA t74]
* Updates post views, so Community College section tab names have the format: "DEGREE TYPE: POST Drawing Title" by default. High School tab names are unchanged. [LL OR t166]

### Additional Info
* When moving images in the image library:
* A user can move an image out of a bucket if they have the delete permission for that bucket (since they're essentially removing it from that bucket).
* A user will only see buckets that they are allowed to write TO, excluding the current bucket  (obviously). This causes a case where there are no buckets to move the image to, and that's expected/desired as far as I'm aware. I added a message that says something like "Sorry, there are no buckets available to move the image to".



## 4.1.3b (March 16, 2016)
### Config
* None

### Release Notes
* Adds a stop message to the image library to encourage users to select their school bucket before uploading.


## 4.1.3a (March 14, 2016)
### Config
* None

### Release Notes
* Updates county selector to show saved state and county. [NAC t1]


## 4.1.3 (February 24, 2016)
### Config
* Execute: `core/scripts/sql/updates/d20160224_update_counties_table.sql`

### Release Notes
* Update system to accommodate nation-wide counties


## 4.1.2 (January 5, 2016)
### Config
* Install composer and run it with `(core)/composer.json`
* Set up a gmail account and get username and password into site config:
  `function gmail_credentials() { return array( 'username' => '', 'password' => '' ); }`
* Log in to this gmail account and allow "less secure" apps. (https://support.google.com/accounts/answer/6010255?hl=en)
* Make sure `function email_name() { return "Humboldt CTE Pathways"; }` is in the config

### Release Notes
* Bug fix: adds scroll bars to media library pop up in Firefox on Windows. [LL OR t185]
* Fixes bug that didn't allow saving external link in post views. [LL OR t187]
* Makes accurate the site email that shows when editing email templates. [OR t188]
* Image Manager shows buckets to admins, even if there are not images yet. [WA t71]
* Emails now sent out via gmail which allows users to be BCC'd properly. [LL OR t188]


## 4.1.1 (October 29, 2015)
### Config
* Run `(core)/scripts/sql/updates/d20151014_reorder_admin_sidebar.sql`
* Run `(core)/scripts/sql/updates/d20151014_add_permissions_to_sidebar.sql`

### Release Notes
* Updates server requirements in readme. [LL OR t175]
* Restores Custom Program Name in new drawings. [LL OR t180]
* Reorders the admin sidebar. [LL OR t182]
* Updates link and contact info on the licensing page. [OR t184]

#### Older notes, probably redundant with above:
* t183 Bug:  oregon.ctepathways.org is missing valid tracking code (Google Analytics).
* t184 Update Licensing page for Oregon
* t175 Update server requirements on cpwebtool.org
* t179 Box Content - Tables - Table Width - Recommendation
* t180 Restore Custom Program Name in new drawings
* t182 Re-order the admin sidebar


## 4.1.0 (October 8, 2015)
### Config
* WA t18 - Run `(core)/scripts/sql/updates/d20150623_create_assets_table.sql`
* WA t18 - Add function asset_path() to default.settings.php for all sites. It should return a string (absolute path) of a folder where to save assets. This folder should be backed up in production, just like the database, etc. (DO NOT USE THE CACHE FOLDER)
* t32 - Run `(core)/scripts/sql/updates/d20150923_update_assets_table.sql`
* t41 - Clear site cache.
* t47 - Run `(core)/scripts/sql/updates/d20150928_add_asset_manager_admin_module.sql`

### Release Notes
* Adds template and core version information to footer. [LL OR t176]
* Styles template and core version information in footer. Also, version info to footer for core's template. [LL OR t176]
* Makes skillset selector update when choosing an Approved Program Name, for Roadmap, POST drawings and POST Views. [LL OR t178]
* Completes File Upload bucket-by-school feature. [LL WA t55]
* Fixes school color replacement doesn't work on background color. [LL WA t56]
* Fixes bug where Roadmap PDF shrinks to the upper left corner. [LL WA t41]
* Finishes delete button in Asset Manager. Moves asset manager logic to `/asset/Asset_Manager.js`. [LL WA t32]
* Adds a dedicated page for the asset manager, and a sidebar link. Updates asset delete to include both Roadmap and POST drawings at all times. [LL WA t47]
* Adds replace button to the Asset Manager. [LL WA t47]
* Fixes glitch where connection colors use previous parent object's border color instead of current parent. [LL OR t181]
* Makes skillset selector update when choosing an Approved Program Name, for Roadmap, POST drawings and POST Views. [LL OR t178]
* Fixes school color replacement doesn't work on background color. [LL WA t56]
* Fixes school color tally for background color use in Roadmap drawings. [LL WA t56]
* Fixes bug where Roadmap PDF shrinks to the upper left corner. [LL WA t41]
* Fixes glitch where connection colors use previous parent object's border color instead of current parent. [LL OR t181]
* Moves "Questions/Problems" link to the left. [LL OR t176]
* Changes image size in the image manager and removes the upload button. [LL WA t55]
* Adds state name to the template version tag in the footer. [LL OR t176]
* Changes image manager titles to title case. [LL WA t32]
* Changed verbiage from "asset" to "image" when replacing images [LL WA t47]
* Image Manager can no longer delete images in use. [LL WA t32]
* Added note detailing use of replace feature when in replace mode [LL WA t47]



## 4.0.0 (September 17, 2015)
**Note: Covers from cc21e66fc644dda88cf58da318518b1cca945f89 to 4.0.0**
We started formal versioning here.

### Config
* t43 - Run `(core)/scripts/sql/updates/d20150831_archive_inactive_users.sql`

### Release Notes
* Begins formal versioning (4.0.0)
* Bug: Fixes "invalid address" error during account request. [LL WA t43]
* Adds foreground color and background color controls to POST drawing editor. [LL WA t45]
* Move work from "core" branch to Master. [LL WA t52]
* Fixes 404 not found on `/c/cis_roadmap.csv` [LL OR t161]
