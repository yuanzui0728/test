<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class CommonController extends WeModuleSite
{
	/**
		重写微擎模板引擎编译 fish
	**/
	public function display($filename = '')
	{
		global $_W;
		global $_GPC;
		
		
		if (empty($filename)) {
			$filename = str_replace('.', '/', $_W['routes']);
		}
		
		$group = $_GPC['do'];
		$controller = empty($_GPC['ctrl']) ? 'index':strtolower($_GPC['ctrl']);
		$action = empty($_GPC['action']) ? 'index':strtolower($_GPC['action']);
		
		if(!empty($filename) && in_array($filename, array('_header','_header_base','_footer')) )	
		{
			$filename = 'public/' . $filename;
		}
		
		if( empty($filename) )
		{
			$filename = $controller.'/' . $action;
		}
		
		$name = 'lionfish_comshop';
		$moduleroot = IA_ROOT . '/addons/lionfish_comshop';

		$compile = IA_ROOT . '/data/tpl/app/' . $name . '/' . 'web/' . $filename . '.tpl.php';
		//$source = $moduleroot . '/template/web/'. $filename . '.html';
		$source = $moduleroot . '/template/default/'. $filename . '.html';
			
				
		if (!is_file($source)) {
			exit("Error: template source '{$filename}' is not exist!");
		}

		if (DEVELOPMENT || !is_file($compile) || (filemtime($compile) < filemtime($source))) {
			shop_template_compile($source, $compile, true);
		}
		/**
			switch ($flag) {
			case TEMPLATE_DISPLAY:
			default:
				extract($GLOBALS, EXTR_SKIP);
				include $compile;
				break;
			case TEMPLATE_FETCH:
				extract($GLOBALS, EXTR_SKIP);
				ob_clean();
				ob_start();
				include $compile;
				$contents = ob_get_contents();
				ob_clean();
				return $contents;
				break;
			case TEMPLATE_INCLUDEPATH:
				return $compile;
				break;
		}
		**/
		return $compile;
	}
	
	public function message($msg, $redirect = '', $type = '')
	{
		global $_W;
		$title = '';
		$buttontext = '';
		$message = $msg;
		$buttondisplay = true;

		if (is_array($msg)) {
			$message = (isset($msg['message']) ? $msg['message'] : '');
			$title = (isset($msg['title']) ? $msg['title'] : '');
			$buttontext = (isset($msg['buttontext']) ? $msg['buttontext'] : '');
			$buttondisplay = (isset($msg['buttondisplay']) ? $msg['buttondisplay'] : true);
		}

		$redirect = 'javascript:history.back(-1);';
		
		include $this->template('_message');
		exit();
	}

}

?>
