<?php

/**
 * @Author: J_da
 * @Date:   2018-12-13 19:37:41
 * @Last Modified by:   J_da
 * @Last Modified time: 2018-12-13 20:20:47
 */
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class Article_Snailfishshop extends MobileController
{
	public function main()
	{
		global $_W;
		global $_GPC;
		echo json_encode( array('code' =>0) );
		die();
	}

	public function get_article_list()
	{
		global $_W;
		global $_GPC;

		$uniacid = $_W['uniacid'];
		
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

		$param = array();
		$param[':enabled'] = 1;
		$param[':uniacid'] = $_W['uniacid'];
		$list = pdo_fetchall("select * from ".tablename('lionfish_comshop_article')." where uniacid=:uniacid and enabled=:enabled order by displayorder desc limit 10 ", $param);

		if( empty($list) )
		{
			echo json_encode(array('code' => 1));
			die();
		}else{
			echo json_encode( array('code' =>0, 'data' => $list) );
			die();
		}

	}

	public function get_article()
	{
		global $_W;
		global $_GPC;

		$uniacid = $_W['uniacid'];
		
		$token = $_GPC['token'];
		$id = $_GPC['id'];
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

		$param = array();
		$param[':id'] = $id;
		$param[':enabled'] = 1;
		$param[':uniacid'] = $_W['uniacid'];
		$list = pdo_fetch("select * from ".tablename('lionfish_comshop_article')." where id=:id and uniacid=:uniacid and enabled=:enabled ", $param);
		//htmlspecialchars_decode
		$list["content"] = htmlspecialchars_decode($list["content"]);
		
		if( empty($list) )
		{
			echo json_encode(array('code' => 1));
			die();
		}else{
			echo json_encode( array('code' =>0, 'data' => $list) );
			die();
		}

	}

}