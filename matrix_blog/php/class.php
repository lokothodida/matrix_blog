<?php

class MatrixBlog {
  /* constants */
  const ID             = 'blog';
  const FILE           = 'matrix_blog';
  const VERSION        = '0.3';
  const AUTHOR         = 'Lawrence Okoth-Odida';
  const URL            = 'http://lokida.co.uk';
  const PAGE           = 'pages';
  const TABLE_BLOG     = 'matrix-blog';
  const TABLE_CONFIG   = 'matrix-blog-config';
  const TABLE_COMMENTS = 'matrix-blog-comments';
  const SEARCHID       = 'mbl:';
  
  /* properties */
  public  $title;
  public  $desc;
  public  $sidebar;
  private $info = array();
  public  $glob;
  public  $siteurl;
  private $prettyurl;
  private $schema;
  private $commentsSchema;
  private $matrix;
  private $template;
  private $templates;
  private $search;
  private $entry;
  private $authors;
  private $author;
  private $blog;
  private $languages;
  private $categories;
  private $comments;
  private $archive;
  private $uri;
  private $type;
  private $init;
  private $commentParents  = array();
  private $commentChildren = array();
  private $commentCounter;
  
  /* methods */
  
  /* ====== INITIALIZE ====== */
     
  // constructor
  public function __construct() {
    // load language placeholders
    $this->title    = i18n_r(self::FILE.'/TITLE');
    $this->desc     = i18n_r(self::FILE.'/DESC');
    $this->sidebar  = i18n_r(self::FILE.'/SIDEBAR_TEXT');
    $init = false;
  }
  
  // initialize
  private function init() {
    // load globals from XML
    $websiteXML = XML2Array::createArray(file_get_contents(GSDATAOTHERPATH.'website.xml'));
    $this->glob['sitename']   = $websiteXML['item']['SITENAME']['@cdata'];
    $this->glob['siteurl']    = $websiteXML['item']['SITEURL']['@cdata'];
    $this->glob['template']   = $websiteXML['item']['TEMPLATE']['@cdata'];
    $this->glob['prettyurls'] = $websiteXML['item']['PRETTYURLS'];
    $this->glob['permalink']  = $websiteXML['item']['PERMALINK'];
    
    // initialize objects
    $this->matrix  = new TheMatrix;
    $this->parser  = new TheMatrixParser;
    
    // selectable languages
    $this->languages = array('en', 'it', 'ru');
    
    // build tables
    $this->buildTables();
    
    // get schema for blog table and comments
    $this->schema = $this->matrix->getSchema(self::TABLE_BLOG);
    $this->commentsSchema = $this->matrix->getSchema(self::TABLE_COMMENTS);
    
    // configuration
    $config = $this->matrix->query('SELECT * FROM '.self::TABLE_CONFIG, 'SINGLE');
    $config['entryconfig'] = $this->matrix->explodeTrim("\n", $config['entryconfig']);
    $config['imageconfig'] = $this->matrix->explodeTrim("\n", $config['imageconfig']);
    $config['commentsconfigint'] = $this->matrix->explodeTrim("\n", $config['commentsconfigint']);
    $config['commentsconfigcheck'] = $this->matrix->explodeTrim("\n", $config['commentsconfigcheck']);
    $config['commentsbanlist'] = $this->matrix->explodeTrim("\n", $config['commentsbanlist']);
    $config['commentscensor'] = $this->matrix->explodeTrim("\n", $config['commentscensor']);
    $this->config   = array(
      'siteurl'         => $this->glob['siteurl'],
      'url'             => $config['baseurl'],
      'entryurl'        => $config['baseurl'].'$yyyy/$mm/$dd/$slug/',
      'authorurl'       => $config['baseurl'].$config['authorurl'],
      'authorurlrel'    => $config['authorurl'],
      'imgurl'          => $this->glob['siteurl'].'data/uploads/'.$this->schema['fields']['image']['path'],
      'thumburl'        => $this->glob['siteurl'].'data/thumbs/'.$this->schema['fields']['image']['path'],
      'title'           => $config['title'],
      'tags'            => explode_trim(',', $config['tags']),
      'slug'            => $config['slug'],
      'template'        => $config['template'],
      'excerpt'         => $config['entryconfig']['excerpt'],
      'entriesperpage'  => $config['entryconfig']['entries'],
      'paragperentry'   => $config['entryconfig']['paragraphs'],
      'commentsperpage' => $config['commentsconfigint']['comments'],
      'commentsmsg'     => $config['commentsmsg'],
      'commentsminchar' => $config['commentsconfigint']['minchars'],
      'commentsmaxchar' => $config['commentsconfigint']['maxchars'],
      'banlist'         => $config['commentsbanlist'],
      'censor'          => $config['commentscensor'],
    );
    
    // global matrix-blog
    $this->blog    = $this->matrix->query('SELECT * FROM '.self::TABLE_BLOG.' ORDER BY credate DESC', 'MULTI', $cache=false);
    
    // fixes id of key (for search results, so the loop doesn't need to be done there).
    foreach ($this->blog as $key=>$entry) {
      $tmpentry = $entry;
      unset($this->blog[$key]);
      $this->blog[$entry['id']] = $tmpentry;
    }
    
    // categories
    $this->categories = $this->getCategoryTags();
    
    // authors
    $this->authors = $this->matrix->getUsers();
    
    // links to templates
    $this->template = GSDATAOTHERPATH.'/matrix_blog_template.xml';
    $this->templates = array();
    $this->templates['header']   = GSPLUGINPATH.self::FILE.'/php/display/header.php';
    $this->templates['entry']    = GSPLUGINPATH.self::FILE.'/php/display/entry.php';
    $this->templates['excerpt']  = GSPLUGINPATH.self::FILE.'/php/display/excerpt.php';
    $this->templates['sidebar']  = GSPLUGINPATH.self::FILE.'/php/display/sidebar.php';
    $this->templates['authors']  = GSPLUGINPATH.self::FILE.'/php/display/authors.php';
    $this->templates['author']   = GSPLUGINPATH.self::FILE.'/php/display/author.php';
    $this->templates['comments'] = GSPLUGINPATH.self::FILE.'/php/display/comments.php';
    
    // create template file
    if (!file_exists($this->template)) {
      $tmp = array();
      foreach ($this->templates as $key => $template) {
        $tmp[$key]['@cdata'] = file_get_contents($template);
      }
      $xml = Array2XML::createXML('channel', $tmp);
      $xml->save($this->template);
      #echo $xml->saveXML();
    }
    
    // load uri
    $this->uri = $this->parseURI();
    
    if ($this->isFrontEnd() && $this->initBlog()) {
      $this->type = $this->getPageType($this->slug);
    }
    
    $this->init = true;
    return true;
  }
  
  public function info($key) {
    // fill in information
    if (empty($this->info)) {
      $this->info['id']      = self::FILE;
      $this->info['title']   = i18n_r(self::FILE.'/TITLE');
      $this->info['version'] = self::VERSION;
      $this->info['author']  = self::AUTHOR;
      $this->info['url']     = self::URL;
      $this->info['desc']    = i18n_r(self::FILE.'/DESC');
      $this->info['page']    = self::PAGE;
      $this->info['sidebar'] = i18n_r(self::FILE.'/SIDEBAR_TEXT');
    }
    
    // return desired information
    if (isset($this->info[$key])) {
      return $this->info[$key];
    }
  }
  
  // check dependencies
  private function checkDependencies($end='front') {
  
    // matrix
    $return[] = (class_exists('TheMatrix') || (class_exists('TheMatrix') && TheMatrix::VERSION < '1.03')) ? true : false;
  
    // i18n
    $return[] = (function_exists('i18n_init')) ? true : false;
    
    // i18n search
    $return[] = (function_exists('get_i18n_search_results')) ? true : false;
    
    // front-end only
    if ($end == 'front') {
      // pagify
      $return[] = (function_exists('pagify')) ? true : false;
    }
    
    if (!in_array(false, $return)) return true;
    else return false;
  }
  
  // missing dependencies
  private function missingDependencies() {
    $dependencies = array();
    
    if (!class_exists('TheMatrix') || (class_exists('TheMatrix') && TheMatrix::VERSION < '1.03')) {
      $dependencies[] = array('name' => 'The Matrix (1.03+)', 'url' => 'https://github.com/n00dles/DM_matrix/');
    }
    if (!function_exists('i18n_init')) {
      $dependencies[] = array('name' => 'I18N (3.2.3+)', 'url' => 'http://get-simple.info/extend/plugin/i18n/69/');
    }
    if (!function_exists('get_i18n_search_results')) {
      $dependencies[] = array('name' => 'I18N Search (2.11+)', 'url' => 'http://get-simple.info/extend/plugin/i18n-search/82/');
    }
    if (!function_exists('pagify')) {
      $dependencies[] = array('name' => 'Pagify (1.1+)', 'url' => 'http://get-simple.info/extend/plugin/pagify/83/');
    }
    
    return $dependencies;
  }
    
