#blocks-newsfeed-ui-feedlist .feedlist-main {
    width: 40em;
    margin: 2em auto;
}
#blocks-newsfeed-ui-feedlist .xhierarchy {
    width:auto;
}

.xhierarchy {
     margin: 1em 0 0.5em;
     border:1px solid black;
     width:30em;
    
     height:20em;
     overflow:scroll;
     overflow-y:scroll;
     overflow-x:hidden;
     -moz-user-select:none;
     -khtml-user-select:none;
     user-select:none;     
     cursor:default;
}
.xhierarchy div {
    padding:4px;
}
.xhierarchy ul div {
    padding-left:16px;
}
.xhierarchy ul ul div {
    padding-left:28px;
}
.xhierarchy ul ul ul div {
    padding-left:40px;
}
.xhierarchy ul ul ul ul div {
    padding-left:52px;
}
.xhierarchy ul {
    padding:0;
    margin:0;
}
.xhierarchy li {
    display:block;
    margin:0;
    padding:0;
}
.xhierarchy .transparent {
    opacity:0.5;
    filter:alpha(opacity=50);
}
.ie6 .xhierarchy .transparent {
    height:1px;
    background-color:white;
}

#blocks-newsfeed-ui-feedlist form {
    margin:0;
    padding:0;
    display:inline;
}
#blocks-newsfeed-ui-editincludes #namepart h3 {
    display:inline;
}
#blocks-newsfeed-ui-editincludes #namepart a {
    margin-left:1em;
}

#blocks-newsfeed-ui-viewfeed  #content,#blocks-newsfeed-ui-entryhistory #content {
  width:50em;
  margin:2em auto;
  line-height:140%;
}

#blocks-newsfeed-ui-viewfeed .nf_feed,#blocks-newsfeed-ui-entryhistory .nf_info {
  background:#bbb;
  color:white;
  font-size:0.85em;
  padding:0 2px;
  margin-bottom:8px;
}
#blocks-newsfeed-ui-viewfeed .nf_entry {
    clear:both;
    margin-bottom:1em;
}
#blocks-newsfeed-ui-viewfeed .nf_date {
    font-size:0.85em;
    text-align:right;
}
#blocks-newsfeed-ui-viewfeed .nf_subject {
    font-weight:bold;
}
#blocks-newsfeed-ui-viewfeed .nf_topbuttons form {
    display:inline;
}
#blocks-newsfeed-ui-viewfeed .nf_topbuttons {
    margin-left:20px;
    margin-bottom:1.5em;
}
#blocks-newsfeed-ui-viewfeed .nf_toptext {
    margin-left:20px;
    margin-bottom:2em;
}
#blocks-newsfeed-ui-viewfeed .nf_entry .nf_buttons {
    float:left;
    width:20px;
    padding-top:2em;
}
.ie6#blocks-newsfeed-ui-viewfeed .nf_entry .nf_buttons { 
    margin-top:2px;
}
#blocks-newsfeed-ui-viewfeed .nf_entry .nf_buttons form {
    margin:0;
}
.ie6#blocks-newsfeed-ui-viewfeed .nf_entry .nf_buttons form {
    margin:-5px 0 0;
}
#blocks-newsfeed-ui-viewfeed .nf_entrychange {
    float:left;
    font-size:0.85em;
    color:#800;
    width:400px;
}
#blocks-newsfeed-ui-viewfeed .nf_authid {
    font-size:0.85em;
    color:#800;
}
#blocks-newsfeed-ui-viewfeed .nf_entrychange em {
    font-style:normal;
    font-weight:bold;
}
#blocks-newsfeed-ui-viewfeed .nf_admin .nf_buttons {
    float:right;
    width:auto;
    padding-top:0;
}
#blocks-newsfeed-ui-viewfeed .nf_admin .nf_buttons input {
    font-size:0.85em;
}
#blocks-newsfeed-ui-viewfeed .nf_admin .nf_buttons form {
    display:inline;
}
#blocks-newsfeed-ui-viewfeed .nf_entry .nf_buttons form {
    margin-bottom:4px;
}
#blocks-newsfeed-ui-viewfeed .nf_entry .nf_content {
    margin-left:20px;
    padding-bottom:1px;
}
#blocks-newsfeed-ui-viewfeed .nf_visiblepart {
    border:1px solid #bbb;
    padding:4px;
}
#blocks-newsfeed-ui-viewfeed .nf_admin {
    margin-top:4px;
}
#blocks-newsfeed-ui-viewfeed .nf_future .nf_visiblepart,
#blocks-newsfeed-ui-viewfeed .nf_deleted .nf_visiblepart,
#blocks-newsfeed-ui-viewfeed .nf_future .newsfeed_attachments span,
#blocks-newsfeed-ui-viewfeed .nf_faintexample {
    color:#888;
}
#blocks-newsfeed-ui-viewfeed .nf_unapproved .nf_entryblock, 
#blocks-newsfeed-ui-viewfeed .nf_unapprovedexample {
    background:#ffffdd;
}
#blocks-newsfeed-ui-viewfeed .nf_deleted .nf_feed {    
    margin-bottom:4px;
}
#blocks-newsfeed-ui-viewfeed .nf_deleted .nf_admin {    
    margin-top:0;
}
#blocks-newsfeed-ui-viewfeed .nf_deleted .nf_subject {    
    font-weight:normal;
}
#blocks-newsfeed-ui-viewfeed .nf_deleted .nf_subject span {    
    font-weight:bold;
    color:black;
}
#blocks-newsfeed-ui-viewfeed .nf_deleted .nf_visiblepart {
    border:none;
    padding:0;    
}

