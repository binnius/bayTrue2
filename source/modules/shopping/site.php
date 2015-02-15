<?php
/**
 * 微商城模块微站定义
 *
 * @author WeEngine Team
 * @url
 */
defined('IN_IA') or exit('Access Denied');
session_start();
class ShoppingModuleSite extends WeModuleSite {
	
	public function doWebCategory() {
		global $_GPC, $_W;
		$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		if ($operation == 'display') {
			if (!empty($_GPC['displayorder'])) {
				foreach ($_GPC['displayorder'] as $id => $displayorder) {
					pdo_update('shopping_category', array('displayorder' => $displayorder), array('id' => $id));
				}
				message('分类排序更新成功！', $this->createWebUrl('category', array('op' => 'display')), 'success');
			}
			$children = array();
			$category = pdo_fetchall("SELECT * FROM ".tablename('shopping_category')." WHERE weid = '{$_W['weid']}' ORDER BY parentid ASC, displayorder DESC");
			foreach ($category as $index => $row) {
				if (!empty($row['parentid'])){
					$children[$row['parentid']][] = $row;
					unset($category[$index]);
				}
			}
			include $this->template('category');
		} elseif ($operation == 'post') {
			$parentid = intval($_GPC['parentid']);
			$id = intval($_GPC['id']);
			if(!empty($id)) {
				$category = pdo_fetch("SELECT * FROM ".tablename('shopping_category')." WHERE id = '$id'");
			} else {
				$category = array(
					'displayorder' => 0,
				);
			}
			if (!empty($parentid)) {
				$parent = pdo_fetch("SELECT id, name FROM ".tablename('shopping_category')." WHERE id = '$parentid'");
				if (empty($parent)) {
					message('抱歉，上级分类不存在或是已经被删除！', $this->createWebUrl('post'), 'error');
				}
			}
			if (checksubmit('submit')) {
				if (empty($_GPC['name'])) {
					message('抱歉，请输入分类名称！');
				}
				$data = array(
					'weid' => $_W['weid'],
					'name' => $_GPC['catename'],
					'displayorder' => intval($_GPC['displayorder']),
					'parentid' => intval($parentid),
				);
				if (!empty($id)) {
					unset($data['parentid']);
					pdo_update('shopping_category', $data, array('id' => $id));
				} else {
					pdo_insert('shopping_category', $data);
					$id = pdo_insertid();
				}
				message('更新分类成功！', $this->createWebUrl('category', array('op' => 'display')), 'success');
			}
			include $this->template('category');
		} elseif ($operation == 'delete') {
			$id = intval($_GPC['id']);
			$category = pdo_fetch("SELECT id, parentid FROM ".tablename('shopping_category')." WHERE id = '$id'");
			if (empty($category)) {
				message('抱歉，分类不存在或是已经被删除！', $this->createWebUrl('category', array('op' => 'display')), 'error');
			}
			pdo_delete('shopping_category', array('id' => $id, 'parentid' => $id), 'OR');
			message('分类删除成功！', $this->createWebUrl('category', array('op' => 'display')), 'success');
		}
	}