  // checks to see if this is the front/back end
  public function isFrontEnd() {
    global $base;
    if ($base) return true;
    else return false;
  }
    
  // parse the URI and return the page type
  public function parseURI() {
    // load essential globals for changing the 404 error messages
    global $id, $uri, $data_index;
    
    // parse uri
    $uri = trim(str_replace('index.php', '', $_SERVER['REQUEST_URI']), '/#');
    $uri = str_replace('?id=', '', $uri);
    $uri = preg_split('#(&|\?|\/&|\/\?)#', $uri);
    $uri = reset($uri);
    $uri = explode('/', $uri);
    $slug = end($uri);
    
    $this->slug = $slug;
    $this->uri = $uri;
    
    return $uri;
  }
    
  // returns whether we are in the blog
  public function initBlog() {
    if (in_array($this->config['slug'], $this->uri)) return true;
    else return false;
  }
    
  // breadcrumb trail
  public function getBreadcrumbs($showHome=false, $delim='&nbsp;&nbsp;&bull;&nbsp;&nbsp;') {
    $breadcrumbs = $this->uri;
    $categories = $this->categories();
    $trail = array();
    $indic = array();
    $url = $title = $year = $month = $day = $archive = $authors = $author = $entry = false;
    $first = array_slice($breadcrumbs, 0, 1);
    $last  = array_slice($breadcrumbs, -1, 1);
    foreach ($breadcrumbs as $key => $breadcrumb) {
      // default name
      $title = $breadcrumb;
      
      // home
      if ($key == 0) {
        if ($showHome) {
          $url = $this->glob['siteurl'];
          $title = returnPageField('index', 'menu');
        }
        else continue;
      }
      
      // blog
      elseif ($key == 1 && $breadcrumb == $this->config['slug']) {
        $url = $this->config['url'];
        $title = $this->config['title'];
      }
      
      else {
        // continue the path
        $url = $url.$breadcrumb.'/';
        
        // year/month
        if (is_numeric($breadcrumb)) {
          if (strlen($breadcrumb) == 4) {
            $indic['year'] = true;
          }
          elseif (strlen($breadcrumb) == 2) {
            if ($month == true) {
              $indic['day'] = true;
            }
            $indic['month'] = true;
          }
        }
        
        // entry
        elseif (isset($indic['year']) && isset($indic['month']) && isset($indic['year']) && $this->entry) {
          $title = $this->entry['title'];
        }
        
        // authors
        elseif (strpos($_SERVER['REQUEST_URI'], $this->config['authorurlrel']) !== false) {
          if ($breadcrumb == 'authors') {
            $title = i18n_r(self::FILE.'/AUTHORS');
          }
          elseif ($this->author) {
            $title = $this->getAuthorName($this->author);
          }
        }
        
        // categories
        elseif(array_key_exists('_matrixblog_category_'.$breadcrumb, $categories)) {
          $title = $categories['_matrixblog_category_'.$breadcrumb]['title'];
        }
      }
      $trail[] = '<a href="'.$url.'" class="breadcrumb">'.$title.'</a>';
    }
    if (in_array($this->config['slug'], $breadcrumbs)) return implode($delim, $trail);
    else return null;
  }
    
  /* == PUBLIC METHODS == */
  
  // get configuration
  public function getConfig() {
    return $this->config;
  }
  
  // include template
  public function includeTemplate($template, $vars=array()) {
    $file = XML2Array::createArray(file_get_contents($this->template));
    if (isset($file['channel'][$template]['@cdata'])) {
      foreach ($vars as $key => $var) {
        ${$key} = $var;
      }
      eval("?>".$file['channel'][$template]['@cdata']);
    }
  }
  
  // output sidebar
  public function showSidebar() {
    include_once($this->templates['sidebar']);
  }
  
  // explodes the category field
  public function getCategory($category) {
    $cat = explode('/', $category);
    return end($cat);
  }
  
  // get image url
  public function getImageURL($img) {
    return $this->config['imgurl'].$img;
  }
  
  // get thumb url
  public function getThumbURL($img) {
    return $this->config['thumburl'].$img;
  }
  
  // get author's field details
  public function getAuthorField($name, $field) {
    $return = null;
    $authors = $this->matrix->getUsers();
    if (is_string($name) && is_string($field) && array_key_exists($name, $authors) && array_key_exists($field, $authors[$name])) {
      $return = $authors[$name][$field];
    }
    return $return;
  }
  
  // get authors name/username  
  public function getAuthorName($authorarray) {
    // format the array/string
    if (is_string($authorarray)) {
      $authors = $this->matrix->getUsers();
      $authorarray = $authors[$authorarray];
    }
    
    // output correct name
    if (!empty($authorarray['NAME'])) {
      return $authorarray['NAME'];
    }
    elseif (!empty($authorarray['USERSNAME'])) {
      return $authorarray['USERSNAME'];
    }
    else return $authorarray['USR'];
  }
  
  // get author's bio
  public function getAuthorBio($authorarray) {
    if (!empty($authorarray['USERSBIO'])) {
      return $authorarray['USERSBIO'];
    }
    else return null;
  }
    
  // get url for author's page
  public function getAuthorURL($author) {
    return $this->config['authorurl'].$author.'/';
  }

  // show latest entries of an author
  public function showLatestEntries($author, $max) {
    $this->getSearchResults(
      $tags = $author,
      $words = '',
      $first=0,
      $max,
      $order=null,
      $lang=null,
      $showPaging=array(true, true)
    );
  }
    
