<?php
if (!defined('BASEPATH')) exit('No direct script access allowed'); 
  
function ulogin_autoload() { 
	if (function_exists('curl_init'))	{ 
		mso_hook_add('init', 'ulogin_init'); 
		mso_hook_add('login_form_auth', 'ulogin_login_form_auth');
		mso_hook_add('page-comment-form', 'ulogin_auth_page_comment_form');
		mso_hook_add('admin_init', 'ulogin_admin_init');  
		mso_hook_add('head', 'ulogin_head');
	} 
}
 
function ulogin_activate($args = array()){	
	mso_create_allow('ulogin_edit', t('Админ-доступ к настройкам ulogin'));
	 $CI = & get_instance(); 
	if ( !$CI->db->table_exists('ulogintable')) { 	 
		$charset = $CI->db->char_set ? $CI->db->char_set : 'utf8';
		$collate = $CI->db->dbcollat ? $CI->db->dbcollat : 'utf8_general_ci';
		$charset_collate = ' DEFAULT CHARACTER SET ' . $charset . ' COLLATE ' . $collate;
		$sql = "
		CREATE TABLE " . $CI->db->dbprefix . "ulogintable (
		id int(10) unsigned NOT NULL AUTO_INCREMENT,
		user_id int(10) unsigned NOT NULL,
		PRIMARY KEY (id)
		)"  . $charset_collate;
		$CI->db->query($sql);
	} 
	return $args;
}
 
function ulogin_uninstall($args = array())
{	
	mso_delete_option('plugin_ulogin', 'plugins' );  
	mso_remove_allow('ulogin_edit');  
	mso_delete_option_mask('ulogin_widget', 'plugins' );  
	return $args;
}
 
function ulogin_admin_init($args = array()) {
    if ( mso_check_allow('ulogin_edit') ) 	{
		$this_plugin_url = 'plugin_options/ulogin';  
		mso_admin_menu_add('plugins', $this_plugin_url, t('ulogin Auth'));
		mso_admin_url_hook ($this_plugin_url, 'plugin_ulogin');
	} 
	return $args;
}
 
function ulogin_head($args = array()) {
	if (!is_login() and !is_login_comuser())
		echo '<script src="//ulogin.ru/js/ulogin.js"></script>';
	return $args;
}

function ulogin_login_form_auth($text = '') {
    global $idUcounter;
 	$idUcounter++;
	$text .= 'соцсети';
    $page = mso_current_url();
    $curpage = getinfo('siteurl') . $page;
    $current_url =$curpage;
	$text .= '<div id="ul_'.$idUcounter.'"  data-ulogin="verify=1;display=small;fields=first_name,last_name,photo_big,email,bdate,'.
	'nickname;providers=vkontakte,google,mailru,facebook;hidden=other;redirect_uri='.urlencode($current_url).'" ></div>';
    if($idUcounter==1 && $page=="login" ){
        $text .= '<script>document.getElementById("ul_1").parentNode.setAttribute("style","margin-top:5px;text-align:left;")</script>';
    }
	$text .= '[end]';

	return $text;
}

function ulogin_auth_page_comment_form($args = array()) { 
	$curpage = getinfo('siteurl') . mso_current_url();
	$current_url = getinfo('siteurl') . 'maxsite-ulogin-auth?' . $curpage;
    echo '<div id="uLogComDiv" style="padding:10px 0 5px 0">
	        <label class="ffirst" style="display:block;float:left;margin:6px 5px 0 0"><input type="radio" checked="checked" name="comments_reg"  id="ulogRBut"  value="noreg" style="margin:0px 5px 0 0">'.
	        'Вход через соцсети</label>
        	 <div id="ulogin_c" data-ulogin="verify=1;display=panel;fields=first_name,last_name,photo_big,email,bdate,
              nickname;providers=vkontakte,google,mailru,facebook;hidden=other;redirect_uri='.urlencode($current_url).'"></div>
          </div>
          <script>e=document.getElementById("uLogComDiv"); e1=e.previousSibling; e.parentNode.insertBefore(e,e.parentNode.firstChild); e1.parentNode.insertBefore( e1,e)</script>';
	return $args;
}

function ulogin_init($arg = array()) {
	if(isset($_POST['token']) and !is_login() and !is_login_comuser()){
		$s = file_get_contents('http://ulogin.ru/token.php?token=' . $_POST['token'] . '&host='.$_SERVER['HTTP_HOST'] );
		$user = json_decode($s, true);

        require_once "ulogin.class.php";

        $ulogin = new Ulogin($user);
        $ulogin->initAutorith();

        unset($_POST['token']);
	}
	return $arg;
}
