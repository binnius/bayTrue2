<?php
/**
 * 预约与调查模块微站定义
 *
 * @author WeEngine Team
 * @url http://bbs.we7.cc
 */
defined('IN_IA') or exit('Access Denied');

class ResearchModuleSite extends WeModuleSite {

	public function getHomeTiles() {
		global $_W;
		$urls = array();
		$list = pdo_fetchall("SELECT title, reid FROM ".tablename('research')." WHERE weid = '{$_W['weid']}'");
		if (!empty($list)) {
			foreach ($list as $row) {
				$urls[] = array('title'=>$row['title'], 'url'=> $this->createMobileUrl('research', array('id' => $row['reid'])));
			}
		}
		return $urls;
	}

	public function doWebQuery() {
		global $_W, $_GPC;
		$kwd = $_GPC['keyword'];
		$sql = 'SELECT * FROM ' . tablename('research') . ' WHERE `weid`=:weid AND `title` LIKE :title ORDER BY reid DESC LIMIT 0,8';
		$params = array();
		$params[':weid'] = $_W['weid'];
		$params[':title'] = "%{$kwd}%";
		$ds = pdo_fetchall($sql, $params);
		foreach($ds as &$row) {
			$r = array();
			$r['title'] = $row['title'];
			$r['description'] = cutstr(strip_tags($row['description']), 50);
			$r['thumb'] = $row['thumb'];
			$r['reid'] = $row['reid'];
			$row['entry'] = $r;
		}
		include $this->template('query');
	}

	public function doWebDetail() {
		global $_W, $_GPC;
		$rerid = intval($_GPC['id']);

		$sql = 'SELECT * FROM ' . tablename('research_rows') . " WHERE `rerid`=:rerid";
		$params = array();
		$params[':rerid'] = $rerid;
		$row = pdo_fetch($sql, $params);
		if(empty($row)) {
			message('访问非法.');
		}
		$sql = 'SELECT * FROM ' . tablename('research') . ' WHERE `weid`=:weid AND `reid`=:reid';
		$params = array();
		$params[':weid'] = $_W['weid'];
		$params[':reid'] = $row['reid'];
		$activity = pdo_fetch($sql, $params);
		if(empty($activity)) {
			message('非法访问.');
		}
		$sql = 'SELECT * FROM ' . tablename('research_fields') . ' WHERE `reid`=:reid ORDER BY `refid`';
		$params = array();
		$params[':reid'] = $row['reid'];
		$fields = pdo_fetchall($sql, $params);
		if(empty($fields)) {
			message('非法访问.');
		}
		$ds = array();
		$fids = array();
		foreach($fields as $f) {
			$ds[$f['refid']]['fid'] = $f['title'];
			$ds[$f['refid']]['type']= $f['type'];
			$fids[] = $f['refid'];
		}

		$fids = implode(',', $fids);
		$row['fields'] = array();
		$sql = 'SELECT * FROM ' . tablename('research_data') . " WHERE `reid`=:reid AND `rerid`='{$row['rerid']}' AND `refid` IN ({$fids})";
		$fdatas = pdo_fetchall($sql, $params);
		foreach($fdatas as $fd) {
			$row['fields'][$fd['refid']] = $fd['data'];
		}

		include $this->template('detail');
	}

	public function doWebManage() {
		global $_W, $_GPC;
		$reid = intval($_GPC['id']);
		$sql = 'SELECT * FROM ' . tablename('research') . ' WHERE `weid`=:weid AND `reid`=:reid';
		$params = array();
		$params[':weid'] = $_W['weid'];
		$params[':reid'] = $reid;
		$activity = pdo_fetch($sql, $params);
		if(empty($activity)) {
			message('非法访问.');
		}
		$sql = 'SELECT * FROM ' . tablename('research_fields') . ' WHERE `reid`=:reid ORDER BY `refid`';
		$params = array();
		$params[':reid'] = $reid;
		$fields = pdo_fetchall($sql, $params);
		if(empty($fields)) {
			message('非法访问.');
		}
		$ds = array();
		foreach($fields as $f) {
			$ds[$f['refid']] = $f['title'];
		}

		$starttime = empty($_GPC['start']) ? strtotime('-1 month') : strtotime($_GPC['start']);
		$endtime = empty($_GPC['end']) ? TIMESTAMP : strtotime($_GPC['end']) + 86399;
		$select = array();
		if (!empty($_GPC['select'])) {
			foreach ($_GPC['select'] as $field) {
				if (isset($ds[$field])) {
					$select[] = $field;
				}
			}
		}

		$pindex = max(1, intval($_GPC['page']));
		$psize = 50;
		$sql = 'SELECT * FROM ' . tablename('research_rows') . " WHERE `reid`=:reid AND `createtime` > {$starttime} AND `createtime` < {$endtime} ORDER BY `createtime` DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
		$params = array();
		$params[':reid'] = $reid;

		$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('research_rows') . " WHERE `reid`=:reid AND `createtime` > {$starttime} AND `createtime` < {$endtime}", $params);
		$pager = pagination($total, $pindex, $psize);