  // build tables
  public function buildTables() {
    $matrix = $this->matrix;
    
    // create your blog entries table
    if (!$matrix->tableExists(self::TABLE_BLOG)) {
      // fields
      $fields = array(
        array(
          'name' => 'title',
          'type' => 'textlong',
          'placeholder' => i18n_r(self::FILE.'/LABEL_TITLE'),
          'required' => 'required',
        ),
        array(
          'name' => 'subtitle',
          'type' => 'text',
          'label' => i18n_r(self::FILE.'/LABEL_SUBTITLE'),
          'placeholder'  => i18n_r(self::FILE.'/LABEL_SUBTITLE'),
          'class' => 'leftopt',
        ),
        array(
          'name' => 'slug',
          'type' => 'slug',
          'label' => i18n_r(self::FILE.'/LABEL_SLUG'),
          'placeholder'  => strtolower(i18n_r(self::FILE.'/LABEL_SLUG')),
          'class' => 'leftopt',
        ),
        array(
          'name' => 'author',
          'type' => 'users',
          'label' => i18n_r(self::FILE.'/LABEL_AUTHOR'),
          'class' => 'leftopt',
        ),
        array(
          'name' => 'tags',
          'type' => 'tags',
          'label' => i18n_r(self::FILE.'/LABEL_TAGS'),
          'class' => 'rightopt',
        ),
        array(
          'name' => 'template',
          'type' => 'template',
          'label' => i18n_r(self::FILE.'/LABEL_TEMPLATE'),
          'class' => 'rightopt',
        ),
        array(
          'name' => 'language',
          'type' => 'dropdowncustom',
          'label' => i18n_r(self::FILE.'/LABEL_LANGUAGE'),
          'default' => $this->languages[0],
          'options' => implode("\n", $this->languages),
          'class' => 'rightopt',
        ),
        array(
          'name' => 'credate',
          'type' => 'datetimelocal', 
          'label' => i18n_r(self::FILE.'/LABEL_CREDATE'), 
          'class' => 'leftopt',
        ),
        array(
          'name' => 'pubdate',
          'type' => 'datetimelocal', 
          'label' => i18n_r(self::FILE.'/LABEL_PUBDATE'), 
          'class' => 'leftopt',
          'readonly' => 'readonly',
        ),
        array(
          'name' => 'image',
          'type' => 'imageuploadadmin', 
          'label' => i18n_r(self::FILE.'/LABEL_IMAGE'), 
          'class' => 'leftopt',
          'path' => 'blog/',
        ),
        array(
          'name' => 'category',
          'type' => 'dropdownhierarchy', 
          'label' => i18n_r(self::FILE.'/LABEL_CATEGORY'), 
          'class' => 'leftopt', 
        ),
        array(
          'name' => 'index',
          'type' => 'dropdowncustom', 
          'label' => i18n_r(self::FILE.'/LABEL_INDEX'),
          'options' => implode("\n", array(i18n_r(self::FILE.'/OPTION_YES'), i18n_r(self::FILE.'/OPTION_NO'))),
          'class' => 'rightopt',
        ),
        array(
          'name' => 'content',
          'type' => 'wysiwyg',
        ),
      );
      $matrix->createTable(self::TABLE_BLOG, $fields, 0);
    }
    
    // create your configuration table
    if (!$matrix->tableExists(self::TABLE_CONFIG)) {
      // fields
      $fields = array(
        array(
          'name' => 'title',
          'type' => 'textlong',
          'class' => '',
        ),
        array(
          'name' => 'baseurl',
          'type' => 'text',
          'default' => $this->glob['siteurl'].self::ID.'/',
          'label' => i18n_r(self::FILE.'/LABEL_BASEURL'),
          'class' => 'leftsec',
        ),
        array(
          'name' => 'slug',
          'type' => 'slug',
          'label' => i18n_r(self::FILE.'/LABEL_SLUG'),
          'class' => 'leftsec',
        ),
        array(
          'name' => 'template',
          'type' => 'template',
          'label' => i18n_r(self::FILE.'/LABEL_TEMPLATE'),
          'class' => 'leftsec',
        ),
        array(
          'name' => 'authorurl',
          'type' => 'text',
          'label' => i18n_r(self::FILE.'/LABEL_AUTHORURL'),
          'class' => 'leftsec',
        ),
        array(
          'name' => 'tags',
          'type' => 'tags',
          'label' => i18n_r(self::FILE.'/LABEL_TAGS'),
          'class' => 'leftsec',
        ),
        array(
          'name' => 'category',
          'type' => 'textarea',
          'default' => implode("\n", array('Category 1', '> SubCategory 1', 'Category 2')),
          'label' => i18n_r(self::FILE.'/LABEL_CATEGORIES'),
          'class' => 'leftsec',
        ),
        array(
          'name' => 'language',
          'type' => 'textarea',
          'default' => implode("\n", $this->languages),
          'label' => i18n_r(self::FILE.'/LABEL_LANGUAGE'),
          'class' => 'leftsec',
        ),
        array(
          'name' => 'imageconfig',
          'type' => 'intmulti',
          'default' => implode("\n", array(8, 900, 900, 100, 100)),
          'labels' => implode("\n", array(i18n_r(self::FILE.'/LABEL_MAXIMAGESIZE'), i18n_r(self::FILE.'/LABEL_MAXIMAGEWIDTH'), i18n_r(self::FILE.'/LABEL_MAXIMAGEHEIGHT'), i18n_r(self::FILE.'/LABEL_THUMBWIDTH'), i18n_r(self::FILE.'/LABEL_THUMBHEIGHT'))),
          'rows' => 5,
          'class' => 'leftsec',
        ),
        array(
          'name' => 'entryconfig',
          'type' => 'intmulti',
          'default' => implode("\n", array(500, 10, 0)),
          'other' => implode("\n", array(i18n_r(self::FILE.'/LABEL_EXCERPTLENGTH'), i18n_r(self::FILE.'/LABEL_ENTRIESPERPAGE'), i18n_r(self::FILE.'/LABEL_PARAGPERENTRY'))),
          'rows' => 3,
          'class' => 'rightsec',
        ),
        array(
          'name' => 'commentsconfigint',
          'type' => 'intmulti',
          'default' => implode("\n", array(10, 10, 250, 30)),
          'other' => implode("\n", array(i18n_r(self::FILE.'/LABEL_COMMENTSPERPAGE'), i18n_r(self::FILE.'/LABEL_MINCOMMENTCHARS'), i18n_r(self::FILE.'/LABEL_MAXCOMMENTCHARS'), i18n_r(self::FILE.'/LABEL_COMMENTSAUTOCLOSE'))),
          'rows' => 3,
          'class' => 'rightsec',
        ),
        array(
          'name' => 'commentsconfigcheck',
          'type' => 'checkbox',
          'options' => implode("\n", array(i18n_r(self::FILE.'/OPTION_REQUIRENAME'), i18n_r(self::FILE.'/OPTION_REQUIREEMAIL'), i18n_r(self::FILE.'/OPTION_REQUIREURL'))),
          'rows' => 3,
          'class' => 'rightsec',
        ),
        array(
          'name' => 'commentscensor',
          'type' => 'textarea',
          'label' => i18n_r(self::FILE.'/LABEL_COMMENTSCENSOR'),
          'class' => 'rightsec',
        ),
        array(
          'name' => 'commentsbanlist',
          'type' => 'textarea',
          'label' => i18n_r(self::FILE.'/LABEL_COMMENTSBANLIST'),
          'class' => 'rightsec',
        ),
        array(
          'name' => 'commentsmsg',
          'type' => 'wysiwyg',
          'label' => i18n_r(self::FILE.'/LABEL_COMMENTSMSG'),
          'class' => '',
        ),
      );
      $matrix->createTable(self::TABLE_CONFIG, $fields, 1);
      
      // create initial config
      $config = array(
        'title'           => 'Your GetSimple Blog',
        'baseurl'         => $this->glob['siteurl'].self::ID.'/',
        'authorurl'       => 'authors/',
        'tags'            => 'blog,entry',
        'category'        => implode("\n", array('Category 1', '> SubCategory 1', 'Category 2')),
        'slug'            => 'blog',
        'template'        => 'template.php',
        'excerpt'         => 100,
        'entriesperpage'  => 5,
        'paragperentry'   => 5,
        'commentsperpage' => 10,
        'commentsmsg'     => '<h3>Submit your comment</h3><p>Why not leave a comment below?<p><hr/>',
      );
      $matrix->createRecord(self::TABLE_CONFIG, $config);
    }
    
    // create your comments table
    if (!$matrix->tableExists(self::TABLE_COMMENTS)) {
      // fields
      $fields = array(
        array(
          'name' => 'entry',
          'type' => 'int',
          'visibility'  => 0,
        ),
        array(
          'name' => 'date',
          'type' => 'datetimelocal',
          'visibility'  => 0,
          'required' => 'required',
        ),
        array(
          'name' => 'userid',
          'type' => 'int',
          'visibility' => 0,
        ),
        array(
          'name' => 'username',
          'type' => 'text',
          'visibility' => 0,
        ),
        array(
          'name' => 'parent',
          'type' => 'int',
          'visibility' => 0,
        ),
        array(
          'name' => 'ip',
          'type' => 'text',
          'visibility' => 0,
        ),
        array(
          'name' => 'name',
          'type' => 'textlong',
          'desc' => i18n_r(self::FILE.'/LABEL_NAME'),
          'required' => 'required',
        ),
        array(
          'name' => 'email',
          'type' => 'email',
          'desc' => 'youremail@domain.com',
          'class' => 'leftopt',
          'required' => 'required',
        ),
        array(
          'name' => 'url',
          'type' => 'url',
          'desc' => 'http://',
          'class' => 'rightopt',
        ),
        array(
          'name' => 'content',
          'type' => 'bbcodeeditor',
          'required' => 'required',
        ),
      );
      $matrix->createTable(self::TABLE_COMMENTS, $fields, 0);
    }
  }
    
  // get category url
  public function getCategoryURL($string) {
    $category = explode('/', $string);
    $category = array_map(array($this->matrix, 'str2slug'), $category);
    return $this->config['url'].implode('/', $category).'/';
  }
  
  // get entry url
  public function getEntryURL($entry) {
    if (is_string($entry)) {
      $entry = $this->matrix->query('SELECT * FROM '.self::TABLE_BLOG.' WHERE slug = "'.$entry.'"', 'SINGLE');
      if (!$entry) return null;
    }
    $timestamp = strtotime($entry['credate']);
    $year  = date('Y', $timestamp);
    $month = date('m', $timestamp);
    $day   = date('d', $timestamp);
    $replace = array();
    $replace['from'] = array('$yyyy', '$mm', '$dd', '$slug');
    $replace['to'] = array($year, $month, $day, $entry['slug']);
    return str_replace($replace['from'], $replace['to'], $this->config['entryurl']);
  }
  
  // array of available languages for an entry
  public function entryLangs($slug) {
    $langs = array();
    $entries = $this->matrix->query('SELECT slug, language FROM '.self::TABLE_BLOG.' WHERE slug = "'.$slug.'"');
    foreach ($entries as $entry) $langs[] = $entry['language'];
    return $langs;
  }
  
  // function to output links to available language of current entry
  public function getEntryLangs($slug) {
    $langs = $this->entryLangs($slug);
    if (count($langs)>1) {
      echo '<div id="langavail">'.i18n_r(self::FILE.'/AVAIL_LANG').': ';
      foreach ($langs as $lang) {
        echo '<a href="'.$this->getEntryURL($slug).'?setlang='.$lang.'" data-lang="'.$lang.'">'.$lang.'</a> ';
      }
      echo '</div>';
    }
  }
  
