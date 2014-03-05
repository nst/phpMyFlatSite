<?php

/*
    phpMyFlatSite 0.1
    August 10th, 2007
    Nicolas Seriot
    http://seriot.ch
*/

$GLOBALS['texts_folder'] = 'texts';
$GLOBALS['blog_folder'] = 'blog';
$GLOBALS['sidebar_folder'] = 'sidebar';
$GLOBALS['templates_folder'] = 'templates';
$GLOBALS['markup_ext'] = 'markdown';

require_once('include/markdown.php');
include "include/config.php";
ini_set('url_rewriter.tags', '');
ini_set('session.save_handler', 'files');
session_start();

class TextItem {
    var $id;
    var $title;
    var $text;
    
    function exists($name) {
        return file_exists($GLOBALS['texts_folder']."/".$name.".".$GLOBALS['markup_ext']);
    }

    function TextItem($id) {
        $this->id = remove_file_extension($id);
        $this->read();
    }

    function file_path() {
        return $GLOBALS['texts_folder'].'/'.$this->id.'.'.$GLOBALS['markup_ext'];
    }
    
    function allTexts() {
        $ids = array_reverse(files_in_dir($GLOBALS['texts_folder']));
        $a = array();
        foreach($ids as $i) {
            array_push($a, new TextItem($i));
        }
        return $a;
    }
    
    function read() {
        if(!$this->exists($this->id)) {
            return;
        }
        
        $lines = file($this->file_path());
        $this->title = $lines[0];
        $this->text = implode('', array_slice($lines, 1));
    }
    
    function display() {
        $is_logged_in = $_SESSION['is_logged_in'] == True;
        $edit_state = $is_logged_in && $_GET['edit'] == '1';
        $d = array();
        $d['id'] = $this->id;
        $d['title'] = $this->title;
        $d['text'] = $edit_state ? $this->text : Markdown($this->text);        
        $d['admin'] = '';
        if($is_logged_in) {
            if(is_writable($this->file_path())) {
                $d['admin'] .= '<a href="'.$PHP_SELF.'?edit=1">[edit]</a> ';
            }
            $d['admin'] .= '<a href="logout.php">[logout]</a>';
        }
        $template = $edit_state ? 'textblock_edit.html' : 'textblock.html';
        return evaluate_template_keys($d, $template);
    }
    
    function update($title, $text) {
        $this->title = stripslashes($title);
        $this->text = stripslashes($text);
        $this->save();
    }

    function save() {
        $f = fopen($this->file_path(), "w") or die ("can't open or write file");
	    fwrite($f, "$this->title\n");
	    fwrite($f, "$this->text");
	    fclose($f);
    }    
}

class SidebarItem extends TextItem {
    function display() {
        return Markdown($this->text);
    }

    function exists($name) {
        return file_exists($GLOBALS['sidebar_folder']."/".$name.".".$GLOBALS['markup_ext']);
    }

    function file_path() {
        return $GLOBALS['sidebar_folder'].'/'.$this->id.'.'.$GLOBALS['markup_ext'];
    }
    
    function read() {
        if(!$this->exists($this->id)) {
            return;
        }
        $this->text = file_get_contents($this->file_path());
    }
}

class BlogPost extends TextItem {
    var $date;
    
    function exists($name) {
        return file_exists($GLOBALS['blog_folder']."/".$name.".".$GLOBALS['markup_ext']);
    }

    function file_path() {
        return $GLOBALS['blog_folder'].'/'.$this->id.'.'.$GLOBALS['markup_ext'];
    }
    
    function all_posts($limit = False) {
        $ids = array_reverse(files_in_dir($GLOBALS['blog_folder']));
        $a = array();
        foreach($ids as $i) {
            array_push($a, new BlogPost($i));
        }
        if($limit) {
            return array_slice($a, 0, $limit);
        }
        return $a;
    }
    
    function all_posts_display($limit=False) {
        $a = array();
        $head = '<dl class="entries">';

        if($_SESSION['is_logged_in']) {
            $head .= '<p><a href="blog_new_post.php">[new post]</a></p>';
        }
        foreach(BlogPost::all_posts($limit) as $p) {
            array_push($a, $p->display());
        }
        $foot = '</dl>';      
        return $head.implode('', $a).$foot;
    }
    
    function history_link() {
        return '<a href="'.$this->permalink_url().'">'.$this->title.'</a>';
    }
    
    function histories_links($current_id) {
        $a = array();
        $head = '<h4>History</h4><ul>';
        foreach(BlogPost::all_posts() as $p) {
            $line = $p->id === $current_id ? $p->title : $p->history_link();
            array_push($a, '<li>'.$line);
        }
        $foot = '</ul>';
        return $head.implode('', $a).$foot;
    }
    
