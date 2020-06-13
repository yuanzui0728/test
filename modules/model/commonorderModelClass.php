<?php
if (!(defined('IN_IA'))) {
	exit('Access Denied');
}

/**
	粒度喜欢到订单商品中的某个商品数量
**/

class Commonorder_SnailFishShopModel
{
	public function __construct()
	{
	}
	
	//TODO....设置售后期的情况下进行确认收货，跟系统自动结算两个方法
	
	/**
		获取一个订单中，商品的数量，
	**/
	public function get_order_goods_quantity( $order_id,$order_goods_id=0,$uniacid = 0)
	{
		global $_W;
		global $_GPC;
		
		if( empty($uniacid) )
		{
			$uniacid = $_W['uniacid'];
		}
		
		$where = "";
		
		if( !empty($order_goods_id) && $order_goods_id >0 )
		{
			$where .= " and order_goods_id={$order_goods_id} ";
		}
		
		//原来有的数量
		$total_quantity = pdo_fetchcolumn("select sum(quantity)  from ".tablename('lionfish_comshop_order_goods').
							" where uniacid=:uniacid and order_id =:order_id {$where}  ", 
							array(':uniacid' => $uniacid, ':order_id' => $order_id ));
		
		$total_quantity = empty($total_quantity) ? 0 : $total_quantity;
		
		$refund_quantity = $this->refund_order_goods_quantity( $order_id,$order_goods_id,$uniacid);
		 
		 $surplus_quantity = $total_quantity - $refund_quantity;
		 
		 return $surplus_quantity;
	}
	
	/**
		已经退掉的订单商品数量
	**/
	public function refund_order_goods_quantity( $order_id,$order_goods_id=0,$uniacid = 0 )
	{
		global $_W;
		global $_GPC;
		
		if( empty($uniacid) )
		{
			$uniacid = $_W['uniacid'];
		}
		
		$where = "";
		
		if( !empty($order_goods_id) && $order_goods_id >0 )
		{
			$where .= " and order_goods_id={$order_goods_id} ";
		}
		
		$refund_quantity = pdo_fetchcolumn("select sum(quantity)  from ".tablename('lionfish_comshop_order_goods_refund').
							" where uniacid=:uniacid and order_id =:order_id {$where}  ", 
							array(':uniacid' => $uniacid, ':order_id' => $order_id ));
		
		$refund_quantity = empty($refund_quantity) ? 0 : $refund_quantity;
		
		return $refund_quantity;
	}
	
	/**
		该笔子订单已经退款了多少钱
	**/
	public function get_order_goods_refund_money( $order_id,$order_goods_id,$uniacid = 0 )
	{
		global $_W;
		global $_GPC;
		
		if( empty($uniacid) )
		{
			$uniacid = $_W['uniacid'];
		}
		
		$where = "";
		
		if( !empty($order_goods_id) && $order_goods_id >0 )
		{
			$where .= " and order_goods_id={$order_goods_id} ";
		}
		
		$refund_money = pdo_fetchcolumn("select sum(money)  from ".tablename('lionfish_comshop_order_goods_refund').
							" where uniacid=:uniacid and order_id =:order_id {$where}  ", 
							array(':uniacid' => $uniacid, ':order_id' => $order_id ));
		
		$refund_money = empty($refund_money) ? 0 : $refund_money;
		
		return $refund_money;
		
	}
	