  // load categories
  public function categories() {
    
    $categories = explode("\n", $this->schema['fields']['category']['options']);
    $names = array();
    foreach ($categories as $key => $category) {
      $categories[$key] = preg_replace('/> */', '', $category);
    }
    $categories = $names = array_map('trim', $categories);
    $categories = array_map(array($this->matrix, 'str2slug'), $categories);
    foreach ($categories as $key => $category) {
      $categories[$key] = '_matrixblog_category_'.$category;
    }
    
    // load dropdown
    ob_start();
    $this->matrix->displayField(self::TABLE_BLOG, 'category');
    $content = ob_get_contents();
    ob_end_clean();
    
    // find category values for url
    $urls = array();
    preg_match_all('/value="(.*)"/', $content, $matches);
    foreach ($matches[1] as $key => $catstack) {
      // slugify the values
      $replace = $catstack;
      $catstack = explode('/', $catstack);
      $catstack = array_map(array($this->matrix, 'str2slug'), $catstack);
      
      // store tags for i18n search
      $tags = $catstack;
      $catstack = implode('/', $catstack);
      $urls[] = $catstack;
      $content = str_replace('value="'.$replace.'"', 'value="'.$catstack.'/"', $content);
    }

    // returned array
    $return = array();
    foreach ($categories as $key => $category) {
      $return[$category] = array(
        'title' => $names[$key],
        'tag' => $category,
        'url' => $this->config['url'].$urls[$key].'/',
      );
    }
    return $return;
  }

  // get category tags
  public function getCategoryTags() {
    $categories = explode("\n", $this->schema['fields']['category']['options']);
    foreach ($categories as $key => $category) {
      $categories[$key] = preg_replace('/> */', '', $category);
    }
    $categories = array_map('trim', $categories);
    $categories = array_map(array($this->matrix, 'str2slug'), $categories);
    foreach ($categories as $key => $category) {
      $categories[$key] = '_matrixblog_category_'.$category;
    }
    return $categories;
  }
    
  // parse categories
  public function showCategories($getMenu=false) {
    // load dropdown
    ob_start();
    $this->matrix->displayField(self::TABLE_BLOG, 'category');
    $content = ob_get_contents();
    ob_end_clean();
    
    // find category values
    preg_match_all('/value="(.*)"/', $content, $matches);
    foreach ($matches[1] as $key => $catstack) {
      // slugify the values
      $replace = $catstack;
      $catstack = explode('/', $catstack);
      $catstack = array_map(array($this->matrix, 'str2slug'), $catstack);
      
      // store tags for i18n search
      $tags = $catstack;
      $catstack = implode('/', $catstack);
      $content = str_replace('value="'.$replace.'"', 'value="'.$catstack.'/"', $content);
    }
    
    if ($getMenu) return $content;
  }
    
  // dropdown for categories
  public function showCategoriesMenu() {
  ?>
    <form id="categorylist">
      <?php echo $this->showCategories(true); ?>
      <input type="submit" class="submit">
    </form>
    <script>
      $(document).ready(function(){
        // category functionality
        $('#categorylist input[type="submit"]').click(function(){
          window.location.href = <?php echo json_encode($this->config['url']); ?> + $('#categorylist select').val();
          return false;
        }); // click
      }); // ready
    </script>
  <?php
  }
    
  // parse link structure into archive array
  public function archive($year=false, $month=false, $day=false) {
    // array we will eventually return
    $archive = array();
    
    // variables for the current y/m/d
    $currentyear = $currentmonth = $currentday = null;
    
    // query
    $blog = $this->matrix->query('SELECT title, slug, credate, pubdate FROM '.self::TABLE_BLOG.' ORDER BY credate DESC', 'MULTI', $cache=false);
    foreach ($blog as $entry) {
      $tmpyear  = date('Y', $entry['credate']);
      $tmpmonth = date('m', $entry['credate']);
      $tmpday   = date('d', $entry['credate']);
      $archive[$tmpyear][$tmpmonth][$tmpday][] = $entry;
    }
    
    // get year/month
    if ($year) {
      if (isset($archive[$year])) {
        $archive = array($year => $archive[$year]);
        
        if ($month) {
          if (isset($archive[$year][$month])) {
            $archive[$year] = array($month => $archive[$year][$month]);
            
            if ($day) {
              if (isset($archive[$year][$month][$day])) {
                $archive[$year][$month] = array($day => $archive[$year][$month][$day]);
              }
            }
          }
          else $archive = array();
        }
      }
      else $archive = array();
    }
    
    // finally return
    return $archive;
  }
    
  // archive
  public function showArchive($yr=false, $mon=false, $d=false) {
    $archive = $this->archive($yr, $mon, $d);
    ?>
    <div class="archiveWrap">
    <table id="archive" class="archive pajinate">
      <thead>
        <tr>
          <th colspan="100%" class="header1"><span class="page_navigation"></span></th>
        </tr>
      </thead>
      <tbody class="content">
        <?php
          foreach ($archive as $year => $month) {
            foreach ($month as $m => $day) {
              $tempmonth = strtotime($year.'-'.$m);
              $tempmonth = date('F', $tempmonth);
        ?>
        <tr>
          <th colspan="100%" class="header2"><?php echo $tempmonth; ?> <?php echo $year; ?></th>
        </tr>
        <?php
              foreach ($day as $d => $entry) {
                foreach ($entry as $e) {
        ?>
        <tr>
          <td class="day"><?php echo date('dS', $e['credate']); ?></td>
          <td class="title"><a href="<?php echo $this->getEntryURL($e); ?>"><?php echo $e['title']; ?></a></td>
        </tr>
        <?php
                }
              }
            }
          }
          if (empty($archive)) { ?>
        <tr>
          <td><?php echo i18n_r(self::FILE.'/ARCHIVE_NORESULTS'); ?></td>
        </tr>
        <?php
          }
        ?>
      </tbody>
    </table>
    </div>
    <script>
      $(document).ready(function(){
        $('.pajinate').pajinate({
          'items_per_page'  : <?php echo json_encode($this->config['commentsperpage']); ?>,
          'nav_label_first' : '|&lt;&lt;', 
          'nav_label_prev'  : '&lt;', 
          'nav_label_next'  : '&gt;', 
          'nav_label_last'  : '&gt;&gt;|', 
        });
      }); // ready
    </script>
    <?php
  }
    
  // archive dropdown list
  public function showArchiveMenu($yr=false, $mon=false) {
    $uri = array_slice($this->uri, -2, 2);
    $current = implode("/", $uri).'/';
    $archive = $this->archive($yr, $mon);
    ?>
    <form id="archivelist">
      <select name="archive">
      <?php
        foreach ($archive as $year => $month) {
          foreach ($month as $m => $day) {
            $tempmonth = strtotime($year.'-'.$m);
            $tempmonth = date('F', $tempmonth);
      ?>
          <option value="<?php echo $year.'/'.$m.'/'; ?>" <?php if (in_array($year, $this->uri) && in_array($m, $this->uri)) { echo 'selected="selected"'; } ?>><?php echo $tempmonth; ?> <?php echo $year; ?></option>
          
      <?php
          } 
        }
       ?>
      </select>
      <input type="submit" class="submit">
    </form>
    <script>
      $(document).ready(function(){
        // archive functionality
        $('#archivelist input[type="submit"]').click(function(){
          window.location.href = <?php echo json_encode($this->config['url']); ?> + $('#archivelist select').val();
          return false;
        }); // click
      }); // ready
    </script>
    <?php
  }
    
  // parse tags
  public function getTags($tags, $separator=', ') {
    $parser = new TheMatrixParser;
    $tags = explode(',', $tags);
    if (count($tags)==1 && trim($tags[0])=='') {
      return false;
    }
    $tags = $this->matrix->formatTags($tags);
    $tags = implode(',', $tags);
    echo $parser->getTags($tags, $separator, $this->config['url'].'?tags=$tag');
  }
  
  /* ====== DISPLAY ====== */
  
  // get page type
  public function getPageType($slug) {
    $slug = $this->matrix->str2slug($slug);
    $entry = $this->matrix->query('SELECT * FROM '.self::TABLE_BLOG.' WHERE slug = "'.$slug.'"');
    // entry
    if ($entry) {
      // get language
      i18n_init(); // initialize i18n
      if (isset($_SESSION['language'])) {
        $lang = $_SESSION['language'];
      }
      elseif (isset($_GET['lang'])) {
        $lang = $_GET['lang'];
      }
      elseif (isset($_GET['setlang'])) {
        $lang = $_GET['setlang'];
      }
      else {
        $lang = $this->languages[0];
      }
      
      // load correct content based on language
      foreach ($entry as $p) {
        if ($p['language'] == $lang) {
          $entry = $p;
          break;
        }
        $entry = $p; // otherwise load the page's language setting
      }
      
      // correct language
      $this->entry = $entry;
      return 'entry';
    }
    // search
    elseif (isset($_GET['search']) || isset($_GET['tags'])) {
      return 'search';
    }
    // category
    elseif (in_array('_matrixblog_category_'.$slug, $this->categories)) {
      return 'category';
    }
    // archive
    elseif(is_numeric($slug) && (strlen($slug) == 4 || strlen($slug) == 2)) {
      return 'archive';
    }
    // authors
    elseif ($slug == 'authors') {
      return 'authors';
    }
    // author
    elseif (array_key_exists($slug, $this->authors)) {
      return 'author';
    }
    // entries
    elseif ($slug == $this->config['slug']) {
      return 'entries';
    }
    else return null;
  }
  