    function read() {
        if(!$this->exists($this->id)) {
            return;
        }
        $lines = file($this->file_path());
        $this->title = $lines[0];
        $this->date = $lines[1];
        $this->text = implode('', array_slice($lines, 2));
    }
    
    function display() {
        $is_logged_in = $_SESSION['is_logged_in'] == True;
        $edit_state = $is_logged_in && $_GET['edit'] == '1';
        $is_writable = is_writable($this->file_path());
        $remove_state = $_GET['remove'];
        $d = array();
        $d['title'] = $this->title;
        $d['date'] = $this->date;
        $d['text'] = $edit_state ? $this->text : Markdown($this->text);
        $d['permalink'] = $this->permalink();
        if($is_logged_in) {
            $d['admin'] = '<div class="text_right">';
            if($is_writable) {
                $d['admin'] .= $this->edit_link().' '.$this->remove_link().' ';
            }
            $d['admin'] .= '<a href="logout.php">[logout]</a></div>';
            
            if($remove_state) {
                $d['admin'] .= '<br /><div class="text_right"><a href="'.$PHP_SELF.'?article='.$this->id.'&remove_confirmed=1">[confirm removal]</a>';
                $d['admin'] .= ' <a href="'.current_file().'">[cancel removal]</a></div>';
            }
        }
        $template = $edit_state ? 'blogpost_edit.html' : 'blogpost.html';
        return evaluate_template_keys($d, $template);
    }
    
    function update($title, $date, $text) {
        $this->title = stripslashes($title);
        $this->date = stripslashes($date);
        $this->text = stripslashes($text);
        $this->save();
    }

    function save() {
        $f = fopen($this->file_path(), "w") or die ("can't open or write file");
	    fwrite($f, "$this->title\n");
	    fwrite($f, "$this->date\n");
	    fwrite($f, "$this->text");
	    fclose($f);
    }

    function remove() {
        if($_SESSION['article_to_remove'] == $this->id && is_writable($this->file_path())) {
            unlink($this->file_path());
            unset($_SESSION['article_to_remove']);
        }
    }
    
    function edit_link() {
        return '<a href="'.$PHP_SELF.'?article='.$this->id.'&edit=1">[edit]</a>';
    }
    
    function remove_link() {
        return '<a href="'.current_file().'?article='.$this->id.'&remove=1">[x]</a>';
    }
    
    function permalink_url() {
        return 'blog.php?article='.$this->id;
    }
    
    function permalink() {
        return '<a href="'.$this->permalink_url().'">permanent link</a>';
    }
}

function create_unique_blog_file($file_name, $file_content) {
    $file_name = remove_file_extension($file_name).".".$GLOBALS['markup_ext']; // ensure right extension schema

    while(in_array($file_name, files_in_dir($GLOBALS['blog_folder']))) {
        $file_name = remove_file_extension($file_name).'_'.'.'.$GLOBALS['markup_ext']; // ensure uniqueness
    }

    $file_handle = fopen($GLOBALS['blog_folder'].'/'.$file_name,"w+b") or die ("can't open or write file ".$GLOBALS['blog_folder'].'/'.$file_name.", try to $ chmod 777 ".$GLOBALS['blog_folder']);
    fwrite($file_handle, stripslashes($file_content));
    fclose($file_handle);
    
    return $file_name;
}

function sidebar($id) {
    $s = new SidebarItem($id);
    return $s->display();
}

function current_file() {
    $current_file = explode("?", basename($_SERVER['PHP_SELF']));
    return $current_file[0];
}

function build_text_page($id, $sidebar=False) {
    $ti = new TextItem($id);
    
    if($_POST['title'] && $_POST['text']) {    
        $ti->update($_POST['title'], $_POST['text']);
        header("Location: ".current_file()."?edit=0");
    }
    
    $d = array();
    $d['menu'] = build_menu();
    $d['page_title'] = $ti->title;
    $d['page_sidebar'] = $sidebar ? sidebar($sidebar) : '';
    $d['page_content'] = $ti->display();
    
    return evaluate_template_keys($d, 'template.html');
}

function build_create_blogpost_page($id, $sidebar=False) {
	if(!$_SESSION['is_logged_in']) {
	    header("Location: login.php");
	}

    if($_POST['file'] && $_POST['title'] && $_POST['date'] && $_POST['text']) {
        $file_content = $_POST['title'].'\n'.$_POST['date'].'\n'.$_POST['text'];
        $file = create_unique_blog_file($_POST['file'], $file_content);
        $b = new BlogPost($file);
        $b->update($_POST['title'], $_POST['date'], $_POST['text']);
        $b->save();
 	    header("Location: ".$b->permalink_url());
    }
    
    $d = array();
    $d['today_compact'] = date("Ymd");
    $d['today_full'] = date("F j, Y");
    $content = evaluate_template_keys($d, 'blogpost_create.html');
	
    $d = array();
    $d['menu'] = build_menu();
    $d['page_title'] = 'Create New Post';
    $d['page_sidebar'] = $sidebar ? sidebar($sidebar) : '';
    $d['page_content'] = $content;
	
    return evaluate_template_keys($d, 'template.html');
}