	/**
		插入子订单退款
	**/
	public function ins_order_goods_refund($order_id, $order_goods_id,$total_quantity, $refund_quantity,$refund_money, $is_back_sellcount)
	{
		global $_W;
		global $_GPC;
		
		//计算需要抵扣多少佣金 ims_ lionfish_comshop_order
		
		$commiss_info = pdo_fetch(" select * from ".tablename('lionfish_community_head_commiss_order')." 
						where uniacid=:uniacid and order_id=:order_id and order_goods_id=:order_goods_id and type='orderbuy' limit 1", 
						array(':uniacid' => $_W['uniacid'], ':order_id' => $order_id, ':order_goods_id' => $order_goods_id ));
		
		// lionfish_comshop_order_goods
		
		$order_goods_info = pdo_fetch("select * from ".tablename('lionfish_comshop_order_goods')." where uniacid=:uniacid and order_goods_id=:order_goods_id ", 
							array(':uniacid' => $_W['uniacid'], ':order_goods_id' => $order_goods_id ));
							
		
		//order_status_id
		$order_info = pdo_fetch("select * from ".tablename('lionfish_comshop_order')." where uniacid=:uniacid and order_id=:order_id ", 
						array(':uniacid' => $_W['uniacid'], ':order_id' => $order_id ));
		
		$refund_data = array();
		$refund_data['order_goods_id'] = $order_goods_id;
		$refund_data['order_id'] = $order_id;
		$refund_data['uniacid'] = $_W['uniacid'];
		$refund_data['quantity'] = $refund_quantity;
		$refund_data['money'] = $refund_money;
		$refund_data['order_status_id'] = $order_info['order_status_id'];
		$refund_data['is_back_quantity'] = $is_back_sellcount;
		
		//---  以下需要计算了 refundorder
		$refund_data['back_score_for_money'] = 0;//退还积分兑换商品的积分 orderbuy
		$refund_data['back_send_score'] = 0; //退还赠送积分 goodsbuy
		$refund_data['back_head_orderbuycommiss'] = 0; //退还团长佣金
		$refund_data['back_head_supplycommiss'] = 0; //退还供应商佣金
		$refund_data['back_head_commiss_1'] = 0; //退1级团长佣金
		$refund_data['back_head_commiss_2'] = 0; //退2级团长佣金
		$refund_data['back_head_commiss_3'] = 0; //退3级团长佣金
		$refund_data['back_member_commiss_1'] = 0; //退会员1级佣金
		$refund_data['back_member_commiss_2'] = 0; //退会员2级佣金
		$refund_data['back_member_commiss_3'] = 0; //退会员3级佣金
		$refund_data['addtime'] = time(); //添加时间
		
		
		if( !empty($commiss_info) && $commiss_info['state'] == 1 )
		{
			//已经结算了
			
		}else{
			//未结算的
			$score_for_money_info = pdo_fetch("select * from ".tablename('lionfish_comshop_member_integral_flow')." 
									where uniacid=:uniacid and order_id=:order_id and order_goods_id=:order_goods_id and type='orderbuy' ", 
									array(':uniacid' => $_W['uniacid'],':order_id' => $order_id, ':order_goods_id' => $order_goods_id ));
			
			if( !empty($score_for_money_info) )
			{
				$refund_data['back_score_for_money'] =  intval( ($refund_quantity / $total_quantity ) * $score_for_money_info['score'] );
				//退回去给用户
				load_model_class('member')->sendMemberPointChange($order_info['member_id'],$refund_data['back_score_for_money'], 0 ,'退款'.$refund_quantity.'个商品，增加积分', $_W['uniacid'],'refundorder', $order_info['order_id'] ,$order_goods_id );
				
			}
			
			$send_score_info = pdo_fetch("select * from ".tablename('lionfish_comshop_member_integral_flow')." 
									where uniacid=:uniacid and order_id=:order_id and order_goods_id=:order_goods_id and type='goodsbuy' ", 
									array(':uniacid' => $_W['uniacid'],':order_id' => $order_id, ':order_goods_id' => $order_goods_id ));
			
			if( !empty($send_score_info) )
			{
				$refund_data['back_send_score'] =  intval( ($refund_quantity / $total_quantity ) * $send_score_info['score'] );
				
				$refund_data['back_send_score'] = $refund_data['back_send_score'] <= 0 ? 0 : $refund_data['back_send_score'];
				//减去相应的分数，然后插入
				
				$up_total_sql = "update ".tablename('lionfish_comshop_member_integral_flow')." SET score = (score - " . $refund_data['back_send_score'] . ") where order_id={$order_id} and type='goodsbuy' and order_goods_id=".$order_goods_id;
				
				pdo_query($up_total_sql);
			}
			
			//$refund_data['back_head_orderbuycommiss'] = 0; //退还团长佣金
			
			$head_commisslist = pdo_fetchall("select * from ".tablename('lionfish_community_head_commiss_order').
				" where uniacid=:uniacid and type in ('orderbuy','commiss') and order_id=:order_id and order_goods_id=:order_goods_id and state=0 ", 
				array(':uniacid' => $_W['uniacid'], ':order_id' => $order_id, ':order_goods_id' => $order_goods_id ));
			
			if( !empty($head_commisslist) )
			{
				foreach( $head_commisslist as $val )
				{
					if( $val['type'] == 'orderbuy' )
					{
						$head_orderbuycommiss = round( ($refund_quantity / $total_quantity ) * $val['money'] , 2);
						
						$head_orderbuycommiss = $head_orderbuycommiss <= 0 ? 0 : $head_orderbuycommiss;
						
						$up_total_sql = "update ".tablename('lionfish_community_head_commiss_order')." SET money = (money - " . $head_orderbuycommiss . ") where id=".$val['id'];
							
						pdo_query($up_total_sql);
						
						$refund_data['back_head_orderbuycommiss'] = $head_orderbuycommiss;
					}
					if( $val['type'] == 'commiss' )
					{
						if( $val['level'] == 1 )
						{
							$head_levelcommiss = round( ($refund_quantity / $total_quantity ) * $val['money'] , 2);
						
							$head_levelcommiss = $head_levelcommiss <= 0 ? 0 : $head_levelcommiss;
							
							$up_total_sql = "update ".tablename('lionfish_community_head_commiss_order')." SET money = (money - " . $head_levelcommiss . ") where id=".$val['id'];
					
							pdo_query($up_total_sql);
							
							$refund_data['back_head_commiss_1'] = $head_levelcommiss;
						}
						if( $val['level'] == 2 )
						{
							$head_levelcommiss = round( ($refund_quantity / $total_quantity ) * $val['money'] , 2);
						
							$head_levelcommiss = $head_levelcommiss <= 0 ? 0 : $head_levelcommiss;
							
							$up_total_sql = "update ".tablename('lionfish_community_head_commiss_order')." SET money = (money - " . $head_levelcommiss . ") where id=".$val['id'];
					
							pdo_query($up_total_sql);
							
							$refund_data['back_head_commiss_2'] = $head_levelcommiss;
						}
						if( $val['level'] == 3 )
						{
							$head_levelcommiss = round( ($refund_quantity / $total_quantity ) * $val['money'] , 2);
						
							$head_levelcommiss = $head_levelcommiss <= 0 ? 0 : $head_levelcommiss;
							
							$up_total_sql = "update ".tablename('lionfish_community_head_commiss_order')." SET money = (money - " . $head_levelcommiss . ") where id=".$val['id'];
					
							pdo_query($up_total_sql);
							
							$refund_data['back_head_commiss_3'] = $head_levelcommiss;
						}
					}
				}
			}
			
			//back_head_supplycommiss    ims_lionfish_supply_commiss_order ims_ 
			
			$supply_commisslist = pdo_fetch("select * from ".tablename('lionfish_supply_commiss_order').
				" where uniacid=:uniacid and  order_id=:order_id and order_goods_id=:order_goods_id and state=0 ", 
				array(':uniacid' => $_W['uniacid'], ':order_id' => $order_id, ':order_goods_id' => $order_goods_id ));
			
			if( !empty($supply_commisslist) )
			{
				$supply_orderbuycommiss = round( ($refund_quantity / $total_quantity ) * $supply_commisslist['total_money'] , 2);
				$supply_orderbuycommiss_money = round( ($refund_quantity / $total_quantity ) * $supply_commisslist['money'] , 2);
						
				$supply_orderbuycommiss = $supply_orderbuycommiss <= 0 ? 0 : $supply_orderbuycommiss;
				$supply_orderbuycommiss_money = $supply_orderbuycommiss_money <= 0 ? 0 : $supply_orderbuycommiss_money;
				
				$up_total_sql = "update ".tablename('lionfish_supply_commiss_order')." SET money = (money - " . $supply_orderbuycommiss_money . "),total_money = (total_money - " . $supply_orderbuycommiss . ") where id=".$supply_commisslist['id'];
		
				pdo_query($up_total_sql);
				
				$refund_data['back_head_supplycommiss'] = $supply_orderbuycommiss;
			}
			
			// 
			
			//$refund_data['back_member_commiss_1'] = 0; //退会员1级佣金
			//$refund_data['back_member_commiss_2'] = 0; //退会员2级佣金
			//$refund_data['back_member_commiss_3'] = 0; //退会员3级佣金
			
			
			$member_commisslist = pdo_fetchall("select * from ".tablename('lionfish_comshop_member_commiss_order').
				" where uniacid=:uniacid  and order_id=:order_id and order_goods_id=:order_goods_id and state=0 ", 
				array(':uniacid' => $_W['uniacid'], ':order_id' => $order_id, ':order_goods_id' => $order_goods_id ));
			
			if( !empty($member_commisslist) )
			{
				foreach( $member_commisslist as $val )
				{
						if( $val['level'] == 1 )
						{
							$member_levelcommiss = round( ($refund_quantity / $total_quantity ) * $val['money'] , 2);
						
							$member_levelcommiss = $member_levelcommiss <= 0 ? 0 : $member_levelcommiss;
							
							$up_total_sql = "update ".tablename('lionfish_comshop_member_commiss_order')." SET money = (money - " . $member_levelcommiss . ") where id=".$val['id'];
					
							pdo_query($up_total_sql);
							
							$refund_data['back_member_commiss_1'] = $member_levelcommiss;
						}
						if( $val['level'] == 2 )
						{
							$member_levelcommiss = round( ($refund_quantity / $total_quantity ) * $val['money'] , 2);
						
							$member_levelcommiss = $member_levelcommiss <= 0 ? 0 : $member_levelcommiss;
							
							$up_total_sql = "update ".tablename('lionfish_comshop_member_commiss_order')." SET money = (money - " . $member_levelcommiss . ") where id=".$val['id'];
					
							pdo_query($up_total_sql);
							
							$refund_data['back_member_commiss_2'] = $member_levelcommiss;
						}
						if( $val['level'] == 3 )
						{
							$member_levelcommiss = round( ($refund_quantity / $total_quantity ) * $val['money'] , 2);
						
							$member_levelcommiss = $member_levelcommiss <= 0 ? 0 : $member_levelcommiss;
							
							$up_total_sql = "update ".tablename('lionfish_comshop_member_commiss_order')." SET money = (money - " . $member_levelcommiss . ") where id=".$val['id'];
					
							pdo_query($up_total_sql);
							
							$refund_data['back_member_commiss_3'] = $member_levelcommiss;
						}
				}
			}
			
			//INSERT
			pdo_insert('lionfish_comshop_order_goods_refund', $refund_data);
			$id = pdo_insertid();
			
			
			$up_total_sql = "update ".tablename('lionfish_comshop_order_goods')." SET has_refund_money = (has_refund_money + " . $refund_money . ") ,has_refund_quantity=(has_refund_quantity+".$refund_quantity.") where order_goods_id=".$order_goods_id;
					
			pdo_query($up_total_sql);
							
			//has_refund_money
			
			return $id;
		}
		
	}
	