  // display entry
  public function displayEntry() {
    // globals
    global $id, $uri, $data_index;
    
    // meta
    $data_index->title    = $this->entry['title'];
    $data_index->date     = $this->entry['credate'];
    $data_index->metak    = $this->entry['tags'];
    $data_index->meta     = '';
    $data_index->url      = $this->entry['slug'];
    $data_index->parent   = '';
    $data_index->template = $this->config['template'];
    $data_index->private  = '';
    
    // content
    ob_start();
    
    // pagify content
    if (function_exists('return_pagify_content') && $this->config['paragperentry']!=0) {
      $entry = $this->entry;
      
      // get page number
      if (!isset($_GET['page'])) $_GET['page'] = 1;
      $pageNum = $_GET['page'];
      
      // paginate by paragraphs
      $pageSize = $this->config['paragperentry'].'p';
      
      // url links for navigation
      $link = $this->getEntryURL($entry).'?page=%PAGE%';
      
      // return content
      $entry['content'] = return_pagify_content($entry['content'], $pageSize, $pageNum-1, $link);
    }
    
    // load template
    $this->includeTemplate('entry', array('entry' => $this->entry, 'comments' => $this->getComments($this->entry['id'], true)));
    
    $data_index->content .= ob_get_contents();
    ob_end_clean();
  }
  
  // display entries
  public function displayEntries() {
    global $id, $uri, $data_index;
    // initialize i18n
    if (!isset($_SESSION['language'])) i18n_init();
    
    // meta
    $data_index->title    = $this->config['title'];
    $data_index->date     = time();
    $data_index->metak    = '';
    $data_index->meta     = '';
    $data_index->url      = $this->config['slug'];
    $data_index->parent   = '';
    $data_index->template = $this->config['template'];
    $data_index->private  = '';
    
    // content
    ob_start();
    
    // pull entry list
    $entries = $this->matrix->query('SELECT * FROM '.self::TABLE_BLOG.' ORDER BY credate DESC');
    
    // enable I18nSearchExcerpt class for excerpt
    $initI18nSearch = return_i18n_search_results($tags=null, $words=null, $first=0, $max=0, $order=null, $lang=null);
    
    // paginate query
    $entries = $this->matrix->paginateQuery($entries, 'p', $this->config['entriesperpage'], 5, $this->config['url'], '?p=$1');
    
    // output display
    echo '<div class="pagination">';
    if ($entries['pages']>1) echo $entries['links'];
    echo '</div>';
    echo '<div class="entries">';
    $slug = null;
    foreach ($entries['results'] as $entry) {
      if ($slug == $entry['slug']) {
        continue; // prevents multi-language entries from showing up in the results
      }
      
      // arguments for loading template
      $args = array(
        'comments' => $this->getComments($entry['id'], $cache=true),
        'entry' => $entry,
      );
      $args['entry']['content'] = new I18nSearchExcerpt($entry['content'], $this->config['excerpt']);
      
      // load template
      $this->includeTemplate('excerpt', $args);
      
      // fix slug variable
      $slug = $entry['slug'];
    }
    echo '</div>';
    echo '<div class="pagination">';
    if ($entries['pages']>1) echo $entries['links'];
    echo '</div>';
    $data_index->content .= ob_get_contents();
    ob_end_clean();
  }
  
  // display results of word or tag search
  public function displaySearchResults() {
    global $id, $uri, $data_index;
    
    // meta
    $data_index->title    = $this->config['title'].' '.i18n_r(self::FILE.'/SEARCHRESULTS');
    $data_index->date     = time();
    $data_index->metak    = '';
    $data_index->meta     = '';
    $data_index->url      = $this->config['slug'];
    $data_index->parent   = '';
    $data_index->template = $this->config['template'];
    $data_index->private  = '';
    
    // content
    ob_start();

    // words
    if (isset($_GET['search'])) {
      $this->getSearchResults(
        $tags='_matrixblog',
        $words=urlencode(trim($_GET['search'])),
        $first=0,
        $max=9999,
        $order=null,
        $lang=null,
        $showPaging=array(true, true)
      );
    }
    // tags
    elseif (isset($_GET['tags'])) {
      $this->getSearchResults(
        $tags=array('_matrixblog', urlencode($_GET['tags'])),
        $words='',
        $first=0,
        $max=9999,
        $order=null,
        $lang=null,
        $showPaging=array(true, true)
      );
    }
    $data_index->content .= ob_get_contents();
    ob_end_clean();
  }
  
  // display archive
  public function displayArchive() {
    global $id, $uri, $data_index;
    $lastpath = count($this->uri)-1;
    
    // meta content
    $data_index->title    = i18n_r(self::FILE.'/TITLE_ARCHIVE');
    $data_index->date     = time();
    $data_index->metak    = time();
    $data_index->meta     = '';
    $data_index->url      = $id;
    $data_index->parent   = '';
    $data_index->template = $this->config['template'];
    $data_index->private  = '';
    
    // buffer the contents of the template file into the content variable
    ob_start();
    if (in_array($this->config['slug'], $this->uri)) {
      // year
      if (is_numeric($uri[$lastpath]) && strlen($uri[$lastpath])==4) {
        $this->showArchive($uri[$lastpath]);
      }
      // month
      elseif (
        is_numeric($uri[$lastpath-1]) && strlen($uri[$lastpath-1])==4 &&
        is_numeric($uri[$lastpath]) && strlen($uri[$lastpath])==2
      ) {
        $this->showArchive($uri[$lastpath-1], $uri[$lastpath]);
      }
      // day
      elseif (
        is_numeric($uri[$lastpath-2]) && strlen($uri[$lastpath-2])==4 &&
        is_numeric($uri[$lastpath-1]) && strlen($uri[$lastpath-1])==2 &&
        is_numeric($uri[$lastpath]) && strlen($uri[$lastpath])==2
      ) {
        $this->showArchive($uri[$lastpath-2], $uri[$lastpath-1], $uri[$lastpath]);
      }
      // all archive
      else {
        $this->showArchive();
      }
    }
    $data_index->content .= ob_get_contents();
    ob_end_clean();
  }
  
  // display category
  public function displayCategory() {
    global $id, $uri, $data_index;
    
    // get tags to search for
    $search = $title = array();
    $categories = $this->categories();
    foreach ($this->uri as $category) {
      if (in_array('_matrixblog_category_'.$category, $this->categories)) {
        $key = '_matrixblog_category_'.$category;
        $search[] = $key;
      }
    }
    $title = $categories['_matrixblog_category_'.$this->slug]['title'];
    
    // metadata
    $data_index->title    = $title;
    $data_index->date     = time();
    $data_index->metak    = '';
    $data_index->meta     = '';
    $data_index->url      = $this->slug;
    $data_index->parent   = '';
    $data_index->template = $this->config['template'];
    $data_index->private  = '';
    
    // buffer the contents of the template file into the content variable
    ob_start();
    
    // output the search results
    $this->getSearchResults($tags=$search, $words=null, $first=0, 9999, $order=null, $lang=null);
    
    $data_index->content .= ob_get_contents();
    ob_end_clean();
  }
  
  // display list of authors
  public function displayAuthors() {
    global $id, $uri, $data_index;
    
    // metadata
    $data_index->title    = i18n_r(self::FILE.'/AUTHORS');
    $data_index->date     = time();
    $data_index->metak    = '';
    $data_index->meta     = '';
    $data_index->url      = $this->slug;
    $data_index->parent   = '';
    $data_index->template = $this->config['template'];
    $data_index->private  = '';
    
    // content
    ob_start();
    foreach ($this->authors as $author) {
      $this->includeTemplate('authors', array('author' => $author));
    }
    $data_index->content .= ob_get_contents();
    ob_end_clean();
  }
  
