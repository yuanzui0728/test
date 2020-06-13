<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class Order_Snailfishshop extends MobileController
{
	public function main()
	{
		global $_W;
		global $_GPC;
		echo json_encode( array('code' =>0) );
		die();
	}
	
	
	/**
		直接取消订单
		1、已付款待发货 状态
		2、是不是自己的订单
		3、判断后台是否开启了状态
		4、记录日志
		5、处理订单，
		6、处理退款，
		7、打印小票
		
		结束
	**/
	public function del_cancle_order()
	{
		global $_W;
		global $_GPC;
		
		$token = $_GPC['token'];
		
		$token_param = array();
		$token_param[':uniacid'] = $_W['uniacid'];
		$token_param[':token'] = $token;
		
		$weprogram_token = pdo_fetch("select member_id from ".tablename('lionfish_comshop_weprogram_token')." where uniacid=:uniacid and token=:token limit 1", $token_param);

		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 2) );
			die();
		}
		
		$member_id = $weprogram_token['member_id'];
		
		$order_id = $_GPC['order_id'];
		$order_info = pdo_fetch("select * from ".tablename('lionfish_comshop_order').
				" where uniacid=:uniacid and order_id=:order_id and member_id=:member_id ", array(':uniacid' => $_W['uniacid'], ':order_id' => $order_id,':member_id' => $member_id) );
		
		if( empty($order_info) )
		{
			echo json_encode( array('code' => 1, 'msg' => '订单不存在') );
			die();
		}
		
		if( $order_info['order_status_id'] == 1)
		{
			$order_can_del_cancle = load_model_class('front')->get_config_by_name('order_can_del_cancle'); 
			
			if( empty($order_can_del_cancle) || $order_can_del_cancle == 0 )
			{
				//4、记录日志
				$order_history = array();
				$order_history['uniacid'] = $_W['uniacid'];
				$order_history['order_id'] = $order_id;
				$order_history['order_status_id'] = 5;
				$order_history['notify'] = 0;
				$order_history['comment'] = '会员前台申请，直接取消已支付待发货订单';
				$order_history['date_added'] = time();
				pdo_insert('lionfish_comshop_order_history', $order_history);
				
				//5、处理订单
				$result = load_model_class('weixin')->del_cancle_order($order_id);
				
				
				//6、发送取消通知订单给平台
				
				$weixin_template_cancle_order = load_model_class('front')->get_config_by_name('weixin_template_cancle_order'); 
				$platform_send_info_member_id = load_model_class('front')->get_config_by_name('platform_send_info_member'); 
				
				if( !empty($weixin_template_cancle_order) && !empty($platform_send_info_member_id) )
				{
					$weixin_template_order =array();
					$weixin_appid = load_model_class('front')->get_config_by_name('weixin_appid' );
					
					
					if( !empty($weixin_appid) && !empty($weixin_template_cancle_order) )
					{
						$head_pathinfo = "lionfish_comshop/pages/index/index";
						
						$weopenid = pdo_fetch("select * from ".tablename('lionfish_comshop_member')." where member_id=:member_id ", 
								array(':member_id' => $platform_send_info_member_id));		
					
						$weixin_template_order = array(
												'appid' => $weixin_appid,
												'template_id' => $weixin_template_cancle_order,
												'pagepath' => $head_pathinfo,
												'data' => array(
																'first' => array('value' => '您好，您收到了一个取消订单，请尽快处理','color' => '#030303'),
																'keyword1' => array('value' => $order_info['order_num_alias'],'color' => '#030303'),
																'keyword2' => array('value' => '取消','color' => '#030303'),  
																'keyword3' => array('value' => sprintf("%01.2f", $order_info['total']),'color' => '#030303'),
																'keyword4' => array('value' => date('Y-m-d H:i:s'),'color' => '#030303'),
																'keyword5' => array('value' => $weopenid['username'],'color' => '#030303'),
																'remark' => array('value' => '此订单已于'.date('Y-m-d H:i:s').'被用户取消，请尽快处理','color' => '#030303'),
																)
										);
						load_model_class('user')->just_send_wxtemplate($weopenid['we_openid'], $_W['uniacid'], $weixin_template_order );					
										
					}				
				}
				
				if( $result['code'] == 0 )
				{
					$is_print_cancleorder = load_model_class('front')->get_config_by_name('is_print_cancleorder');
					if( isset($is_print_cancleorder) && $is_print_cancleorder == 1 )
					{
						load_model_class('printaction')->check_print_order($order_id,$_W['uniacid'],'用户取消订单');
					}
					
					echo json_encode( array('code' => 0 ) );
					die();
				}else{
					echo json_encode( array('code' => 1, 'msg' => $result['msg'] ) );
					die();
				}
				
			}else{
				echo json_encode( array('code' => 1, 'msg' => '未开启此项功能') );
				die();
			}
			
		}else{
			echo json_encode( array('code' => 1, 'msg' => '订单状态不正确，只有已支付，未发货的订单才能取消') );
			die();
		}
		
		
	}
	
	public function order_info()
	{
		global $_W;
		global $_GPC;
		
		$token = $_GPC['token'];
		
		$token_param = array();
		$token_param[':uniacid'] = $_W['uniacid'];
		$token_param[':token'] = $token;
		
		$weprogram_token = pdo_fetch("select member_id from ".tablename('lionfish_comshop_weprogram_token')." where uniacid=:uniacid and token=:token limit 1", $token_param);

		if(  empty($weprogram_token) ||  empty($weprogram_token['member_id']) )
		{
			echo json_encode( array('code' => 2) );
			die();
		}
		
		$member_id = $weprogram_token['member_id'];
		
		
		
		$order_id = $_GPC['id'];
	    
	    
		$order_info = pdo_fetch("select * from ".tablename('lionfish_comshop_order')." where uniacid=:uniacid and order_id=:order_id and member_id=:member_id ", array(':uniacid' => $_W['uniacid'], ':order_id' => $order_id,':member_id' => $member_id) );
		
		
		
		$pick_up_info = array();
		$pick_order_info = array();
		
		if( $order_info['delivery'] == 'pickup' )
		{
			//查询自提点
			$pick_order_info = pdo_fetch("select * from ".tablename('lionfish_comshop_pick_order')." where uniacid=:uniacid and order_id=:order_id ",array(':uniacid' => $_W['uniacid'], ':order_id' => $order_id));
			
			$pick_id = $pick_order_info['pick_id'];
			
			$pick_up_info = pdo_fetch("select * from ".tablename('lionfish_comshop_pick_up')." where uniacid=:uniacid and id=:id ", array(':uniacid' => $_W['uniacid'], ':id' => $pick_id));
			
		}
	    
		$order_status_info = pdo_fetch("select * from ".tablename('lionfish_comshop_order_status')." where order_status_id=:order_status_id ", array(':order_status_id' => $order_info['order_status_id'] ));
		
		
	    //10 name
		if($order_info['order_status_id'] == 10)
		{
			$order_status_info['name'] = '等待退款';
		}
		else if($order_info['order_status_id'] == 4 && $order_info['delivery'] =='pickup')
		{
			//delivery 6
			$order_status_info['name'] = '待自提';
			//已自提
		}
		else if($order_info['order_status_id'] == 6 && $order_info['delivery'] =='pickup')
		{
			//delivery 6
			$order_status_info['name'] = '已自提';
			
		}
		else if($order_info['order_status_id'] == 1 && $order_info['type'] == 'lottery')
		{
			//等待开奖
			//一等奖
			if($order_info['lottery_win'] == 1)
			{
				$order_status_info['name'] = '一等奖';
			}else {
				$order_status_info['name'] = '等待开奖';
			}
			
		}
		
		//$order_info['type']
		//open_auto_delete
		if($order_info['order_status_id'] == 3)
		{
			$open_auto_delete = load_model_class('front')->get_config_by_name('open_auto_delete');
			$auto_cancle_order_time = load_model_class('front')->get_config_by_name('auto_cancle_order_time');
			
			$order_info['open_auto_delete'] = $open_auto_delete;
			//date_added
			if($open_auto_delete == 1)
			{
				$order_info['over_buy_time'] = $order_info['date_added'] + 3600 * $auto_cancle_order_time;
				$order_info['cur_time'] = time();
			}
		}
		
	  
		$shipping_province = pdo_fetch("select * from ".tablename('lionfish_comshop_area')." where id=:id ", array(':id' => $order_info['shipping_province_id']));
	    $shipping_city = pdo_fetch("select * from ".tablename('lionfish_comshop_area')." where id=:id ", array(':id' => $order_info['shipping_city_id']));
	    $shipping_country = pdo_fetch("select * from ".tablename('lionfish_comshop_area')." where id=:id ", array(':id' => $order_info['shipping_country_id']));
	    
		$order_goods_list = pdo_fetchall("select * from ".tablename('lionfish_comshop_order_goods')." where order_id=:order_id ",array(':order_id' => $order_id));
	    
		$shiji_total_money = 0;
		$member_youhui = 0.00;
		
		$pick_up_time = "";
		$pick_up_type = -1;
		$pick_up_weekday = '';
		$today_time = $order_info['pay_time'];
		
		$arr = array('天','一','二','三','四','五','六');
	
	    foreach($order_goods_list as $key => $order_goods)
	    {
			
			$order_refund_goods = pdo_fetch("select * from ".tablename('lionfish_comshop_order_refund')." where uniacid=:uniacid and order_id=:order_id and order_goods_id=:order_goods_id order by ref_id desc  ",
						array(':uniacid' => $_W['uniacid'], ':order_id' => $order_id,':order_goods_id' => $order_goods['order_goods_id'] ));
		
			if(!empty($order_refund_goods))
			{
				$order_refund_goods['addtime'] = date('Y-m-d H:i:s', $order_refund_goods['addtime']);
			}
			
			$order_option_info = pdo_fetchall("select value from ".tablename('lionfish_comshop_order_option')." where order_id=:order_id and order_goods_id=:order_goods_id ",array(':order_id' => $order_id,':order_goods_id' => $order_goods['order_goods_id'] ));
			 
			$order_goods['order_refund_goods'] = $order_refund_goods;
			 
	        foreach($order_option_info as $option)
	        {
	            $order_goods['option_str'][] = $option['value'];
	        }
			if(empty($order_goods['option_str']))
			{
				//option_str
				 $order_goods['option_str'] = '';
			}else{
				 $order_goods['option_str'] = implode(',', $order_goods['option_str']);
			}
	       //
		    $order_goods['shipping_fare'] = round($order_goods['shipping_fare'],2);
		    $order_goods['price'] = round($order_goods['price'],2);
			$order_goods['total'] = round($order_goods['total'],2);
				
			if( $order_goods['is_vipcard_buy'] == 1 || $order_goods['is_level_buy'] ==1 )
			{
				$order_goods['price'] = round($order_goods['oldprice'],2);
			}	
				
			
			$order_goods['real_total'] = round($order_goods['quantity'] * $order_goods['price'],2);
	        
			/**
					$goods_images = file_image_thumb_resize($vv['goods_images'],400);
					if(is_array($goods_images))
					{
						$vv['goods_images'] = $vv['goods_images'];
					}else{
						 $vv['goods_images']= tomedia( file_image_thumb_resize($vv['goods_images'],400) ); 
					}	
					
			**/
			$goods_images = file_image_thumb_resize($order_goods['goods_images'],400);
			
			if( !is_array($goods_images) )
			{
				$order_goods['image']=  tomedia( $goods_images );
				$order_goods['goods_images']= tomedia( $goods_images ); 
			}else{
				$order_goods['image']=  $order_goods['goods_images'];
			}
	       
		   $order_goods['hascomment'] = 0;
		   
		   $order_goods_comment_info = pdo_fetch('select comment_id from '.tablename('lionfish_comshop_order_comment')." where order_id=:order_id and uniacid=:uniacid and goods_id=:goods_id ", 
										array(':uniacid' => $_W['uniacid'], ':order_id' => $order_id,':goods_id' => $order_goods['goods_id'] ));
			if( !empty($order_goods_comment_info) )
			{
				$order_goods['hascomment'] = 1;
			}
			
			$order_goods['can_ti_refund'] = 1;
			
			$disable_info = pdo_fetch('select * from '.tablename('lionfish_comshop_order_refund_disable').
								" where uniacid=:uniacid and order_id=:order_id and order_goods_id=:order_goods_id ", 
								array(':uniacid' => $_W['uniacid'], ':order_id' => $order_id, ':order_goods_id' => $order_goods['order_goods_id'] ));
			
			
			if( !empty($disable_info) )
			{
				$order_goods['can_ti_refund'] = 0;
			}
				
				
			if($order_goods['is_refund_state'] == 1)
			{
				//已经再申请退款中了。或者已经退款了。
				
				$order_refund_info = pdo_fetch("select state from ".tablename('lionfish_comshop_order_refund').
							" where uniacid=:uniacid and order_id=:order_id and order_goods_id=:order_goods_id ", 
							array(':uniacid' => $_W['uniacid'], ':order_id' => $order_id, ':order_goods_id' => $order_goods['order_goods_id']));
				if( $order_refund_info['state'] == 3 )
				{
					$order_goods['is_refund_state'] = 2;
				}
				
				
			}
			
			
			//ims_ 
			$goods_info = pdo_fetch("select productprice as price from ".tablename('lionfish_comshop_goods')." where id=:goods_id", array(':goods_id' => $order_goods['goods_id'] ));
 			
		
			$goods_cm_info = pdo_fetch("select pick_up_type,pick_up_modify,goods_share_image from ".tablename('lionfish_comshop_good_common'). " where uniacid=:uniacid and goods_id=:goods_id ", 
						array(':uniacid' => $_W['uniacid'], ':goods_id' => $order_goods['goods_id']));
			if($pick_up_type == -1 || $goods_cm_info['pick_up_type'] > $pick_up_type)
			{
				$pick_up_type = $goods_cm_info['pick_up_type'];
				
				if($pick_up_type == 0)
				{
					$pick_up_time = date('m-d', $today_time);
					//$pick_up_weekday = '周'.$arr[date('w',$today_time)];
				}else if( $pick_up_type == 1 ){
					$pick_up_time = date('m-d', $today_time+86400);
					//$pick_up_weekday = '周'.$arr[date('w',$today_time+86400)];
				}else if( $pick_up_type == 2 )
				{
					$pick_up_time = date('m-d', $today_time+86400*2);
					//$pick_up_weekday = '周'.$arr[date('w',$today_time+86400*2)];
				}else if($pick_up_type == 3)
				{
					$pick_up_time = $goods_cm_info['pick_up_modify'];
				}
			}

			if( !empty($goods_cm_info['goods_share_image']) )
			{
				$order_goods['goods_share_image']=  tomedia( $goods_cm_info['goods_share_image'] );
			}else{
				$order_goods['goods_share_image'] = $order_goods['image'];
			}
			
			$order_goods['shop_price'] = $goods_info['price'];
			 
			
			$store_info	= array('s_true_name' =>'','s_logo' => '');
			
			$store_info['s_true_name'] = load_model_class('front')->get_config_by_name('shoname');
			
			//$store_info['s_logo'] = load_model_class('front')->get_config_by_name('shoplogo'); 
			
			if( !empty($store_info['s_logo']) )
			{
				$store_info['s_logo'] = tomedia($store_info['s_logo']);
			}else{
				$store_info['s_logo'] = '';
			}
			
			
			$order_goods['store_info'] = $store_info;
			
			unset($order_goods['model']);
			unset($order_goods['rela_goodsoption_valueid']);
			unset($order_goods['comment']);
			
			
	        $order_goods_list[$key] = $order_goods;
			$shiji_total_money += $order_goods['quantity'] * $order_goods['price'];
			
			$member_youhui += ($order_goods['real_total'] - $order_goods['total']);
	    }
	    
		unset($order_info['store_id']);
		unset($order_info['email']);
		unset($order_info['shipping_city_id']);
		unset($order_info['shipping_country_id']);
		unset($order_info['shipping_province_id']);
		// unset($order_info['comment']);
		unset($order_info['voucher_id']);
		unset($order_info['is_balance']);
		unset($order_info['lottery_win']);
		unset($order_info['ip']);
		unset($order_info['ip_region']);
		unset($order_info['user_agent']);
		
		
		//enum('express', 'pickup', 'tuanz_send')
		
		
		//var_dump($order_info['total'],$order_info['shipping_fare'],$order['voucher_credit'],$order['fullreduction_money'] );die();
		
		
		$order_info['shipping_fare'] = round($order_info['shipping_fare'],2) < 0.01 ? '0.00':round($order_info['shipping_fare'],2) ;
		$order_info['voucher_credit'] = round($order_info['voucher_credit'],2) < 0.01 ? '0.00':round($order_info['voucher_credit'],2) ;
		$order_info['fullreduction_money'] = round($order_info['fullreduction_money'],2) < 0.01 ? '0.00':round($order_info['fullreduction_money'],2) ;
		
		$need_data = array();
		
		if($order_info['type'] == 'integral')
		{
			//暂时屏蔽积分商城
			$order_info['score'] = round($order_info['total'],2);
			
		}
		
		$order_info['total'] = round($order_info['total']+$order_info['shipping_fare']-$order_info['voucher_credit']-$order_info['fullreduction_money'] - $order_info['score_for_money'],2)	;
		
		if($order_info['total'] < 0)
		{
			$order_info['total'] = '0.00';
		}
		
		
		
		$order_info['real_total'] = round($shiji_total_money,2)+$order_info['shipping_fare'];		
		$order_info['price'] = round($order_info['price'],2);		
		$order_info['member_youhui'] = round($member_youhui,2) < 0.01 ? '0.00':round($member_youhui,2);	
		$order_info['pick_up_time'] = $pick_up_time;
		
		
		$order_info['shipping_fare'] = sprintf("%.2f",$order_info['shipping_fare']);
		$order_info['voucher_credit'] = sprintf("%.2f",$order_info['voucher_credit']);
		$order_info['fullreduction_money'] = sprintf("%.2f",$order_info['fullreduction_money']);
		$order_info['total'] = sprintf("%.2f",$order_info['total']);
		$order_info['real_total'] = sprintf("%.2f",$order_info['real_total']);
		
		
		
			
		$order_info['date_added'] = date('Y-m-d H:i:s', $order_info['date_added']);
		
	
		// if($order_info['delivery'] =='pickup')
		// {}else{}
	
		if( !empty($order_info['pay_time']) && $order_info['pay_time'] >0 )
		{
			$order_info['pay_date'] = date('Y-m-d H:i:s', $order_info['pay_time']);
		}else{
			$order_info['pay_date'] = '';
		}
		
		$order_info['express_tuanz_date'] = date('Y-m-d H:i:s', $order_info['express_tuanz_time']);
		$order_info['receive_date'] = date('Y-m-d H:i:s', $order_info['receive_time']);
		
		//"delivery": "pickup", enum('express', 'pickup', 'tuanz_send')
		if($order_info['delivery'] == 'express')
		{
			$order_info['delivery_name'] = '快递';
		}else if($order_info['delivery'] == 'pickup')
		{
			$order_info['delivery_name'] = '用户自提';
		}else if($order_info['delivery'] == 'tuanz_send'){
			$order_info['delivery_name'] = '团长配送';
		}
		
		$need_data['order_info'] = $order_info;
		$need_data['order_status_info'] = $order_status_info;
		$need_data['shipping_province'] = $shipping_province;
		$need_data['shipping_city'] = $shipping_city;
		$need_data['shipping_country'] = $shipping_country;
		$need_data['order_goods_list'] = $order_goods_list;
		
		$need_data['goods_count'] = count($order_goods_list);
		
		//$order_info['order_status_id'] 13  平台介入退款
		$order_refund_historylist = array();
		$pingtai_deal = 0;
		
		//判断是否已经平台处理完毕
		$order_refund_historylist = pdo_fetchall("select * from ".tablename('lionfish_comshop_order_refund_history')." where uniacid=:uniacid and order_id=:order_id order by addtime asc ", array(':uniacid' => $_W['uniacid'],':order_id' => $order_id));
		
		foreach($order_refund_historylist as $key => $val)
		{
			if($val['type'] ==3)
			{
				$pingtai_deal = 1;
			}
		}
		
		$order_refund = pdo_fetch("select * from ".tablename('lionfish_comshop_order_refund')." where uniacid=:uniacid and order_id=:order_id ",
						array(':uniacid' => $_W['uniacid'], ':order_id' => $order_id));
		
		if(!empty($order_refund))
		{
			$order_refund['addtime'] = date('Y-m-d H:i:s', $order_refund['addtime']);
		}
		
		$need_data['pick_up'] = $pick_up_info;
		
		
		
		$need_data['pick_order_info'] = $pick_order_info;
		$order_pay_after_share = load_model_class('front')->get_config_by_name('order_pay_after_share');
		if($order_pay_after_share==1){
			$order_pay_after_share_img = load_model_class('front')->get_config_by_name('order_pay_after_share_img');
			$order_pay_after_share_img = $order_pay_after_share_img ? tomedia($order_pay_after_share_img) : '';
			$need_data['share_img'] = $order_pay_after_share_img;
		}
		
		//$order_info['order_status_id']
		//0开启，1关闭   取消订单
		$order_can_del_cancle = load_model_class('front')->get_config_by_name('order_can_del_cancle');
		$order_can_del_cancle = empty($order_can_del_cancle) || $order_can_del_cancle == 0 ? 1 : 0;
		
		$is_hidden_orderlist_phone = load_model_class('front')->get_config_by_name('is_hidden_orderlist_phone');
		$is_show_guess_like = load_model_class('front')->get_config_by_name('is_show_order_guess_like');
		$user_service_switch = load_model_class('front')->get_config_by_name('user_service_switch');
		
		echo json_encode(
			array(
				'code' => 0,
				'data' => $need_data,
				'pingtai_deal' => $pingtai_deal,
				'order_refund' => $order_refund,
				'order_can_del_cancle' => $order_can_del_cancle,
				'order_pay_after_share' => $order_pay_after_share,
				'is_hidden_orderlist_phone' => $is_hidden_orderlist_phone,
				'is_show_guess_like' => $is_show_guess_like,
				'user_service_switch' => $user_service_switch
			)
		);
		die();
	}
	
	public function sign_dan_order()
	{
		global $_W;
		global $_GPC;
		
		$token = $_GPC['token'];
		$order_id = $_GPC['order_id'];
		
		$token_param = array();
		$token_param[':uniacid'] = $_W['uniacid'];
		$token_param[':token'] = $token;
		
		$weprogram_token = pdo_fetch("select member_id from ".tablename('lionfish_comshop_weprogram_token')." where uniacid=:uniacid and token=:token limit 1", $token_param);
		
		$member_id = $weprogram_token['member_id'];
		
		
		$community_info = load_model_class('front')->get_member_community_info($member_id);
		
		
		$order_info = pdo_fetch("select * from ".tablename('lionfish_comshop_order')." where uniacid=:uniacid and order_id=:order_id and head_id=:head_id", 
						array(':uniacid' => $_W['uniacid'], ':order_id' => $order_id , ':head_id' => $community_info['id']));
		
		if(!empty($order_info) && $order_info['order_status_id'] == 14 )
		{
			//....
			$oh = array();	
			$oh['order_id']=$order_id;
			$oh['uniacid']= $_W['uniacid'];
			$oh['order_status_id']= 4;
				
			$oh['comment']='团长签收货物';
			$oh['date_added']=time();
			$oh['notify']= $order_info['order_status_id'];
			
			pdo_insert('lionfish_comshop_order_history', $oh);
			
			//更改订单为已发货
			load_model_class('frontorder')->send_order_operate($order_id);
			echo json_encode( array('code' => 0) );
		}else{
			echo json_encode( array('code' => 1) );
		}
		
		
	}
	
	public function order_commission()
	{
		global $_W;
		global $_GPC;
		
		$token = $_GPC['token'];
		
		$token_param = array();
		$token_param[':uniacid'] = $_W['uniacid'];
		$token_param[':token'] = $token;
		
		$weprogram_token = pdo_fetch("select member_id from ".tablename('lionfish_comshop_weprogram_token')." where uniacid=:uniacid and token=:token limit 1", $token_param);
		
		$member_id = $weprogram_token['member_id'];
		
		if( empty($member_id) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}
		
		$community_info = load_model_class('front')->get_member_community_info($member_id);
		$head_id = $community_info['id'];
		
		$choose_date = $_GPC['chooseDate'];
			
		$choose_date = str_replace('年','-', $choose_date);
		$choose_date = str_replace('月','-', $choose_date);
		$choose_date = $choose_date.'01 00:00:00';
		
		$BeginDate=date('Y-m-d', strtotime($choose_date));
		
		$end_date = date('Y-m-d', strtotime("$BeginDate +1 month -1 day")).' 23:59:59';
		 
		$begin_time = strtotime($BeginDate.' 00:00:00');
		$end_time = strtotime($end_date);
		
		
		$where = " and addtime >= {$begin_time} and addtime < {$end_time} ";
		
		$money = pdo_fetchcolumn("select sum(money) from ".tablename('lionfish_community_head_commiss_order').
					" where uniacid=:uniacid and head_id=:head_id and state=0 {$where}", 
					array(':uniacid' => $_W['uniacid'], ':head_id' => $head_id ) );
		if( empty($money))
		{
			$money = 0;
		}			
		echo json_encode( array('code' => 0, 'money' => $money) );
		die();
			
			
		
	}
	
	public function refundorderlist()
	{
		global $_W;
		global $_GPC;
		
		$token = $_GPC['token'];
		
		$token_param = array();
		$token_param[':uniacid'] = $_W['uniacid'];
		$token_param[':token'] = $token;
		
		$weprogram_token = pdo_fetch("select member_id from ".tablename('lionfish_comshop_weprogram_token')." where uniacid=:uniacid and token=:token limit 1", $token_param);
		
		$member_id = $weprogram_token['member_id'];
		
		
		$page = isset($_GPC['page']) ? $_GPC['page']:'1';
		
		$size = isset($_GPC['size']) ? $_GPC['size']:'6';
		$offset = ($page - 1)* $size;
		
		$type =  isset($_GPC['type']) ? $_GPC['type']:'';
		
		
		$where = ' and o.member_id = '.$member_id;
		
		$fields = " orf.state as refund_state ,orf.order_goods_id as r_order_goods_id, ";
			
		$currentTab = isset($_GPC['currentTab']) ? $_GPC['currentTab']:0;
		
		//$join = " ".tablename('lionfish_comshop_order_refund').' as orf,  ';
		
		if($currentTab == 0)
		{
			
		}else if($currentTab == 1){
			//售后
			$where .= ' and o.order_status_id = 12 ';
		}else if($currentTab == 2){
			$where .= ' and  orf.state =3 ';
		}else if($currentTab == 3)
		{
			$where .= ' and orf.state =1 ';
		}
		
		$sql = "select orf.ref_id,orf.state,orf.order_goods_id,o.order_id,o.order_num_alias,o.date_added,o.delivery,o.is_pin,{$fields} o.is_zhuli,o.shipping_fare,o.shipping_tel,o.shipping_name,o.voucher_credit,o.fullreduction_money,o.store_id,o.total,o.order_status_id,o.lottery_win,o.type from ".tablename('lionfish_comshop_order_refund')." as orf left join  " .tablename('lionfish_comshop_order')." as o on orf.order_id = o.order_id  
						where  o.uniacid=:uniacid  {$where}  
	                    order by orf.addtime desc limit {$offset},{$size}";
	  
	    $list =  pdo_fetchall($sql, array(':uniacid' => $_W['uniacid']));
	   
	 
	  
	   $lionfish_comshop_order_status_list = pdo_fetchall("select * from ".tablename('lionfish_comshop_order_status') );
	   
	   $status_arr = array();
	   
	   foreach( $lionfish_comshop_order_status_list as $kk => $val )
	   {
		   $status_arr[ $val['order_status_id'] ] = $val['name'];
	   }
	   
	    //createTime
	    foreach($list as $key => $val)
	    {
			
	        $val['createTime'] = date('Y-m-d H:i:s', $val['date_added']);
			
			//$val['status_name'] = $status_arr[$val['order_status_id']];
			
			switch( $val['state'] )
			{
				case 0:
					$val['status_name'] = '申请中';
				break;
				case 1:
					$val['status_name'] = '商家拒绝';
				break;
				case 2:
				
					break;
				case 3:
					$val['status_name'] = '退款成功';
				break;
				case 4:
					$val['status_name'] = '退款失败';
				break;
				case 5:
					$val['status_name'] = '撤销申请';
					break;
			}
			
			
			if($val['shipping_fare']<=0.001 || $val['delivery'] == 'pickup')
			{
				$val['shipping_fare'] = '免运费';
			}else{
				$val['shipping_fare'] = ''.$val['shipping_fare'];
			}
			
			
			if($val['order_status_id'] == 10)
			{
				$val['status_name'] = '等待退款';
			}
			else if($val['order_status_id'] == 4 && $val['delivery'] =='pickup')
			{
				//delivery 6
				$val['status_name'] = '待自提';
				//已自提
			}
			else if($val['order_status_id'] == 6 && $val['delivery'] =='pickup')
			{
				//delivery 6
				$val['status_name'] = '已自提';
				//已自提
			}
			else if($val['order_status_id'] == 1 && $val['type'] == 'lottery')
			{
				//等待开奖
				//一等奖
				if($val['lottery_win'] == 1)
				{
					$val['status_name'] = '一等奖';
				}else {
					$val['status_name'] = '等待开奖';
				}
			}
			else if($val['order_status_id'] == 2 && $val['type'] == 'lottery')
			{
				//等待开奖
				$val['status_name'] = '等待开奖';
			}
			
	        $quantity = 0;
	        
			if( $val['order_goods_id'] > 0 )
			{
				$goods_sql = "select order_goods_id,head_disc,member_disc,level_name,goods_id,is_pin,shipping_fare,name,goods_images,quantity,price,total,rela_goodsoption_valueid   
					from ".tablename('lionfish_comshop_order_goods')." where uniacid=:uniacid and order_goods_id=".$val['order_goods_id']." and  order_id= ".$val['order_id']."";
	        
			}else{
				$goods_sql = "select order_goods_id,head_disc,member_disc,level_name,goods_id,is_pin,shipping_fare,name,goods_images,quantity,price,total,rela_goodsoption_valueid   
					from ".tablename('lionfish_comshop_order_goods')." where uniacid=:uniacid and  order_id= ".$val['order_id']."";
	        
			}
			
	        
	        $goods_list = pdo_fetchall($goods_sql, array(':uniacid' => $_W['uniacid'])); //M()->query($goods_sql);
			$total_commision = 0;
			if($val['delivery'] =='tuanz_send')
			{
				$total_commision += $val['shipping_fare'];
			}
			
	        foreach($goods_list as $kk => $vv) 
	        {
	            //commision
				$order_option_list = pdo_fetchall("select * from ".tablename('lionfish_comshop_order_option')." where uniacid=:uniacid and order_goods_id=:order_goods_id ", array(':uniacid' => $_W['uniacid'], ':order_goods_id' => $vv['order_goods_id']));
	            
				
				if( !empty($vv['goods_images']))
				{
					
					$goods_images = file_image_thumb_resize($vv['goods_images'],400);
					if(is_array($goods_images))
					{
						$vv['goods_images'] = $vv['goods_images'];
					}else{
						 $vv['goods_images']= tomedia( file_image_thumb_resize($vv['goods_images'],400) ); 
					}	
					
				}else{
					 $vv['goods_images']= ''; 
				}
	           
				$goods_filed = pdo_fetch("select productprice as price from ".tablename('lionfish_comshop_goods')." where id=:goods_id", array(':goods_id' => $vv['goods_id'] ));
 			
				$vv['orign_price'] = $goods_filed['price'];
	            $quantity += $vv['quantity'];
	            foreach($order_option_list as $option)
	            {
	                $vv['option_str'][] = $option['value'];
	            }
				if( !isset($vv['option_str']) )
				{
					$vv['option_str'] = '';
				}else{
					$vv['option_str'] = implode(',', $vv['option_str']);
				}
	            // $vv['price'] = round($vv['price'],2);
	            $vv['price'] = sprintf("%.2f",$vv['price']);
	            $vv['orign_price'] = sprintf("%.2f",$vv['orign_price']);
	            $vv['total'] = sprintf("%.2f",$vv['total']);
				
	            
	            $goods_list[$kk] = $vv;
	        }
			$val['total_commision'] = $total_commision;
	        $val['quantity'] = $quantity;
	        if( empty($val['store_id']) )
			{
				$val['store_id'] = 1;
			}
			
			
			$store_info	= array('s_true_name' =>'','s_logo' => '');
			
			$store_info['s_true_name'] = load_model_class('front')->get_config_by_name('shoname');
			
			$store_info['s_logo'] = load_model_class('front')->get_config_by_name('shoplogo'); 
			
			
		
			if( !empty($store_info['s_logo']))
			{
				$store_info['s_logo'] = tomedia($store_info['s_logo']);
			}else{
				
				$store_info['s_logo'] = '';
			}
			
			
			$order_goods['store_info'] = $store_info;
			
			$val['store_info'] = $store_info;
			
	        $val['goods_list'] = $goods_list;
			
			$val['total'] = $val['total'] + $val['shipping_fare']-$val['voucher_credit']-$val['fullreduction_money'];
			if($val['total'] < 0)
			{
				$val['total'] = 0;
			}
			
			$val['total'] = sprintf("%.2f",$val['total']);
	        $list[$key] = $val;
	    }
		
		$need_data = array('code' => 0);
		
		if( !empty($list) )
		{
			$need_data['data'] = $list;
			
		}else {
			$need_data = array('code' => 1);
		}
		
		echo json_encode( $need_data );
		die();
		
	}
	
	public function orderlist()
	{
		global $_W;
		global $_GPC;
		
		$is_tuanz  = isset($_GPC['is_tuanz']) ? $_GPC['is_tuanz'] :0;
		$token = $_GPC['token'];
		
		$token_param = array();
		$token_param[':uniacid'] = $_W['uniacid'];
		$token_param[':token'] = $token;
		
		$weprogram_token = pdo_fetch("select member_id from ".tablename('lionfish_comshop_weprogram_token')." where uniacid=:uniacid and token=:token limit 1", $token_param);
		
		$member_id = $weprogram_token['member_id'];
		
		$sqlcondition = "";
		$left_join = "";
		
		$page = isset($_GPC['page']) ? $_GPC['page']:'1';
		
		$size = isset($_GPC['size']) ? $_GPC['size']:'6';
		$offset = ($page - 1)* $size;
		
		$type =  isset($_GPC['type']) ? $_GPC['type']:'';
		
		$order_status = isset($_GPC['order_status']) ? $_GPC['order_status']:'-1';
	   
		if($is_tuanz == 1)
		{
			$community_info = load_model_class('front')->get_member_community_info($member_id);
			
			if( isset($_GPC['chooseDate']) && !empty($_GPC['chooseDate']) )
			{
				$where = ' and o.head_id = '.$community_info['id'] ;
			}else{
				//$where = ' and o.head_id = '.$community_info['id'].' and o.delivery != "express"  ';
				$where = ' and o.head_id = '.$community_info['id'].'  ';
			}
			
			$searchfield = isset($_GPC['searchfield']) && !empty($_GPC['searchfield']) ? $_GPC['searchfield'] : '';
			
			if( !empty($searchfield) && !empty($_GPC['keyword']))
			{
				$keyword = $_GPC['keyword'];
				
				switch($searchfield)
				{
					case 'ordersn':
						$where .= ' AND locate("'.$keyword.'",o.order_num_alias)>0'; 
					break;
					case 'member':
						$where .= ' AND (locate("'.$keyword.'",m.username)>0 or "'.$keyword.'"=o.member_id )';
						$left_join .= ' left join ' . tablename('lionfish_comshop_member') . ' m on m.member_id = o.member_id ';
					break;
					case 'address':
						$where .= ' AND ( locate("'.$keyword.'",o.shipping_name)>0 )';
						
					break;
					case 'mobile':
						$where .= ' AND ( locate("'.$keyword.'",o.shipping_tel)>0 )';
						
					break;
					case 'location':
						$where .= ' AND (locate("'.$keyword.'",o.shipping_address)>0 )';
					break;
					case 'shipping_no':
						$where .= ' AND (locate("'.$keyword.'",o.shipping_no)>0 )';
					break;
					case 'goodstitle':
						$left_join = ' inner join ( select DISTINCT(og.order_id) from ' . tablename('lionfish_comshop_order_goods') . ' og  where  (locate("'.$keyword.'",og.name)>0)) gs on gs.order_id=o.order_id';
						
					break;
					case 'trans_id':
						$where .= ' AND (locate("'.$keyword.'",o.transaction_id)>0 )';
					break;
					
				}
			}
			
		}else{
			$where = ' and o.member_id = '.$member_id;
		}
		
	    if( isset($_GPC['chooseDate']) && !empty($_GPC['chooseDate']) )
		{
			$choose_date = $_GPC['chooseDate'];
			
			$choose_date = str_replace('年','-', $choose_date);
			$choose_date = str_replace('月','-', $choose_date);
			$choose_date = $choose_date.'01 00:00:00';
			
			$BeginDate=date('Y-m-d', strtotime($choose_date));
			
			$end_date = date('Y-m-d', strtotime("$BeginDate +1 month -1 day")).' 23:59:59';
			 
			$begin_time = strtotime($BeginDate.' 00:00:00');
			$end_time = strtotime($end_date);
			
			
			$where .= ' and o.date_added >= '.$begin_time.' and o.date_added < '.$end_time;
		}
	    
		//全部 -1  待付款 3 待配送1 待提货4 已提货6 代配送14
		//order_status $order_status
		$join = "";
		$fields = "";
		
		switch($order_status)
		{
			case -1:
			//全部 -1
			
			break;
			case 3:
			//待付款 3
				$where .= ' and o.order_status_id = 3 ';
			break;
			case 1:
			//待配送1
			// if($is_tuanz == 1)
			// {
				$where .= ' and o.order_status_id = 1 ';
			// }else {
			// 	$where .= ' and o.order_status_id in (1,14) ';
			// }
			break;
			case 4:
			//待提货4
			$where .= ' and o.order_status_id = 4 ';
			break;
			case 14:
			//待提货4
			$where .= ' and o.order_status_id = 14 ';
			break;
			
			case 22:
			//待确认佣金的
				$where .= ' and o.order_status_id in (1,4,14) ';
			break;
			case 357:
			//待确认佣金的
				$where .= ' and o.order_status_id in (3,5,7) ';
			break;
			case 6:
			//已提货6
				$where .= ' and o.order_status_id in (6,11) ';
			break;
			case 11:
			//已完成
			$where .= ' and o.order_status_id = 11 ';
			break;
			case 12:
			$fields = " orf.state as refund_state , ";
			
			$currentTab = isset($_GPC['currentTab']) ? $_GPC['currentTab']:0;
			
			$join = " ".tablename('lionfish_comshop_order_refund').' as orf,  ';
			$where .= ' and o.order_id = orf.order_id ';
			if($currentTab == 0)
			{
				
			}else if($currentTab == 1){
				//售后
				$where .= ' and o.order_status_id = 12 ';
			}else if($currentTab == 2){
				$where .= ' and  orf.state =3 ';
			}else if($currentTab == 3)
			{
				$where .= ' and orf.state =1 ';
			}
			
			break;
			case 7:
			//已退款
			$where .= ' and o.order_status_id = 7 ';
			break;
		}
		
		$where .= ' and o.type != "ignore" ';
		
		if( !empty($type) )
		{
			//$where .= ' and o.type != "ignore" ';
		}
	   
	    $sql = "select o.order_id,o.order_num_alias,o.date_added,o.delivery,o.is_pin,{$fields} o.is_zhuli,o.shipping_fare,o.shipping_tel,o.shipping_name,o.voucher_credit,o.fullreduction_money,o.store_id,o.total,o.order_status_id,o.lottery_win,o.type,os.name as status_name from ".tablename('lionfish_comshop_order')." as o  {$left_join}, {$join} 
                ".tablename('lionfish_comshop_order_status')." as os ".$sqlcondition." 
	                    where  o.uniacid=:uniacid and o.order_status_id = os.order_status_id {$where}  
	                    order by o.date_added desc limit {$offset},{$size}";
	  
	    $list =  pdo_fetchall($sql, array(':uniacid' => $_W['uniacid']));
	   
	    //createTime
	    foreach($list as $key => $val)
	    {
			
			if($val['delivery'] == 'pickup')
			{
				//$val['total'] = round($val['total'],2) - round($val['voucher_credit'],2);
			}else{
				//$val['total'] = round($val['total'],2)+round($val['shipping_fare'],2) - round($val['voucher_credit'],2);
			}
	        $val['createTime'] = date('Y-m-d H:i:s', $val['date_added']);
			
			// $val['delivery'] =='pickup'
			
			if($val['shipping_fare']<=0.001 || $val['delivery'] == 'pickup')
			{
				$val['shipping_fare'] = '免运费';
			}else{
				$val['shipping_fare'] = ''.$val['shipping_fare'];
			}
			
			
			if($val['order_status_id'] == 10)
			{
				$val['status_name'] = '等待退款';
			}
			else if($val['order_status_id'] == 4 && $val['delivery'] =='pickup')
			{
				//delivery 6
				$val['status_name'] = '待自提';
				//已自提
			}
			else if($val['order_status_id'] == 6 && $val['delivery'] =='pickup')
			{
				//delivery 6
				$val['status_name'] = '已自提';
				//已自提
			}
			else if($val['order_status_id'] == 1 && $val['type'] == 'lottery')
			{
				//等待开奖
				//一等奖
				if($val['lottery_win'] == 1)
				{
					$val['status_name'] = '一等奖';
				}else {
					$val['status_name'] = '等待开奖';
				}
			}
			else if($val['order_status_id'] == 2 && $val['type'] == 'lottery')
			{
				//等待开奖
				$val['status_name'] = '等待开奖';
			}
			
	        $quantity = 0;
	        
	        $goods_sql = "select order_goods_id,head_disc,member_disc,level_name,goods_id,is_pin,shipping_fare,name,goods_images,quantity,price,total,rela_goodsoption_valueid   
	            from ".tablename('lionfish_comshop_order_goods')." where uniacid=:uniacid and  order_id= ".$val['order_id']."";
	        
	        $goods_list = pdo_fetchall($goods_sql, array(':uniacid' => $_W['uniacid'])); //M()->query($goods_sql);
			$total_commision = 0;
			if($val['delivery'] =='tuanz_send')
			{
				$total_commision += $val['shipping_fare'];
			}
			
	        foreach($goods_list as $kk => $vv) 
	        {
	            //commision
				
				if($is_tuanz == 1){
					$community_order_info = pdo_fetch("select * from ".tablename('lionfish_community_head_commiss_order')." 
											where uniacid=:uniacid and order_goods_id=:order_goods_id and head_id=:head_id ", 
											array(':uniacid' => $_W['uniacid'], ':order_goods_id' => $vv['order_goods_id'],':head_id' => $community_info['id']));
					if(!empty($community_order_info))
					{
						$vv['commision'] = $community_order_info['money']-$community_order_info['add_shipping_fare'];
						$vv['commision'] = sprintf("%.2f",$vv['commision']);
						$total_commision += $vv['commision'];
					}else{
						$vv['commision'] = "0.00";
					}				
							
				}
				$order_option_list = pdo_fetchall("select * from ".tablename('lionfish_comshop_order_option')." where uniacid=:uniacid and order_goods_id=:order_goods_id ", array(':uniacid' => $_W['uniacid'], ':order_goods_id' => $vv['order_goods_id']));
	            
				
				if( !empty($vv['goods_images']))
				{
					
					$goods_images = file_image_thumb_resize($vv['goods_images'],400);
					if(is_array($goods_images))
					{
						$vv['goods_images'] = $vv['goods_images'];
					}else{
						 $vv['goods_images']= tomedia( file_image_thumb_resize($vv['goods_images'],400) ); 
					}	
					
				}else{
					 $vv['goods_images']= ''; 
				}
	           
				$goods_filed = pdo_fetch("select productprice as price from ".tablename('lionfish_comshop_goods')." where id=:goods_id", array(':goods_id' => $vv['goods_id'] ));
 			
				$vv['orign_price'] = $goods_filed['price'];
	            $quantity += $vv['quantity'];
	            foreach($order_option_list as $option)
	            {
	                $vv['option_str'][] = $option['value'];
	            }
				if( !isset($vv['option_str']) )
				{
					$vv['option_str'] = '';
				}else{
					$vv['option_str'] = implode(',', $vv['option_str']);
				}
	            // $vv['price'] = round($vv['price'],2);
	            $vv['price'] = sprintf("%.2f",$vv['price']);
	            $vv['orign_price'] = sprintf("%.2f",$vv['orign_price']);
	            $vv['total'] = sprintf("%.2f",$vv['total']);
				
	            
	            $goods_list[$kk] = $vv;
	        }
			$val['total_commision'] = $total_commision;
	        $val['quantity'] = $quantity;
	        if( empty($val['store_id']) )
			{
				$val['store_id'] = 1;
			}
			
			
			$store_info	= array('s_true_name' =>'','s_logo' => '');
			
			$store_info['s_true_name'] = load_model_class('front')->get_config_by_name('shoname');
			
			$store_info['s_logo'] = load_model_class('front')->get_config_by_name('shoplogo'); 
			
			
		
			if( !empty($store_info['s_logo']))
			{
				$store_info['s_logo'] = tomedia($store_info['s_logo']);
			}else{
				
				$store_info['s_logo'] = '';
			}
			
			
			$order_goods['store_info'] = $store_info;
			
			
			
			
			$val['store_info'] = $store_info;
			
			
	        $val['goods_list'] = $goods_list;
			
			if($val['type'] == 'integral')
			{
				//暂时屏蔽积分
				$val['score'] =  round($val['total'],2);
			}
			
			
			$val['total'] = $val['total'] + $val['shipping_fare']-$val['voucher_credit']-$val['fullreduction_money'];
			if($val['total'] < 0)
			{
				$val['total'] = 0;
			}
			
			$val['total'] = sprintf("%.2f",$val['total']);
	        $list[$key] = $val;
	    }
		
		$need_data = array('code' => 0);
		
		if( !empty($list) )
		{
			$need_data['data'] = $list;
			
		}else {
			$need_data = array('code' => 1);
		}
		
		echo json_encode( $need_data );
		die();
		
	}
	
	function receive_order_list()
	{
		global $_W;
		global $_GPC;
		
		$uniacid = $_W['uniacid'];
		
		$order_data = $_GPC['order_data'];
		$token = $_GPC['token'];
		
		
		$token_param = array();
		$token_param[':uniacid'] = $_W['uniacid'];
		$token_param[':token'] = $token;

		$weprogram_token = pdo_fetch("select member_id from ".tablename('lionfish_comshop_weprogram_token')." where uniacid=:uniacid and token=:token limit 1", $token_param);
	    $member_id = $weprogram_token['member_id'];
	    $member_info = pdo_fetch("select * from ".tablename('lionfish_comshop_member')." where uniacid=:uniacid and member_id=:member_id limit 1", array(':uniacid' => $_W['uniacid'], ':member_id' => $member_id ));
		
		if( empty($member_info) )
		{
			echo json_encode( array('code' => 1) );
			die();
		}
		
		$community_info = load_model_class('front')->get_member_community_info($member_id);
		
		$is_member_hexiao = false;
		if( empty($community_info) && $member_info['pickup_id'] > 0  )
		{
			$parent_community_info = pdo_fetch("select * from ".tablename('lionfish_comshop_community_pickup_member').
									" where uniacid=:uniacid and member_id=:member_id " , 
									array(':uniacid' => $_W['uniacid'], ':member_id' => $member_id));
			if(!empty($parent_community_info))
			{
				$is_member_hexiao = true;
				$community_info = pdo_fetch("select * from ".tablename('lionfish_community_head')." where uniacid=:uniacid and id=:id ",
						array(':uniacid' => $_W['uniacid'], ':id' => $parent_community_info['community_id']));
			}
		}
		
		if( empty($community_info) || $community_info['state'] != 1 )
		{
			echo json_encode( array('code' => 1) );
			die();	
		}
		if( is_array($order_data) )
		{
			$order_data_str = implode(',', $order_data);
		}else{
			$order_data_str = $order_data;
		}
		
		
		$where = ' and o.head_id = '.$community_info['id'];
		
		$where .= ' and o.order_status_id = 4 and order_id in ('.$order_data_str.') ';
		
		
		
		
		$sql = "select o.order_id,o.order_num_alias   
				from ".tablename('lionfish_comshop_order')." as o , 
                ".tablename('lionfish_comshop_order_status')." as os 
	                    where  o.uniacid=:uniacid and o.order_status_id = os.order_status_id {$where}  
	                    order by o.date_added desc ";
	  
	    $list =  pdo_fetchall($sql, array(':uniacid' => $_W['uniacid']));
		
		if( !empty($list) )
		{
			foreach($list as $val)
			{
				load_model_class('frontorder')->receive_order($val['order_id']);
				if($is_member_hexiao)
				{
					$pickup_member_record_data = array();
					$pickup_member_record_data['uniacid'] = $_W['uniacid'];
					$pickup_member_record_data['order_id'] = $val['order_id'];
					$pickup_member_record_data['order_sn'] = $val['order_num_alias'];
					$pickup_member_record_data['community_id'] = $community_info['id'];
					$pickup_member_record_data['member_id'] = $member_id;
					$pickup_member_record_data['addtime'] = time();
					
					pdo_insert('lionfish_comshop_community_pickup_member_record', $pickup_member_record_data);
				}
				
			}
			echo json_encode( array('code' => 0) );
			die();
		}else{
			echo json_encode( array('code' => 1) );
			die();
		}
	   
		//load_model_class('frontorder')->receive_order($order_id);
		
		//string(15) "35,34,31,19,5,2"
		//string(32) "b55feabc517fa686f79c1bbd303cdeda"

		
	}
	
	function receive_order()
	{
		global $_W;
		global $_GPC;

		$token = $_GPC['token'];
		
		$token_param = array();
		$token_param[':uniacid'] = $_W['uniacid'];
		$token_param[':token'] = $token;
		
		$weprogram_token = pdo_fetch("select member_id from ".tablename('lionfish_comshop_weprogram_token')." where uniacid=:uniacid and token=:token limit 1", $token_param);
		
		$member_id = $weprogram_token['member_id'];
		
		$order_id = $_GPC['order_id'];

		if( empty($member_id) )
		{

			$result['code'] = 2;	

	        $result['msg'] = '登录失效';

	        echo json_encode($result);

	        die();

		}

		$order_info = pdo_fetch("select * from ".tablename('lionfish_comshop_order')." where uniacid=:uniacid and member_id=:member_id and order_id=:order_id ", array(':uniacid' => $_W['uniacid'] , ':member_id' => $member_id,':order_id' => $order_id ));
		

	    if(empty($order_info)){

			$result['code'] = 1;	

	        $result['msg'] = '非法操作,会员不存在该订单';

	        echo json_encode($result);

	        die();
	    }

		load_model_class('frontorder')->receive_order($order_id);

	    $result['code'] = 0;

	    echo json_encode($result);

	    die();

	}
	
	/**

		取消订单操作

	**/

	public function cancel_order()
	{
		
		global $_W;
		global $_GPC;

	    $token = $_GPC['token'];
		
		$token_param = array();
		$token_param[':uniacid'] = $_W['uniacid'];
		$token_param[':token'] = $token;
		
		$weprogram_token = pdo_fetch("select member_id from ".tablename('lionfish_comshop_weprogram_token')." where uniacid=:uniacid and token=:token limit 1", $token_param);
		
		$member_id = $weprogram_token['member_id'];
		
		$order_id = $_GPC['order_id'];

		if( empty($member_id) )

		{

			$result['code'] = 2;	

	        $result['msg'] = '登录失效';

	        echo json_encode($result);

	        die();

		}

	    
		$order_info = pdo_fetch("select * from ".tablename('lionfish_comshop_order')." where uniacid=:uniacid and member_id=:member_id and order_id=:order_id ", array(':order_id' => $order_id,':member_id' => $member_id, ':uniacid' => $_W['uniacid']));
		

	    if(empty($order_info)){

			$result['code'] = 1;	

	        $result['msg'] = '非法操作,会员不存在该订单';

	        echo json_encode($result);

	        die();

	    }
		//

		load_model_class('frontorder')->cancel_order($order_id);
		
	    $result['code'] = 0;

	    echo json_encode($result);

	    die();

	   

	}

	public function order_head_info()
	{
		global $_W;
		global $_GPC;
		
		$token = $_GPC['token'];
		$is_share = $_GPC['is_share'];
		
		$token_param = array();
		$token_param[':uniacid'] = $_W['uniacid'];
		$token_param[':token'] = $token;
		
		$weprogram_token = pdo_fetch("select member_id from ".tablename('lionfish_comshop_weprogram_token')." where uniacid=:uniacid and token=:token limit 1", $token_param);
		$member_id = $weprogram_token['member_id'];
		$order_id = $_GPC['id'];
	    
		$order_info = pdo_fetch("select * from ".tablename('lionfish_comshop_order')." where uniacid=:uniacid and order_id=:order_id ", array(':uniacid' => $_W['uniacid'], ':order_id' => $order_id ) );
		
		$pick_up_info = array();
		$pick_order_info = array();

		$community_info = load_model_class('front')->get_member_community_info($member_id);

		if($is_share){
			$user_param = array();
			$user_param[':uniacid'] = $_W['uniacid'];
			$user_param[':member_id'] = $order_info['member_id'];
			$userInfo = pdo_fetch("select avatar from ".tablename('lionfish_comshop_member')." where uniacid=:uniacid and member_id=:member_id limit 1", $user_param);
			$order_info['avatar'] = $userInfo['avatar'];
		}
		
		if( $order_info['delivery'] == 'pickup' )
		{
			//查询自提点
			$pick_order_info = pdo_fetch("select * from ".tablename('lionfish_comshop_pick_order')." where uniacid=:uniacid and order_id=:order_id ",array(':uniacid' => $_W['uniacid'], ':order_id' => $order_id));
			
			$pick_id = $pick_order_info['pick_id'];
			
			$pick_up_info = pdo_fetch("select * from ".tablename('lionfish_comshop_pick_up')." where uniacid=:uniacid and id=:id ", array(':uniacid' => $_W['uniacid'], ':id' => $pick_id));
			
		}

		$order_status_info = pdo_fetch("select * from ".tablename('lionfish_comshop_order_status')." where order_status_id=:order_status_id ", array(':order_status_id' => $order_info['order_status_id'] ));
		
	    //10 name
		if($order_info['order_status_id'] == 10)
		{
			$order_status_info['name'] = '等待退款';
		}
		else if($order_info['order_status_id'] == 4 && $order_info['delivery'] =='pickup')
		{
			//delivery 6
			$order_status_info['name'] = '待自提';
			//已自提
		}
		else if($order_info['order_status_id'] == 6 && $order_info['delivery'] =='pickup')
		{
			//delivery 6
			$order_status_info['name'] = '已自提';
			
		}
		else if($order_info['order_status_id'] == 1 && $order_info['type'] == 'lottery')
		{
			//等待开奖
			//一等奖
			if($order_info['lottery_win'] == 1)
			{
				$order_status_info['name'] = '一等奖';
			}else {
				$order_status_info['name'] = '等待开奖';
			}
		}
		
		//$order_info['type']
		//open_auto_delete
		
		if($order_info['order_status_id'] == 3)
		{
			$open_auto_delete = load_model_class('front')->get_config_by_name('open_auto_delete');
			$auto_cancle_order_time = load_model_class('front')->get_config_by_name('auto_cancle_order_time');
			
			$order_info['open_auto_delete'] = $open_auto_delete;
			//date_added
			if($open_auto_delete == 1)
			{
				$order_info['over_buy_time'] = $order_info['date_added'] + 3600 * $auto_cancle_order_time;
				$order_info['cur_time'] = time();
			}
		
		}

		if($order_info['delivery'] == 'express')
		{
			$order_info['delivery_name'] = '快递';
		}else if($order_info['delivery'] == 'pickup')
		{
			$order_info['delivery_name'] = '自提';
		}else if($order_info['delivery'] == 'tuanz_send'){
			$order_info['delivery_name'] = '团长配送';
		}
		
	  
		$shipping_province = pdo_fetch("select * from ".tablename('lionfish_comshop_area')." where id=:id ", array(':id' => $order_info['shipping_province_id']));
	    $shipping_city = pdo_fetch("select * from ".tablename('lionfish_comshop_area')." where id=:id ", array(':id' => $order_info['shipping_city_id']));
	    $shipping_country = pdo_fetch("select * from ".tablename('lionfish_comshop_area')." where id=:id ", array(':id' => $order_info['shipping_country_id']));
		$order_goods_list = pdo_fetchall("select * from ".tablename('lionfish_comshop_order_goods')." where order_id=:order_id ",array(':order_id' => $order_id));
		
		$shiji_total_money = 0;
		$member_youhui = 0.00;
		
		
		$pick_up_time = "";
		$pick_up_type = -1;
		$pick_up_weekday = '';
		$today_time = $order_info['pay_time'];
		$arr = array('天','一','二','三','四','五','六');
	
	    foreach($order_goods_list as $key => $order_goods)
	    {
			
			$order_option_info = pdo_fetchall("select value from ".tablename('lionfish_comshop_order_option')." where order_id=:order_id and order_goods_id=:order_goods_id ",array(':order_id' => $order_id,':order_goods_id' => $order_goods['order_goods_id'] ));
			  
	        foreach($order_option_info as $option)
	        {
	            $order_goods['option_str'][] = $option['value'];
	        }
			if(empty($order_goods['option_str']))
			{
				//option_str
				 $order_goods['option_str'] = '';
			}else{
				 $order_goods['option_str'] = implode(',', $order_goods['option_str']);
			}
	       //
		    $order_goods['shipping_fare'] = round($order_goods['shipping_fare'],2);
			
			if( $order_goods['is_vipcard_buy'] == 1 )
			{
				$order_goods['price'] = round($order_goods['oldprice'],2);
				$order_goods['total'] = round($order_goods['old_total'],2);
			}else{
				$order_goods['price'] = round($order_goods['price'],2);
				$order_goods['total'] = round($order_goods['total'],2);
			}
			
		    
			
		    $order_goods['real_total'] = round($order_goods['quantity'] * $order_goods['price'],2);

		    $community_order_info = pdo_fetch("select * from ".tablename('lionfish_community_head_commiss_order')." where uniacid=:uniacid and order_goods_id=:order_goods_id and head_id=:head_id ", array(':uniacid' => $_W['uniacid'], ':order_goods_id' => $order_goods['order_goods_id'],':head_id' => $community_info['id']));
			if(!empty($community_order_info))
			{
				$order_goods['commision'] = $community_order_info['money'];		
			}else{
				$order_goods['commision'] = 0;
			}
	        
			/**
					$goods_images = file_image_thumb_resize($vv['goods_images'],400);
					if(is_array($goods_images))
					{
						$vv['goods_images'] = $vv['goods_images'];
					}else{
						 $vv['goods_images']= tomedia( file_image_thumb_resize($vv['goods_images'],400) ); 
					}	
					
			**/
			$goods_images = file_image_thumb_resize($order_goods['goods_images'],400);
			
			if( !is_array($goods_images) )
			{
				 $order_goods['image']=  tomedia( $goods_images );
				$order_goods['goods_images']= tomedia( $goods_images ); 
			}else{
				 $order_goods['image']=  $order_goods['goods_images'];
			}
	       
		   $order_goods['hascomment'] = 0;
		   
		   $order_goods_comment_info = pdo_fetch('select comment_id from '.tablename('lionfish_comshop_order_comment')." where order_id=:order_id and uniacid=:uniacid and goods_id=:goods_id ", 
										array(':uniacid' => $_W['uniacid'], ':order_id' => $order_id,':goods_id' => $order_goods['goods_id'] ));
			if( !empty($order_goods_comment_info) )
			{
				$order_goods['hascomment'] = 1;
			}
			
			//ims_ 
			$goods_info = pdo_fetch("select productprice as price from ".tablename('lionfish_comshop_goods')." where id=:goods_id", array(':goods_id' => $order_goods['goods_id'] ));
		
			$goods_cm_info = pdo_fetch("select pick_up_type,pick_up_modify from ".tablename('lionfish_comshop_good_common').
						" where uniacid=:uniacid and goods_id=:goods_id ", 
						array(':uniacid' => $_W['uniacid'], ':goods_id' => $order_goods['goods_id']));
			if($pick_up_type == -1 || $goods_cm_info['pick_up_type'] > $pick_up_type)
			{
				$pick_up_type = $goods_cm_info['pick_up_type'];
				
				if($pick_up_type == 0)
				{
					$pick_up_time = date('m-d', $today_time);
					//$pick_up_weekday = '周'.$arr[date('w',$today_time)];
				}else if( $pick_up_type == 1 ){
					$pick_up_time = date('m-d', $today_time+86400);
					//$pick_up_weekday = '周'.$arr[date('w',$today_time+86400)];
				}else if( $pick_up_type == 2 )
				{
					$pick_up_time = date('m-d', $today_time+86400*2);
					//$pick_up_weekday = '周'.$arr[date('w',$today_time+86400*2)];
				}else if($pick_up_type == 3)
				{
					$pick_up_time = $goods_cm_info['pick_up_modify'];
				}
			}
			
			$order_goods['shop_price'] = $goods_info['price'];
			 
			
			$store_info	= array('s_true_name' =>'','s_logo' => '');
			
			$store_info['s_true_name'] = load_model_class('front')->get_config_by_name('shoname');
			
			//$store_info['s_logo'] = load_model_class('front')->get_config_by_name('shoplogo'); 
			
			if( !empty($store_info['s_logo']) )
			{
				$store_info['s_logo'] = tomedia($store_info['s_logo']);
			}else{
				$store_info['s_logo'] = '';
			}
			
			
			$order_goods['store_info'] = $store_info;
			
			unset($order_goods['model']);
			unset($order_goods['rela_goodsoption_valueid']);
			unset($order_goods['comment']);
			
	        $order_goods_list[$key] = $order_goods;
			$shiji_total_money += $order_goods['quantity'] * $order_goods['price'];
			
			$member_youhui += ($order_goods['real_total'] - $order_goods['total']);
	    }
	    
		unset($order_info['store_id']);
		unset($order_info['email']);
		unset($order_info['shipping_city_id']);
		unset($order_info['shipping_country_id']);
		unset($order_info['shipping_province_id']);
		// unset($order_info['comment']);
		unset($order_info['voucher_id']);
		unset($order_info['is_balance']);
		unset($order_info['lottery_win']);
		unset($order_info['ip']);
		unset($order_info['ip_region']);
		unset($order_info['user_agent']);
		
		
		
		$order_info['shipping_fare'] = round($order_info['shipping_fare'],2) < 0.01 ? '0.00':round($order_info['shipping_fare'],2) ;
		$order_info['voucher_credit'] = round($order_info['voucher_credit'],2) < 0.01 ? '0.00':round($order_info['voucher_credit'],2) ;
		$order_info['fullreduction_money'] = round($order_info['fullreduction_money'],2) < 0.01 ? '0.00':round($order_info['fullreduction_money'],2) ;
		
		
		$order_info['total'] = round($order_info['total'],2)< 0.01 ? '0.00':round($order_info['total']+$order_info['shipping_fare']-$order_info['voucher_credit']-$order_info['fullreduction_money'],2)	;
		
		if($order_info['total'] < 0)
		{
			$order_info['total'] = '0.00';
		}
		
		$order_info['total'] = round($order_info['total'],2)< 0.01 ? '0.00':round($order_info['total'],2)	;
		$order_info['real_total'] = round($shiji_total_money,2)+$order_info['shipping_fare'];		
		$order_info['price'] = round($order_info['price'],2);		
		$order_info['member_youhui'] = round($member_youhui,2) < 0.01 ? '0.00':round($member_youhui,2);	
		$order_info['pick_up_time'] = $pick_up_time;
			
			
		$order_info['shipping_fare'] = sprintf("%.2f",$order_info['shipping_fare']);
		$order_info['voucher_credit'] = sprintf("%.2f",$order_info['voucher_credit']);
		$order_info['fullreduction_money'] = sprintf("%.2f",$order_info['fullreduction_money']);
		$order_info['total'] = sprintf("%.2f",$order_info['total']);
		$order_info['real_total'] = sprintf("%.2f",$order_info['real_total']);
		
		
		$order_info['date_added'] = date('Y-m-d H:i:s', $order_info['date_added']);
		$need_data = array();
	
		// if($order_info['delivery'] =='pickup')
		// {}else{}
		
		if($order_info['type'] == 'integral')
		{
			//暂时屏蔽积分商城
			//$integral_order = M('integral_order')->field('score')->where( array('order_id' => $order_id) )->find();
			//$need_data['score'] = intval($integral_order['score']);
			
		}
		if( !empty($order_info['pay_time']) && $order_info['pay_time'] >0 )
		{
			$order_info['pay_date'] = date('Y-m-d H:i:s', $order_info['pay_time']);
		}else{
			$order_info['pay_date'] = '';
		}
		
		$order_info['express_tuanz_date'] = empty($order_info['express_tuanz_time']) ? '' : date('Y-m-d H:i:s', $order_info['express_tuanz_time']);
		$order_info['receive_date'] = date('Y-m-d H:i:s', $order_info['receive_time']);
		
		if($is_share==1){ $order_info['shipping_tel'] = substr_replace($order_info['shipping_tel'],'****',3,4); }
		
		$need_data['order_info'] = $order_info;
		$need_data['order_status_info'] = $order_status_info;
		$need_data['shipping_province'] = $shipping_province;
		$need_data['shipping_city'] = $shipping_city;
		$need_data['shipping_country'] = $shipping_country;
		$need_data['order_goods_list'] = $order_goods_list;
		
		$need_data['goods_count'] = count($order_goods_list);
		
		//$order_info['order_status_id'] 13  平台介入退款
		$order_refund_historylist = array();
		$pingtai_deal = 0;
		
		//判断是否已经平台处理完毕   
		$order_refund_historylist = pdo_fetchall("select * from ".tablename('lionfish_comshop_order_refund_history')." where uniacid=:uniacid and order_id=:order_id order by addtime asc ", array(':uniacid' => $_W['uniacid'],':order_id' => $order_id));
		
		foreach($order_refund_historylist as $key => $val)
		{
			if($val['type'] ==3)
			{
				$pingtai_deal = 1;
			}
		}
		
		$order_refund = pdo_fetch("select * from ".tablename('lionfish_comshop_order_refund')." where uniacid=:uniacid and order_id=:order_id ",array(':uniacid' => $_W['uniacid'], ':order_id' => $order_id));
		
		if(!empty($order_refund))
		{
			$order_refund['addtime'] = date('Y-m-d H:i:s', $order_refund['addtime']);
		}
		
		$need_data['pick_up'] = $pick_up_info;
		
		if( empty($pick_order_info['qrcode']) && false)
		{
			//qrcode
			include_once SNAILFISH_VENDOR . 'Weixin/Jssdk.class.php';
			
			$appid = load_model_class('front')->get_config_by_name('wepro_appid'); 
			$appsecret = load_model_class('front')->get_config_by_name('wepro_appsecret'); 
			
			$jssdk = new Jssdk( $appid, $appsecret);
			
			$weqrcode = $jssdk->getWeQrcode($pick_order_info['pick_sn']);
			//保存图片
			//$srcfile = ATTACHMENT_ROOT .$srcfile;
			//attachment/images/2/2018/10
			$image_dir = ATTACHMENT_ROOT.'/images/2';
			$image_dir .= '/'.date('Y/m').'/';
			$file_path = 'images/2/'.date('Y/m').'/';
			load()->func('file');
			mkdirs($image_dir);
			
			$file_name = md5('qrcode_'.$pick_order_info['pick_sn'].time()).'.png';
			//qrcode
			file_put_contents($image_dir.$file_name, $weqrcode);
			
			$param	= array();
			$param['qrcode'] = tomedia($file_path.$file_name);
			 
			pdo_update('lionfish_comshop_pick_order', $param,   array('id' => $pick_order_info['id'],'uniacid' => $_W['uniacid']));
			
			$pick_order_info['qrcode'] = tomedia($file_path.$file_name);
		}
		
		$need_data['pick_order_info'] = $pick_order_info;
		
		
		echo json_encode( array('code' => 0,'data' => $need_data,'pingtai_deal' => $pingtai_deal,'order_refund' => $order_refund ) );
		die();
	}

}

?>