	/**
		后台订单详情 部分商品退款操作，检测是否整单退款
	**/
	public function check_refund_order_goods_status($order_id, $order_goods_id, $refund_money,$is_back_sellcount, $refund_quantity,$is_refund_shippingfare)
	{
		global $_W;
		global $_GPC;
		
		//   quantity
		$refund_total_quantity =  pdo_fetchcolumn("select sum(quantity) as quantity from ".tablename('lionfish_comshop_order_goods_refund').
					" where uniacid=:uniacid and order_id=:order_id and order_goods_id=:order_goods_id ", 
				array(':uniacid' => $_W['uniacid'], ':order_id' =>$order_id, ':order_goods_id' => $order_goods_id ) );
			
		$order_goods_info = pdo_fetch("select * from ".tablename('lionfish_comshop_order_goods')." where uniacid=:uniacid and order_goods_id=:order_goods_id ", 
							array(':uniacid' => $_W['uniacid'], ':order_goods_id' => $order_goods_id ));
							
		$order_info = pdo_fetch("select * from ".tablename('lionfish_comshop_order')." where uniacid=:uniacid and order_id=:order_id ", 
						array(':uniacid' => $_W['uniacid'], ':order_id' => $order_id ));
		
		
		if( $refund_total_quantity >= $order_goods_info['quantity'] || $order_goods_info['has_refund_money'] >= $order_goods_info['total'])
		{
			pdo_update('lionfish_comshop_order_goods',array('is_refund_state' => 1 ) , 
				array('order_goods_id' => $order_goods_id ,'uniacid' => $_W['uniacid'] ));
			
			
			$order_goods_list = pdo_fetchall("select * from ".tablename('lionfish_comshop_order_goods')." 
							where uniacid=:uniacid and order_id=:order_id ", 
							array(':uniacid' => $_W['uniacid'], ':order_id' => $order_id ));
			
			$is_all_refund = true;
			
			foreach($order_goods_list as $val )
			{
				if($val['is_refund_state'] != 1)
				{
					$is_all_refund = false;
				}
			}
			
			if($is_all_refund)
			{
				$comment = '后台操作立即退款,退款金额:'.$refund_money.'元';
			
				if( $order_info['type'] == 'integral' )
				{
					if( $order_info['shipping_fare'] > 0 )
					{
						$comment = '后台操作子订单退款,退款金额:'.$order_info['shipping_fare'].'元，积分:'.$order_info['total'];
					}else{
						$comment = '后台操作子订单退款,退还积分:'.$order_info['total'];
					}
				}
				
				if($is_refund_shippingfare == 1)
				{
					$comment .= '. 退配送费：'.$order_goods_info['shipping_fare'].'元';
				}
				
				if($is_back_sellcount == 1)
				{
					$comment .= '. 退库存：'.$refund_quantity;
				}else{
					$comment .= '. 不退库存';
				}
				
				
				$order_history = array();
				$order_history['uniacid'] = $_W['uniacid'];
				$order_history['order_id'] = $order_id;
				$order_history['order_status_id'] = 7;
				$order_history['notify'] = 0;
				$order_history['comment'] =  $comment;
				$order_history['date_added'] = time();
			
				
				pdo_insert('lionfish_comshop_order_history', $order_history);
				
				pdo_update('lionfish_comshop_order',array('order_status_id' => 7) , 
							array('order_id' => $order_id));
				
				//$data[''] = isset($data['is_print_admin_cancleorder']) ? $data['is_print_admin_cancleorder'] : 0;
				$is_print_admin_cancleorder = load_model_class('front')->get_config_by_name('is_print_admin_cancleorder');
				
				if( isset($is_print_admin_cancleorder) && $is_print_admin_cancleorder == 1 )
				{
					load_model_class('printaction')->check_print_order($order_id,$_W['uniacid'],'后台操作取消订单');
				}
				
			}
			
		}else{
			
				
		}
		
		
		
	}
	
	/**
		整单退款，切割退款金额到子订单
	**/
	public  function def_order_refund_togoods( $order_id, $refund_money,$free_tongji,$is_refund_shippingfare )
	{
		global $_W;
		global $_GPC;
		
		
		
		
	}
	
	
}


?>