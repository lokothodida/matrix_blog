Matrix Blog
===========
A blogging plugin for GetSimple using n00dles(001)'s The Matrix.

FEATURES
===========
* Full template editing - modify almost any aspect of the blog's look
* Custom field creation - by virtue of using The Matrix, you can use The Matrix to add extra fields that may be needed
* Category creation - easily created from a textarea, with sub-categories created simply using '> ' for different levels of indentation
* i18n integration - entries can be created in multiple languages and can be created exclusively in one language (without being the base langauge of your site)
* i18n searchable - entries are fully indexed by i18n search (and you can remove individual entries from the results)
* Built-in comments system - readers can comment on your posts. Don't worry about commenters trying to impersonate you though - admin comments and author comments are specially highlighted
 * Gravatar support

CORE DEPENDENCIES
===========
* The Matrix (n00dles)
* i18n (mvlcek)
* i18n Search (mvlcek)
* Pagify (mvlcek)

INSTALLATION
===========

__NOTE: This plugin currently assumes that your apache server has mod_rewrite enabled.__

Download and install the CORE DEPENDENCIES first to your /plugins folder. Then to the same for this plugin.

BASIC USAGE
===========
On the Pages tab of the admin panel, you will see 'Blog Entries'. click there to open the main panel.

To show the sidebar, call the function 'mblog_show_sidebar' on your current theme's template/in a component (e.g. <?php mblog_show_sidebar(); ?>)

# VIEW
  Click 'View' to view your blog's main page.
  
# ENTRIES
  * Click 'Create Entry' to create a blog entry.
  * Click '#' next to an entry to view it.
  * Click 'x' next to an entry to delete it (this can be undone on the immediately following page)
  * Click on an entry's name to edit it.
  
  You can also sort your view of the entries via Title, Slug, Creation and Publication date.
  
# TEMPLATES
  * You can edit the look of the following templates:
    * HEADER (for HTML, CSS and Javascript at the top of the page)
    * ENTRY (for the individual entry)
    * EXCERPT (for how entries look on the main blog page and in search results)
    * COMMENTS (for how each comment will be formatted)
    * AUTHOR (for your author's page)
    * SIDEBAR (for reorganizing the widgets)

# CONFIG
 * You can modify the configuration of your blog.
  * Base URL: base URL of all aspects of the blog
  * Slug: dummy slug for your blog to be located on (this should reflect the Base URL)
  * Template: core template file for blog based on your current theme
  * Authors URL: relative path for blog author profiles
  * Tags: tags to exist for all blog entries when searched
  * Categories: categories for your blog entries to be sorted into (one category name per line)
   * Sub categories are created by prefixing the category with '>' for how many indentations are needed, then a space.
  * Language: languages you would like to select from when creating an entry; one language code per line
  * Image Upload Settings: Set the max file size, image dimensions and thumb dimensions of uploads
  * Excerpt Length: length of excerpts on main blog page and search results
  * Entries per page: number of entries per blog page/search results
  * Comments per page: max number of comments per page before pagination
  * Min chars per comment: min length of a comment (NEEDS FIXING)
  * Max chars per comment: max length of a comment
  * Require name, email, URL: sets whether those entities are required prior to posting a comment
  * Censored words: list of words you'd like to censor in a comment (replaced with ****); one line per word (and if a line is of the form 'foo, bar', the word 'foo' will be replaced with 'bar' rather than '****')
  * Banned IPs: one IP per line - restricts users from commenting (user IPs are visible on comment moderation page - go to the entry and click 'Comments')
  * Message above form: message above the comments form
  