  // display individual author
  public function displayAuthor() {
    global $id, $uri, $data_index;
    
    // meta
    $data_index->title    = $this->config['title'];
    $data_index->date     = time();
    $data_index->metak    = time();
    $data_index->meta     = '';
    $data_index->url      = $this->slug;
    $data_index->parent   = '';
    $data_index->template = $this->config['template'];
    $data_index->private  = '';
    
    // individual author's page
    $author = $this->authors[$this->slug];
    
    $data_index->title    = $this->getAuthorName($author);
    ob_start();
    $this->includeTemplate('author', array('author' => $author));
    $data_index->content .= ob_get_contents();
    ob_end_clean();
  }

  // main front-end display
  public function display() {
    // load if the dependencies exist
    if ($this->checkDependencies()) {
      // initialize
      $this->init();
      
      // globals
      global $data_index;
      
      // load header
      ob_start();
      $this->includeTemplate('header');
      $data_index->content = ob_get_contents();
      ob_end_clean();
      
      // page type
      #$type = $this->getPageType($this->slug);
      $type = $this->type;
      
      // change output depending on type
      if ($type == 'entry') {
        $this->displayEntry($this->slug);
      }
      elseif($type == 'entries') {
        $this->displayEntries();
      }
      elseif ($type == 'search') {
        $this->displaySearchResults();
      }
      elseif ($type == 'category') {
        $this->displayCategory();
      }
      elseif ($type == 'archive') {
        $this->displayArchive();
      }
      elseif ($type == 'authors') {
        $this->displayAuthors();
      }
      elseif ($type == 'author') {
        $this->displayAuthor();
      }
    }
  }
  
  /* ====== ADMIN ====== */
  
  // fix compatibility issues upgrading from Matrix 1.02 to 1.03
  private function compatibility() {
    $schema = array();
    $schema[self::TABLE_BLOG]     = $this->matrix->getSchema(self::TABLE_BLOG);
    $schema[self::TABLE_COMMENTS] = $this->matrix->getSchema(self::TABLE_COMMENTS);
    $schema[self::TABLE_CONFIG]   = $this->matrix->getSchema(self::TABLE_CONFIG);
    
    // blog
    foreach ($schema[self::TABLE_BLOG]['fields'] as $key => $field) {
      if (empty($field['placeholder']) && !empty($field['desc'])) {
        $schema[self::TABLE_BLOG]['fields'][$key]['placeholder'] = $field['desc'];
        $schema[self::TABLE_BLOG]['fields'][$key]['desc'] = '';
      }
    }
    // comments
    foreach ($schema[self::TABLE_COMMENTS]['fields'] as $key => $field) {
      if (empty($field['placeholder']) && !empty($field['desc'])) {
        $schema[self::TABLE_COMMENTS]['fields'][$key]['placeholder'] = $field['desc'];
        $schema[self::TABLE_COMMENTS]['fields'][$key]['desc'] = '';
      }
    }
    
    // config
    foreach ($schema[self::TABLE_CONFIG]['fields'] as $key => $field) {
      // placeholders
      if (empty($field['placeholder']) && !empty($field['desc'])) {
        $schema[self::TABLE_CONFIG]['fields'][$key]['placeholder'] = $field['desc'];
        $schema[self::TABLE_CONFIG]['fields'][$key]['desc'] = '';
      }
      // labels
      if (empty($field['labels']) && !empty($field['other'])) {
        $schema[self::TABLE_CONFIG]['fields'][$key]['labels'] = $field['other'];
        $schema[self::TABLE_CONFIG]['fields'][$key]['other'] = '';
      }
      // imageconfig
      if ($field['name'] == 'imageconfig') {
        // fix row names
        if (is_numeric($field['rows'])) {
          $schema[self::TABLE_CONFIG]['fields'][$field['name']]['rows'] = implode("\n", array('maxsize', 'width', 'height', 'thumbx', 'thumby'));
        }
      }
      // entryconfig
      if ($field['name'] == 'entryconfig') {
        // fix row names
        if (is_numeric($field['rows'])) {
          $schema[self::TABLE_CONFIG]['fields'][$field['name']]['rows'] = implode("\n", array('excerpt', 'entries', 'paragraphs'));
        }
      }
      // commentsconfigint
      if ($field['name'] == 'commentsconfigint') {
        // fix row names
        if (is_numeric($field['rows'])) {
          $schema[self::TABLE_CONFIG]['fields'][$field['name']]['rows'] = implode("\n", array('comments', 'minchar', 'maxchar', 'close'));
        }
      }
    }
    
    $return = array();
    foreach ($schema as $name => $prop) {
      $return[] = $this->matrix->modSchema($name, $prop);
    }
    
    if (!in_array(false, $return)) return true;
    else return false;
  }
  
  // admin panel (back-end)
  public function admin() {
    $this->init();
    $matrix = $this->matrix; // just to save you writing $this->matrix all the time
    
    if ($this->checkDependencies('back')) {
      // compatibility
      if (isset($_GET['compatibility'])) {
        $compat = $this->compatibility();
        if ($compat) {
          $matrix->getAdminError(i18n_r(self::FILE.'/COMPAT_SUCCESS'), true);
        }
        // error message
        else {
          $matrix->getAdminError(i18n_r(self::FILE.'/COMPAT_ERROR'), false);
        }
      }
      
      
      // create an entry
      if (isset($_GET['create'])) {
        include_once(GSPLUGINPATH.self::FILE.'/php/admin/create.php');
      }
      // edit an entry
      elseif (isset($_GET['edit'])) {
        include_once(GSPLUGINPATH.self::FILE.'/php/admin/edit.php');
      }
      // manage comments
      elseif (isset($_GET['comments'])) {
        if (is_numeric($_GET['comments'])) include_once(GSPLUGINPATH.self::FILE.'/php/admin/comments.php');
      }
      // edit templates
      elseif (isset($_GET['template'])) {
        // load template
        $template = XML2Array::createArray(file_get_contents($this->template));
        
        // save changes
        if ($_SERVER['REQUEST_METHOD']=='POST') {
          // update the template
          $template['channel'][$_GET['template']]['@cdata'] = $_POST['edit-template'];
          $xml = Array2XML::createXML('channel', $template['channel']);
          $xml->save($this->template);

          // success message
          if ($xml) {
            $matrix->getAdminError(i18n_r(self::FILE.'/TEMPLATE_UPDATESUCCESS'), true);
          }
          // error message
          else {
            $matrix->getAdminError(i18n_r(self::FILE.'/TEMPLATE_UPDATEERROR'), false);
          }
        }
        $template = $template['channel'][$_GET['template']]['@cdata'];

        ?>
        
      <!--header-->
        <h3 class="floated"><?php echo i18n_r(self::FILE.'/'.strtoupper($_GET['template'])); ?></h3>
        <div class="edit-nav">
          <a href="load.php?id=<?php echo self::FILE; ?>"><?php echo i18n_r(self::FILE.'/BACK'); ?></a>
          <a href="load.php?id=<?php echo self::FILE; ?>&template=sidebar" <?php if ($_GET['template']=='sidebar') echo 'class="current"'; ?>><?php echo i18n_r(self::FILE.'/LABEL_SIDEBAR'); ?></a>  
          <a href="load.php?id=<?php echo self::FILE; ?>&template=author" <?php if ($_GET['template']=='author') echo 'class="current"'; ?>><?php echo i18n_r(self::FILE.'/AUTHOR'); ?></a>
          <a href="load.php?id=<?php echo self::FILE; ?>&template=comments" <?php if ($_GET['template']=='comments') echo 'class="current"'; ?>><?php echo i18n_r(self::FILE.'/COMMENTS'); ?></a>
          <a href="load.php?id=<?php echo self::FILE; ?>&template=excerpt" <?php if ($_GET['template']=='excerpt') echo 'class="current"'; ?>><?php echo i18n_r(self::FILE.'/LABEL_EXCERPT'); ?></a>  
          <a href="load.php?id=<?php echo self::FILE; ?>&template=entry" <?php if ($_GET['template']=='entry') echo 'class="current"'; ?>><?php echo i18n_r(self::FILE.'/ENTRY'); ?></a>
          <a href="load.php?id=<?php echo self::FILE; ?>&template=header" <?php if ($_GET['template']=='header') echo 'class="current"'; ?>><?php echo i18n_r(self::FILE.'/LABEL_HEADER'); ?></a> 
          <div class="clear"></div>
        </div>
        
        
        <!--entry template-->
        <form method="post">
          <?php
            $params = array();
            $params['properties'] = ' name="edit-template" class="codeeditor DM_codeeditor text" id="post-edit-template"';
            $params['value'] = $template;
            $params['id'] = 'post-edit-template';
            $matrix->getEditor($params);
          ?>
          <input type="submit" class="submit" value="<?php echo i18n_r('BTN_SAVECHANGES'); ?>"/>
        </form>
        <?php
      }
      // configuration
      elseif (isset($_GET['config'])) {
        include_once(GSPLUGINPATH.self::FILE.'/php/admin/config.php');
      }
      // view all entries
      else {
        include_once(GSPLUGINPATH.self::FILE.'/php/admin/entries.php');
      }
    }
    else {
      $dependencies = $this->missingDependencies();
      include(GSPLUGINPATH.self::FILE.'/php/admin/dependencies.php');
    }
  }
    
