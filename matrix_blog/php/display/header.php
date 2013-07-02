<style>
  /* general */
    .clear { clear: both; }
    .search-results { list-style-type: none; margin: 0; padding: 0; }
  /* pagination */
    .pagination { margin: 0 0 10px 0; }
    .page_navigation a {
      padding: 0 5px 0 0;
    }
    
  /* entries on main blog list */
    .entry, .comment, .commentsForm, #entrysidebar {
      position: relative;
      background:#fff;
      border-bottom:1px solid #c8c8c8;
      border-left:1px solid #e4e4e4;
      border-right:1px solid #c8c8c8;
      border-top:1px solid #e4e4e4;
      -moz-box-shadow: 2px 1px 10px rgba(0,0,0, .07);
      -webkit-box-shadow: 2px 1px 10px rgba(0,0,0, .07);
      box-shadow: 2px 1px 10px rgba(0,0,0, .07);
      margin: 0 0 10px 0;
      padding: 10px;
      overflow: hidden;
      border-radius: 5px;
    }
    .entry:nth-child(even), .commentWrap:nth-child(even) .comment, #archive .content tr:nth-child(even) td {
      background: #F1F1F1;
    }
    .entry .thumb {
      float: left;
      margin: 0 10px 10px 0;
    }
    .entry .date, #entry .date {
      background: #6B94B4;
      background: -moz-linear-gradient(top, #6B94B4 0%, #316594 100%);
      background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#6B94B4), color-stop(100%,#316594)); 
      filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#6B94B4', endColorstr='#316594',GradientType=0 );
      padding: 5px;
      width: 50px;
      float: right;
      margin: 0 0 10px 10px;
      border-radius: 15px;
    }
      .entry .date span, #entry .date span {
        display: block;
        color: white;
        font-weight: bold;
        text-align: center;
      }
        .entry .date .day, #entry .date .day { font-size: 80% !important; }
        .entry .date .month, #entry .date .month { font-size: 200% !important; }
        .entry .date .year, #entry .date .year { font-size: 90% !important; }
    .entry .commentsTotal {
      position: relative;
      background:#333;
      border-top:1px solid rgba(255,255,255,.4);
      text-shadow: 1px 1px 0px rgba(0,0,0,.5);
      text-transform:uppercase;
      background: -moz-linear-gradient(top, #444 0%, #222 100%);
      background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#444), color-stop(100%,#222));
      filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#444444', endColorstr='#222222',GradientType=0 ); 
      font-family: 'Yanone Kaffeesatz', arial, helvetica, sans-serif;
      font-weight: 100;
      color:#fff;
      font-size:19px;
      line-height:19px;
      margin:0;
      padding: 4px 10px 4px 10px;
      border-radius:4px;
      -moz-border-radius:4px;
      -khtml-border-radius:4px;
      -webkit-border-radius:4px;
    }
    .entry .commentsTotal a {
      color: #fff;
      text-decoration: none;
    }

  /* individual entry pages */
    #entry {
      overflow: hidden;
    }
    #entrysidebar {
      float: right;
      width: 200px;
      margin: 0 0 10px 10px;
    }
    #entrycontent {
      
    }
    #entrycontent .tags {
      overflow: hidden;
      margin: 0 0 5px 0;
    }
    #entrycontent .tags .tag {
      float: left;
      display: block;
      padding: 4px;
      margin: 3px;
      color:#666;
      text-transform:uppercase;
      background: #eee;
      background: -moz-linear-gradient(top, #EDEDED 0%, #D5D5D5 100%);
      background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#EDEDED), color-stop(100%,#D5D5D5));
      filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#EDEDED', endColorstr='#D5D5D5',GradientType=0 ); 
      border-radius: 5px;
    }
    
  /* archive */
    .archiveWrap {
      position: relative;
      background:#fff;
      border-bottom:1px solid #c8c8c8;
      border-left:1px solid #e4e4e4;
      border-right:1px solid #c8c8c8;
      border-top:1px solid #e4e4e4;
      -moz-box-shadow: 2px 1px 10px rgba(0,0,0, .07);
      -webkit-box-shadow: 2px 1px 10px rgba(0,0,0, .07);
      box-shadow: 2px 1px 10px rgba(0,0,0, .07);
      padding: 1px;
      overflow: hidden;
      border-radius: 5px;
    }
    #archive { width: 100%; }
    #archive .header1 {
      background: #6B94B4;
      background: -moz-linear-gradient(top, #6B94B4 0%, #316594 100%);
      background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#6B94B4), color-stop(100%,#316594)); 
      filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#6B94B4', endColorstr='#316594',GradientType=0 );
      padding: 5px;
      color: #fff;
    }
    #archive .header2 {
      padding: 5px;
      background: #eee;
      background: -moz-linear-gradient(top, #EDEDED 0%, #D5D5D5 100%);
      background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#EDEDED), color-stop(100%,#D5D5D5));
      filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#EDEDED', endColorstr='#D5D5D5',GradientType=0 ); 
    }
      #archive .header1 a, #archive .header2 a {
        color: #fff;
        text-decoration: none !important;
      }
    #archive td { padding: 5px; }
    #archive .day {
      width: 20px;
      text-align: right;
      text-decoration: italic;
     
    }

  /* comments */
    .comments {
    }
    .comments .commentWrap {
      overflow: hidden;
    }
    .comments .commentTab {
      float: left;
      height: 10px;
    }
    .comments .comment {
      
    }
    .comments .comment .gravatar {
      float: left;
      margin: 0 10px 10px 0;
      overflow: hidden;
      border: 5px solid #9A9A9A;
      -webkit-border-radius: 100px;
      -moz-border-radius: 100px;
      border-radius: 100px;
      -moz-box-shadow: 2px 2px 2px 2px rgba(0,0,0, .07);
      -webkit-box-shadow: 2px 2px 2px 2px rgba(0,0,0, .07);
      box-shadow: 2px 2px 2px 2px rgba(0,0,0, .07);
    }
    .comments .comment .options {
      overflow: hidden;
      clear: both;
    }
    .comments .comment .options .reply, .comments .comment .options .quote {
      display: block;
      float: left;
      color: #fff;
      font-weight: bold;
      text-decoration: none;
      padding: 3px 10px 3px 25px;
      margin: 0 5px 0 0;
      background: #FD7D55 url(<?php echo mblog_config('siteurl'); ?>/plugins/matrix/img/comment_add.png) 5px 7px no-repeat;
      border-bottom:1px solid #C8380C;
      border-left:1px solid #FFE9D2;
      border-right:1px solid #C8380C;
      border-top:1px solid #FFE9D2;
      -webkit-border-radius: 10px;
      -moz-border-radius: 10px;
      border-radius: 10px;
    }
    .comments .comment .options .reply:hover, .comments .comment .options .quote:hover {
      background-color: #F8AA71;
    }
    .comments .comment .options .quote {
      background: #FD7D55 url(<?php echo mblog_config('siteurl'); ?>/plugins/matrix/img/quotes.png) 5px 7px no-repeat;
    }
    .comments .comment.adminComment {
      border-bottom:1px solid #2D88DD;
      border-left:1px solid #CDE7FF;
      border-right:1px solid #2D88DD;
      border-top:1px solid #CDE7FF;
    }
    .comments .comment.adminComment .gravatar {
      border-color: #2D88DD;
    }
    .comments .comment.authorComment .gravatar {
      border-color: #FD7D55;
    }
    .comments .comment.authorComment {
      border-bottom:1px solid #FF9524;
      border-left:1px solid #FFE9D2;
      border-right:1px solid #FF9524;
      border-top:1px solid #FFE9D2;
    }
    .comments .commentsHeader {
      overflow: hidden;
      margin: 5px 0 10px 0;
    }
    .comments .commentsFooter {
      clear: both;
      overflow: hidden;
      margin: 5px 0 10px 0;
    }
    .comments .page_navigation {
      float: left;
    }
    .comments .sort {
      float: right;
    }
    .commentsForm {
      clear: both;
    }
      .commentsForm p { padding: 0; margin: 0 0 5px 0; }
      .commentsForm label { 
        float: left;
        width: 75px;
        padding: 4px;
        margin: 0 2px 0 0;
        text-align: right;
        font-weight: bold; 
      }
      .commentsForm input:not([type="submit"]) {
        padding: 4px;
        border:1px solid #c8c8c8;
        border-radius: 5px;
        width: 100%;
        -webkit-box-sizing: border-box;
        -moz-box-sizing: border-box;
        box-sizing: border-box; 
      }
      .commentsForm input[type="submit"] {
        background: #6B94B4;
        background: -moz-linear-gradient(top, #6B94B4 0%, #316594 100%);
        background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#6B94B4), color-stop(100%,#316594)); 
        filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#6B94B4', endColorstr='#316594',GradientType=0 );
        padding: 10px;
        width: 100px;
        color: #fff;
        border: none;
        border-radius: 15px;
      }
      .commentsForm input:required:invalid, input:focus:invalid, .commentsForm textarea:required:invalid, textarea:focus:invalid {
        box-shadow: none;
      }
      .commentsForm input:required:valid {
        box-shadow: none;
      }
      .commentsForm .markItUp {
        clear: both;
      }
    /* latest comments */
    .latestComments .comment {
    }
    .latestComments .name {
      display: block;
      font-weight: bold;
    }
    .latestComments .gravatar {
      float: left;
      max-width: 30px;
      max-height: 30px;
      width: auto;
      height: 30px;
      margin: 0 5px 5px 0;
    }

  /* success/error */
    .success, .error {
      position: relative;
      border-bottom:1px solid #c8c8c8;
      border-left:1px solid #e4e4e4;
      border-right:1px solid #c8c8c8;
      border-top:1px solid #e4e4e4;
      -moz-box-shadow: 2px 1px 10px rgba(0,0,0, .07);
      -webkit-box-shadow: 2px 1px 10px rgba(0,0,0, .07);
      box-shadow: 2px 1px 10px rgba(0,0,0, .07);
      margin: 0 0 10px 0;
      padding: 10px;
      overflow: hidden;
      border-radius: 5px;
    }
    .success {
      background: #FFFCAA;
      border-bottom:1px solid #F7CD84;
      border-left:1px solid #FFF7D1;
      border-right:1px solid #F7CD84;
      border-top:1px solid #FFF7D1;
    }
    .error {
      background: #EF7370;
      border-bottom:1px solid #C01F1B;
      border-left:1px solid #CF3733;
      border-right:1px solid #C01F1B;
      border-top:1px solid #CF3733;
      color: #fff;
    }
</style>