#blocks-newsfeed-ui-editentry ul.nf_appearsin {
    margin:0;
    margin-top:0.5em;
    padding:0;
}
#blocks-newsfeed-ui-editentry ul.nf_appearsin li {
    display:inline;
    padding:0; 
    margin:0;
    padding-right:12px;
    padding-left:9px;
    background:url(../../blocks/newsfeed/ui/bullet.gif) no-repeat left center;
}


.newsfeed_entry {
    border:1px solid #ccc;
    padding:4px;    
    margin-bottom:1em;
    line-height:140%;
}
.newsfeed_entry .newsfeed_date {
    font-size:0.85em;
    color:#555;
    text-align:right;   
}
.newsfeed_entry h3 {
    margin:0;
    font-size:1em;
}

.block_newsfeed .newsfeed_entry {
    border:none;
    padding:0;    
}
#course-view .block_newsfeed .newsfeed_entry h3 {
    margin:0 0 2px 0;
}
.block_newsfeed .newsfeed_entry .newsfeed_date {
    font-size:0.95em;
}

.newsfeed_attachments {
    margin-top:0.5em;
    margin-bottom:0;
    padding:0;
    margin-left:0;
}
.newsfeed_attachments li {
    margin-bottom:2px;
    display:block;
    padding:0;
    margin-left:0;
}
.newsfeed_attachments a span {
    color:black; 
}
.newsfeed_attachments a:hover {
    text-decoration:none;
}
.newsfeed_attachments a:hover .newsfeed_afilename {
    text-decoration:underline;
}
.newsfeed_attachments .newsfeed_adetails {
    font-size:0.75em;
}

#blocks-newsfeed-ui-editincludes h4 {
    margin:0.5em 0 0.5em;
}
#blocks-newsfeed-ui-editincludes #includes {
    float:left;
    padding-left:0.5em;
    width:20em;
}
#blocks-newsfeed-ui-editincludes #includes input {
    margin-top:0.5em;
}
#blocks-newsfeed-ui-editincludes #includeslist {
    width:100%;
}
#blocks-newsfeed-ui-editincludes #transfer {
    float:left;
    width:8em;
    text-align:center;
    padding:6em 0.5em;
}
#blocks-newsfeed-ui-editincludes #transfer input {
    width:100%;
    margin-top:0.5em;
}
#blocks-newsfeed-ui-editincludes #available {
    float:left;
    width:25em;
}
#blocks-newsfeed-ui-editincludes #available .xhierarchy{
    width:24.3em;
    margin-top:0.5em;
}
#blocks-newsfeed-ui-editincludes #including {
    clear:both;
    padding:0.5em 0.5em 0;
}

.nf_externalerror span {
    font-weight:bold;
    color: #800;
}

#blocks-newsfeed-ui-editfeed .newsfeed_roles {
    margin-left:10em;
}
#blocks-newsfeed-ui-editfeed .newsfeed_roles h3 {
    font-size:1em;
}
#blocks-newsfeed-ui-editfeed .newsfeed_roles h4 {
    font-size:1em;
    margin-top:0.5em;
    margin-bottom:0;
}


#course-view .newsfeed_form {
    width:50em;
    margin-left:auto;
    margin-right:auto;
}
#course-view .newsfeed_formline {
    margin:1em 0;
}
#course-view .newsfeed_label {
    float:left;
    width:12em;
}
#course-view .newsfeed_formfield {
    margin-left:12em;
}

.block_newsfeed .newsfeed_entry {
    margin-bottom:0.5em;
}


.boxconfirm {
    text-align:center;
}
