<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class Delivery_Snailfishshop extends AdminController
{
	public function main()
	{
		global $_W;
        global $_GPC;
      
		$this->delivery();
	}
	
	public function delivery()
	{
		global $_W;
        global $_GPC;
		
		$uniacid            = $_W['uniacid'];
        $params[':uniacid'] = $uniacid;

        $pindex    = max(1, intval($_GPC['page']));
        $psize     = 20;
		
		$searchtime = isset($_GPC['searchtime']) ? $_GPC['searchtime'] : '';
		$starttime = isset($_GPC['time']['start']) ? strtotime($_GPC['time']['start']) : strtotime(date('Y-m-d'.' 00:00:00'));
		$endtime = isset($_GPC['time']['end']) ? strtotime($_GPC['time']['end']) : strtotime(date('Y-m-d'.' 23:59:59'));
		
		$condition = "";
		
		if( !empty($searchtime) )
		{
			if(  $searchtime == 'create_time')
			{
				$condition .= " and d.create_time > {$starttime} and d.create_time < {$endtime} ";
			}
			if( $searchtime == 'express_time')
			{
				$condition .= " and d.express_time > {$starttime} and d.express_time < {$endtime} ";
			}
			if( $searchtime == 'head_get_time')
			{
				$condition .= " and d.head_get_time > {$starttime} and d.head_get_time < {$endtime} ";
			}
		}
		

        if (!empty($_GPC['keyword'])) {
            $_GPC['keyword'] = trim($_GPC['keyword']);
            $condition .= ' and (d.head_name like :keyword or d.head_mobile like :keyword or d.line_name like :keyword or d.clerk_name like :keyword or d.clerk_mobile like :keyword or h.community_name like :keyword  )';
            $params[':keyword'] = '%' . $_GPC['keyword'] . '%';
        }
		
		
		if( isset($_GPC['export']) && $_GPC['export'] > 0 )
		{
			@set_time_limit(0);
			
			$excel_title = "";
			$search_tiaoj = "";
			
			if( !empty($searchtime) )
			{
				if(  $searchtime == 'create_time')
				{
					$excel_title .= "创建清单时间:".date('Y-m-d H:i:s', $starttime).'  '.date('Y-m-d H:i:s', $endtime);
					
					$search_tiaoj .= "清单时间： ".date('Y-m-d H:i:s', $starttime).'  '.date('Y-m-d H:i:s', $endtime);
				}
				if( $searchtime == 'express_time')
				{
					$excel_title .= "配送时间:".date('Y-m-d H:i:s', $starttime).'  '.date('Y-m-d H:i:s', $endtime);
					$search_tiaoj .= "配送时间： ".date('Y-m-d H:i:s', $starttime).'  '.date('Y-m-d H:i:s', $endtime);
				}
				if( $searchtime == 'head_get_time')
				{
					$excel_title .= "送达时间:".date('Y-m-d H:i:s', $starttime).'  '.date('Y-m-d H:i:s', $endtime);
					$search_tiaoj .= "送达时间： ".date('Y-m-d H:i:s', $starttime).'  '.date('Y-m-d H:i:s', $endtime);
				}
			}
			$excel_title = "";
			if (!empty($_GPC['keyword'])) {
				$excel_title .= $_GPC['keyword'];
				$search_tiaoj .= "关键词： ".$_GPC['keyword'];
			}
		
			
			$list = pdo_fetchall('SELECT d.*,h.community_name FROM ' . tablename('lionfish_comshop_deliverylist') . " as d , ".tablename('lionfish_community_head')." as h 
				WHERE d.uniacid=:uniacid and d.head_id = h.id " . $condition . ' order by d.id desc ', $params);
		
		
			//导出商品总单
			if($_GPC['export'] == 1)
			{
				$columns = array(
					
					array('title' => '商品名称', 'field' => 'goods_name', 'width' => 24),
					array('title' => '商品规格', 'field' => 'sku_name', 'width' => 24),
					array('title' => '数量', 'field' => 'quantity', 'width' => 12),
					array('title' => '单价', 'field' => 'price', 'width' => 12),
					array('title' => '金额', 'field' => 'total_price', 'width' => 12),
				);
				
				$list_id_arr = array();
				foreach($list as $val)
				{
					$list_id_arr[] = $val['id'];
				}
				
				$goods_list = pdo_fetchall("select * from ".tablename('lionfish_comshop_deliverylist_goods')." where uniacid=:uniacid and list_id in ( ".implode(',',$list_id_arr )." ) ",
								array(':uniacid' => $_W['uniacid']));
				
				$need_goods_list = array();
				
				foreach($goods_list as $val)
				{
					if(empty($need_goods_list) || !in_array(  $val['goods_id'].'_'.$val['rela_goodsoption_valueid'] , array_keys($need_goods_list) ) )
					{
						//goods_id   rela_goodsoption_valueid 
						$price = 0;
						if( !empty($val['rela_goodsoption_valueid']) )
						{
							
							$price_value = pdo_fetch( "select * from ".tablename('lionfish_comshop_order_goods').
											" where uniacid=:uniacid and goods_id=:goods_id and rela_goodsoption_valueid=:rela_goodsoption_valueid  order by order_goods_id desc ", 
											array(':uniacid' => $_W['uniacid'], ':goods_id' =>$val['goods_id'], ':rela_goodsoption_valueid' => $val['rela_goodsoption_valueid'] ) );
							
							$price = $price_value['price'];
						}else{
							
							$price_value = pdo_fetch("select price from ".tablename('lionfish_comshop_goods')." where uniacid=:uniacid and id=:id ",
											array(':uniacid' => $_W['uniacid'], ':id' => $val['goods_id'] ));
							$price = $price_value['price'];
						}
						
						$need_goods_list[ $val['goods_id'].'_'.$val['rela_goodsoption_valueid'] ] = array('quantity' => $val['goods_count'],'price' => $price,'total_price' => ($val['goods_count'] * $price),'sku_name' => $val['sku_str'],'goods_name' => $val['goods_name']);
					
					
					}else{
						$need_goods_list[ $val['goods_id'].'_'.$val['rela_goodsoption_valueid'] ]['quantity'] += $val['goods_count'];
						$need_goods_list[ $val['goods_id'].'_'.$val['rela_goodsoption_valueid'] ]['total_price'] = $need_goods_list[ $val['goods_id'].'_'.$val['rela_goodsoption_valueid'] ]['quantity'] * $need_goods_list[ $val['goods_id'].'_'.$val['rela_goodsoption_valueid'] ]['price'];
					}
				}
				
				$last_index_sort = array_column($need_goods_list,'goods_name');
				array_multisort($last_index_sort,SORT_ASC,$need_goods_list);
				
				$lists_info = array(
									'line1' => '商品拣货单',
									'line2' => '检索条件: '.$search_tiaoj,
								);
				
				load_model_class('excel')->export_delivery_goodslist($need_goods_list, array('list_info' => $lists_info,'title' => '商品拣货单', 'columns' => $columns));
				
			}
			//导出团长总单
			if($_GPC['export'] == 2)
			{
				//导出配送总单
				
				$columns = array(
					array('title' => '序号', 'field' => 'sn', 'width' => 12),
					array('title' => '商品编码', 'field' => 'goods_goodssn', 'width' => 20),
					array('title' => '商品名称', 'field' => 'goods_name', 'width' => 24),
					array('title' => '数量', 'field' => 'quantity', 'width' => 12),
					array('title' => '商品规格', 'field' => 'sku_name', 'width' => 24),
					array('title' => '单价', 'field' => 'price', 'width' => 12),
					array('title' => '总价', 'field' => 'total_price', 'width' => 12),
				);
				
				//-----------------  这里要合并开始 downexcel---------------------
				
				$tuanz_data_list = array();
				$exportlist = array();
				
				$list_id_arr = array();
				foreach($list as $val)
				{
					$list_id = $val['id'];
					
					$params_arr = array();
					$uniacid            = $_W['uniacid'];
					$params_arr[':uniacid'] = $uniacid;
					$params_arr[':list_id'] = $list_id;

					$condition = " and del.list_id=:list_id ";
					
					$list_data = pdo_fetchall('SELECT del.*,gds.codes FROM ' . tablename('lionfish_comshop_deliverylist_goods') . ' as del left join ' . tablename('lionfish_comshop_goods') . " gds on gds.id = del.goods_id  \r\n 
					WHERE del.uniacid=:uniacid " . $condition . ' order by del.id desc ', $params_arr);
					
				
				    
					$list_info = pdo_fetch("select * from ".tablename('lionfish_comshop_deliverylist')." where uniacid=:uniacid and id =:list_id ", 
							array(':uniacid' => $_W['uniacid'], ':list_id' => $list_id ));
					
					if( $val['clerk_id'] > 0)
					{
						$clerk_name = pdo_fetch('SELECT * FROM ' . tablename('lionfish_comshop_deliveryclerk') . ' WHERE uniacid=:uniacid and id=:clerk_id ' , 
									array(':uniacid' => $_W['uniacid'], ':clerk_id' => $val['clerk_id'] ));
						$list_info['clerk_info'] = $clerk_name['name'];
					}
			
					if( !isset($exportlist[$list_info['head_id']]) )
					{
						$exportlist[$list_info['head_id']] = array('list_info' => $list_info ,'data' => array() );
					}
					
					$i =1;
					foreach($list_data as $val)
					{
						$tmp_exval = array();
						$tmp_exval['num_no'] = $i;
						$tmp_exval['name'] = $val['goods_name'];
						$tmp_exval['goods_goodssn'] = $val['codes'];
						$tmp_exval['quantity'] = $val['goods_count'];
						$tmp_exval['sku_str'] = $val['sku_str'];
						
						$info = pdo_fetch("select price from ".tablename('lionfish_comshop_order_goods').
									" where goods_id=:goods_id and uniacid=:uniacid and rela_goodsoption_valueid=:rela_goodsoption_valueid order by order_goods_id desc ", 
									array(':uniacid' =>$uniacid,':goods_id' => $val['goods_id'],':rela_goodsoption_valueid' => $val['rela_goodsoption_valueid'] ) );
						
						
						$tmp_exval['price'] = $info['price'];
						$tmp_exval['total_price'] = round($info['price'] * $val['goods_count'],2) ;
						
						//goods_id  rela_goodsoption_valueid
						
						if( isset($exportlist[$list_info['head_id']]['data'][ $val['goods_id'].'_'.$val['rela_goodsoption_valueid'] ]) )
						{
							$tmp_exp = $exportlist[$list_info['head_id']]['data'][ $val['goods_id'].'_'.$val['rela_goodsoption_valueid'] ];
							
							$tmp_exval['quantity'] += $tmp_exp['quantity'];
							$tmp_exval['total_price'] = round($info['price'] * $tmp_exval['quantity'],2) ;
							
							$exportlist[$list_info['head_id']]['data'][ $val['goods_id'].'_'.$val['rela_goodsoption_valueid'] ] = $tmp_exval;
						}else{
							$exportlist[$list_info['head_id']]['data'][ $val['goods_id'].'_'.$val['rela_goodsoption_valueid'] ] = $tmp_exval;
						}
						
						//$exportlist[] = $tmp_exval;
						$i++;
					}
				}
				
				foreach( $exportlist as $key => $val )
				{
					$s_data = $val['data'];
					
					$last_index_sort = array_column($s_data,'name');
					array_multisort($last_index_sort,SORT_ASC,$s_data);
				
					$val['data'] = $s_data;
					
					$exportlist[$key] = $val;
				}
				
				$columns = array(
					array('title' => '序号', 'field' => 'num_no', 'width' => 12),
					array('title' => '商品编码', 'field' => 'goods_goodssn', 'width' => 20),
					array('title' => '商品名称', 'field' => 'name', 'width' => 24),
					array('title' => '数量', 'field' => 'quantity', 'width' => 12),
					array('title' => '规格', 'field' => 'sku_str', 'width' => 24),
					array('title' => '单价', 'field' => 'price', 'width' => 24),
					array('title' => '总价', 'field' => 'total_price', 'width' => 24),
				);
				
				
				
				//$params['list_info']
				
				$lists_info = array(
									'line1' => $list_info['head_name'],//团老大
									'line2' => '团长：'.$list_info['head_name'].'     提货地址：'.$list_info['head_address'].'     联系电话：'.$list_info['head_mobile'],//团长：团老大啦     提货地址：湖南大剧院     联系电话：13000000000
									'line3' => '配送单：'.$list_info['list_sn'].'     时间：'.date('Y-m-d H:i:s', $list_info['create_time']),
									'line4' => '配送路线：'.$list_info['line_name'].'     配送员：'.$list_info['clerk_name'],
								);
				
				
				
				load_model_class('excel')->export_delivery_list_pi($exportlist, array('list_info' => $lists_info,'title' => '批量导出清单数据', 'columns' => $columns));
		
				//-------------------这里要合并结束----------------------
				
			}
			//导出团长旗下订单
			if($_GPC['export'] == 3)
			{
				
				//导出配送总单
				
				$columns = array(
					array('title' => '序号', 'field' => 'sn', 'width' => 12),
					array('title' => '商品编码', 'field' => 'goods_goodssn', 'width' => 20), 
					array('title' => '商品名称', 'field' => 'goods_name', 'width' => 24),
					array('title' => '商品规格', 'field' => 'sku_name', 'width' => 24),
					
					array('title' => '单价', 'field' => 'price', 'width' => 12),
					array('title' => '总价', 'field' => 'total_price', 'width' => 12),
					array('title' => '订购数', 'field' => 'quantity', 'width' => 12),
					array('title' => '团长', 'field' => 'head_name', 'width' => 12),
					array('title' => '合计数量', 'field' => 'total_quantity', 'width' => 12),
					
				);
				
				//-----------------  这里要合并开始 downexcel---------------------
				
				$tuanz_data_list = array();
				$exportlist = array();
				
				$list_id_arr = array();
				
				
				foreach($list as $val)
				{
					$list_id = $val['id'];
					
					
					$list_data = pdo_fetchall("select * from ".tablename('lionfish_comshop_deliverylist_goods')." where uniacid=:uniacid and list_id =:list_id order by id desc ", 
							array(':uniacid' => $_W['uniacid'], ':list_id' => $list_id ));
					 
					$list_info = pdo_fetch("select * from ".tablename('lionfish_comshop_deliverylist')." where uniacid=:uniacid and id =:list_id ", 
							array(':uniacid' => $_W['uniacid'], ':list_id' => $list_id ));
					
					
					
					if( $val['clerk_id'] > 0)
					{	
						
						$clerk_name = pdo_fetch("select * from ".tablename('lionfish_comshop_deliveryclerk')." where uniacid=:uniacid and id =:clerk_id ", 
							array(':uniacid' => $_W['uniacid'], ':clerk_id' => $val['clerk_id'] ));
						
						$list_info['clerk_info'] = $clerk_name['name'];
					}
			
					if( !isset($exportlist[$list_info['head_id']]) )
					{
						$exportlist[$list_info['head_id']] = array('list_info' => $list_info ,'data' => array() );
					}
					
					$i =1;
					foreach($list_data as $val)
					{
						$tmp_exval = array();
						$tmp_exval['num_no'] = $i;
						$tmp_exval['name'] = $val['goods_name'];
						$tmp_exval['quantity'] = $val['goods_count'];
						$tmp_exval['sku_str'] = $val['sku_str'];
						
						
						$gd_info = pdo_fetch("select codes from ".tablename('lionfish_comshop_goods')." where uniacid=:uniacid and id =:goods_id ", 
							array(':uniacid' => $_W['uniacid'], ':goods_id' => $val['goods_id'] ));
						
						
						$tmp_exval['goods_goodssn'] = $gd_info['codes'];		
						
						$info = pdo_fetch("select price from ".tablename('lionfish_comshop_order_goods')." where uniacid=:uniacid and rela_goodsoption_valueid =:rela_goodsoption_valueid and goods_id =:goods_id order by order_goods_id desc ", 
							array(':uniacid' => $_W['uniacid'], ':goods_id' => $val['goods_id'], ':rela_goodsoption_valueid' => $val['rela_goodsoption_valueid'] ));
						
						$tmp_exval['price'] = $info['price'];
						$tmp_exval['total_price'] = round($info['price'] * $val['goods_count'],2) ;
						
						//goods_id  rela_goodsoption_valueid
						
						if( isset($exportlist[$list_info['head_id']]['data'][ $val['goods_id'].'_'.$val['rela_goodsoption_valueid'] ]) )
						{
							$tmp_exp = $exportlist[$list_info['head_id']]['data'][ $val['goods_id'].'_'.$val['rela_goodsoption_valueid'] ];
							
							$tmp_exval['quantity'] += $tmp_exp['quantity'];
							$tmp_exval['total_price'] = round($info['price'] * $tmp_exval['quantity'],2) ;
							
							$exportlist[$list_info['head_id']]['data'][ $val['goods_id'].'_'.$val['rela_goodsoption_valueid'] ] = $tmp_exval;
						}else{
							$exportlist[$list_info['head_id']]['data'][ $val['goods_id'].'_'.$val['rela_goodsoption_valueid'] ] = $tmp_exval;
						}
						
						//$exportlist[] = $tmp_exval;
						$i++;
					}
				}
				
				
				$new_need_goods_list = array();
				
				
				foreach($exportlist as $val)
				{
					$head_name = $val['list_info']['head_name'];
					
					
					$head_id = $val['list_info']['head_id'];
					
					foreach($val['data'] as $gid_skuid => $goods_info)
					{
						if( empty($new_need_goods_list) || !isset($new_need_goods_list[$gid_skuid]) )
						{
							//新签
							$new_need_goods_list[$gid_skuid] = array();
							$new_need_goods_list[$gid_skuid]['goods_name'] = $goods_info['name'];
							$new_need_goods_list[$gid_skuid]['sku_str'] = $goods_info['sku_str'];
							$new_need_goods_list[$gid_skuid]['goods_goodssn'] = $goods_info['goods_goodssn'];
							$new_need_goods_list[$gid_skuid]['goods_count'] = $goods_info['quantity'];
							$new_need_goods_list[$gid_skuid]['head_goods_list'] = array();
							$new_need_goods_list[$gid_skuid]['head_goods_list'][$head_id] = array(
																								'price' => $goods_info['price'],
																								'total_price' => $goods_info['total_price'],
																								'buy_quantity' => $goods_info['quantity'],
																								'head_name' => $head_name,
																								'total_quatity' => $goods_info['quantity']
																							);
						}else if( isset($new_need_goods_list[$gid_skuid]) ){
							//续签
							
							$new_need_goods_list[$gid_skuid]['goods_count'] += $goods_info['quantity'];
							
							if( isset($new_need_goods_list[$gid_skuid]['head_goods_list'][$head_id]) )
							{
								$new_need_goods_list[$gid_skuid]['head_goods_list'][$head_id]['price'] = $goods_info['price'];
								$new_need_goods_list[$gid_skuid]['head_goods_list'][$head_id]['total_price'] += $goods_info['total_price'];
								$new_need_goods_list[$gid_skuid]['head_goods_list'][$head_id]['buy_quantity'] += $goods_info['buy_quantity'];
								$new_need_goods_list[$gid_skuid]['head_goods_list'][$head_id]['total_quatity'] += $goods_info['total_quatity'];
							}else{
								
								$new_need_goods_list[$gid_skuid]['head_goods_list'][$head_id] = array(
																								'price' => $goods_info['price'],
																								'total_price' => $goods_info['total_price'],
																								'buy_quantity' => $goods_info['quantity'],
																								'head_name' => $head_name,
																								'total_quatity' => $goods_info['quantity']
																							);
							}
							
						}
					}
				}
				
			
				
				$lists_info = array(
									'line1' => $list_info['head_name'],//团老大
									'line2' => '团长：'.$list_info['head_name'].'     提货地址：'.$list_info['head_address'].'     联系电话：'.$list_info['head_mobile'],//团长：团老大啦     提货地址：湖南大剧院     联系电话：13000000000
									'line3' => '配送单：'.$list_info['list_sn'].'     时间：'.date('Y-m-d H:i:s', $list_info['create_time']),
									'line4' => '配送路线：'.$list_info['line_name'].'     配送员：'.$list_info['clerk_name'],
								);
				
				
				
				array_multisort($new_need_goods_list,SORT_ASC);
				
				
				load_model_class('excel')->export_delivery_list_pinew($new_need_goods_list, array('list_info' => $lists_info,'title' => '商品拣货单', 'columns' => $columns));
				//-------------------这里要合并结束----------------------
			
			}
			//导出配货单
			if($_GPC['export'] == 4)
			{
				
			}
			
			//var_dump( $list );die();
			//load_model_class('excel')->export_delivery_list($exportlist, array('list_info' => $lists_info,'title' => '清单数据', 'columns' => $columns));
			//die();
		}
		
		/**	
		$head_info = pdo_fetch("select * from ".tablename('lionfish_community_head')." where uniacid=:uniacid and id=:head_id ",  
				array(':uniacid' => $_W['uniacid'], ':head_id' => $val['head_id']));
		
		$val['community_name'] = $head_info['community_name'];
		**/
		
        $list = pdo_fetchall('SELECT d.*,h.community_name FROM ' . tablename('lionfish_comshop_deliverylist') . " as d , ".tablename('lionfish_community_head')." as h 
				WHERE d.uniacid=:uniacid and d.head_id = h.id " . $condition . ' order by d.id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize, $params);
		//order_count
		
		if( !empty($list) )
		{
			foreach($list as $key => $val )
			{
				
				
				$head_info = pdo_fetch("select * from ".tablename('lionfish_community_head')." where uniacid=:uniacid and id=:head_id ",  
						array(':uniacid' => $_W['uniacid'], ':head_id' => $val['head_id']));
				
				//$val['community_name'] = $head_info['community_name'];
				
				$order_count = pdo_fetchcolumn("select count(1) from ".tablename('lionfish_comshop_deliverylist_order')." 
									where uniacid=:uniacid and list_id=:list_id ", array(':uniacid' => $_W['uniacid'],':list_id' => $val['id']));
				$val['order_count'] = $order_count;
				
				$list[$key] = $val;
			}
		}
       
	    $total = pdo_fetchcolumn('SELECT count(1) FROM ' . tablename('lionfish_comshop_deliverylist') ." as d , ".tablename('lionfish_community_head')." as h ". ' WHERE d.uniacid=:uniacid and d.head_id = h.id ' . $condition, $params);

        $pager = pagination2($total, $pindex, $psize);
		
		include $this->display('delivery/delivery');
	}
	
	
	public function downorderexcel()
	{
		global $_W;
        global $_GPC;
		
		$list_id = $_GPC['list_id'];
		
		
		$paras =array();
		
		$uniacid = $_W['uniacid'];
		
		$sqlcondition = "";
		
		$condition = " o.uniacid = {$uniacid} ";
		
		$order_ids_arr = pdo_fetchall("select * from ".tablename('lionfish_comshop_deliverylist_order')." where list_id=:list_id ", 
							array(':list_id' => $list_id));
		
		$order_ids = array();
		
		foreach($order_ids_arr as $vv)
		{
			$order_ids[] = $vv['order_id'];
		}
		
		$condition .= " and o.order_id in (".implode(',',$order_ids ).") ";
		
		
		$sql = 'SELECT count(o.order_id) as count FROM ' . tablename('lionfish_comshop_order') . ' as o  '.$sqlcondition.' where ' .  $condition ;
		$total = pdo_fetchcolumn($sql,$paras);
		
		
		$order_status_arr = load_model_class('order')->get_order_status_name();
		
		
		
			@set_time_limit(0);
			$columns = array(
				array('title' => '订单编号', 'field' => 'order_num_alias', 'width' => 24),
				array('title' => '昵称', 'field' => 'name', 'width' => 12),
				//array('title' => '会员姓名', 'field' => 'mrealname', 'width' => 12),
				array('title' => 'openid', 'field' => 'openid', 'width' => 24),
				array('title' => '会员手机号', 'field' => 'telephone', 'width' => 12),
				array('title' => '收货姓名(或自提人)', 'field' => 'shipping_name', 'width' => 12),
				array('title' => '联系电话', 'field' => 'shipping_tel', 'width' => 12),
				array('title' => '收货地址', 'field' => 'address_province', 'width' => 12),
				array('title' => '', 'field' => 'address_city', 'width' => 12),
				array('title' => '', 'field' => 'address_area', 'width' => 12),
				//array('title' => '', 'field' => 'address_street', 'width' => 12),
				array('title' => '', 'field' => 'address_address', 'width' => 12),
				array('title' => '商品名称', 'field' => 'goods_title', 'width' => 24),
				//array('title' => '商品编码', 'field' => 'goods_goodssn', 'width' => 12),
				array('title' => '商品规格', 'field' => 'goods_optiontitle', 'width' => 12),
				array('title' => '商品数量', 'field' => 'quantity', 'width' => 12),
				array('title' => '商品单价', 'field' => 'goods_price1', 'width' => 12),
				//array('title' => '商品单价(折扣后)', 'field' => 'goods_price2', 'width' => 12),
				//array('title' => '商品价格(折扣前)', 'field' => 'goods_rprice1', 'width' => 12),
				array('title' => '商品价格', 'field' => 'goods_rprice2', 'width' => 12),
				array('title' => '支付方式', 'field' => 'paytype', 'width' => 12),
				array('title' => '配送方式', 'field' => 'delivery', 'width' => 12),
				//array('title' => '自提门店', 'field' => 'pickname', 'width' => 24),
				//array('title' => '商品小计', 'field' => 'goodsprice', 'width' => 12),
				array('title' => '运费', 'field' => 'dispatchprice', 'width' => 12),
				//array('title' => '积分抵扣', 'field' => 'deductprice', 'width' => 12),
				//array('title' => '余额抵扣', 'field' => 'deductcredit2', 'width' => 12),
				//array('title' => '满额立减', 'field' => 'deductenough', 'width' => 12),
				//array('title' => '优惠券优惠', 'field' => 'couponprice', 'width' => 12),
				array('title' => '订单改价', 'field' => 'changeprice', 'width' => 12),
				array('title' => '运费改价', 'field' => 'changedispatchprice', 'width' => 12),
				array('title' => '应收款', 'field' => 'price', 'width' => 12),
				array('title' => '状态', 'field' => 'status', 'width' => 12),
				array('title' => '下单时间', 'field' => 'createtime', 'width' => 24),
				array('title' => '付款时间', 'field' => 'paytime', 'width' => 24),
				array('title' => '发货时间', 'field' => 'sendtime', 'width' => 24),
				array('title' => '完成时间', 'field' => 'finishtime', 'width' => 24),
				array('title' => '快递公司', 'field' => 'expresscom', 'width' => 24),
				array('title' => '快递单号', 'field' => 'expresssn', 'width' => 24),
				
				array('title' => '小区名称', 'field' => 'community_name', 'width' => 12),
				array('title' => '团长姓名', 'field' => 'head_name', 'width' => 12),
				array('title' => '团长电话', 'field' => 'head_mobile', 'width' => 12),
				array('title' => '完整地址', 'field' => 'fullAddress', 'width' => 24),
				
				
				array('title' => '订单备注', 'field' => 'remark', 'width' => 36),
				array('title' => '卖家订单备注', 'field' => 'remarksaler', 'width' => 36),
				//array('title' => '核销员', 'field' => 'salerinfo', 'width' => 24),
				//array('title' => '核销门店', 'field' => 'storeinfo', 'width' => 36),
				//array('title' => '订单自定义信息', 'field' => 'order_diyformdata', 'width' => 36),
				//array('title' => '商品自定义信息', 'field' => 'goods_diyformdata', 'width' => 36)
			);
			$exportlist = array();
			
			if (!(empty($total))) {
			
				//searchfield goodstitle
				$sqlcondition .= ' left join ' . tablename('lionfish_comshop_order_goods') . ' ogc on ogc.order_id = o.order_id ';
			
				$sql = 'SELECT o.*,ogc.name as goods_title,ogc.order_goods_id ,ogc.quantity as ogc_quantity,ogc.price,
							ogc.total as goods_total 
						FROM ' . tablename('lionfish_comshop_order') . ' as o  '.$sqlcondition.' where '  . $condition . ' ORDER BY o.head_id asc,ogc.goods_id desc,  o.`order_id` DESC  ';
				
				$list = pdo_fetchall($sql,$paras);
				
				$look_member_arr = array();
				$area_arr = array();
				
				foreach($list as $val)
				{
					if( empty($look_member_arr) || !isset($look_member_arr[$val['member_id']]) )
					{
						$member_info = pdo_fetch("select * from ".tablename('lionfish_comshop_member')." where uniacid=:uniacid and member_id=:member_id ", array(":uniacid" => $_W['uniacid'], ':member_id' => $val['member_id']));
						$look_member_arr[$val['member_id']] = $member_info;
					}
					$tmp_exval= array();
					$tmp_exval['order_num_alias'] = $val['order_num_alias']."\t";
					$tmp_exval['name'] = $look_member_arr[$val['member_id']]['username'];
					//from_type
					if($val['from_type'] == 'wepro')
					{
						$tmp_exval['openid'] = $look_member_arr[$val['member_id']]['we_openid'];
					}else{
						$tmp_exval['openid'] = $look_member_arr[$val['member_id']]['openid'];
					}
					$tmp_exval['telephone'] = $look_member_arr[$val['member_id']]['telephone'];
					
					$tmp_exval['shipping_name'] = $val['shipping_name'];
					$tmp_exval['shipping_tel'] = $val['shipping_tel'];
					
					//area_arr
					if( empty($area_arr) || !isset($area_arr[$val['shipping_province_id']]) )
					{ 
						$area_arr[$val['shipping_province_id']] = load_model_class('front')->get_area_info($val['shipping_province_id']);
					}
					
					if( empty($area_arr) || !isset($area_arr[$val['shipping_city_id']]) )
					{ 
						$area_arr[$val['shipping_city_id']] = load_model_class('front')->get_area_info($val['shipping_city_id']);
					}
					
					if( empty($area_arr) || !isset($area_arr[$val['shipping_country_id']]) )
					{ 
						$area_arr[$val['shipping_country_id']] = load_model_class('front')->get_area_info($val['shipping_country_id']);
					}
					
					$province_info = $area_arr[$val['shipping_province_id']];
					$city_info = $area_arr[$val['shipping_city_id']];
					$area_info = $area_arr[$val['shipping_country_id']];
					
					$tmp_exval['address_province'] = $province_info['name'];
					$tmp_exval['address_city'] = $city_info['name'];
					$tmp_exval['address_area'] = $area_info['name'];
					$tmp_exval['address_address'] = $val['shipping_address'];
					$tmp_exval['goods_title'] = $val['goods_title'];
					
					$goods_optiontitle = load_model_class('order')->get_order_option_sku($val['order_id'], $val['order_goods_id']);
					$tmp_exval['goods_optiontitle'] = $goods_optiontitle;
					$tmp_exval['quantity'] = $val['ogc_quantity'];
					$tmp_exval['goods_price1'] = $val['price'];
					$tmp_exval['goods_rprice2'] = $val['goods_total'];
					
					$paytype = $val['payment_code'];
					switch($paytype)
					{
						case 'admin':
							$paytype='后台支付';
							break;
						case 'yuer':
							$paytype='余额支付';
							break;
						case 'weixin':
							$paytype='微信支付';
						break;
						default:
							$paytype = '未支付';

					}
					
					$community_info = load_model_class('front')->get_community_byid($val['head_id']);
					
						
					$tmp_exval['community_name'] = $community_info['communityName'];
					$tmp_exval['fullAddress'] = $community_info['fullAddress'];
					$tmp_exval['head_name'] = $community_info['disUserName'];
					$tmp_exval['head_mobile'] = $community_info['head_mobile'];
				
				
					$tmp_exval['paytype'] = $paytype;
					
					
					if($val['delivery'] == 'express')
					{
						$tmp_exval['delivery'] =  '快递';
					}else if($val['delivery'] == 'pickup')
					{
						$tmp_exval['delivery'] =  '自提';
					}else if($val['delivery'] == 'tuanz_send'){
						$tmp_exval['delivery'] =  '团长配送';
					}
					
					
					$tmp_exval['dispatchprice'] = $val['shipping_fare'];
					$tmp_exval['changeprice'] = $val['changedtotal'];
					$tmp_exval['changedispatchprice'] = $val['changedshipping_fare'];
					$tmp_exval['price'] = $val['total'];
					$tmp_exval['status'] = $order_status_arr[$val['order_status_id']];
					
					$tmp_exval['createtime'] = date('Y-m-d H:i:s', $val['date_added']);
					$tmp_exval['paytime'] = date('Y-m-d H:i:s', $val['pay_time']);
					
					$tmp_exval['sendtime'] = date('Y-m-d H:i:s', $val['express_time']);
					$tmp_exval['finishtime'] = date('Y-m-d H:i:s', $val['finishtime']);
					
					$tmp_exval['expresscom'] = $val['dispatchname'];
					$tmp_exval['expresssn'] = $val['shipping_no'];
					$tmp_exval['remark'] = $val['comment'];
					$tmp_exval['remarksaler'] = $val['remarksaler'];
					
					$exportlist[] = $tmp_exval;
				}
			}
			
			load_model_class('excel')->export($exportlist, array('title' => '配送清单-订单数据', 'columns' => $columns));
			
		
	}
	public function downexcel()
	{
		global $_W;
        global $_GPC;
		
		$list_id = $_GPC['list_id'];
		
		
		$params_arr = array();
		$uniacid            = $_W['uniacid'];
        $params_arr[':uniacid'] = $uniacid;
		$params_arr[':list_id'] = $list_id;

		$condition = " and list_id=:list_id ";
		
        $list = pdo_fetchall('SELECT * FROM ' . tablename('lionfish_comshop_deliverylist_goods') . "\r\n 
		WHERE uniacid=:uniacid " . $condition . ' order by id desc ', $params_arr);
       
	   
	   
		$exportlist = array();
		
		$i =1;
		foreach($list as $val)
		{
			$tmp_exval = array();
			$tmp_exval['num_no'] = $i;
			$tmp_exval['name'] = $val['goods_name'];
			$tmp_exval['quantity'] = $val['goods_count'];
			$tmp_exval['sku_str'] = $val['sku_str'];
			
		
			$info = pdo_fetch("select price from ".tablename('lionfish_comshop_order_goods').
						" where goods_id=:goods_id and uniacid=:uniacid and rela_goodsoption_valueid=:rela_goodsoption_valueid order by order_goods_id desc ", 
						array(':uniacid' =>$uniacid,':goods_id' => $val['goods_id'],':rela_goodsoption_valueid' => $val['rela_goodsoption_valueid'] ) );
			
			
			$tmp_exval['price'] = $info['price'];
			$tmp_exval['total_price'] = round($info['price'] * $val['goods_count'],2) ;
			
			//goods_id  rela_goodsoption_valueid
			
			$exportlist[] = $tmp_exval;
			$i++;
		}
		
		$columns = array(
			array('title' => '序号', 'field' => 'num_no', 'width' => 12),
			array('title' => '商品名称', 'field' => 'name', 'width' => 24),
			array('title' => '数量', 'field' => 'quantity', 'width' => 12),
			array('title' => '规格', 'field' => 'sku_str', 'width' => 24),
			array('title' => '单价', 'field' => 'price', 'width' => 24),
			array('title' => '总价', 'field' => 'total_price', 'width' => 24),
		);
		
		
		$list_info = pdo_fetch("select * from ".tablename('lionfish_comshop_deliverylist')." where uniacid=:uniacid and id =:list_id ", 
					array(':uniacid' => $_W['uniacid'], ':list_id' => $list_id ));
		//$params['list_info']
		
		$lists_info = array(
							'line1' => $list_info['head_name'],//团老大
							'line2' => '团长：'.$list_info['head_name'].'     提货地址：'.$list_info['head_address'].'     联系电话：'.$list_info['head_mobile'],//团长：团老大啦     提货地址：湖南大剧院     联系电话：13000000000
							'line3' => '配送单：'.$list_info['list_sn'].'     时间：'.date('Y-m-d H:i:s', $list_info['create_time']),
							'line4' => '配送路线：'.$list_info['line_name'].'     配送员：'.$list_info['clerk_name'],
						);
		
		
		load_model_class('excel')->export_delivery_list($exportlist, array('list_info' => $lists_info,'title' => '清单数据', 'columns' => $columns));
		die();
		
		
	}
	
	public function list_goodslist()
	{
		global $_W;
        global $_GPC;
		
		$list_id = $_GPC['list_id'];
		
		$params = array();
		$uniacid            = $_W['uniacid'];
        $params[':uniacid'] = $uniacid;
		$params[':list_id'] = $list_id;

        $pindex    = max(1, intval($_GPC['page']));
        $psize     = 20;

		$condition = " and list_id=:list_id ";
		
        if (!empty($_GPC['keyword'])) {
            $_GPC['keyword'] = trim($_GPC['keyword']);
            $condition .= ' and (name like :keyword  )';
            $params[':keyword'] = '%' . $_GPC['keyword'] . '%';
        }

        $list = pdo_fetchall('SELECT * FROM ' . tablename('lionfish_comshop_deliverylist_goods') . "\r\n 
		WHERE uniacid=:uniacid " . $condition . ' order by id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize, $params);
       
	    $total = pdo_fetchcolumn('SELECT count(1) FROM ' . tablename('lionfish_comshop_deliverylist_goods') . ' WHERE uniacid=:uniacid' . $condition, $params);

        $pager = pagination2($total, $pindex, $psize);
		
		
		$list_info = pdo_fetch("select * from ".tablename('lionfish_comshop_deliverylist')." where uniacid=:uniacid and id =:list_id ", 
					array(':uniacid' => $_W['uniacid'], ':list_id' => $list_id ));
		
		include $this->display();
		
	}

	
	public function sub_song()
	{
		global $_W;
        global $_GPC;
		
		$list_id = $_GPC['id'];
		
		$this->do_sub_song( $list_id  );

		show_json(1, array('msg' =>'配送清单成功','url' => referer()));
	}
	
	/**
		将订单状态为配送中
	**/
	private function do_sub_song( $list_id  )
	{
		global $_W;
        global $_GPC;
		
		$list_info = pdo_fetch("select * from ".tablename('lionfish_comshop_deliverylist')." where uniacid=:uniacid and id=:list_id ", 
						array(':uniacid' => $_W['uniacid'], ':list_id' => $list_id));
						
		if( !empty($list_info) )
		{
			//变更线路状态。变更订单状态为配送中
			
			$order_relates = pdo_fetchall("select * from ".tablename('lionfish_comshop_deliverylist_order')." where uniacid=:uniacid and list_id=:list_id ", 
								array(':uniacid' => $_W['uniacid'], ':list_id' => $list_id));
								
			if( !empty($order_relates) )
			{
				foreach($order_relates as $order_val)
				{
					
					$order_status_id = pdo_fetchcolumn('SELECT order_status_id FROM ' . tablename('lionfish_comshop_order') . 
							' WHERE uniacid=:uniacid and order_id=:order_id ', array(':uniacid' => $_W['uniacid'],':order_id' => $order_val['order_id']) );
					
					//待发货才行
					if($order_status_id == 1)
					{
						$data = array();
			
						$data['express_time'] = time();
						
						$data['order_status_id'] = 14;
						
						pdo_update('lionfish_comshop_order', $data, array('order_id' => $order_val['order_id'], 'uniacid' => $_W['uniacid'] ));
						
						$history_data = array();
						$history_data['uniacid'] = $_W['uniacid'];
						$history_data['order_id'] = $order_val['order_id'];
						$history_data['order_status_id'] = 14;
						$history_data['notify'] = 0;
						$history_data['comment'] = '订单配送中，使用清单发货';
						$history_data['date_added'] = time();
						
						pdo_insert('lionfish_comshop_order_history', $history_data);
					}
				}
			}
			
			pdo_update('lionfish_comshop_deliverylist', array('state' => 1,'express_time' => time()), array('id' => $list_id, 'uniacid' => $_W['uniacid'] ));			
		}
	}
	
	
	public function delivery_clerk()
	{
		
		global $_W;
        global $_GPC;
		
		$uniacid            = $_W['uniacid'];
        $params[':uniacid'] = $uniacid;

        $pindex    = max(1, intval($_GPC['page']));
        $psize     = 20;

        if (!empty($_GPC['keyword'])) {
            $_GPC['keyword'] = trim($_GPC['keyword']);
            $condition .= ' and (name like :keyword  )';
            $params[':keyword'] = '%' . $_GPC['keyword'] . '%';
        }

        $list = pdo_fetchall('SELECT * FROM ' . tablename('lionfish_comshop_deliveryclerk') . "\r\n 
		WHERE uniacid=:uniacid " . $condition . ' order by id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize, $params);
       
	    $total = pdo_fetchcolumn('SELECT count(1) FROM ' . tablename('lionfish_comshop_deliveryclerk') . ' WHERE uniacid=:uniacid' . $condition, $params);

        $pager = pagination2($total, $pindex, $psize);
      
		include $this->display();
	}
	
	public function onekey_tosend()
	{
		global $_W;
        global $_GPC;
		
		$ids_arr = $_GPC['ids_arr'];
		$sec = $_GPC['sec'];
		
		$cache_key = md5(time().count($ids_arr).$sec);
		
		$quene_order_list = array();
		
		if( $sec == 1 )
		{
			//限定配送数组
			cache_write($_W['uniacid'].'_deliveryquene_'.$cache_key, $ids_arr);
			// lionfish_comshop_deliverylist
		}else{
			//全部群发数组
			$deliverylist = pdo_fetchall("select id  from ".tablename('lionfish_comshop_deliverylist')." where uniacid=:uniacid and  state = 0 ", 
							array(':uniacid' => $_W['uniacid'] ) );
			
			foreach($deliverylist as $val)
			{
				$quene_order_list[]  = $val['id'];
			}
			
			cache_write($_W['uniacid'].'_deliveryquene_'.$cache_key, $quene_order_list);
		}
		
		include $this->display();
	}
	
	public function onekey_tosendallorder()
	{
		global $_W;
        global $_GPC;
		
		
		
		$cache_key = md5(time().mt_rand(1,999));
		
		$quene_order_list = array();
		
		
			//全部群发数组
			$orderlist = pdo_fetchall("select order_id  from ".tablename('lionfish_comshop_order')." 
								where uniacid=:uniacid and  order_status_id=1  and (delivery ='pickup' or delivery='tuanz_send') order by order_id desc  ", 
							array(':uniacid' => $_W['uniacid'] ) );
			
			foreach($orderlist as $val)
			{
				$quene_order_list[]  = $val['order_id'];
			}
			
			cache_write($_W['uniacid'].'_sendallorderquene_'.$cache_key, $quene_order_list);
		
		
		include $this->display();
	}
	
	public function onekey_opreceive()
	{
		global $_W;
        global $_GPC;
		
		
		
		$cache_key = md5(time().mt_rand(1,999));
		
		$quene_order_list = array();
		
		
			//全部群发数组
			$orderlist = pdo_fetchall("select order_id  from ".tablename('lionfish_comshop_order')." 
								where uniacid=:uniacid and  order_status_id=4  order by order_id desc  ", 
							array(':uniacid' => $_W['uniacid'] ) );
			
			foreach($orderlist as $val)
			{
				$quene_order_list[]  = $val['order_id'];
			}
			
			cache_write($_W['uniacid'].'_opreceivequene_'.$cache_key, $quene_order_list);
		
		
		include $this->display();
		
	}
	
	public function onekey_opsend_tuanz_over()
	{
		global $_W;
        global $_GPC;
		
		
		
		$cache_key = md5(time().mt_rand(1,999));
		
		$quene_order_list = array();
		
		
			//全部群发数组
			$orderlist = pdo_fetchall("select order_id  from ".tablename('lionfish_comshop_order')." 
								where uniacid=:uniacid and  order_status_id=14  and (delivery ='pickup' or delivery='tuanz_send') order by order_id desc  ", 
							array(':uniacid' => $_W['uniacid'] ) );
			
			foreach($orderlist as $val)
			{
				$quene_order_list[]  = $val['order_id'];
			}
			
			cache_write($_W['uniacid'].'_tuanzoverquene_'.$cache_key, $quene_order_list);
		
		
		include $this->display();
	}
	
	public function do_opreceive_quene()
	{
		
		
		global $_W;
		global $_GPC;
		
		
		$cache_key = $_GPC['cache_key'];
		
		$quene_order_list = cache_load($_W['uniacid'].'_opreceivequene_'.$cache_key);
		
		$order_id = array_shift($quene_order_list);
		
		cache_write($_W['uniacid'].'_opreceivequene_'.$cache_key, $quene_order_list);
		
		
		$order_info = pdo_fetch("select * from ".tablename('lionfish_comshop_order')." where uniacid=:uniacid and order_id=:order_id ", 
						array(':uniacid' => $_W['uniacid'], ':order_id' => $order_id ));	
		
		if( $order_info['order_status_id'] == 4 )
		{
			load_model_class('order')->receive_order($order_id);
	
	
			pdo_update('lionfish_comshop_order_history', 
			array( 'comment' => '后台操作一键，确认收货'), 
			array('order_id' => $order_id,'order_status_id' => 6, 'uniacid' => $_W['uniacid']));
		}
		
		if( empty($quene_order_list) )
		{
			echo json_encode( array('code' => 2) );
			die();
		}
		
		//清单编号   
		
		echo json_encode( array('code' => 0, 'msg' => '订单编号：'.$order_info['order_num_alias']." 处理成功，还剩余".count($quene_order_list)."个订单未处理") );
		die();
		
	}
	
	public function do_tuanzover_quene()
	{
		
		global $_W;
		global $_GPC;
		
		
		$cache_key = $_GPC['cache_key'];
		
		$quene_order_list = cache_load($_W['uniacid'].'_tuanzoverquene_'.$cache_key);
		
		$order_id = array_shift($quene_order_list);
		
		cache_write($_W['uniacid'].'_tuanzoverquene_'.$cache_key, $quene_order_list);
		
		
		$order_info = pdo_fetch("select * from ".tablename('lionfish_comshop_order')." where uniacid=:uniacid and order_id=:order_id ", 
						array(':uniacid' => $_W['uniacid'], ':order_id' => $order_id ));	
		
		if( $order_info['order_status_id'] == 14 )
		{
			
			load_model_class('order')->do_tuanz_over($order_id);
	
			$history_data = array();
			$history_data['uniacid'] = $_W['uniacid'];
			$history_data['order_id'] = $order_id;
			$history_data['order_status_id'] = 4;
			$history_data['notify'] = 0;
			$history_data['comment'] = '后台一键操作，批量操作发货到团长';
			$history_data['date_added'] = time();
			
			pdo_insert('lionfish_comshop_order_history', $history_data);
			
			load_model_class('frontorder')->send_order_operate($order_id);
				
			
		}
		
		if( empty($quene_order_list) )
		{
			echo json_encode( array('code' => 2) );
			die();
		}
		
		//清单编号   
		
		echo json_encode( array('code' => 0, 'msg' => '订单编号：'.$order_info['order_num_alias']." 处理成功，还剩余".count($quene_order_list)."个订单未处理") );
		die();
	}
	
	/**
		批量处理配送队列
	**/
	public function do_sendallorder_quene()
	{
		global $_W;
		global $_GPC;
		
		
		$cache_key = $_GPC['cache_key'];
		
		$quene_order_list = cache_load($_W['uniacid'].'_sendallorderquene_'.$cache_key);
		
		$order_id = array_shift($quene_order_list);
		
		cache_write($_W['uniacid'].'_sendallorderquene_'.$cache_key, $quene_order_list);
		
		
		$order_info = pdo_fetch("select * from ".tablename('lionfish_comshop_order')." where uniacid=:uniacid and order_id=:order_id ", 
						array(':uniacid' => $_W['uniacid'], ':order_id' => $order_id ));	
		
		if( $order_info['order_status_id'] == 1 )
		{
			
			load_model_class('order')->do_send_tuanz($order_id);
			
		}
		
		if( empty($quene_order_list) )
		{
			echo json_encode( array('code' => 2) );
			die();
		}
		
		//清单编号   
		
		echo json_encode( array('code' => 0, 'msg' => '订单编号：'.$order_info['order_num_alias']." 处理成功，还剩余".count($quene_order_list)."个订单未处理") );
		die();
		
		
	}
	
	public function config()
	{
		global $_W;
		global $_GPC;
		//goods_stock_notice
		if ($_W['ispost']) {
			$data = ((is_array($_GPC['parameter']) ? $_GPC['parameter'] : array()));
			
			$data['is_delivery_add'] = isset($data['is_delivery_add']) ? 1:0;
			
			load_model_class('config')->update($data);
			
			
			show_json(1);
		}
		$data = load_model_class('config')->get_all_config();
		
		include $this->display();
	}
	
	public function onekey_tosendover()
	{
		global $_W;
        global $_GPC;
		
		$ids_arr = $_GPC['ids_arr'];
		$sec = $_GPC['sec'];
		
		$cache_key = md5(time().count($ids_arr).$sec);
		
		$quene_order_list = array();
		
		if( $sec == 1 )
		{
			//限定配送数组
			cache_write($_W['uniacid'].'_deliveryqueneing_'.$cache_key, $ids_arr);
			// lionfish_comshop_deliverylist
		}else{
			//全部群发数组
			$deliverylist = pdo_fetchall("select id  from ".tablename('lionfish_comshop_deliverylist')." where uniacid=:uniacid and  state = 1 ", 
							array(':uniacid' => $_W['uniacid'] ) );
			
			foreach($deliverylist as $val)
			{
				$quene_order_list[]  = $val['id'];
			}
			
			cache_write($_W['uniacid'].'_deliveryqueneing_'.$cache_key, $quene_order_list);
		}
		
		include $this->display();
	}
	
	/**
		批量处理队列
	**/
	public function do_deliverying_quene()
	{
		global $_W;
		global $_GPC;
		
		
		$cache_key = $_GPC['cache_key'];
		
		$quene_order_list = cache_load($_W['uniacid'].'_deliveryqueneing_'.$cache_key);
		
		$delivery_id = array_shift($quene_order_list);
		
		cache_write($_W['uniacid'].'_deliveryqueneing_'.$cache_key, $quene_order_list);
		
		
		$delivery_info = pdo_fetch("select * from ".tablename('lionfish_comshop_deliverylist')." where uniacid=:uniacid and id=:id ", 
						array(':uniacid' => $_W['uniacid'], ':id' => $delivery_id ));	
		
		if( $delivery_info['state'] == 1 )
		{
			
			pdo_update('lionfish_comshop_deliverylist', array('state' => 2,'head_get_time' => time() ), 
					array('id' => $delivery_id, 'uniacid' => $_W['uniacid'] ));
					
			//对订单操作，可以去提货了
			
			$order_ids_all = pdo_fetchall("select * from ".tablename('lionfish_comshop_deliverylist_order')." 
								where uniacid=:uniacid and list_id=:list_id", array(':list_id' => $delivery_id,':uniacid' => $_W['uniacid']));
								
			if( !empty($order_ids_all) )
			{
				foreach($order_ids_all as $order_val)
				{
					$order_status_id = pdo_fetchcolumn('SELECT order_status_id FROM ' . tablename('lionfish_comshop_order') . 
							' WHERE uniacid=:uniacid and order_id=:order_id ', array(':uniacid' => $_W['uniacid'],':order_id' => $order_val['order_id']) );
					
					//配送中才能
					if($order_status_id == 14)
					{
						$history_data = array();
						$history_data['uniacid'] = $_W['uniacid'];
						$history_data['order_id'] = $order_val['order_id'];
						$history_data['order_status_id'] = 4;
						$history_data['notify'] = 0;
						$history_data['comment'] = '后台一键团长签收配送清单';
						$history_data['date_added'] = time();
						
						pdo_insert('lionfish_comshop_order_history', $history_data);
			
						//send_order_operate
						load_model_class('frontorder')->send_order_operate($order_val['order_id']);
					}
				}
			}
		}
		
		if( empty($quene_order_list) )
		{
			echo json_encode( array('code' => 2) );
			die();
		}
		
		//清单编号   
		
		echo json_encode( array('code' => 0, 'msg' => '清单编号：'.$delivery_info['list_sn']." 处理成功，还剩余".count($quene_order_list)."个清单未处理") );
		die();
		
		
	}
	
	/**
		批量处理配送队列
	**/
	public function do_delivery_quene()
	{
		global $_W;
		global $_GPC;
		
		
		$cache_key = $_GPC['cache_key'];
		
		$quene_order_list = cache_load($_W['uniacid'].'_deliveryquene_'.$cache_key);
		
		$delivery_id = array_shift($quene_order_list);
		
		cache_write($_W['uniacid'].'_deliveryquene_'.$cache_key, $quene_order_list);
		
		
		$delivery_info = pdo_fetch("select * from ".tablename('lionfish_comshop_deliverylist')." where uniacid=:uniacid and id=:id ", 
						array(':uniacid' => $_W['uniacid'], ':id' => $delivery_id ));	
		
		if( $delivery_info['state'] == 0 )
		{
			if( $delivery_info['state'] == 0 )
			{
				$this->do_sub_song( $delivery_id  );
			}
		}
		
		if( empty($quene_order_list) )
		{
			echo json_encode( array('code' => 2) );
			die();
		}
		
		//清单编号   
		
		echo json_encode( array('code' => 0, 'msg' => '清单编号：'.$delivery_info['list_sn']." 处理成功，还剩余".count($quene_order_list)."个清单未处理") );
		die();
		
		
	}
	
	
	
	public function head_ordergoods_detail()
	{
		//id=1
		global $_W;
        global $_GPC;
		
		$head_id = $_GPC['head_id'];
		
		
		$is_delivery_add = load_model_class('front')->get_config_by_name('is_delivery_add');
		
		$is_delivery_add = isset($is_delivery_add) && $is_delivery_add == 1 ?  1: 0; 
		
		
		//searchtime
		//$starttime = isset($_GPC['time']['start']) ? strtotime($_GPC['time']['start']) : strtotime(date('Y-m-d'.' 00:00:00'));
		//$endtime = isset($_GPC['time']['end']) ? strtotime($_GPC['time']['end']) : strtotime(date('Y-m-d'.' 23:59:59'));
		
		$searchtime = isset($_GPC['searchtime']) ? $_GPC['searchtime'] : '';
		$starttime = isset($_GPC['time']['start']) ? strtotime($_GPC['time']['start']) : strtotime(date('Y-m-d'.' 00:00:00'));
		$endtime = isset($_GPC['time']['end']) ? strtotime($_GPC['time']['end']) : strtotime(date('Y-m-d'.' 23:59:59'));
		
		
		
		$order_condition = "  ";
		
		if( !empty($searchtime) )
		{
			$order_condition .= " and pay_time >={$starttime} and pay_time<= {$endtime} ";
		}
		
		if( $is_delivery_add == 1 )
		{
			$goods_count_sql = "SELECT * FROM ".tablename('lionfish_comshop_order_goods')." where uniacid=:uniacid and order_id in 
								(SELECT order_id from ".tablename('lionfish_comshop_order')." where is_refund_state=0 and is_delivery_flag = 0 and head_id=:head_id {$order_condition}  and order_status_id =1 )";
		
		}else{
			$goods_count_sql = "SELECT * FROM ".tablename('lionfish_comshop_order_goods')." where uniacid=:uniacid and order_id in 
								(SELECT order_id from ".tablename('lionfish_comshop_order')." where is_refund_state=0 and is_delivery_flag = 0 and head_id=:head_id {$order_condition} and delivery != 'express'  and order_status_id =1 )";
		
		}
		
		$goods_list = pdo_fetchall($goods_count_sql, array(':uniacid' => $_W['uniacid'],':head_id' => $head_id ));
		
		$show_goods_list = array();
		
		foreach($goods_list as $val)
		{
			if( empty($show_goods_list) || !in_array( $val['goods_id'].'_'.$val['rela_goodsoption_valueid'], array_keys($show_goods_list) ) )
			{
				$sku_name = '';
				$sku_arr = array();
				
				$order_option_info = pdo_fetchall("select value from ".tablename('lionfish_comshop_order_option')." where order_id=:order_id and order_goods_id=:order_goods_id ",array(':order_id' => $val['order_id'],':order_goods_id' => $val['order_goods_id'] ));
			  
				foreach($order_option_info as $option)
				{
					$sku_arr[] = $option['value'];
				}
				
				if(empty($sku_arr))
				{
					$sku_name = '';
				}else{
					$sku_name = implode(',', $sku_arr);
				}
				
				$show_goods_list[ $val['goods_id'].'_'.$val['rela_goodsoption_valueid'] ] = 
								array( 'name' => $val['name'],'sku_name' =>$sku_name,'quantity' => $val['quantity'],'rela_goodsoption_valueid' => $val['rela_goodsoption_valueid'] );
			}else{
				$show_goods_list[ $val['goods_id'].'_'.$val['rela_goodsoption_valueid'] ]['quantity'] += $val['quantity'];
			}
		}
		
		
		include $this->display();
	}
	
	
	//deldeliverylist
	
	public function deldeliverylist()
	{
		global $_W;
        global $_GPC;
		
		$line_id =  $_GPC['id'];
		
		pdo_delete('lionfish_comshop_deliveryline_headrelative', array('line_id' => $line_id));
		pdo_delete('lionfish_comshop_deliveryline', array('id' => $line_id));
		 
		
		
		show_json(1);
	}
	

	public function sub_delivery_list()
	{
		global $_W;
        global $_GPC;
		
		
		$is_delivery_add = load_model_class('front')->get_config_by_name('is_delivery_add');
		
		$is_delivery_add = isset($is_delivery_add) && $is_delivery_add == 1 ?  1: 0; 
		
		
		
		$head_id = $_GPC['head_id'];
		$searchtime  = isset($_GPC['searchtime']) ? $_GPC['searchtime'] : '';
		$starttime  = isset($_GPC['starttime']) ? $_GPC['starttime'] : '';
		$endtime  = isset($_GPC['endtime']) ? $_GPC['endtime'] : '';
		
		if (empty($head_id)) {
            $head_id = (is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0);
        }
			
		
		$head_arr =explode(',', $head_id);
		
		
		foreach( $head_arr as $head_id )
		{
			if( empty($head_id) )
			{
				continue;
			}
			//community_name head_name  head_mobile
			$head_info = pdo_fetch("select * from ".tablename('lionfish_community_head')." where uniacid=:uniacid and id=:head_id ", 
						array(':uniacid' => $_W['uniacid'], ':head_id' => $head_id ));
			
			$province = load_model_class('front')->get_area_info($head_info['province_id']); 
			$city = load_model_class('front')->get_area_info($head_info['city_id']); 
			$area = load_model_class('front')->get_area_info($head_info['area_id']); 
			$country = load_model_class('front')->get_area_info($head_info['country_id']); 
		
			$full_name = $province['name'].$city['name'].$area['name'].$country['name'].$head_info['address'];
		
		
			$order_condition = "  ";
			
			if( !empty($searchtime) )
			{
				//create
				
				if( $searchtime == 'create' )
				{
					$order_condition .= " and date_added >={$starttime} and date_added<= {$endtime} ";
				}else if( $searchtime == 'pay' ){
					$order_condition .= " and pay_time >={$starttime} and pay_time<= {$endtime} ";
					
				}
			}
			
			if( $is_delivery_add == 1 )
			{
				$goods_count_sql = "SELECT * FROM ".tablename('lionfish_comshop_order_goods')." where uniacid=:uniacid and is_refund_state=0  and order_id in 
									(SELECT order_id from ".tablename('lionfish_comshop_order')." where is_refund_state=0 and is_delivery_flag = 0 and head_id=:head_id {$order_condition}  and order_status_id =1 )";
			
			}else{
				$goods_count_sql = "SELECT * FROM ".tablename('lionfish_comshop_order_goods')." where uniacid=:uniacid and is_refund_state=0  and order_id in 
									(SELECT order_id from ".tablename('lionfish_comshop_order')." where is_refund_state=0 and is_delivery_flag = 0 and head_id=:head_id {$order_condition} and delivery != 'express'  and order_status_id =1 )";
			
			}
			
			$goods_list = pdo_fetchall($goods_count_sql, array(':uniacid' => $_W['uniacid'],':head_id' => $head_id ));
			
			$show_goods_list = array();

			$goods_count =0;
			
			$order_id_list = array();
			
			foreach($goods_list as $val)
			{
				if( empty($order_id_list) || !in_array( $val['order_id'], $order_id_list ) )
				{
					$order_id_list[] = $val['order_id'];
				}
				
				if( empty($show_goods_list) || !in_array( $val['goods_id'].'_'.$val['rela_goodsoption_valueid'], array_keys($show_goods_list) ) )
				{
					$sku_name = '';
					$sku_arr = array();
					
					$order_option_info = pdo_fetchall("select value from ".tablename('lionfish_comshop_order_option')." where order_id=:order_id and order_goods_id=:order_goods_id ",array(':order_id' => $val['order_id'],':order_goods_id' => $val['order_goods_id'] ));
				  
					foreach($order_option_info as $option)
					{
						$sku_arr[] = $option['value'];
					}
					
					if(empty($sku_arr))
					{
						$sku_name = '';
					}else{
						$sku_name = implode(',', $sku_arr);
					}
					$goods_count += $val['quantity'];
					$show_goods_list[ $val['goods_id'].'_'.$val['rela_goodsoption_valueid'] ] = 
									array('goods_id' => $val['goods_id'], 'name' => $val['name'],'goods_images' => $val['goods_images'],'sku_name' =>$sku_name,'quantity' => $val['quantity'],'rela_goodsoption_valueid' => $val['rela_goodsoption_valueid'] );
				}else{
					$goods_count += $val['quantity'];
					$show_goods_list[ $val['goods_id'].'_'.$val['rela_goodsoption_valueid'] ]['quantity'] += $val['quantity'];
				}
			}
			
			//ims_ 
			
			$line_relate_head = pdo_fetch("select line_id from ".tablename('lionfish_comshop_deliveryline_headrelative')." 
										where uniacid=:uniacid and head_id=:head_id " , 
								array(':uniacid' => $_W['uniacid'], ':head_id' => $head_id));
							
			$line_id = 0;
			$line_name = '';
			$clerk_id = 0;
			
			if( !empty($line_relate_head) )
			{
				$line_id = $line_relate_head['line_id'];
				
				$line_info = pdo_fetch('select name,clerk_id from '.tablename('lionfish_comshop_deliveryline')." where uniacid=:uniacid and id=:line_id ",
							array(':uniacid' => $_W['uniacid'], ':line_id' => $line_id ));
				$line_name = $line_info['name'];
				
				$clerk_id = $line_info['clerk_id'];
				//line_name
			}
			
			$clerk_name = '';
			$clerk_mobile = '';
			
			if( $clerk_id > 0 )
			{
				$clerk_info = pdo_fetch("select * from ".tablename('lionfish_comshop_deliveryclerk')." where uniacid=:uniacid and id=:clerk_id ", 
								array(':uniacid' => $_W['uniacid'], ':clerk_id' => $clerk_id));
				
				$clerk_name = $clerk_info['name'];
				$clerk_mobile = $clerk_info['mobile'];
			}
			
			
			$lionfish_comshop_deliverylist_data = array();
			$lionfish_comshop_deliverylist_data['uniacid'] = $_W['uniacid'];
			$lionfish_comshop_deliverylist_data['list_sn'] = build_order_no($head_id);
			$lionfish_comshop_deliverylist_data['head_id'] = $head_id;
			$lionfish_comshop_deliverylist_data['head_name'] = $head_info['head_name'];
			$lionfish_comshop_deliverylist_data['head_mobile'] = $head_info['head_mobile'];
			$lionfish_comshop_deliverylist_data['head_address'] = $full_name;
			$lionfish_comshop_deliverylist_data['line_id'] = $line_id;
			$lionfish_comshop_deliverylist_data['line_name'] = $line_name;
			$lionfish_comshop_deliverylist_data['clerk_id'] = $clerk_id;
			$lionfish_comshop_deliverylist_data['clerk_name'] = $clerk_name;
			$lionfish_comshop_deliverylist_data['clerk_mobile'] = $clerk_mobile;
			$lionfish_comshop_deliverylist_data['state'] = 0;
			$lionfish_comshop_deliverylist_data['goods_count'] = $goods_count;
			$lionfish_comshop_deliverylist_data['express_time'] = 0;
			$lionfish_comshop_deliverylist_data['create_time'] = time();
			$lionfish_comshop_deliverylist_data['addtime'] = time();
			
			pdo_insert('lionfish_comshop_deliverylist',$lionfish_comshop_deliverylist_data);
			
			$list_id = pdo_insertid();
			
			foreach($show_goods_list as $goods_val)
			{
				//ims_ lionfish_comshop_deliverylist_goods
				$lionfish_comshop_deliverylist_goods_data = array();
				$lionfish_comshop_deliverylist_goods_data['uniacid'] = $_W['uniacid'];
				$lionfish_comshop_deliverylist_goods_data['list_id'] = $list_id;
				$lionfish_comshop_deliverylist_goods_data['goods_id'] = $goods_val['goods_id'];
				$lionfish_comshop_deliverylist_goods_data['goods_name'] = $goods_val['name'];
				$lionfish_comshop_deliverylist_goods_data['rela_goodsoption_valueid'] = $goods_val['rela_goodsoption_valueid'];
				$lionfish_comshop_deliverylist_goods_data['sku_str'] = $goods_val['sku_name'];
				$lionfish_comshop_deliverylist_goods_data['goods_image'] = $goods_val['goods_images'];
				$lionfish_comshop_deliverylist_goods_data['goods_count'] = $goods_val['quantity'];
				$lionfish_comshop_deliverylist_goods_data['addtime'] = time();
				
				pdo_insert('lionfish_comshop_deliverylist_goods',$lionfish_comshop_deliverylist_goods_data);
			}
			
			foreach($order_id_list as $order_id)
			{
				//ims_ lionfish_comshop_deliverylist_order
				$lionfish_comshop_deliverylist_order_data = array();
				$lionfish_comshop_deliverylist_order_data['uniacid'] = $_W['uniacid'];
				$lionfish_comshop_deliverylist_order_data['list_id'] = $list_id;
				$lionfish_comshop_deliverylist_order_data['order_id'] = $order_id;
				$lionfish_comshop_deliverylist_order_data['addtime'] = time();
				pdo_insert('lionfish_comshop_deliverylist_order',$lionfish_comshop_deliverylist_order_data);
				
				pdo_update('lionfish_comshop_order', array('is_delivery_flag' => 1), array('order_id' => $order_id ));
			
			}
			
			//line_id  clerk_id   lionfish_comshop_deliverylist_order
			
			//ims_  lionfish_comshop_order
			
			
		}
		
		
		 show_json(1, array('msg' =>'生成清单成功','url' => referer()));
		
		
	}
	
	public function get_delivery_list()
	{
		global $_W;
        global $_GPC;
		
		
		$searchtime = isset($_GPC['searchtime']) ? $_GPC['searchtime'] : '';
		$starttime = isset($_GPC['time']['start']) ? strtotime($_GPC['time']['start']) : strtotime(date('Y-m-d'.' 00:00:00'));
		$endtime = isset($_GPC['time']['end']) ? strtotime($_GPC['time']['end']) : strtotime(date('Y-m-d'.' 23:59:59'));
		
		$keyword = isset($_GPC['keyword']) ? $_GPC['keyword'] : '';
		
		$line_id = isset($_GPC['line_id']) ? intval($_GPC['line_id']) : 0;
		
		$is_delivery_add = load_model_class('front')->get_config_by_name('is_delivery_add');
		
		$is_delivery_add = isset($is_delivery_add) && $is_delivery_add == 1 ?  1: 0; 
		
		
		
		if( $is_delivery_add == 1 )
		{
			$condition = " and is_delivery_flag = 0 and order_status_id =1   ";
		}else{
			$condition = " and is_delivery_flag = 0 and order_status_id =1 and delivery != 'express'  ";
		}
		
		$timewhere = "";
		
		if( !empty($searchtime) )
		{
			if( $searchtime == 'create' )
			{
				$condition .= " and date_added >={$starttime} and date_added<= {$endtime} ";
				
				$timewhere .= " and date_added >={$starttime} and date_added<= {$endtime} ";
			}else if( $searchtime == 'pay' ){
				$condition .= " and pay_time >={$starttime} and pay_time<= {$endtime} ";
				
				$timewhere .= " and pay_time >={$starttime} and pay_time<= {$endtime} ";
			}
		}
		
		
		
		if( !empty($keyword) )
		{
			//ims_lionfish_community_head
			$key_heads = pdo_fetchall("select id from ".tablename('lionfish_community_head')." where uniacid=:uniacid and community_name like :kwy ", 
						array(':uniacid' => $_W['uniacid'], ':kwy' => "%{$keyword}%"));
			
			if( !empty($key_heads) )
			{
				$head_ids = array();
				foreach($key_heads as $vv)
				{
					$head_ids[] = $vv['id'];
				}
				$head_ids_str = implode(',', $head_ids);
				$condition .= " and head_id in({$head_ids_str}) ";
			}else{
				$condition .= " and 0 ";
			}
		}
		
		if( $line_id > 0 )
		{
			$relate_heads = pdo_fetchall("select head_id from ".tablename('lionfish_comshop_deliveryline_headrelative')." 
							where uniacid=:uniacid and line_id=:line_id ", array(':uniacid' => $_W['uniacid'], ':line_id' => $line_id ));
							
			if( !empty($relate_heads) )
			{
				$head_ids = array();
				foreach($relate_heads as $vv)
				{
					$head_ids[] = $vv['head_id'];
				}
				$head_ids_str = implode(',', $head_ids);
				
				$condition .= " and head_id in({$head_ids_str}) ";
			}else{
				$condition .= " and 0 ";
			}		
		}
		
		//line_id=1
		//searchtime
		
		$params = array();
		$uniacid  = $_W['uniacid'];
        $params[':uniacid'] = $uniacid;

        $pindex    = max(1, intval($_GPC['page']));
        $psize     = 20;
		
		
		$list = pdo_fetchall('SELECT head_id FROM ' . tablename('lionfish_comshop_order') . "\r\n 
			WHERE uniacid=:uniacid " . $condition . ' and head_id > 0 group by head_id order by head_id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize, $params);
		
		
		$total_arr = pdo_fetchall('SELECT head_id FROM ' . tablename('lionfish_comshop_order') . "\r\n 
			WHERE uniacid=:uniacid " . $condition . ' and head_id > 0  group by head_id order by head_id desc  ' , $params);
		
		$total = count($total_arr);
		
		
		foreach($list as $key => $val)
		{
			//店铺名称 配送路线  商品总数 	操作
			
			// lionfish_community_head
			$head_info = pdo_fetch("select community_name from ".tablename('lionfish_community_head')." 
						where uniacid=:uniacid and id=:head_id ",
						array(':uniacid' => $_W['uniacid'], ':head_id' => $val['head_id']));
						
			$line_id_info = pdo_fetch("select line_id from ".tablename('lionfish_comshop_deliveryline_headrelative')." 
					where head_id=:head_id and uniacid=:uniacid ", array(':uniacid' => $_W['uniacid'], ':head_id' => $val['head_id'] ));
					
			$line_info  = array();
			
			if( !empty($line_id_info) )
			{
				//line_id 
				$line_info = pdo_fetch("select * from ".tablename('lionfish_comshop_deliveryline')." where uniacid=:uniacid and id=:line_id ",
							array(':uniacid' => $_W['uniacid'], ':line_id' => $line_id_info['line_id'] ));
			}
			
			if( $is_delivery_add == 1 )
			{
				$goods_count_sql = "SELECT sum(quantity) as total_quantity FROM ".tablename('lionfish_comshop_order_goods')." where uniacid=:uniacid and is_refund_state=0 and order_id in 
								(SELECT order_id from ".tablename('lionfish_comshop_order')." where is_delivery_flag = 0 {$timewhere} and head_id=:head_id    and order_status_id =1 )";
			
			}else{
				
				$goods_count_sql = "SELECT sum(quantity) as total_quantity FROM ".tablename('lionfish_comshop_order_goods')." where uniacid=:uniacid and is_refund_state=0 and order_id in 
								(SELECT order_id from ".tablename('lionfish_comshop_order')." where is_delivery_flag = 0 {$timewhere} and head_id=:head_id  and delivery != 'express'  and order_status_id =1 )";
			
			}
			
			$goods_count = pdo_fetchcolumn($goods_count_sql, array(':uniacid' => $_W['uniacid'], ':head_id' => $val['head_id']));
			
			$val['community_name'] = $head_info['community_name'];
			$val['line_name'] = $line_info['name'];
			$val['goods_count'] = $goods_count;
			
			$list[$key] = $val;
		}
		
        $pager = pagination2($total, $pindex, $psize);
		
		// lionfish_comshop_deliveryline 
		
		$line_list = pdo_fetchall("select * from ".tablename('lionfish_comshop_deliveryline')." where uniacid=:uniacid" ,
					array(':uniacid' => $_W['uniacid']));
		
		
		
		include $this->display();
	}
	
	public function delivery_line()
	{
		global $_W;
        global $_GPC;
		
		$uniacid            = $_W['uniacid'];
        $params[':uniacid'] = $uniacid;

        $pindex    = max(1, intval($_GPC['page']));
        $psize     = 20;

        if (!empty($_GPC['keyword'])) {
            $_GPC['keyword'] = trim($_GPC['keyword']);
            $condition .= ' and (name like :keyword  )';
            $params[':keyword'] = '%' . $_GPC['keyword'] . '%';
        }

        $list = pdo_fetchall('SELECT * FROM ' . tablename('lionfish_comshop_deliveryline') . "\r\n 
		WHERE uniacid=:uniacid " . $condition . ' order by id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize, $params);
        
		foreach($list as $key => $val)
		{
			//clerk_id
			if( $val['clerk_id'] > 0)
			{
				$clerk_name = pdo_fetch('SELECT * FROM ' . tablename('lionfish_comshop_deliveryclerk') . ' WHERE uniacid=:uniacid and id=:clerk_id ' , 
							array(':uniacid' => $_W['uniacid'], ':clerk_id' => $val['clerk_id'] ));
				$val['clerk_info'] = $clerk_name;
			}
			
			// lionfish_comshop_deliveryline_headrelative
			
			$head_relative = pdo_fetchall("select * from ".tablename('lionfish_comshop_deliveryline_headrelative')." 
								where uniacid=:uniacid and line_id=:line_id order by id asc ", array(':uniacid' => $_W['uniacid'], ':line_id' => $val['id']));
			
			$val['line_to_str'] = '';
			
			if( !empty($head_relative) )
			{
				$head_id_arr = array();
				foreach($head_relative as $vv)
				{
					$head_id_arr[] = $vv['head_id'];
				}
				$head_list = pdo_fetchall("select community_name from ".tablename('lionfish_community_head')." 
							where uniacid=:uniacid and id in (".implode(',', $head_id_arr ).")", array(':uniacid' => $_W['uniacid'] ) );
				$line_to_arr = array();
				
				foreach($head_list as $hd_val)
				{
					$line_to_arr[] = $hd_val['community_name'];
				}
				$val['line_to_str'] = implode('->', $line_to_arr );
			}
			//line_to_str
			
			$list[$key] = $val;
		}
		
	    $total = pdo_fetchcolumn('SELECT count(1) FROM ' . tablename('lionfish_comshop_deliveryline') . ' WHERE uniacid=:uniacid' . $condition, $params);

        $pager = pagination2($total, $pindex, $psize);
      
		include $this->display();
	}
	
	
	public function adddelivery_clerk()
	{
		global $_W;
        global $_GPC;
      
		$uniacid            = $_W['uniacid'];
        $params[':uniacid'] = $uniacid;

        $id = intval($_GPC['id']);
        if (!empty($id)) {
            $item = pdo_fetch('SELECT * from ' . tablename('lionfish_comshop_deliveryclerk') . "\r\n
			WHERE id=:id and uniacid=:uniacid limit 1 ", array(':id' => $id, ':uniacid' => $uniacid));
        }

        if ($_W['ispost']) {
            $data = $_GPC['data'];
            load_model_class('delivery')->adddelivery_clerk($data);
			
            show_json(1, array('url' => shopUrl('delivery/delivery_clerk') ));
        }
		
		include $this->display();
	}
	
	
	public function queryclerk()
	{
		global $_W;
		global $_GPC;
		$kwd = trim($_GPC['keyword']);
		$is_ajax = isset($_GPC['is_ajax']) ? intval($_GPC['is_ajax']) : 0;
		
		$params = array();
		$params[':uniacid'] = $_W['uniacid'];
		$condition = ' and uniacid=:uniacid and line_id<= 0 ';

		if (!empty($kwd)) {
			$condition .= ' AND ( `name` LIKE :keyword or `mobile` LIKE :keyword )';
			$params[':keyword'] = '%' . $kwd . '%';
		}

		$ds = pdo_fetchall('SELECT * FROM ' . tablename('lionfish_comshop_deliveryclerk') . ' WHERE 1 ' . $condition . ' order by id asc', $params);

		foreach ($ds as &$value) {
			$value['nickname'] = htmlspecialchars($value['name'], ENT_QUOTES);
			$value['avatar'] = tomedia($value['logo']);

			if($is_ajax == 1)
			{
				$ret_html .= '<tr>';
				$ret_html .= '	<td><img src="'.$value['avatar'].'" style="width:30px;height:30px;padding1px;border:1px solid #ccc" />'. $value['nickname'].'</td>';
				   
				$ret_html .= '	<td>'.$value['mobile'].'</td>';
				$ret_html .= '	<td style="width:80px;"><a href="javascript:;" class="choose_dan_link" data-json=\''.json_encode($value).'\'>选择</a></td>';
				$ret_html .= '</tr>';
			}
			
		}

		if( $is_ajax == 1 )
		{
			echo json_encode( array('code' => 0, 'html' => $ret_html) );
			die();
		}

		unset($value);


		include $this->display('delivery/queryclerk');
		
	}
	
	public function adddeliverylist()
	{
		global $_W;
        global $_GPC;
      
		$uniacid            = $_W['uniacid'];
        $params[':uniacid'] = $uniacid;

        $id = intval($_GPC['id']);
        if (!empty($id)) {
            $item = pdo_fetch('SELECT * from ' . tablename('lionfish_comshop_deliveryline') . "\r\n
			WHERE id=:id and uniacid=:uniacid limit 1 ", array(':id' => $id, ':uniacid' => $uniacid));
			
			//clerk_id
			$saler = pdo_fetch("select id,name as nickname, logo as avatar   from ".tablename('lionfish_comshop_deliveryclerk')." where uniacid=:uniacid and id=:clerk_id ",
					 array(':uniacid' => $_W['uniacid'], ':clerk_id' => $item['clerk_id'] ));
			
			
			$headlist = array();
			
			$head_relative = pdo_fetchall("select * from ".tablename('lionfish_comshop_deliveryline_headrelative')." 
								where uniacid=:uniacid and line_id=:line_id order by id asc ", array(':uniacid' => $_W['uniacid'], ':line_id' => $item['id']));
		
			if( !empty($head_relative) )
			{
				$head_id_arr = array();
				foreach($head_relative as $vv)
				{
					$head_id_arr[] = $vv['head_id'];
				}
				$headlist = pdo_fetchall("select id,community_name from ".tablename('lionfish_community_head')." 
							where uniacid=:uniacid and id in (".implode(',', $head_id_arr ).")", array(':uniacid' => $_W['uniacid'] ) );
			}
        }

        if ($_W['ispost']) {
            $data = $_GPC['data'];
			
            $clerk_id = $_GPC['clerk_id'];
            $head_id = $_GPC['head_id'];
			
			$data['clerk_id'] = $clerk_id;
			$data['head_id'] = $head_id;
			
			
            load_model_class('delivery')->adddeliverylist($data);
            show_json(1, array('url' => referer()));
        }
		
		include $this->display();
	}
	
	
	public function article()
	{
		$this->main();
	}

	/**
     * 编辑添加
     */
	public function add()
	{
		global $_W;
        global $_GPC;
        $uniacid            = $_W['uniacid'];
        $params[':uniacid'] = $uniacid;

        $id = intval($_GPC['id']);
        if (!empty($id)) {
            $item = pdo_fetch('SELECT * from ' . tablename('lionfish_comshop_article') . "\r\n
			WHERE id=:id and uniacid=:uniacid limit 1 ", array(':id' => $id, ':uniacid' => $uniacid));
        }

        if ($_W['ispost']) {
            $data = $_GPC['data'];
            load_model_class('article')->update($data);
            show_json(1, array('url' => referer()));
        }

		include $this->display('article/post');
	}

	/**
     * 改变状态
     */
    public function change()
    {

        global $_W;
        global $_GPC;
        $id = intval($_GPC['id']);

        //ids
        if (empty($id)) {
            $id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
        }

        if (empty($id)) {
            show_json(0, array('message' => '参数错误'));
        }

        $type  = trim($_GPC['type']);
        $value = trim($_GPC['value']);

        if (!(in_array($type, array('enabled', 'displayorder')))) {
            show_json(0, array('message' => '参数错误'));
        }

        $items = pdo_fetchall('SELECT id FROM ' . tablename('lionfish_comshop_article') . ' WHERE id in( ' . $id . ' ) AND uniacid=' . $_W['uniacid']);

        foreach ($items as $item) {
            pdo_update('lionfish_comshop_article', array($type => $value), array('id' => $item['id']));
        }

        show_json(1);

    }
	
	public function deldelivery_clerk()
	{
		global $_W;
        global $_GPC;
		
		$id = intval($_GPC['id']);

        if (empty($id)) {
            $id = (is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0);
        }

        $items = pdo_fetchall('SELECT id FROM ' . tablename('lionfish_comshop_deliveryclerk') . ' WHERE id in( ' . $id . ' ) AND uniacid=' . $_W['uniacid']);

        if (empty($item)) {
            $item = array();
        }

        foreach ($items as $item) {
            pdo_delete('lionfish_comshop_deliveryclerk', array('id' => $item['id']));
        }

        show_json(1, array('url' => referer()));
	}
	
	/**
	 * 删除公告
	 */
    public function delete()
    {
        global $_W;
        global $_GPC;
        $id = intval($_GPC['id']);

        if (empty($id)) {
            $id = (is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0);
        }

        $items = pdo_fetchall('SELECT id,title FROM ' . tablename('lionfish_comshop_article') . ' WHERE id in( ' . $id . ' ) AND uniacid=' . $_W['uniacid']);

        if (empty($item)) {
            $item = array();
        }

        foreach ($items as $item) {
            pdo_delete('lionfish_comshop_article', array('id' => $item['id']));
            snialshoplog('article.delete', '删除文章<br/>ID: ' . $item['id'] . '<br/>文章标题: ' . $item['title']);
        }

        show_json(1, array('url' => referer()));
    }

}