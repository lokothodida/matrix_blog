matrix_blog
===========

A blogging plugin for GetSimple using n00dles' The Matrix.

FEATURES
===========
* Full template editing - modify almost any aspect of the blog's look
* Custom field creation - by virtue of using The Matrix, you can use The Matrix to add extra fields that may be needed
* Category creation - easily created from a textarea, with sub-categories created simply using '> ' for different levels of indentation
* i18n integration - entries can be created in multiple languages and can be created exclusively in one language (without
                  being the base langauge of your site)
i18n searchable - entries are fully indexed by i18n search (and you can remove individual entries from the results)
Built-in comments system - readers can comment on your posts. Don't worry about commenters trying to impersonate
            the

#CORE DEPENDENCIES

* i18n
* i18n Search
* Pajify
* The Matrix

INSTALLATION
===========
Download and install the CORE DEPENDENCIES first to your /plugins folder. Then to the same for this plugin.

USAGE
===========
On the Pages tab of the admin panel, you will see 'Blog Entries'. click there to open the main panel.

  VIEW
  =========
  Click 'View' to view your blog's main page.
  
  ENTRIES
  Click 'Create Entry' to create a blog entry.
  Click '#' next to an entry to view it.
  Click 'x' next to an entry to delete it (this can be undone on the immediately following page)
  Click on an entry's name to edit it.
  
  You can also sort your view of the entries via Title, Slug, Creation and Publication date.
  
  TEMPLATES
  You can edit the look of the following templates:
    HEADER (for HTML, CSS and Javascript at the top of the page)
    ENTRY (for the individual entry)
    EXCERPT (for how entries look on the main blog page and in search results)
    COMMENTS (for how each comment will be formatted)
    AUTHOR (for your author's page)
    SIDEBAR
