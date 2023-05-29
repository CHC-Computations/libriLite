<?php

class CMS {
	
	var $JS = array();
	var $redirectTo = null;
	var $GET = array();
	
	var $errors = array();
	
	
	public function __construct($sql = null) {

		$this->start_time=$this->gen_www();	
		$this->settings = json_decode(@file_get_contents('./config/settings.json'));
		if (empty($this->settings)) {
			die("settings.json file not found");
			}
		$conFiles = glob ('./config/*.ini'); 
		if (is_array($conFiles))
			foreach ($conFiles as $fullFileName) {
				$confName = str_replace(['./config/', '.ini'], '', $fullFileName);
				$this->config[$confName] = parse_ini_file($fullFileName, true);
				}
		$this->title = '';
		$this->theme = 'default';
		$this->themePath = 'themes/'.$this->theme;
		
		$this->langCode = 'en';
		$this->userLang = 'en';
		$this->defaultLanguage = 'en';
		
		if (!empty($_SERVER['HTTP_HOST']))
			$this->HOST = 'https://'.$_SERVER['HTTP_HOST'].'/';
		$this->SERVER = new stdclass;
		if (!empty($_SERVER['SERVER_NAME']))
			$this->SERVER->domain = $_SERVER['SERVER_NAME'];
		$this->ignorePath = 'lite/';
		
		if (empty($this->HOST))
			$this->HOST = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].'/';
		if (!empty($_SERVER['REQUEST_URI']))
			$this->REQUEST_URI = $_SERVER['REQUEST_URI'];
			else 
			$this->REQUEST_URI = '';
		
		
		$this->sql = $sql;
		$this->time = time();
		
		$tRU = explode('?',$_SERVER['REQUEST_URI']);
		$REQUEST_URI = current($tRU);
		$this->linkParts = explode('/', str_replace($this->ignorePath, '', $REQUEST_URI)); // or use $_SERVER['SCRIPT_URL'] ? 
		unset($this->linkParts[0]);
		# echo "Path: <pre>".print_r($this->linkParts,1)."</pre>";
		$this->params = $this->linkParts;
		
		if (!empty($this->linkParts[2]))
			$this->router=$this->linkParts[2];
			else 
			$this->router='home';
		
		if (stristr($_SERVER['REQUEST_URI'], '/functions/')){
			/* ############################### ajax mode ########################### */
			
			$this->AjaxMode = true;
			
			if (is_array($_SESSION)) {
				foreach ($_SESSION as $k=>$v)
					$this->$k = $v;
				echo "AjaX Mode SESSION<pre>".print_r($_SESSION ,1)."</pre>";
				} else {
				$this->addError('Your session faild :-(');
				$this->redirectTo = $this->HOST;	
				}
			
			} else {
			/* ############################### normal mode ########################### */
		
			$this->AjaxMode = false;	
			
			$langGlobalDir = './languages/';
			$langFiles = glob ($langGlobalDir.'*', GLOB_ONLYDIR);
			foreach ($langFiles as $langDir) {
				$langCode = str_replace($langGlobalDir, '', $langDir);
				if ((file_exists($langDir.'/'.$langCode.'.ini'))and(file_exists($langDir.'/settings.ini'))) {
					$langSetting = parse_ini_file( $langDir.'/settings.ini' );
					$this->lang['available'][$langCode]=$langSetting['langName'];
					}
				}
			if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
				$clang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
				if (array_key_exists($clang, $this->lang['available'])) {
					$this->langCode = $clang;
					$this->userLang = $clang;
					$this->defaultLanguage = $clang;
					}
				}
			
			if (array_key_exists($this->linkParts[1], $this->lang['available'])) { // not in langList
				$this->userLang = $this->lang['userLang'] = $this->linkParts[1];
				$this->translations = parse_ini_file( $langGlobalDir.$this->userLang.'/'.$this->userLang.'.ini' );
				# echo "trans: <pre>".print_r($this->translations,1)."</pre>";
				
				} else {
				$this->redirectTo = $this->HOST.$this->defaultLanguage.'/';	
				# header( "Location: ".$this->redirectTo );
				}
			
			parse_str(urldecode($_SERVER['QUERY_STRING']), $this->GET);
			#echo urldecode($_SERVER['QUERY_STRING'])."<pre>".print_r($this->GET,1)."</pre>";
			