	public function doWebGoods() {
		global $_GPC, $_W;
		$category = pdo_fetchall("SELECT * FROM ".tablename('shopping_category')." WHERE weid = '{$_W['weid']}' ORDER BY parentid ASC, displayorder DESC", array(), 'id');
		if (!empty($category)) {
			$children = '';
			foreach ($category as $cid => $cate) {
				if (!empty($cate['parentid'])) {
					$children[$cate['parentid']][$cate['id']] = array($cate['id'], $cate['name']);
				}
			}
		}

		$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		if ($operation == 'post') {
			$id = intval($_GPC['id']);
			if (!empty($id)) {
				$item = pdo_fetch("SELECT * FROM ".tablename('shopping_goods')." WHERE id = :id" , array(':id' => $id));
				if (empty($item)) {
					message('抱歉，商品不存在或是已经删除！', '', 'error');
				}
				if (!empty($item['spec'])) {
					$item['spec'] = iunserializer($item['spec']);
					$products = pdo_fetchall("SELECT * FROM ".tablename('shopping_product')." WHERE goodsid = '{$item['id']}'");
					if (!empty($products)) {
						foreach ($products as &$row) {
							$row['spec'] = iunserializer($row['spec']);
						}
						unset($row);
					}
				}
			}
			if (empty($category)) {
				message('抱歉，请您先添加商品分类！', $this->createWebUrl('category', array('op' => 'post')), 'error');
			}
			if (checksubmit('submit')) {
				if (empty($_GPC['goodsname'])) {
					message('请输入商品名称！');
				}
				if (empty($_GPC['pcate'])) {
					message('请选择商品分类！');
				}
				$data = array(
					'weid' => intval($_W['weid']),
					'displayorder' => intval($_GPC['displayorder']),
					'title' => $_GPC['goodsname'],
					'pcate' => intval($_GPC['pcate']),
					'ccate' => intval($_GPC['ccate']),
					'type' => intval($_GPC['type']),
					'description' => $_GPC['description'],
					'content' => htmlspecialchars_decode($_GPC['content']),
					'goodssn' => $_GPC['goodssn'],
					'unit' => $_GPC['unit'],
					'createtime' => TIMESTAMP,
				);
				if (!empty($_GPC['thumb'])) {
					$data['thumb'] = $_GPC['thumb'];
					file_delete($_GPC['thumb-old']);
				}
				if (empty($_GPC['spec'])) {
					//$data['spec'] = '';
					$data['productsn'] = $_GPC['productsn-row'];
					$data['marketprice'] = $_GPC['marketprice-row'];
					$data['productprice'] = $_GPC['productprice-row'];
					$data['total'] = !empty($_GPC['unlimited-row']) ? -1 : intval($_GPC['total-row']);
					$data['status'] = intval($_GPC['status-row']);
					if (empty($id)) {
						pdo_insert('shopping_goods', $data);
					} else {
						unset($data['createtime']);
						pdo_update('shopping_goods', $data, array('id' => $id));
					}
				} else {
					/**
					foreach ($_GPC['spec']['id'] as $i => $row) {
						$specs[$row] = $_GPC['spec']['title'][$i];
					}
					$data['spec'] = iserializer($specs);
					**/
					if (empty($id)) {
						pdo_insert('shopping_goods', $data);
						$id = pdo_insertid();
					} else {
						unset($data['createtime']);
						pdo_update('shopping_goods', $data, array('id' => $id));
					}
					if (!empty($_GPC['productsn-new'])) {
						foreach ($_GPC['productsn-new'] as $i => $productsn) {
							if (empty($productsn)) {
								continue;
							}
							$specvalue = array();
							foreach ($specs as $j => $val) {
								$specvalue[$j] = $_GPC['specvalue-new'][$j][$i];
							}
							pdo_insert('shopping_product', array(
								'goodsid' => $id,
								'productsn' => $productsn,
								'title' => implode(',', $specvalue),
								'marketprice' => $_GPC['marketprice-new'][$i],
								'productprice' => $_GPC['productprice-new'][$i],
								'total' => !empty($_GPC['unlimited-new'][$i]) ? -1 : intval($_GPC['total-new'][$i]),
								'status' => intval($_GPC['status-new'][$i]),
								'spec' => iserializer($specvalue),
							));
						}
					}
					if (!empty($_GPC['productsn'])) {
						foreach ($_GPC['productsn'] as $i => $productsn) {
							if (empty($productsn)) {
								continue;
							}
							$specvalue = array();
							foreach ($specs as $j => $val) {
								$specvalue[$j] = $_GPC['specvalue'][$j][$i];
							}
							pdo_update('shopping_product', array(
								'productsn' => $productsn,
								'title' => implode(',', $specvalue),
								'marketprice' => $_GPC['marketprice'][$i],
								'productprice' => $_GPC['productprice'][$i],
								'total' => !empty($_GPC['unlimited'][$i]) ? -1 : intval($_GPC['total'][$i]),
								'status' => intval($_GPC['status'][$i]),
								'spec' => iserializer($specvalue),
							), array('id' => $i));
						}
					}
				}
				message('商品更新成功！', $this->createWebUrl('goods', array('op' => 'display')), 'success');
			}
			//获取规格
			/**
			$specs = pdo_fetchall("SELECT * FROM ".tablename('shopping_spec')." WHERE weid = '{$_W['weid']}'", array(), 'id');
			if (!empty($specs)) {
				foreach ($specs as &$row) {
					$row['content'] = iunserializer($row['content']);
				}
			}
			unset($row);
			**/
		} elseif ($operation == 'display') {
			$pindex = max(1, intval($_GPC['page']));
			$psize = 20;
			$condition = '';
			if (!empty($_GPC['keyword'])) {
				$condition .= " AND title LIKE '%{$_GPC['keyword']}%'";
			}

			if (!empty($_GPC['cate_2'])) {
				$cid = intval($_GPC['cate_2']);
				$condition .= " AND ccate = '{$cid}'";
			} elseif (!empty($_GPC['cate_1'])) {
				$cid = intval($_GPC['cate_1']);
				$condition .= " AND pcate = '{$cid}'";
			}

			if (isset($_GPC['status'])) {
				$condition .= " AND status = '".intval($_GPC['status'])."'";
			}

			$list = pdo_fetchall("SELECT * FROM ".tablename('shopping_goods')." WHERE weid = '{$_W['weid']}' $condition ORDER BY status DESC, displayorder DESC, id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('shopping_goods') . " WHERE weid = '{$_W['weid']}'");
			$pager = pagination($total, $pindex, $psize);
		} elseif ($operation == 'delete') {
			$id = intval($_GPC['id']);
			$row = pdo_fetch("SELECT id, thumb FROM ".tablename('shopping_goods')." WHERE id = :id", array(':id' => $id));
			if (empty($row)) {
				message('抱歉，商品不存在或是已经被删除！');
			}
			if (!empty($row['thumb'])) {
				file_delete($row['thumb']);
			}
			pdo_delete('shopping_goods', array('id' => $id));
			message('删除成功！', referer(), 'success');
		} elseif ($operation == 'productdelete') {
			$id = intval($_GPC['id']);
			pdo_delete('shopping_product', array('id' => $id));
			message('删除成功！', '', 'success');
		}
		include $this->template('goods');
	}

	public function doWebOrder() {
		global $_W, $_GPC;
		$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		if ($operation == 'display') {
			$pindex = max(1, intval($_GPC['page']));
			$psize = 20;
			$status = !isset($_GPC['status']) ? 1 : $_GPC['status'];
			$condition = '';
			if (!empty($_GPC['keyword'])) {
				$condition .= " AND title LIKE '%{$_GPC['keyword']}%'";
			}

			if (!empty($_GPC['cate_2'])) {
				$cid = intval($_GPC['cate_2']);
				$condition .= " AND ccate = '{$cid}'";
			} elseif (!empty($_GPC['cate_1'])) {
				$cid = intval($_GPC['cate_1']);
				$condition .= " AND pcate = '{$cid}'";
			}
			
			if ($status != '-1') {
				$condition .= " AND status = '".intval($status)."'";
			}
			
			$list = pdo_fetchall("SELECT * FROM ".tablename('shopping_order')." WHERE weid = '{$_W['weid']}' $condition ORDER BY status ASC, createtime DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('shopping_order') . " WHERE weid = '{$_W['weid']}'");
			$pager = pagination($total, $pindex, $psize);
			if (!empty($list)) {
				foreach ($list as $row) {
					!empty($row['addressid']) && $addressids[$row['addressid']] = $row['addressid'];
				}
			}
			if (!empty($addressids)) {
				$address = pdo_fetchall("SELECT * FROM ".tablename('shopping_address')." WHERE id IN ('".implode("','", $addressids)."')", array(), 'id');
			}
		} elseif ($operation == 'detail') {
			$id = intval($_GPC['id']);
			if (checksubmit('confirmsend')) {
				if (!empty($_GPC['isexpress']) && empty($_GPC['expresssn'])) {
					message('请输入快递单号！');
				}
				$item = pdo_fetch("SELECT transid FROM ".tablename('shopping_order')." WHERE id = :id", array(':id' => $id));
				if (!empty($item['transid'])) {
					$this->changeWechatSend($id, 1);
				}
				pdo_update('shopping_order', array(
					'status' => 2, 
					'remark' => $_GPC['remark'],
					'expresscom' => $_GPC['expresscom'],
					'expresssn' => $_GPC['expresssn'],
				), array('id' => $id));
				message('发货操作成功！', referer(), 'success');
			}
			if (checksubmit('cancelsend')) {
				$item = pdo_fetch("SELECT transid FROM ".tablename('shopping_order')." WHERE id = :id", array(':id' => $id));
				if (!empty($item['transid'])) {
					$this->changeWechatSend($id, 0, $_GPC['cancelreson']);
				}
				pdo_update('shopping_order', array(
					'status' => 1,
					'remark' => $_GPC['remark'],
				), array('id' => $id));
				message('取消发货操作成功！', referer(), 'success');
			}
			if (checksubmit('finish')) {
				pdo_update('shopping_order', array('status' => 3, 'remark' => $_GPC['remark']), array('id' => $id));
				message('订单操作成功！', referer(), 'success');
			}
			if (checksubmit('cancel')) {
				pdo_update('shopping_order', array('status' => 1, 'remark' => $_GPC['remark']), array('id' => $id));
				message('取消完成订单操作成功！', referer(), 'success');
			}
			if (checksubmit('cancelpay')) {
				pdo_update('shopping_order', array('status' => 0, 'remark' => $_GPC['remark']), array('id' => $id));
				message('取消订单付款操作成功！', referer(), 'success');
			}
			if (checksubmit('confrimpay')) {
				pdo_update('shopping_order', array('status' => 1, 'paytype' => 2, 'remark' => $_GPC['remark']), array('id' => $id));
				message('确认订单付款操作成功！', referer(), 'success');
			}
			if (checksubmit('close')) {
				$item = pdo_fetch("SELECT transid FROM ".tablename('shopping_order')." WHERE id = :id", array(':id' => $id));
				if (!empty($item['transid'])) {
					$this->changeWechatSend($id, 0, $_GPC['reson']);
				}
				pdo_update('shopping_order', array('status' => -1, 'remark' => $_GPC['remark']), array('id' => $id));
				message('订单关闭操作成功！', referer(), 'success');
			}
			if (checksubmit('open')) {
				pdo_update('shopping_order', array('status' => 0, 'remark' => $_GPC['remark']), array('id' => $id));
				message('开启订单操作成功！', referer(), 'success');
			}
			$item = pdo_fetch("SELECT * FROM ".tablename('shopping_order')." WHERE id = :id", array(':id' => $id));
			$item['user'] = pdo_fetch("SELECT * FROM ".tablename('shopping_address')." WHERE id = {$item['addressid']}");
			$goodsid = pdo_fetchall("SELECT goodsid, total FROM ".tablename('shopping_order_goods')." WHERE orderid = '{$item['id']}'", array(), 'goodsid');
			$goods = pdo_fetchall("SELECT * FROM ".tablename('shopping_goods')."  WHERE id IN ('".implode("','", array_keys($goodsid))."')");
			$item['goods'] = $goods;
		}
		include $this->template('order');
	}
	
	public function doWebSpec() {
		global $_W, $_GPC;
		$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'post';
		
		if ($operation == 'post') {
			$id = intval($_GPC['id']);
			if (!empty($id)) {
				$item = pdo_fetch("SELECT * FROM ".tablename('shopping_spec')." WHERE id = :id" , array(':id' => $id));
				if (empty($item)) {
					message('抱歉，商品不存在或是已经删除！', '', 'error');
				}
				$item['content'] = iunserializer($item['content']);
			}
			if (checksubmit('submit')) {
				if (empty($_GPC['title'])) {
					message('请输入规格的名称！');
				}
				if (empty($_GPC['spec'])) {
					message('请添加规格项目！');
				}
				$data = array(
					'weid' => intval($_W['weid']),
					'title' => $_GPC['title'],
					'description' => $_GPC['description'],
					'displaytype' => intval($_GPC['displaytype']),
					'content' => iserializer($_GPC['spec']),
				);
				if (empty($id)) {
					pdo_insert('shopping_spec', $data);
				} else {
					pdo_update('shopping_spec', $data, array('id' => $id));
				}
				message('商品规格更新成功！', $this->createWebUrl('spec', array('op' => 'display')), 'success');
			}
		} elseif ($operation == 'display') {
			$pindex = max(1, intval($_GPC['page']));
			$psize = 20;
			$condition = '';
		
			$list = pdo_fetchall("SELECT * FROM ".tablename('shopping_spec')." WHERE weid = '{$_W['weid']}' ORDER BY id ASC LIMIT ".($pindex - 1) * $psize.','.$psize);
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('shopping_spec') . " WHERE weid = '{$_W['weid']}'");
			$pager = pagination($total, $pindex, $psize);
		} elseif ($operation == 'delete') {
			$id = intval($_GPC['id']);
			$row = pdo_fetch("SELECT id, thumb FROM ".tablename('shopping_goods')." WHERE id = :id", array(':id' => $id));
			if (empty($row)) {
				message('抱歉，商品不存在或是已经被删除！');
			}
			if (!empty($row['thumb'])) {
				file_delete($row['thumb']);
			}
			pdo_delete('shopping_goods', array('id' => $id));
			message('删除成功！', referer(), 'success');
		}
		include $this->template('spec');
	}

	public function doWebDeleteImage() {
		global $_GPC, $_W;
		$setting = $_W['account']['modules'][$this->_saveing_params['mid']]['config'];
		$setting['picurl'] = '';
		$this->saveSettings($setting);
		message('删除图片成功！', '', 'success');
	}
	
	public function doWebNotice(){
		global $_GPC,$_W;
		
		$operation = empty($_GPC['op']) ? 'display' : $_GPC['op'];
		$operation = in_array($operation, array('display')) ? $operation : 'display';
		
		$pindex = max(1, intval($_GPC['page']));
		$psize = 50;
			
		$starttime = empty($_GPC['starttime']) ? strtotime('-1 month') : strtotime($_GPC['starttime']);
		$endtime = empty($_GPC['endtime']) ? TIMESTAMP : strtotime($_GPC['endtime']) + 86399;
			
		$where .= " WHERE `weid` = :weid AND `createtime` >= :starttime AND `createtime` < :endtime";
		$paras = array(
			':weid' => $_W['weid'],
			':starttime' => $starttime,
			':endtime' => $endtime
		);
		$keyword = $_GPC['keyword'];
		if (!empty($keyword)) {
			$where .= " AND `feedbackid`=:feedbackid";
			$paras[':feedbackid'] = $keyword;
		}
		
		$type = empty($_GPC['type']) ? 0 : $_GPC['type'];
		$type = intval($type);
		if ($type != 0) {
			$where .= " AND `type`=:type";
			$paras[':type'] = $type;
		}
		$status = empty($_GPC['status']) ? 0 : intval ($_GPC['status']);
		$status = intval($status);
		if ($status!=-1) {
			$where .= " AND `status` = :status";
			$paras[':status'] = $status;
		}
			
		$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('shopping_feedback').$where, $paras);
		$list = pdo_fetchall("SELECT * FROM ".tablename('shopping_feedback').$where." ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize, $paras);
		$pager = pagination($total, $pindex, $psize);

		$transids = array();
		foreach ($list as $row) {
			$transids[] = $row['transid'];
		}
		if (!empty($transids)) {
			$sql = "SELECT * FROM ".tablename('shopping_order')." WHERE weid='{$_W['weid']}' AND transid IN ( '".implode("','", $transids)."' )";
			$orders = pdo_fetchall($sql, array(), 'transid');
		}
		$addressids = array();
		foreach ($orders as $transid => $order) {
			$addressids[] = $order['addressid'];
		}
		$addresses = array();
		if (!empty($addressids)) {
			$sql = "SELECT * FROM ".tablename('shopping_address')." WHERE weid='{$_W['weid']}' AND id IN ( '".implode("','", $addressids)."' )";
			$addresses = pdo_fetchall($sql, array(), 'id');
		}
		
		foreach ($list as &$feedback) {
			$transid = $feedback['transid'];
			$order = $orders[$transid];
			$feedback['order'] = $order;
			$addressid = $order['addressid'];
			$feedback['address'] = $addresses[$addressid];
		}
		
		include $this->template('notice');
	}
	
	private function getFeedbackType($type){
		$types = array(1=>'维权',2=>'告警');
		return $types[intval($type)];
	}
	
	private function getFeedbackStatus($status){
		$statuses = array('未解决','用户同意','用户拒绝');
		return $statuses[intval($status)];
	}
	
	public function doMobilelist() {
		global $_GPC, $_W;
		$cart = $this->getCartGoods();
		
		$pindex = max(1, intval($_GPC['page']));
		$psize = 10;
		$condition = '';
		if (!empty($_GPC['ccate'])) {
			$cid = intval($_GPC['ccate']);
			$condition .= " AND ccate = '{$cid}'";
		} elseif (!empty($_GPC['pcate'])) {
			$cid = intval($_GPC['pcate']);
			$condition .= " AND pcate = '{$cid}'";
		}
		if (!empty($_GPC['keyword'])) {
			$condition .= " AND title LIKE '%{$_GPC['keyword']}%'";
		}
		$children = array();
		$category = pdo_fetchall("SELECT * FROM ".tablename('shopping_category')." WHERE weid = '{$_W['weid']}' ORDER BY parentid ASC, displayorder DESC");
		foreach ($category as $index => $row) {
			if (!empty($row['parentid'])){
				$children[$row['parentid']][] = $row;
				unset($category[$index]);
			}
		}
		$list = pdo_fetchall("SELECT * FROM ".tablename('shopping_goods')." WHERE weid = '{$_W['weid']}' AND status = '1' $condition ORDER BY displayorder DESC, sales DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('shopping_goods') . " WHERE weid = '{$_W['weid']}' AND status = '1' $condition");
		$pager = pagination($total, $pindex, $psize, $url = '', $context = array('before' => 0, 'after' => 0, 'ajaxcallback' => ''));
		
		$carttotal = 0;
		if (!empty($cart['goods'])) {
			foreach ($cart['goods'] as $row) {
				$carttotal += $row['total'];
			}
		}
		include $this->template('list');
	}

	public function doMobileUpdateCart() {
		global $_GPC, $_W;
		$cart =$this->getCartGoods();
		
		$result = array('status' => 0, 'message' => '');
		$operation = $_GPC['op'];
		$goodsid = intval($_GPC['goodsid']);
		$goods = pdo_fetch("SELECT id, type, total FROM ".tablename('shopping_goods')." WHERE id = :id", array(':id' => $goodsid));
		if (empty($goods)) {
			$result['message'] = '抱歉，该商品不存在或是已经被删除！';
			message($result, '', 'ajax');
		}
		if ($goods['total'] == 0) {
			$result['message'] = '抱歉，该商品库存不足！';
			message($result, '', 'ajax');
		}
		if (empty($cart['type'])) {
			$cart['type'] = $goods['type'];
		} else {
			if ($cart['type'] != $goods['type']) {
				$result['message'] = '抱歉，虚拟物品与实体物品请分开购买！';
				message($result, '', 'ajax');
			}
		}
		
		if (empty($cart['goods'][$goodsid])) {
			if ($operation == 'reduce') {
				$result['message'] = '您并没有购买此商品！';
				$result['total'] = 0;
				message($result, '', 'ajax');
			}
			$data = array(
				'weid' => $_W['weid'],
				'goodsid' => $goodsid,
				'goodstype' => $goods['type'],
				'total' => '1',
			);
			$cart['goods'][$goodsid] = $data;
		} else {
			$cart['goods'][$goodsid]['total'] = ($operation == 'reduce') ? ($cart['goods'][$goodsid]['total'] - 1) : ($cart['goods'][$goodsid]['total'] + 1);
			if ($goods['total'] > -1 && $cart['goods'][$goodsid]['total'] > $goods['total']) {
				$result['message'] = '抱歉，该商品库存不足！';
				message($result, '', 'ajax');
			}
			if (empty($cart['goods'][$goodsid]['total'])) {
				unset($cart['goods'][$goodsid]);
			}
		}
		if (!empty($cart['goods'])) {
			foreach ($cart['goods'] as $row) {
				$total += $row['total'];
			}
		}
		$this->setCartGoods($cart);
		$result['status'] = 1;
		$result['message'] = '商品数据更新成功！';
		$result['total'] = intval($cart['goods'][$goodsid]['total']);
		$result['carttotal'] = intval($total);
		message($result, '', 'ajax');
	}

	public function doMobileMyCart() {
		global $_W, $_GPC;
		$this->checkAuth();
		$cart = $this->getCartGoods();
		
		if (!empty($cart['goods'])) {
			$goods = pdo_fetchall("SELECT id, title, thumb, marketprice, unit FROM ".tablename('shopping_goods')." WHERE id IN ('".implode("','", array_keys($cart['goods']))."')");
		}
		if (empty($goods)) {
			message('抱歉，您的购物车里没有任何商品，请先购买！', $this->createMobileUrl('list'), 'error');
		}
		include $this->template('cart');
	}
	
	public function doMobileConfirm() {
		global $_W, $_GPC;
		$this->checkAuth();
		$cart = $this->getCartGoods();
		
		if (empty($cart['goods'])) {
			message('抱歉，您的购物车里没有任何商品，请先购买！', $this->createMobileUrl('list'), 'error');
		}
		if (!empty($cart['goods'])) {
			$goods = pdo_fetchall("SELECT id, title, thumb, marketprice, unit, total FROM ".tablename('shopping_goods')." WHERE id IN ('".implode("','", array_keys($cart['goods']))."')");
		}
		if (empty($goods)) {
			message('抱歉，您的购物车里没有任何商品，请先购买！', $this->createMobileUrl('list'), 'error');
		}
		
		if (checksubmit('submit')) {
			$address = pdo_fetch("SELECT * FROM ".tablename('shopping_address')." WHERE id = :id", array(':id' => intval($_GPC['address'])));
			if (empty($address)) {
				message('抱歉，请您填写收货地址！');
			}
			foreach ($goods as $row) {
				if (empty($cart['goods'][$row['id']]['total'])) {
					continue;
				}
				if ($row['total'] != -1 && $row['total'] < $cart['goods'][$row['id']]['total']) {
					message('抱歉，“'.$row['title'].'”此商品库存不足！', $this->createMobileUrl('confirm'), 'error');
				}
				$price += (floatval($row['marketprice']) * intval($cart['goods'][$row['id']]['total']));
			}
			$data = array(
				'weid' => $_W['weid'],
				'from_user' => $_W['fans']['from_user'],
				'ordersn' => date('md') . random(4, 1),
				'price' => $price,
				'status' => 0,
				'sendtype' => intval($_GPC['sendtype']),
				'paytype' => '2',
				'goodstype' => intval($cart['type']),
				'remark' => $_GPC['remark'],
				'addressid' => $address['id'],
				'createtime' => TIMESTAMP,
			);
			pdo_insert('shopping_order', $data);
			$orderid = pdo_insertid();
			//插入订单商品
			foreach ($goods as $row) {
				if (empty($row)) {
					continue;
				}
				pdo_insert('shopping_order_goods', array(
					'weid' => $_W['weid'],
					'goodsid' => $row['id'],
					'orderid' => $orderid,
					'total' => $cart['goods'][$row['id']]['total'],
					'createtime' => TIMESTAMP,
				));
			}
			//清空购物车
			$this->setCartGoods(array());
			//变更商品库存
			if (!empty($goods)) {
				foreach ($goods as $row) {
					$data = array();
					if (!empty($cart['goods'][$row['id']]['total']) && $row['total'] != -1) {
						$data['total'] = $row['total'] - $cart['goods'][$row['id']]['total'];
					}
					$data['sales'] = $row['sales'] + $cart['goods'][$row['id']]['total'];
					pdo_update('shopping_goods', $data, array('id' => $row['id']));
				}
			}
			message('提交订单成功，现在跳转至付款页面...', $this->createMobileUrl('pay', array('orderid' => $orderid)), 'success');
		}
		$carttotal = 0;
		foreach ($cart['goods'] as $row) {
			$carttotal += $row['total'];
		}
		$profile = fans_search($_W['fans']['from_user'], array('resideprovince', 'residecity', 'residedist', 'address', 'realname', 'mobile'));
		$address = pdo_fetchall("SELECT * FROM ".tablename('shopping_address')." WHERE openid = :openid", array(':openid' => $_W['fans']['from_user']));
		include $this->template('confirm');
	}

	public function doMobilePay() {
		global $_W, $_GPC;
		$this->checkAuth();
		$orderid = intval($_GPC['orderid']);
		$order = pdo_fetch("SELECT * FROM ".tablename('shopping_order')." WHERE id = :id", array(':id' => $orderid));
		if ($order['status'] != '0') {
			message('抱歉，您的订单已经付款或是被关闭，请重新进入付款！', $this->createMobileUrl('myorder'), 'error');
		}
		if (checksubmit('codsubmit')) {
			//邮件提醒
			if (!empty($this->module['config']['noticeemail'])) {
				$ordergoods = pdo_fetchall("SELECT goodsid, total FROM ".tablename('shopping_order_goods')." WHERE orderid = '{$orderid}'", array(), 'goodsid');
				if (!empty($ordergoods)) {
					$goods = pdo_fetchall("SELECT id, title, thumb, marketprice, unit, total FROM ".tablename('shopping_goods')." WHERE id IN ('".implode("','", array_keys($ordergoods))."')");
				}
				$address = pdo_fetch("SELECT * FROM ".tablename('shopping_address')." WHERE id = :id", array(':id' => $order['addressid']));
				$body = "<h3>购买商品清单</h3> <br />";
				if (!empty($goods)) {
					foreach ($goods as $row) {
						$body .= "名称：{$row['title']} ，数量：{$ordergoods[$row['id']]['total']} <br />";
					}
				}
				$body .= "<br />总金额：{$order['price']}元 （已付款）<br />";
				$body .= "<h3>购买用户详情</h3> <br />";
				$body .= "真实姓名：{$address['realname']} <br />";
				$body .= "地区：{$address['province']} - {$address['city']} - {$address['area']}<br />";
				$body .= "详细地址：{$address['address']} <br />";
				$body .= "手机：{$address['mobile']} <br />";
				ihttp_email($this->module['config']['noticeemail'], '微商城订单提醒', $body);
			}
			pdo_update('shopping_order', array('status' => '1', 'paytype' => '3'), array('id' => $orderid));
			message('订单提交成功，请您收到货时付款！', $this->createMobileUrl('myorder'), 'success');
		}
		
		if (checksubmit()) {
			if ($order['paytype'] == 1 && $_W['fans']['credit2'] < $order['price']) {
				message('抱歉，您帐户的余额不够支付该订单，请充值！', create_url('mobile/module/charge', array('name' => 'member', 'weid' => $_W['weid'])), 'error');
			}
			if ($order['price'] == '0') {
				$this->payResult(array('tid' => $orderid, 'from' => 'return', 'type' => 'credit2'));
				exit;
			}
		}
		$params['tid'] = $orderid;
		$params['user'] = $_W['fans']['from_user'];
		$params['fee'] = $order['price'];
		$params['title'] = $_W['account']['name'];
		$params['ordersn'] = $order['ordersn'];
		$params['virtual'] = $order['goodstype'] == 2 ? true : false;
		include $this->template('pay');
	}

	public function doMobileClear() {
		global $_W, $_GPC;
		$this->checkAuth();
		$this->setCartGoods(array());
		message('清空购物车成功！', $this->createMobileUrl('list'), 'success');
	}
	
	public function doMobileDeleteCartGoods() {
		global $_W, $_GPC;
		$this->checkAuth();
		$id = intval($_GPC['id']);
		$cart = $this->getCartGoods();
		unset($cart['goods'][$id]);
		$this->setCartGoods($cart);
		message('删除购物车商品成功！', $this->createMobileUrl('mycart'), 'ajax');
	}

	public function doMobileMyOrder() {
		global $_W, $_GPC;
		$this->checkAuth();
		if (!empty($_GPC['operation']) && $_GPC['operation'] == 'confirm') {
			$orderid = intval($_GPC['orderid']);
			$order = pdo_fetch("SELECT * FROM ".tablename('shopping_order')." WHERE id = :id AND from_user = :from_user", array(':id' => $orderid, ':from_user' => $_W['fans']['from_user']));
			if (empty($order)) {
				message('抱歉，您的订单不存或是已经被取消！', $this->createMobileUrl('myorder'), 'error');
			}
			pdo_update('shopping_order', array('status' => 3), array('id' => $orderid, 'from_user' => $_W['fans']['from_user']));
			message('确认收货完成！', $this->createMobileUrl('myorder'), 'success');
		}
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$list = pdo_fetchall("SELECT * FROM ".tablename('shopping_order')." WHERE weid = '{$_W['weid']}' AND from_user = '{$_W['fans']['from_user']}' ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize, array(), 'id');
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('shopping_order') . " WHERE weid = '{$_W['weid']}' AND from_user = '{$_W['fans']['from_user']}'");
		$pager = pagination($total, $pindex, $psize);

		if (!empty($list)) {
			foreach ($list as &$row) {
				$goodsid = pdo_fetchall("SELECT goodsid,total FROM ".tablename('shopping_order_goods')." WHERE orderid = '{$row['id']}'", array(), 'goodsid');
				$goods = pdo_fetchall("SELECT id, title, thumb, unit, marketprice FROM ".tablename('shopping_goods')."  WHERE id IN ('".implode("','", array_keys($goodsid))."')");
				$row['goods'] = $goods;
				$row['total'] = $goodsid;
			}
		}
		include $this->template('order');
	}

	public function doMobileDetail() {
		global $_W, $_GPC;
		$cart = $this->getCartGoods();

		$goodsid = intval($_GPC['id']);
		$goods = pdo_fetch("SELECT * FROM ".tablename('shopping_goods')." WHERE id = :id", array(':id' => $goodsid));
		if (empty($goods)) {
			message('抱歉，商品不存在或是已经被删除！');
		}
		if (checksubmit('submit')) {
			$_GPC['count'] = intval($_GPC['count']);
			if ($goods['total'] > -1 && $goods['total'] < $_GPC['count']) {
				message('抱歉，该商品库存不足！');
			}
			$row = pdo_fetch("SELECT id, total FROM ".tablename('shopping_cart')." WHERE from_user = :from_user AND weid = '{$_W['weid']}' AND goodsid = :goodsid", array(':from_user' => $_W['fans']['from_user'], ':goodsid' => $goodsid));
			if (empty($row['id'])) {
				$data = array(
					'weid' => $_W['weid'],
					'goodsid' => $goodsid,
					'goodstype' => $goods['type'],
					'from_user' => $_W['fans']['from_user'],
					'total' => $_GPC['count'],
				);
				pdo_insert('shopping_cart', $data);
			} else {
				$data = array(
					'total' => $row['total'] + $_GPC['count'],
				);
				if ($goods['total'] > -1 && $goods['total'] < $data['total']) {
					message('抱歉，该商品库存不足！');
				}
				pdo_update('shopping_cart', $data, array('from_user' => $_W['fans']['from_user'], 'weid' => $_W['weid'], 'goodsid' => $goodsid));
			}
			message('添加商品成功，请去“我的购物车”提交订单！', $this->createMobileUrl('list'), 'success');
		}

		$carttotal = 0;
		if (!empty($cart['goods'])) {
			foreach ($cart['goods'] as $row) {
				$carttotal += $row['total'];
			}
		}
		include $this->template('detail');
	}
	
	public function doMobileAddress() {
		global $_W, $_GPC;
		$this->checkAuth();
		$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'post';
		
		if ($operation == 'post') {
			$id = intval($_GPC['id']);
			$data = array(
				'weid' => $_W['weid'],
				'openid' => $_W['fans']['from_user'],
				'realname' => $_GPC['realname'],
				'mobile' => $_GPC['mobile'],
				'province' => $_GPC['province'],
				'city' => $_GPC['city'],
				'area' => $_GPC['area'],
				'address' => $_GPC['address'],
			);
			if (empty($_GPC['realname']) || empty($_GPC['mobile']) || empty($_GPC['address'])) {
				message('请输完善您的资料！');
			}
			if (!empty($id)) {
				unset($data['weid']);
				unset($data['openid']);
				pdo_update('shopping_address', $data, array('id' => $id));
				message($id, '', 'ajax');
			} else {
				pdo_insert('shopping_address', $data);
				$id = pdo_insertid();
				if (!empty($id)) {
					message($id, '', 'ajax');
				} else {
					message(0, '', 'ajax');
				}
			}
		} elseif ($operation == 'default') {
			$id = intval($_GPC['id']);
			pdo_update('shopping_address', array('isdefault' => 0), array('weid' => $_W['weid'], 'openid' => $_W['fans']['from_user']));
			pdo_update('shopping_address', array('isdefault' => 1), array('id' => $id));
			message(1, '', 'ajax');
		} elseif ($operation == 'detail') {
			$id = intval($_GPC['id']);
			$row = pdo_fetch("SELECT id, realname, mobile, province, city, area, address FROM ".tablename('shopping_address')." WHERE id = :id", array(':id' => $id));
			message($row, '', 'ajax');
		}
	}

	private function checkAuth() {
		global $_W;
		checkauth();
	}
	
	private function changeWechatSend($id, $status, $msg = '') {
		global $_W;
		$paylog = pdo_fetch("SELECT plid, openid, tag FROM ".tablename('paylog')." WHERE tid = '{$id}' AND status = 1 AND type = 'wechat'");
		if (!empty($paylog['openid'])) {
			$paylog['tag'] = iunserializer($paylog['tag']);
			$send = array(
				'appid' => $_W['account']['payment']['wechat']['appid'],
				'openid' => $paylog['openid'],
				'transid' => $paylog['tag']['transaction_id'],
				'out_trade_no' => $paylog['plid'],
				'deliver_timestamp' => TIMESTAMP,
				'deliver_status' => $status,
				'deliver_msg' => $msg,
			);
			$sign = $send;
			$sign['appkey'] = $_W['account']['payment']['wechat']['signkey'];
			ksort($sign);
			foreach($sign as $key => $v) {
				$key = strtolower($key);
				$string .= "{$key}={$v}&";
			}
			$send['app_signature'] = sha1(rtrim($string, '&'));
			$send['sign_method'] = 'sha1';
			$token = account_weixin_token($_W['account']);
			$sendapi = 'https://api.weixin.qq.com/pay/delivernotify?access_token='.$token;
			$response = ihttp_request($sendapi, json_encode($send));
			$response = json_decode($response['content'], true);
			if (empty($response)) {
				message('发货失败，请检查您的公众号权限或是公众号AppId和公众号AppSecret！');
			}
			if (!empty($response['errcode'])) {
				message($response['errmsg']);
			}
		}
	}
	
	private function getCartGoods() {
		global $_W;
		return $_SESSION['cart'][$_W['weid']];
	}
	
	private function setCartGoods($cart) {
		global $_W;
		$_SESSION['cart'][$_W['weid']] = $cart;
		return true;
	}

	public function payResult($params) {
		$fee = intval($params['fee']);
		$data = array('status' => $params['result'] == 'success' ? 1 : 0);
		if ($params['type'] == 'wechat') {
			$data['transid'] = $params['tag']['transaction_id'];
		}
		pdo_update('shopping_order', $data, array('id' => $params['tid']));
		if ($params['from'] == 'return') {
			//邮件提醒
			if (!empty($this->module['config']['noticeemail'])) {
				$order = pdo_fetch("SELECT price, from_user FROM ".tablename('shopping_order')." WHERE id = '{$params['tid']}'");
				$ordergoods = pdo_fetchall("SELECT goodsid, total FROM ".tablename('shopping_order_goods')." WHERE orderid = '{$params['tid']}'", array(), 'goodsid');
				$goods = pdo_fetchall("SELECT id, title, thumb, marketprice, unit, total FROM ".tablename('shopping_goods')." WHERE id IN ('".implode("','", array_keys($ordergoods))."')");
				$address = pdo_fetch("SELECT * FROM ".tablename('shopping_address')." WHERE id = :id", array(':id' => $order['addressid']));
				$body = "<h3>购买商品清单</h3> <br />";
				if (!empty($goods)) {
					foreach ($goods as $row) {
						$body .= "名称：{$row['title']} ，数量：{$ordergoods[$row['id']]['total']} <br />";
					}
				}
				$body .= "<br />总金额：{$order['price']}元 （已付款）<br />";
				$body .= "<h3>购买用户详情</h3> <br />";
				$body .= "真实姓名：{$address['realname']} <br />";
				$body .= "地区：{$address['province']} - {$address['city']} - {$address['area']}<br />";
				$body .= "详细地址：{$address['address']} <br />";
				$body .= "手机：{$address['mobile']} <br />";
				ihttp_email($this->module['config']['noticeemail'], '微商城订单提醒', $body);
			}
			if ($params['type'] == 'credit2') {
				message('支付成功！', $this->createMobileUrl('myorder'), 'success');
			} else {
				message('支付成功！', '../../' . $this->createMobileUrl('myorder'), 'success');
			}
		}
	}
}