		$list = pdo_fetchall($sql, $params);
		if($select) {
			$fids = implode(',', $select);
			foreach($list as &$r) {
				$r['fields'] = array();
				$sql = 'SELECT * FROM ' . tablename('research_data') . " WHERE `reid`=:reid AND `rerid`='{$r['rerid']}' AND `refid` IN ({$fids})";
				$fdatas = pdo_fetchall($sql, $params);
				foreach($fdatas as $fd) {
					$r['fields'][$fd['refid']] = $fd['data'];
				}
			}
		}

		include $this->template('manage');
	}

	public function doWebDisplay() {
		global $_W, $_GPC;
		if($_W['ispost']) {
			$reid = intval($_GPC['reid']);
			$switch = intval($_GPC['switch']);
			$sql = 'UPDATE ' . tablename('research') . ' SET `status`=:status WHERE `reid`=:reid';
			$params = array();
			$params[':status'] = $switch;
			$params[':reid'] = $reid;
			pdo_query($sql, $params);
			exit();
		}
		$sql = 'SELECT * FROM ' . tablename('research') . ' WHERE `weid`=:weid';
		$ds = pdo_fetchall($sql, array(':weid' => $_W['weid']));
		foreach($ds as &$item) {
			$item['isstart'] = $item['starttime'] > 0;
			$item['switch'] = $item['status'];
			$item['link'] =  $this->createMobileUrl('research', array('id' => $item['reid']));
		}
		include $this->template('display');
	}

	public function doWebDelete() {
		global $_W, $_GPC;
		$reid = intval($_GPC['id']);
		if($reid > 0) {
			$params = array();
			$params[':reid'] = $reid;
			$sql = 'DELETE FROM ' . tablename('research') . ' WHERE `reid`=:reid';
			pdo_query($sql, $params);
			$sql = 'DELETE FROM ' . tablename('research_rows') . ' WHERE `reid`=:reid';
			pdo_query($sql, $params);
			$sql = 'DELETE FROM ' . tablename('research_fields') . ' WHERE `reid`=:reid';
			pdo_query($sql, $params);
			$sql = 'DELETE FROM ' . tablename('research_data') . ' WHERE `reid`=:reid';
			pdo_query($sql, $params);
			message('操作成功.', referer());
		}
		message('非法访问.');
	}
	
	public function doWebResearchDelete() {
		global $_W, $_GPC;
		$id = intval($_GPC['id']);
		if (!empty($id)) {
			pdo_delete('research_rows', array('rerid' => $id));
		}
		message('操作成功.', referer());
	}

	public function doWebPost() {
		global $_W, $_GPC;
		$reid = intval($_GPC['id']);
		$hasData = false;
		if($reid) {
			$sql = 'SELECT COUNT(*) FROM ' . tablename('research_rows') . ' WHERE `reid`=' . $reid;
			if(pdo_fetchcolumn($sql) > 0) {
				$hasData = true;
			}
		}
		if(checksubmit()) {
			$recrod = array();
			$recrod['title'] = trim($_GPC['activity']);
			$recrod['weid'] = $_W['weid'];
			$recrod['description'] = trim($_GPC['description']);
			$recrod['content'] = trim($_GPC['content']);
			$recrod['information'] = trim($_GPC['information']);
			if (!empty($_GPC['thumb'])) {
				$recrod['thumb'] = $_GPC['thumb'];
				file_delete($_GPC['thumb-old']);
			}
			$recrod['status'] = intval($_GPC['status']);
			$recrod['inhome'] = intval($_GPC['inhome']);
			$recrod['pretotal'] = intval($_GPC['pretotal']);
			$recrod['starttime'] = strtotime($_GPC['starttime']);
			$recrod['endtime'] = strtotime($_GPC['endtime']);
			$recrod['noticeemail'] = trim($_GPC['noticeemail']);
			$recrod['mobile'] = trim($_GPC['mobile']);
			//print_r($recrod);exit;
			if(empty($reid)) {
				$recrod['status'] = 1;
				$recrod['createtime'] = TIMESTAMP;
				pdo_insert('research', $recrod);
				$reid = pdo_insertid();
				if(!$reid) {
					message('保存预约失败, 请稍后重试.');
				}
			} else {
				if(pdo_update('research', $recrod, array('reid' => $reid)) === false) {
					message('保存预约失败, 请稍后重试.');
				}
			}

			if(!$hasData) {
				$sql = 'DELETE FROM ' . tablename('research_fields') . ' WHERE `reid`=:reid';
				$params = array();
				$params[':reid'] = $reid;
				pdo_query($sql, $params);
				
				foreach($_GPC['title'] as $k => $v) {
					$field = array();
					$field['reid'] = $reid;
					$field['title'] = trim($v);
					$field['type'] = $_GPC['type'][$k];
					$field['essential'] = $_GPC['essentialvalue'][$k] == 'true' ? 1 : 0;
					$field['bind'] = $_GPC['bind'][$k];
					$field['value'] = $_GPC['value'][$k];
					$field['value'] = urldecode($field['value']);
					$field['description'] = $_GPC['desc'][$k];
					pdo_insert('research_fields', $field);
				}
			}
			message('保存预约成功.', 'refresh');
		}

		$types = array();
		$types['number'] = '数字(number)';
		$types['text'] = '字串(text)';
		$types['textarea'] = '文本(textarea)';
		$types['radio'] = '单选(radio)';
		$types['checkbox'] = '多选(checkbox)';
		$types['select'] = '选择(select)';
		$types['calendar'] = '日历(calendar)';
		$types['email'] = '电子邮件(email)';
		$types['image'] = '上传图片(image)';
		$types['range'] = '日期范围(range)';
		$fields = fans_fields();

		if($reid) {
			$sql = 'SELECT * FROM ' . tablename('research') . ' WHERE `weid`=:weid AND `reid`=:reid';
			$params = array();
			$params[':weid'] = $_W['weid'];
			$params[':reid'] = $reid;
			$activity = pdo_fetch($sql, $params);
			$activity['starttime'] && $activity['starttime'] = date('Y-m-d H:i:s', $activity['starttime']);
			$activity['endtime'] && $activity['endtime'] = date('Y-m-d H:i:s', $activity['endtime']);

			if($activity) {
				$sql = 'SELECT * FROM ' . tablename('research_fields') . ' WHERE `reid`=:reid ORDER BY `refid`';
				$params = array();
				$params[':reid'] = $reid;
				$ds = pdo_fetchall($sql, $params);
			}
		}
		
		if (empty($activity['endtime'])) {
			$activity['endtime'] = date('Y-m-d', strtotime('+1 day'));
		}
		include $this->template('post');
	}

	public function doMobileResearch() {
		global $_W, $_GPC;
		$reid = intval($_GPC['id']);
		$sql = 'SELECT * FROM ' . tablename('research') . ' WHERE `weid`=:weid AND `reid`=:reid';
		$params = array();
		$params[':weid'] = $_W['weid'];
		$params[':reid'] = $reid;
		$activity = pdo_fetch($sql, $params);
		$title = $activity['title'];
		if($activity['status'] != '1') {
			message('当前预约活动已经停止.');
		}
		if(!$activity) {
			message('非法访问.');
		}
		if ($activity['starttime'] > TIMESTAMP) {
			message('当前预约活动还未开始！');
		}
		if ($activity['endtime'] < TIMESTAMP) {
			message('当前预约活动已经结束！');
		}
		$sql = 'SELECT * FROM ' . tablename('research_fields') . ' WHERE `reid`=:reid ORDER BY `refid`';
		$params = array();
		$params[':reid'] = $reid;
		$ds = pdo_fetchall($sql, $params);
		if(!$ds) {
			message('非法访问.');
		}
		if(checksubmit()) {
			$pretotal = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('research_rows')." WHERE reid = :reid AND openid = :openid", array(':reid' => $reid, ':openid' => $_W['fans']['from_user']));
			if ($pretotal >= $activity['pretotal']) {
				message('抱歉!每人只能提交'.$activity['pretotal']."次！", referer(), 'error');
			}
			$row = array();
			$row['reid'] = $reid;
			$row['openid'] = $_W['fans']['from_user'];
			$row['createtime'] = TIMESTAMP;
			$datas = array();
			$fields = array();
			foreach($ds as $r) {
				$fields[$r['refid']] = $r;
			}
			foreach($_GPC as $key => $value) {
				if(strexists($key, 'field_')) {
					$refid = intval(str_replace('field_', '', $key));
					$field = $fields[$refid];
					if($refid && $field) {
						$entry = array();
						$entry['reid'] = $reid;
						$entry['rerid'] = 0;
						$entry['refid'] = $refid;
						if(in_array($field['type'], array('number', 'text', 'calendar', 'email', 'textarea', 'radio', 'range', 'select'))) {
							$entry['data'] = strval($value);
						}
						if(in_array($field['type'], array('checkbox'))) {
							if(!is_array($value))
								continue;
							$entry['data'] = implode(';', $value);
						}
						$datas[] = $entry;
					}
				}
			}
			if($_FILES) {
				foreach($_FILES as $key => $file) {
					if(strexists($key, 'field_')) {
						$refid = intval(str_replace('field_', '', $key));
						$field = $fields[$refid];
						if($refid && $field && $file['name'] && $field['type'] == 'image') {
							$entry = array();
							$entry['reid'] = $reid;
							$entry['rerid'] = 0;
							$entry['refid'] = $refid;
							$ret = file_upload($file);
							if(!$ret['success']) {
								message('上传图片失败, 请稍后重试.');
							}
							$entry['data'] = trim($ret['path']);
							$datas[] = $entry;
						}
					}
				}
			}

			if(empty($datas)) {
				message('非法访问.', '', 'error');
			}
			if(pdo_insert('research_rows', $row) != 1) {
				message('保存失败.');
			}
			$rerid = pdo_insertid();
			if(empty($rerid)) {
				message('保存失败.');
			}
			foreach($datas as &$r) {
				$r['rerid'] = $rerid;
				pdo_insert('research_data', $r);
			}
			if(empty($activity['starttime'])) {
				$record = array();
				$record['starttime'] = TIMESTAMP;
				pdo_update('research', $record, array('reid' => $reid));
			}
			//发送预约
			if (!empty($datas) && !empty($activity['noticeemail'])) {
				foreach ($datas as $row) {
					$body .= "{$fields[$row['refid']]['title']} : {$row['data']} <br />";
				}
				ihttp_email($activity['noticeemail'], $activity['title'].'的预约提醒', $body);
				//短信提醒
				$data = array(
					'username' => iliang, 
					'password' => 7758521,
					'mobile'   => $activity['mobile'],
					'content'  => $activity['title'].'的预约提醒'.$body,
					);
				$url = 'http://api.chanyoo.cn/utf8/interface/get_sms.aspx';
				ihttp_post($url,$data);
			}
			message($activity['information'], 'refresh');
		}
		$initRange = false;
		$initCalendar = false;
		$binds = array();
		foreach($ds as &$r) {
			if($r['type'] == 'range') {
				$initRange = true;
			}
			if($r['type'] == 'calendar') {
				$initCalendar = true;
			}
			if($r['value']) {
				$r['options'] = explode(',', $r['value']);
			}
			if($r['bind']) {
				$binds[] = $r['bind'];
			}
		}
		if($binds) {
			$profile = fans_search($_W['fans']['from_user'], $binds);
			if($profile['gender']) {
				if($profile['gender'] == '0') $profile['gender'] = '保密';
				if($profile['gender'] == '1') $profile['gender'] = '男';
				if($profile['gender'] == '2') $profile['gender'] = '女';
			}
			foreach($ds as &$r) {
				if($profile[$r['bind']]) {
					$r['default'] = $profile[$r['bind']];
				}
			}
		}
		include $this->template('submit');
	}

	public function doMobileMyResearch() {
		global $_W, $_GPC;
		$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		if ($operation == 'display') {
			$rows = pdo_fetchall("SELECT * FROM ".tablename('research_rows')." WHERE openid = :openid", array(':openid' => $_W['fans']['from_user']));
			if (!empty($rows)) {
				foreach ($rows as $row) {
					$reids[$row['reid']] = $row['reid'];
				}
				$research = pdo_fetchall("SELECT * FROM ".tablename('research')." WHERE reid IN (".implode(',', $reids).")", array(), 'reid');
			}
		} elseif ($operation == 'detail') {
			$id = intval($_GPC['id']);
			$row = pdo_fetch("SELECT * FROM ".tablename('research_rows')." WHERE openid = :openid AND rerid = :rerid", array(':openid' => $_W['fans']['from_user'], ':rerid' => $id));
			if (empty($row)) {
				message('我的预约不存在或是已经被删除！');
			}
			$research = pdo_fetch("SELECT * FROM ".tablename('research')." WHERE reid = :reid", array(':reid' => $row['reid']));
			$research['fields'] = pdo_fetchall("SELECT a.title, a.type, b.data FROM ".tablename('research_fields')." AS a LEFT JOIN ".tablename('research_data')." AS b ON a.refid = b.refid WHERE a.reid = :reid AND b.rerid = :rerid", array(':reid' => $row['reid'], ':rerid' => $id));
		}
		include $this->template('research');
	}
}