			$_SESSION['lang'] = $this->lang;	
			$_SESSION['GET'] = $this->GET;	
			$_SESSION['parentParams'] = $this->params;
			$_SESSION['parentRouter'] = $this->router;
			
			
			
			}
				
		$this->POST = $_POST;
		}
	
	
	

	public function mdb($o = []) {
		$dsn = 'mdsql:dbname=vufind;host=loacalhost;port=3306;charset=utf8';
		$connection = mysqli_connect($o['host'], $o['user'], $o['password'], $o['dbname']);
		$this->sql = $connection;
		}
 

	public function getMenu($parent=0) {
		
		
		$res = $this->sql->query($Q="SELECT * FROM cms_posts WHERE parent_id='$parent' AND lang='{$this->userLang}' ORDER BY p_order;");
		if ($res->num_rows>0) {
			while ($row = mysqli_fetch_assoc($res)) {
				$Tres[$row['url']]=$row;
				}
			return $Tres;	
			}
		}

	public function getCurrentPost() {
		if (!empty($this->routeParam[0]))
			$currPage = $this->routeParam[0];
			else 
			$currPage = 'home';
		
		$res = $this->sql->query($Q="SELECT * FROM cms_posts WHERE url='{$currPage}' AND lang='{$this->userLang}' ORDER BY p_order LIMIT 1;");
		if ($res->num_rows>0) {
			return mysqli_fetch_assoc($res);
			} else {
			return ['url'=>''];
			}
		}

	
	
	public function getConfig($iniFile) {
		if (!empty($this->config[$iniFile]))
			return $this->config[$iniFile];
		$fullFileName = './config/'.$iniFile.'.ini';
		if (file_exists($fullFileName)) {
			$this->config[$iniFile] = parse_ini_file($fullFileName, true);
			return $this->config[$iniFile];
			} else 
			return null;
		}	
	
	public function getIniArray($file, $section=null, $param=null) {
		if (!empty($param) && !empty($section) && !empty($this->config[$file][$section][$param]) )
			$arr = $this->config[$file][$section][$param];
			else if (!empty($section) && !empty($this->config[$file][$section]) )
				$arr = $this->config[$file][$section];
				else if (!empty($this->config[$file]))
					$arr = $this->config[$file];
		if (!empty($arr))
			if (is_array($arr))
				return $arr;
				else {
				$t = explode(',',$arr);
				foreach ($t as $k=>$v)
					$t[$k]=trim(chop($v));
				return $t;
				}
		}
	
	public function getConfigParam($file, $section=null, $param=null) {
		if (!empty($this->config[$file]))
			$res = $this->config[$file];
		if (!empty($this->config[$file][$section]))
			$res = $this->config[$file][$section];
		if (!empty($this->config[$file][$section][$param])) 
			$res = $this->config[$file][$section][$param];
		if (empty($res))
			return null;
		
		if (is_string($res) && stristr($res, ',')) {
			$t = explode(',', $res);
			foreach ($t as $k=>$v)
				$t[$k] = trim($v);
			return $t;
			} else 
			return $res;
			
		}	
	
	public function getIniParam($file, $section=null, $param=null) {
		if (!empty($this->config[$file][$section][$param])) 
			$res = $this->config[$file][$section][$param];
			else if (!empty($this->config[$file][$section]))
					$res = $this->config[$file][$section];
					else if (!empty($this->config[$file]))
							$res = $this->config[$file];
							else 
							return null;
		if (is_string($res) && stristr($res, ',')) {
			$t = explode(',', $res);
			foreach ($t as $k=>$v)
				$t[$k] = trim($v);
			return $t;
			} else 
			return $res;
			
		}	
	
	public Function getParam($source, $param) {
		if (!empty($this->$source[$param]))
			return $this->$source[$param];
		if (!empty($this->$source->$param))
			return $this->$source->$param;
		return null;
		}
	
	public function postParam($param) {
		if (!empty($_POST[$param]))
			return $_POST[$param];
			else 
			return null;
		}
	

	public function getUserParam($param) {
		if (!empty($_SESSION['userparams'][$param]))
			return $_SESSION['userparams'][$param];
		}
	
	public function saveUserParam($param, $value) {
		$_SESSION['userparams'][$param]=$value;
		}
	
	
	public function linkParts($change) {
		
		return '';
		}
	
	public function gen_www(){
	    $time = explode(" ", microtime());
	    $usec = (double)$time[0];
	    $sec = (double)$time[1];
		return $sec + $usec;
		}
	
	public function runTime() {
		return $this->gen_www() - $this->start_time;
		}
	
	public function register($var, $res) {
		if (empty($this->$var)) {
			$this->$var = $res;
			return true;
			} else 
			return false;
		}
	
	public function addClass($className, $res) {
		if (method_exists($res,'register'))
			$res->register('cms', $this);
		$this->$className = $res;
		}
	
	public function addJS($script) {
		$this->JS[] = $script;
		}
	
	public function addError($msg) {
		$this->errors[] = $msg;
		}
	
	public function addWarning($msg) {
		$this->warnings[] = $msg;
		}
	
	public function addInfo($msg) {
		$this->infos[] = $msg;
		}
	
	
	public function error($error) {
		return $this->Alert('danger', $error);
		}
		
	public function transEsc($content) {
		$content = trim($content);
		if (!empty($this->translations[$content]))
			return $this->translations[$content];
			else 
			return $content;
		}	

	public function setTitle($title) {
		$this->title = strip_tags($title);
		}

	public function addTitle($title) {
		$this->title .= strip_tags($title);
		}

	public function urlName($str) {
		#$str = str_ireplace(',', '', $str);
		$str = str_ireplace(' ', '', $str);
		$str = str_ireplace('.', '', $str);
		#$str = preg_replace ('/[^\p{L}\p{N}]/u', '_', $str );
		$str = urlencode($str);
		$str = str_ireplace('%2C', ',', $str);
		$str = str_ireplace('+', '_', $str);
		return $str;
		}
	
	function urlName2( $str, $replace = " " ){
        setlocale(LC_ALL, 'pl_PL.UTF8');
		$str = iconv('UTF-8', 'ASCII//TRANSLIT', $str); // TRANSLIT
        $charsArr = array( '^', "'", '"', '`', '~');
        $str = str_replace( $charsArr, '', $str );
        $return = trim(preg_replace('# +#',' ',preg_replace('/[^a-zA-Z0-9\s]/','',strtolower($str))));
        return str_replace(' ', $replace, $return);
        }		
		

	public function strToDate($strDate) {
		if ($strDate == '-9999-00-00T00:00:00Z')
			return $this->transEsc('long long time ago');
		if ($strDate == '+'.date("Y-m-d").'T00:00:00Z')
			return $this->transEsc('at present');
		$retDate = substr($strDate,1,10);
		
		if (substr($strDate, 9, 2) == '00') 
			$retDate = substr($strDate,1,7);
		
		if (substr($strDate, 6, 2) == '00') 
			$retDate = floatval(substr($strDate,1,4));
		
		if (substr($strDate,0,1) == '-')
			$retDate.=' '.$this->transEsc('BC');
		
		return $retDate;
		}	
	
	public function buildUri($uri=null,$GET=[]) {
		return $this->buildUrl($uri,$GET);
		}
		
	public function clearGET() {
		$this->GET = [];
		}	
		
	public function buildUrl($uri=null,$GET=[]) {
		if (substr($uri,0,1)!=='/')
			$uri='/'.$uri;
		$nGET = array_merge($this->GET,$GET);
		if ( (!empty($nGET['page']))or(!empty($nGET['sort'])) ) {
			if (empty($this->facetsCode))
				$this->facetsCode = 'null';
			if (empty($nGET['page']))
				$page = $this->getCurrentPage();
				else {
				$page = $nGET['page'];
				unset($nGET['page']);
				}
			if (empty($nGET['sort']))
				$sort = $this->getCurrentSort();
				else {
				$sort = $nGET['sort'];
				unset($nGET['sort']);
				}
			$uri.='/'.$page.'/'.$sort.'/'.$this->facetsCode.'/';
			}
		
		
		$uri .='?'.http_build_query($nGET);
		return $this->HOST.$this->userLang.$uri;
		}
	
	public function selfUrl($str1='', $str2='') {
		$str = $_SERVER['REQUEST_SCHEME'].'://';
		$str .= $_SERVER['SERVER_NAME'];
		$str .= $_SERVER['REQUEST_URI'];
		
		return str_replace($str1, $str2, $str);
		}
	
	public function basicUri($uri=null) {
		if (substr($uri,0,1)!=='/')
			$uri='/'.$uri;
		
		return $this->HOST.$this->userLang.$uri;
		}

	public function baseURL($uri=null) {
		if (substr($uri,0,1)!=='/')
			$uri='/'.$uri;
		return $this->HOST.$this->userLang.$uri;
		}
	
	public function templatesExists($templeName) {
		$fullFileName = $this->themePath.'/templates/'.$templeName;
		
		if (file_exists($fullFileName)) {
			return true;
			} else 
			return false;
		}	
	
	public function render($templeName, $vars=array()) {
		extract($vars);
		$fullFileName = $this->themePath.'/templates/'.$templeName;
		
		if (file_exists($fullFileName)) {
			ob_start();
			include ($fullFileName);
			$content = ob_get_contents();
			ob_clean();
			
			return $content;
			} else 
			return $this->error($this->transEsc('Temple not found: ').$fullFileName);
		}	
		
	public function head() {
		$this->head = new stdclass;
		$this->head->JS = '';
		$this->head->CSS = '';
		
		$js = glob ($this->themePath.'/js/*.js');
		$css = glob ($this->themePath.'/css/*.css');
		if (count($js)>0) 
			foreach ($js as $row) {
				$this->head->JS.="\n\t\t".'<script src="'.$this->HOST.$row.'?t='.$this->time.'"></script>';
			} 
		if (count($css)>0) 
			foreach ($css as $row) {
				$this->head->CSS.="\n\t\t".'<link rel="stylesheet" href="'.$this->HOST.$row.'?t='.$this->time.'">';
			} 
		
		return null;
		}	
		
		
	public function content($content = null) {
		
		if (file_exists('workInProgress.txt')) {
			$work = parse_ini_file('workInProgress.txt');
			#echo "<pre>".print_r($work,1).'</pre>';
			$pauseScreen = '
					<body>
					
					<div style="display:table-cell; width:100vw; height:100vh; text-align:center; vertical-align:middle;">
					<img src="'.$this->HOST.'themes/default/images/libri_logo.svg"><br/>
					<img src="'.$this->HOST.'themes/default/images/extras/workInProgress.svg">
					<h1>Service work is in progress</h1>
					<p>Estimated completion time: <b>'.$work['finishtime'].'</b></p>
					</div>
					</body
					';
			
			if ($_SERVER['REMOTE_ADDR'] <> $work['ip'])
				return $pauseScreen;
			
			}
		$path='';
		$routerError='./routers/error404.php';
		
		if (is_Array($this->linkParts)) {
			$pathArray=$this->linkParts;
			unset($pathArray[1]);
			foreach ($pathArray as $routeFile) {
				$path .= "/$routeFile";
				$this->routePaths[]=$path.'.php';
				}
			krsort($this->routePaths);	
			}
		
		$lp=0;
		$routerFile='./routers/'.$this->router.'.php';
		#echo "<pre>".print_r($this->routePaths,1)."</pre>";
		foreach ($this->routePaths as $k=>$routerFile) {
			if ($routerFile=='/.php')
				$routerFile = './routers/'.$this->router.'.php';
				else 
				$routerFile='./routers'.$routerFile;
			#echo "look for: $routerFile<Br>";
			$lp++;
			if (file_exists($routerFile)) {
				ob_start();
				
				$c = count($this->linkParts);
				$cc= $c-$lp+2;
				for ($i=$cc; $i<=$c; $i++) {
					$this->routeParam[] = $this->linkParts[$i];
					} 
				
				include($routerFile);
				$return = ob_get_contents();
				ob_clean();
				return $return;
				} else {
				$this->addInfo('router does not exists: '.$routerFile);	
				}
			}
		ob_start();
		include($routerError);
		$return = ob_get_contents();
		ob_clean();
		return $return;
		}	
		
	public function setLastPage($param) {
		$this->lastPage = $param;
		return $this->lastPage;
		}
	
	public function getLastPage() {
		if (!empty($this->lastPage))
			return $this->lastPage;
		if (!empty($this->solr->response->numFound)) {
			$results = $this->solr->response->numFound;
			$rpp = $this->getUserParam('limit');
			
			return floor($results/$rpp);
			} else 
			return 1;
		}
		
			
		
	public function getCurrentPage() {
		if (!empty($this->GET['page'])) {
			$page = floatval($this->GET['page']);
			if ($page<=0) $page = 1;
			return $page;
			} else if (!empty($this->params[5])) {
				$page = floatval($this->params[4]); // default page position
				if ($page<=0) $page = 1;
				return $page;
				} else 
				return 1;
		}


	public function defaultSort() {
		return 1;
		}
	
	public function getCurrentSort() {
		if (!empty($this->GET['sort']))
			return $this->GET['sort'];
			else if (!empty($this->params[5])) 
				return $this->params[5]; // default sort position
				else 
				return $this->defaultSort();
			
		}


	
	public function footer() {
		if (count($this->JS)>0)
			return '<script>
				$(document).ready(function(){
					'.implode("\n",$this->JS).'
					});
				</script>';
		}	
		
	public function Alert($klasa,$tresc) {
		return "
			<div class='alert alert-$klasa alert-dismissible' role='alert'><button type='button' class='close' data-dismiss='alert'><span aria-hidden='true'>&times;</span><span class='sr-only'>Zamknij</span></button>
			$tresc
			</div>
			";
		}
		

	}
	
?>