  /* ====== SEARCH ====== */
  
  // search form
  public function searchForm() {
  ?>
    <form method="get" action="<?php echo $this->config['url']; ?>">
      <input type="text" name="search">
      <input type="submit" value="<?php echo i18n_r(self::FILE.'/LABEL_SEARCH'); ?>">
    </form>
  <?php
  }
  
  // search results
  public function getSearchResults($tags=null, $words=null, $first=0, $max=9999, $order=null, $lang=null, $showPaging=array(true, true)) {
    $results = return_i18n_search_results($tags, $words, $first, $max, $order, $lang);
        
    $url = $_SERVER['REQUEST_URI'];
    if (isset($_GET['p'])) {
      $url = explode('?p', $url);
      $url = reset($url);
    }
    
    $entries = $this->matrix->paginateI18nResults(self::TABLE_BLOG, self::SEARCHID, $results, 'p', $this->config['entriesperpage'], 5, $url, '?p=$1');
    
    // top pagination
    if ($showPaging[0]) {
      echo '<div class="pagination">';
      if ($entries['pages']>1) echo $entries['links'];
      echo '</div>';
      echo '<div class="entries">';
    }
    
    // display results
    foreach ($entries['results'] as $entry) {
      // load comment count
      $comments = $this->getComments($entry['id'], $cache=true);
      
      // load template
      $this->includeTemplate('excerpt', array('entry' => $entry, 'comments' => $comments));
    }
    
    // bottom pagination
    if ($showPaging[0]) {
      echo '<div class="pagination">';
      if ($entries['pages']>1) echo $entries['links'];
      echo '</div>';
      echo '<div class="entries">';
    }
  }
  // get blog
  private function getBlog() {
    if (!$this->init) $this->init();
    if (empty($this->matrix)) $this->matrix = new TheMatrix;
    return $this->matrix->query('SELECT * FROM '.self::TABLE_BLOG.' ORDER BY credate DESC', 'MULTI', $cache=false, 'id');
  }
    
  // search index
  public function searchIndex() {
    if (empty($this->blog)) $this->blog = $this->getBlog();
    // for each item call i18n_search_index_item($id, $language, $creDate, $pubDate, $tags, $title, $content)
    foreach ($this->blog as $item) {
      // only index the page if we have selected to
      if ($item['index']==i18n_r(self::FILE.'/OPTION_YES')) {
        // id is prefixed with the constant defined earlier (to make it unique)
        $id = self::SEARCHID.$item['id'];
        
        // language for individual page
        $lang = $item['language'];
      
        // format date correctly (the two stages MUST be done - you cannot just use the raw UNIX stamp as-is
        $item['credate'] = is_numeric($item['credate']) ? $item['credate'] : strtotime($item['credate']);
        $item['pubdate'] = is_numeric($item['pubdate']) ? $item['pubdate'] : strtotime($item['pubdate']);
        
        $creDate = date('j F Y', $item['credate']);
        $creDate = strtotime($creDate);
        $pubDate = date('j F Y', $item['pubdate']);
        $pubDate = strtotime($pubDate);
        
        // explode tags list and add default tags to the array
        $tags   = explode(',', $item['tags']);
        $tags   = array_merge($this->config['tags'], $tags);
        
        // virtual tags for credate and pubdate and ensuring our item is in MatrixBlog
        $tags[] = '_cre_'.date('Y',  $item['credate']);
        $tags[] = '_cre_'.date('Ym', $item['credate']);
        $tags[] = '_pub_'.date('Y',  $item['pubdate']);
        $tags[] = '_pub_'.date('Ym', $item['pubdate']);
        $tags[] = '_matrixblog';
        
        // virtual tags for categories
        $categories = explode('/', $item['category']);
        $categories = array_map(array($this->matrix, 'str2slug'), $categories);
        foreach ($categories as $key =>$category) {
          $categories[$key] = '_matrixblog_category_'.$category;
        }
        $tags = array_merge($tags, $categories);
        
        // add author
        $tags[] = $item['author'];
        
        // format tags correctly for i18n search
        $tags = $this->matrix->formatTags($tags);
        
        // content
        $content = array($item['content']); // add the fields that you want to be indexed
        $content = implode(' ', $content);
        
        // finally index the item
        i18n_search_index_item($id, $lang, $creDate, $pubDate, $tags, $item['title'], $content);
      }
    }
  }
  
  // search item
  public function searchItem($id, $language, $creDate, $pubDate, $score) {
    if (empty($this->blog)) $this->blog = $this->getBlog();
    if (substr($id, 0, strlen(self::SEARCHID)) == self::SEARCHID) {
      // load data
      $data = $this->blog[substr($id, strlen(self::SEARCHID))];
      
      // get key for items of this plugin
      $key = self::SEARCHID;
      
      // translate search result keys into the relevant content
      $transkey = array('title'=>'title', 'description'=>'content', 'content'=>'content', 'link'=>'slug');
      return new TheMatrixSearchResultItem($data, $key, $id, $transkey, $language, $creDate, $pubDate, $score);
    }
    // item is not from our plugin - maybe from another plugin
    else return null; 
  }
  
  // search display
  public function searchDisplay($item, $showLanguage, $showDate, $dateFormat, $numWords) {
    if (!$this->init) $this->init();
    if (substr($item->id, 0, strlen(self::SEARCHID)) == self::SEARCHID) {
      // convert i18n search object to array
      $entry = array();
      foreach ($this->schema['fields'] as $field) {
        $entry[$field['name']] = $item->{$field['name']};
      }
      $entry['id'] = substr($item->id, strlen(self::SEARCHID));
      
      // get template
      $comments = $this->getComments($entry['id'], true);
      $this->includeTemplate('excerpt', array('entry'=>$entry, 'comments'=>$comments));
      return true;
    }
    return false;
  }
    
  /* ====== COMMENTS ====== */
  
  // get (Gr)avatar (taken from Gravatar's docummentation
  public function getGravatar($email, $s = 80, $d = 'mm', $r = 'g', $img=false, $atts=array()) {
    $url = 'http://www.gravatar.com/avatar/';
    $url .= md5( strtolower( trim( $email ) ) );
    $url .= "?s=$s&d=$d&r=$r";
    if ( $img ) {
      $url = '<img src="' . $url . '"';
      foreach ( $atts as $key => $val )
        $url .= ' ' . $key . '="' . $val . '"';
      $url .= ' />';
    }
    return $url;
  }
    
  // get IP address of poster
  public function getIP() {
    if (isset($_SERVER['HTTP_CLIENT_IP']))            return trim($_SERVER['HTTP_CLIENT_IP']);
    elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']))   return trim($_SERVER['HTTP_X_FORWARDED_FOR']);
    elseif(isset($_SERVER['HTTP_X_FORWARDED']))       return trim($_SERVER['HTTP_X_FORWARDED']);
    elseif(isset($_SERVER['HTTP_FORWARDED_FOR']))     return trim($_SERVER['HTTP_FORWARDED_FOR']);
    elseif(isset($_SERVER['HTTP_FORWARDED']))         return trim($_SERVER['HTTP_FORWARDED']);
    elseif(isset($_SERVER['REMOTE_ADDR']))            return trim($_SERVER['REMOTE_ADDR']);
    else                                              return 'n/a';
  }

  // censor the content
  public function censorContent($content) {
    foreach ($this->config['censor'] as $censor) {
      $replace = '';
      $tmp = $this->matrix->explodeTrim(',', $censor);
      if (isset($tmp[1])) $replace = $tmp[1];
      else $replace = '****';
      $content = str_replace($tmp[0], $replace, $content);
    }
    return $content;
  }
    
  // load array of comments
  public function getComments($id, $cache=true) {
    $array = array();
    $comments = $this->matrix->query('SELECT * FROM '.self::TABLE_COMMENTS.' WHERE entry = '.$id.' ORDER BY date DESC', 'MULTI', $cache);
    $array['total'] = count($comments);
    $array['comments'] = $comments;
    
    return $array;
  }
    
