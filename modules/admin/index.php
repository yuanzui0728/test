<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class Index_Snailfishshop extends AdminController
{
	public function main()
	{
		global $_W;
		global $_GPC;

		$shop_data = array();
		
		$is_show_notice = true;
		
		$is_show_notice001 = load_model_class('front')->get_config_by_name('is_show_notice001');
		
		if( !isset($is_show_notice001) )
		{
			$data = array();
			$data['is_show_notice001'] = 1;
			
			load_model_class('config')->update($data);
		}
		
		include $this->display('index/index');
	}
	
	public function index()
	{
		$this->main();
	}
	
	public function test()
	{
		$out_trade_no = '3708-1570754907';
		
		$appid = load_model_class('front')->get_config_by_name('wepro_appid');
		$mch_id =      load_model_class('front')->get_config_by_name('wepro_partnerid');
		$nonce_str =    nonce_str();
		
		$pay_key = load_model_class('front')->get_config_by_name('wepro_key');
		
		
		$post = array();
		$post['appid'] = $appid;
		$post['mch_id'] = $mch_id;
		$post['nonce_str'] = $nonce_str;
		$post['out_trade_no'] = $out_trade_no;
	
		$sign = sign($post,$pay_key);
		
		$post_xml = '<xml>
					   <appid>'.$appid.'</appid>
					   <mch_id>'.$mch_id.'</mch_id>
					   <nonce_str>'.$nonce_str.'</nonce_str>
					   <out_trade_no>'.$out_trade_no.'</out_trade_no>
					   <sign>'.$sign.'</sign>
					</xml>';
			
		$url = "https://api.mch.weixin.qq.com/pay/orderquery";
		
		$result = http_request($url,$post_xml);
		
		$array = xml($result);
		
		if( $array['RETURN_CODE'] == 'SUCCESS' && $array['RETURN_MSG'] == 'OK' )
		{
			if( $array['TRADE_STATE'] == 'SUCCESS' )
			{
				echo json_encode( array('已支付') );
				die();
			}
		}
		
		var_dump($array);
		die();
	}
	
	public function analys ()
	{
		global $_W;
		global $_GPC;
		
		include $this->display('index/analys');
	}
}

?>
