<?php
/* The Matrix Blog
 */

# thisfile
  $thisfile = basename(__FILE__, ".php");
 
# language
  i18n_merge($thisfile) || i18n_merge($thisfile, 'en_US');
 
# requires
  require_once(GSPLUGINPATH.$thisfile.'/php/class.php');
  
# class instantiation
  $mblog = new MatrixBlog; // instantiate class

# register plugin
  register_plugin(
    $mblog::FILE,           // id
    $mblog->title,          // name
    $mblog::VERSION,        // version
    $mblog::AUTHOR,         // author
    $mblog::URL,            // url
    $mblog->desc,           // description
    $mblog::PAGE,           // page type - on which admin tab to display
    array($mblog, 'admin')  // administration function
  );

# activate actions/filters
  # front-end
    add_action('error-404', array($mblog, 'display')); // display for plugin
  # back-end
    add_action($mblog::PAGE.'-sidebar', 'createSideMenu' , array($mblog::FILE, $mblog->sidebar)); // sidebar link
    add_action('search-index',   array($mblog, 'searchIndex'));
    add_filter('search-item',    array($mblog, 'searchItem'));
    add_filter('search-display', array($mblog, 'searchDisplay'));

# public functions
  # get config
  function mblog_config($prop) {
    global $mblog;
    $config = $mblog->getConfig();
    if (isset($config[$prop])) return $config[$prop];
    else return null;
  }
  
  # breadcrumbs
  function mblog_show_breadcrumbs($showHome=false, $delim='&nbsp;&nbsp;&bull;&nbsp;&nbsp;') {
    global $mblog;
    echo $mblog->getBreadcrumbs($showHome, $delim);
  }
  
  # get image url
  function mblog_get_img_url($img) {
    global $mblog;
    echo $mblog->getImageURL($img['image']);
  }
  
  # get thumb url
  function mblog_get_thumb_url($thumb) {
    global $mblog;
    echo $mblog->getThumbURL($thumb['image']);
  }
  
  # get entry field properties 
  function mblog_get_field($entry, $field) {
    if (isset($entry[$field])) echo $entry[$field];
  }
  
  # get field date
  function mblog_get_field_date($entry, $field, $format='r') {
    if (isset($entry[$field])) echo date($format, $entry[$field]);
  }
  
  # get entry url
  function mblog_get_entry_url($entry) {
    global $mblog;
    echo $mblog->getEntryURL($entry);
  }
  
  # return field properties
  function mblog_return_field($entry, $field) {
    if (isset($entry[$field])) return $entry[$field];
  }
  
  # get entry languages
  function mblog_get_entry_langs($entry) {
    global $mblog;
    $mblog->getEntryLangs($entry['slug']);
  }
  
  # get tags
  function mblog_get_tags($entry, $delim) {
    global $mblog;
    $mblog->getTags($entry['tags'], $delim);
  }
  
  # get category
  function mblog_get_category($entry) {
    global $mblog;
    echo $mblog->getCategory($entry['category']);
  }
  
  # get category URL
  function mblog_get_category_url($entry) {
    global $mblog;
    echo $mblog->getCategoryURL($entry['category']);
  }
  
  # get author name 
  function mblog_get_author_name($entry) {
    global $mblog;
    if (isset ($entry['author'])) {
      echo $mblog->getAuthorName($entry['author']);
    }
    elseif (isset($entry['USR'])) {
      echo $mblog->getAuthorName($entry['USR']);
    }
    else return null;
  }
  
  # get author url 
  function mblog_get_author_url($entry) {
    global $mblog;
    if (isset ($entry['author'])) {
      echo $mblog->getAuthorURL($entry['author']);
    }
    elseif (isset($entry['USR'])) {
      echo $mblog->getAuthorURL($entry['USR']);
    }
    else return null;
  }
  
  # get author email
  function mblog_get_author_email($author) {
    global $mblog;
    if (isset ($author['EMAIL'])) {
      echo $author['EMAIL'];
    }
    else return null;
  }
  
  # get author bio
  function mblog_get_author_bio($author) {
    global $mblog;
    $mblog->getAuthorBio($author);
  }
  
  # get latest entries
  function mblog_get_author_latest($author, $max=5) {
    global $mblog;
    $mblog->showLatestEntries($author['USR'], $max);
  }
  
  # search form
  function mblog_show_search_form() {
    global $mblog;
    $mblog->searchForm();
  }
  
  # archive
  function mblog_show_archive() {
    global $mblog;
    $mblog->showArchiveMenu();
  }
  
  # categories dropdown
  function mblog_show_categories() {
    global $mblog;
    $mblog->showCategoriesMenu();
  }
  
  # show comment field
  function mblog_get_comment_field($comment, $field) {
    if (isset($comment[$field])) echo $comment[$field];
  }
  
  # get gravatar
  function mblog_get_comment_gravatar($comment) {
    global $mblog;
    echo $mblog->getGravatar($comment['email']);
  }
  
  # show latest comments
  function mblog_get_entry_comments($entry) {
    global $mblog;
    $mblog->displayComments($entry['id']);
  }
  
  # show latest comments
  function mblog_show_latest_comments($max=5) {
    global $mblog;
    $mblog->showLatestComments($max);
  }
  
  # show sidebar 
  function mblog_show_sidebar() {
    include(GSPLUGINPATH.MatrixBlog::FILE.'/php/display/sidebar.php');
  }