  // widget for showing the latest comments
  public function showLatestComments($max=5) {
    $comments = $this->matrix->query('SELECT * FROM '.self::TABLE_COMMENTS.' ORDER BY date DESC', 'MULTI', $cache=false);
    $comments = array_slice($comments, 0, $max);
    echo '<div class="latestComments">';
    foreach ($comments as $comment) {
      $entry = $this->matrix->query('SELECT title, slug FROM '.self::TABLE_BLOG.' WHERE id = '.$comment['entry'], 'SINGLE');
      
      // admin's name and email
      if (!empty($comment['username'])) {
        $comment['name'] = $this->getAuthorName($comment['username']);
        $comment['email'] = $this->getAuthorField($comment['username'], 'EMAIL');
      }
      
      // final formatting
      $comment['content'] = $this->parser->bbcode($comment['content']);
      $comment['content'] = $this->censorContent($comment['content']);
      $comment['content'] = $this->matrix->getExcerpt($comment['content'], $length = 100)
    ?>
      
      <div class="comment">
        <img class="gravatar" src="<?php echo $this->getGravatar($comment['email']); ?>" alt="" />
        <a href="mailto:<?php echo $comment['email']; ?>" class="name"><?php echo $comment['name']; ?></a>
        <?php echo $comment['content']; ?>
        <hr>
        <a href="<?php echo $this->getEntryURL($entry['slug']); ?>"><?php echo $entry['title']; ?></a> @
        <?php echo $comment['date']; ?>
      </div>
      
    <?php
    }
    echo '</div>';
  }
    
  // threads the comments array
  public function constructComments($comments) {
    if (is_array($comments)) {
      foreach ($comments as $key => $comment) {
        $comment['rawcontent'] = $comment['content'];
        $comment['content'] = $this->parser->bbcode($comment['content']);
        $comment['content'] = $this->censorContent($comment['content']);
        $comment['number'] = $key+1;
        $comment['isadmin'] = $comment['isauthor'] = '';
        
        // admin's name and email
        if (!empty($comment['username'])) {
          $comment['isadmin'] = 'adminComment';
          if ($comment['username'] == $this->entry['author']) $comment['isauthor'] = 'authorComment';
          $comment['name'] = $this->getAuthorName($comment['username']);
          $comment['email'] = $this->getAuthorField($comment['username'], 'EMAIL');
        }
      
        if ($comment['parent'] == 0) {
          $this->commentParents[$comment['id']][] = $comment;
        }
        else {
          $this->commentChildren[$comment['parent']][] = $comment;
        }
        $comment['isadmin'] = $comment['isauthor'] = '';
      }
      $this->showComments();
    }
  }
    
  // formatted comment
  private function formatComment($comment, $depth) {
    echo '<div class="commentWrap" data-id="'.$comment['id'].'" data-name="'.$comment['name'].'" data-date="'.$comment['date'].'" data-thread="'.$this->commentCounter.'">';
    
    // tab depth
    echo '<div class="commentTab" style="width: '.($depth*15).'px;"></div>';
    
    // load template
    $this->includeTemplate('comments', array('comment'=>$comment));
    
    // increment comment counter
    $this->commentCounter++;
    echo '</div>';
  }
  
  // show comment parents
  private function showParent($comment, $depth = 0) {
    foreach ($comment as $key => $c) {
      $this->formatComment($c, $depth);
      if (isset($this->commentChildren[$c['id']])) {
        $this->showParent($this->commentChildren[$c['id']], $depth + 1);
      }
    }
  }

  // function for outputting the formatted comments
  public function showComments() {
    $this->commentCounter = 0;
    foreach ($this->commentParents as $c) {
      $this->showParent($c);
    }
  }
    
  // display the comments (with the form)
  public function displayComments($id) {
    // check if IP is banned
    $ip = $this->getIP();
    
    // comment posted
    if (!empty($_POST['post-content'])) {
      // get correct date, page and IP
      $_POST['post-date'] = time();
      $_POST['post-entry'] = $id;
      $_POST['post-ip'] = $ip;
      
      // checks if this is an admin
      if (isset($_COOKIE['GS_ADMIN_USERNAME'])) {
        $_POST['post-username'] = $_COOKIE['GS_ADMIN_USERNAME'];
      }
      
      // CUsers integration
      if (isset($_SESSION['CUser']['user_id'])) {
        $_POST['post-userid'] = $_SESSION['CUser']['user_id'];
      }
      
      // add the comment
      $success = $this->matrix->createRecord(self::TABLE_COMMENTS, $_POST);
    }
    $comments = $this->getComments($id, false);
    ?>
    <h3><?php echo i18n_r(self::FILE.'/COMMENTS'); ?> (<?php echo $comments['total']; ?>)</h3>
    <div class="clear"></div>
    <?php
      // success messages
      if ($_SERVER['REQUEST_METHOD']=='POST') {
        if ($success) {
          echo '<div class="success">'.i18n_r(self::FILE.'/COMMENTS_POSTSUCCESS').'</div>';
        }
        else {
          echo '<div class="error">'.i18n_r(self::FILE.'/COMMENTS_POSTERROR').'</div>';
        }
      }
    ?>
    <div class="comments">
      <div class="commentsHeader">
        <a name="comments"></a>
        <div class="page_navigation"></div>
        <div class="sort"><?php echo i18n_r(self::FILE.'/LABEL_ORDERBY'); ?> [ <a href="#" data-sort="date"><?php echo i18n_r(self::FILE.'/LABEL_DATE'); ?></a> | <a href="#" data-sort="name"><?php echo i18n_r(self::FILE.'/LABEL_NAME'); ?></a> | <a href="#" data-sort="thread"><?php echo i18n_r(self::FILE.'/LABEL_THREADED'); ?></a> ]</div>
      </div>
      <div class="content">
    <?php
      
      $this->constructComments($comments['comments']);
      
      if (empty($comments['comments'])) {
        echo '<div class="comment">'.i18n_r(self::FILE.'/COMMENTS_NONE').'</div>';
      }
      
      ?>
      </div>
      <div class="commentsFooter">
        <a name="comments"></a>
        <div class="page_navigation"></div>
        <div class="sort"><?php echo i18n_r(self::FILE.'/LABEL_ORDERBY'); ?> [ <a href="#" data-sort="date"><?php echo i18n_r(self::FILE.'/LABEL_DATE'); ?></a> | <a href="#" data-sort="name"><?php echo i18n_r(self::FILE.'/LABEL_NAME'); ?></a> | <a href="#" data-sort="thread"><?php echo i18n_r(self::FILE.'/LABEL_THREADED'); ?></a> ]</div>
      </div>
      <?php
      
      echo '<a id="postreply" href="#postreply"></a>'.
           '<a name="postreply"></a>'.
           '<form method="post" class="commentsForm">';
      
      if (in_array($ip, $this->config['banlist'])) {
        echo 'banned';
      }
      else {
        echo $this->config['commentsmsg'];
        echo '<input type="hidden" name="post-parent" id="post-parent">';
        $this->matrix->displayForm(self::TABLE_COMMENTS);
      ?>
      <input type="submit" name="submit-comment">
      <?php } ?>
    </form>
    </div>
    <script>
      $(document).ready(function(){
        // pajination settings
        var pajinateSettings = {
          'items_per_page'  : <?php echo json_encode($this->config['commentsperpage']); ?>,
          'nav_label_first' : '|&lt;&lt;', 
          'nav_label_prev'  : '&lt;', 
          'nav_label_next'  : '&gt;', 
          'nav_label_last'  : '&gt;&gt;|', 
        };
        
        $('.comments').pajinate(pajinateSettings); // pajinate
        
        // comment sorting
        $('.sort a').toggle(
          function() {
            var sortBy = $(this).data('sort');
            $('.comments').pajinate({'items_per_page': 9999});
            $('.comments .commentWrap').tsort({attr:'data-' + sortBy, order:'asc'});
            $('.comments').pajinate(pajinateSettings);
            
            // format threading
            if (sortBy == 'thread') {
              $('.commentTab').show();
            }
            else {
              $('.commentTab').hide();
            }
          },
          function () {
            var sortBy = $(this).data('sort');
            $('.comments').pajinate({'items_per_page': 9999});
            $('.comments .commentWrap').tsort({attr:'data-' + sortBy, order:'desc'});
            $('.comments').pajinate(pajinateSettings);
            
            // format threading
            if (sortBy == 'thread') {
              $('.commentTab').show();
            }
            else {
              $('.commentTab').hide();
            }
          }
        ); // toggle
        
        // quote button
        $('.comments .quote').click(function(){
          var id      = $(this).closest('.commentWrap').data('id');
          var name    = $(this).closest('.commentWrap').data('name');
          var content = $(this).closest('.commentWrap').find('.rawcontent').text();
          $('#post-content').append('[quote=' + name + ']' + content + '[/quote]');
          return false;
        });
        
        // reply button
        $('.comments .reply').click(function(){
          var id      = $(this).closest('.commentWrap').data('id');
          var name    = $(this).closest('.commentWrap').data('name');
          var content = $(this).prev('.rawcontent').text();
          $('#post-parent').val(id);
          //return false;
        });
      }); // ready
    </script>
    
    <?php

  }
}
?>