function build_blog_page($id, $sidebar=False, $history=False) {
    $d = array();
    $d['menu'] = build_menu();
        
    $blog_content = array();
    $files_in_blog_dir = files_in_dir('blog');
    
    $is_logged_in = $_SESSION['is_logged_in'] == True;
    $single_article_state = isset($_GET['article']);
    $remove_article_state = $_GET['remove'] == '1';
    $remove_confirmed_article_state = $_GET['remove_confirmed'] == '1';
    
    if($single_article_state) {
        if(!BlogPost::exists($_GET['article'])) {
            header("Location: blog.php");
        }

        $b = new BlogPost($_GET['article']);

        if($is_logged_in) {
            if($remove_article_state) {
                $_SESSION['article_to_remove'] = $_GET['article'];
            } else if ($remove_confirmed_article_state) {
                $b->remove();
                header("Location: blog.php");
            }
        }
        
        if($_POST['title'] && $_POST['date'] && $_POST['text']) {   
            $b->update($_POST['title'], $_POST['date'], $_POST['text']);
            header("Location: ".current_file());
        }
        
        $d['page_title'] = $b->title;
        $d['page_content'] = '<dl class="entries">'.$b->display().'</dl>';
    } else {
        unset($_SESSION['article_to_remove']);
        $d['page_title'] = 'Blog';
        echo $EMAIL;
        $d['page_content'] = BlogPost::all_posts_display($GLOBALS['blog_posts_per_page']);
    }

    $d['page_sidebar'] = $sidebar ? sidebar($sidebar) : '';
    $d['page_sidebar'] .= $sidebar && $history ? BlogPost::histories_links($b->id) : '';

	echo evaluate_template_keys($d, 'template.html');
}

function evaluate_template_keys($d, $template) {
    echo $SITE_DOMAIN;
    if(!isset($d['site_name'])) {
        $d['site_name'] = $GLOBALS['site_name'];
    }
    
	$template_content = file_get_contents($GLOBALS['templates_folder'].'/'.$template);

	preg_match_all("{{ [A-Za-z0-9_]* }}", $template_content, $matches, PREG_PATTERN_ORDER);
	
	foreach ($matches[0] as $token) {
	    $token = substr($token, 2, -2);
		$template_content = str_replace("{{ ".$token." }}", $d[$token], $template_content);
	}
	return $template_content;
}

function files_in_dir($dir) {
    $array = array();
    if ($handle = opendir($dir)) {
        while ($file = readdir($handle)) {
            if ($file[0] != "." && !is_dir($file)) {
                array_push($array, $file);
            }
        }
        closedir($handle);
    }
    sort($array);
    return $array;
}

function remove_file_extension($strName) {  
     $ext = strrchr($strName, '.');

     if($ext !== false) {  
         $strName = substr($strName, 0, -strlen($ext));  
     }  
     return $strName;  
}  

class MenuItem {
 	var $name;
	var $link;
	var $submenu;

	function MenuItem($aName, $aLink, $aSubmenu=array()) {
		$this->name = $aName;
		$this->link = $aLink;
		$this->submenu = $aSubmenu;
	}

	function myclass($myparameter) {
    	$this->myvar = $myparameter;
  	}

  	function get_menu() {
		$current_page = basename($_SERVER['SCRIPT_NAME']);
		
		$s = "";

		if($current_page === $this->link) {
			$s .= '<li class="navselected"><a href="'.$this->link.'">'.$this->name.'</a>';
		} else {
			$s .= '<li><a href="'.$this->link.'">'.$this->name.'</a>';
		}

		$display_submenu = $current_page === $this->link;
		
		for($i=0; $i<count($this->submenu); $i++) {
			if($this->submenu[$i]->link === $current_page) {
				$display_submenu = True;
			}
		}		

		if($display_submenu) {
			for($i=0; $i<count($this->submenu); $i++) {
				$s .= "<ul>".$this->submenu[$i]->get_menu()."</ul>\n";
			}
		}
		return $s.'</li>';
  	}	
}

function build_menu() {
	$menu = array(new MenuItem("Home","index.php"),
	
	              new MenuItem("Blog","blog.php"),
	
				  new MenuItem("Resources","resources.php"),

				  new MenuItem("About","about.php",array(new MenuItem("Friends","about_friends.php"))),

				  new MenuItem("Contact","contact.php"));

	$s = "";

	for($i=0; $i<count($menu); $i++) {
		$s .= $menu[$i]->get_menu();
	}

	return $s;
}

?>