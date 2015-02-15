<?php defined('IN_IA') or exit('Access Denied');?><?php  include $this->template('common/header', TEMPLATE_INCLUDEPATH);?>
<ul class="nav nav-tabs">
	<li <?php  if($operation == 'display') { ?>class="active"<?php  } ?>><a href="<?php  echo $this->createWebUrl('order', array('op' => 'display'))?>">管理订单</a></li>
	<li><a href="<?php  echo $this->createWebUrl('order', array('op' => 'cprint'))?>">取消所有未打印订单</a></li>
</ul>
<style>
.label-red{
	background-color: red;
}
</style>
<?php  if($operation == 'display') { ?>
<link type="text/css" rel="stylesheet" href="./resource/style/daterangepicker.css" />
<script type="text/javascript" src="./resource/script/daterangepicker.js"></script>
<script type="text/javascript">
$(function() {
	$('#date-range').daterangepicker({
		format: 'YYYY-MM-DD',
		startDate: $(':hidden[name=start]').val(),
		endDate: $(':hidden[name=end]').val(),
 		locale: {
			applyLabel: '确定',
			cancelLabel: '取消',
			fromLabel: '从',
			toLabel: '至',
			weekLabel: '周',
			customRangeLabel: '日期范围',
			daysOfWeek: moment()._lang._weekdaysMin.slice(),
			monthNames: moment()._lang._monthsShort.slice(),
			firstDay: 0
		}
	}, function(start, end){
		$('#date-range .date-title').html(start.format('YYYY-MM-DD') + ' 至 ' + end.format('YYYY-MM-DD'));
		$(':hidden[name=start]').val(start.format('YYYY-MM-DD'));
		$(':hidden[name=end]').val(end.format('YYYY-MM-DD'));
	});
});
function range(days) {
	var start = moment().add('days', 0 - days).format('YYYY-MM-DD');
	var end = moment().format('YYYY-MM-DD');
	$('#date-range .date-title').html(start + ' 至 ' + end);
	$(':hidden[name=start]').val(start);
	$(':hidden[name=end]').val(end);
//	$('form[method=get]')[0].submit();
}
</script>

<div class="main">
		<div class="stat">
			<div class="stat-div">
				<div class="navbar navbar-static-top">
					<div class="navbar-inner">
						<span class="brand">订单统计</span>
						<div class="pull-left"></div>
					</div>
				</div>
				<div class="sub-item">
					<div class="sub-content">

 					<form action="" method="post">
					<table class="table sub-search">
						<tbody>
						<tr>
							<th>联系方式</th>
							<td>
								<input type="text" name="tel" value="<?php  echo $_GPC['tel'];?>" class="">
							</td>
						</tr>
						<tr>
							<th>预约时间</th>
							<td>
							<div class="pull-left">
								<input name="start" type="hidden" value="<?php  echo $start;?>" />
								<input name="end" type="hidden" value="<?php  echo $end;?>" />
								<button class="btn date" id="date-range" type="button"><span class="date-title"><?php  echo $start;?> 至 <?php  echo $end;?></span> <i class="icon-caret-down"></i></button>
								<span class="date-section"><a href="javascript:;" onclick="range(7);">7天</a><a href="javascript:;" onclick="range(14);">14天</a><a href="javascript:;" onclick="range(30);">30天</a></span>
							</div>
							</td>
						</tr>	
						<tr>
							<th>类型</th>
							<td>
								<select name="status">
 									<option value="-1" <?php  if($_GPC['status']==-1) { ?>selected<?php  } ?>>全部</option>
									<option value="0" <?php  if(isset($_GPC['status'])&&$_GPC['status']==0) { ?>selected<?php  } ?>>未下单</option>
									<option value="1" <?php  if(!isset($_GPC['status'])||$_GPC['status']==1) { ?>selected<?php  } ?>>未确认</option>
									<option value="2" <?php  if($_GPC['status']==2) { ?>selected<?php  } ?>>已确认</option>
									<option value="3" <?php  if($_GPC['status']==3) { ?>selected<?php  } ?>>已完成</option>
 								</select>
							</td>
						</tr>	
						<tr>
							<th></th>
							<td>
								<button type="submit" class="btn btn-primary span2"  name="confrim"  value="1">搜索</button>
								<button type="submit" class="btn btn-primary span2"  name="confrim"  value="2">导出数据</button>
								<input type="hidden" name="token" value="<?php  echo $_W['token'];?>" />
							</td>
						</tr>
					</tbody></table>
					</form>
					</div>
				</div>
				
				<div class="sub-item" id="table-list">
					<h4 class="sub-title">详细数据</h4>
					<div class="sub-content">
						<form action="" method="post" onsubmit="">
						<table class="table table-hover">
							<thead class="navbar-inner">
								<tr>
									<th style="width:30px;" class="row-first">选择</th>
									<th style="width:30px;">ID</th>
									<th style="width:60px;">订单号</th>
									<th style="width:50px;">姓名</th>
									<th style="width:40px;">电话</th>
									<th>订单详情</th>
									<th style="width:50px;">总价</th>
									<th style="width:60px;">状态</th>
									<th style="width:60px;">打印</th>
									<th style="width:150px;">下单时间</th>
									<th style="width:120px; text-align:right;">操作</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td class="row-first"><input type="checkbox" onclick="selectall(this, 'select');" /></td>
									<td colspan="8" style="text-align:left;">
										<input name="token" type="hidden" value="<?php  echo $_W['token'];?>" />
										<input type="submit" class="btn btn-primary" name="submit1" value="批量确认" />
										<input type="submit" name="submit2" value="批量取消" class="btn btn-success" />
										<input type="submit" name="submit3" value="批量完成" class="btn btn-info" />
									</td>
								</tr>
								<?php  if(is_array($list)) { foreach($list as $item) { ?>
								<tr>
									<td class="row-first"><input type="checkbox" name="select[]" value="<?php  echo $item['id'];?>" /></td>
									<td><?php  echo $item['id'];?></td>
									<td><?php  echo $item['ordersn'];?></td>
									<td><?php  echo $item['guest_name'];?></td>
									<td><?php  echo $item['tel'];?></td>
									<td style="text-align:left;">
									<?php  if($item['order_type']==2) { ?><span class="label label-success">[店内]</span>
									<?php  } else if($item['order_type']==3) { ?><span class="label label-red">[自提]</span>
									<?php  } else { ?><span class="label label-info">[外卖]</span><?php  } ?>
									<?php  if(!empty($item['guest_address'])) { ?>地址：<?php  echo $item['guest_address'];?><?php  } else { ?>桌号：<?php  echo $item['desk'];?>(<?php  echo $item['nums'];?>)<?php  } ?></td>
									<td><?php  echo $item['totalprice'];?> 元</td>
									<td>
									<?php  if($item['status'] == 0) { ?><span class="label label-info">未生成订单</span>
									<?php  } else if($item['status'] == 1) { ?><span class="label label-info">已下单</span>
									<?php  } else if($item['status'] == 2) { ?><span class="label label-success">已确认</span>
									<?php  } else if($item['status'] == 3) { ?><span class="label label-success">已完成</span>
									<?php  } else if($item['status'] == -1) { ?><span class="label label-success">已关闭</span><?php  } ?>
									<?php  if($item['ispay'] == 1) { ?><span class="label label-success">已付款</span>
									<?php  } else { ?><span class="label label-info">未付款</span><?php  } ?>
									</td>
									<td>
										<?php  if($item['print_sta'] == -1) { ?><span class="label label-success">未打印</span>
									<?php  } else { ?><span class="label label-info">已打印</span><?php  } ?>
									</td>
									<td><?php  echo date('Y-m-d H:i:s', $item['createtime'])?></td>
									<td style="text-align:right;"><a href="<?php  echo $this->createWebUrl('order', array('op' => 'detail', 'id' => $item['id']))?>">查看</a>&nbsp;&nbsp;<a onclick="return confirm('删除订单无法恢复，确认吗？');return false;" href="<?php  echo $this->createWebUrl('order', array('op' => 'delete', 'id' => $item['id']))?>">删除</a></td>
								</tr>
								<?php  } } ?>
								 
							</tbody>
					</table>
					</form>
					<?php  echo $pager;?>
				</div>
			</div>
			</div>
		</div>
</div>
<?php  } else if($operation == 'detail') { ?>
<div class="main">
	<form class="form-horizontal form" action="" method="post" enctype="multipart/form-data" onsubmit="return formcheck(this)">
		<input type="hidden" name="id" value="<?php  echo $item['id'];?>">
		<h4>订单信息</h4>
		<table class="tb">
			<tr>
				<th><label for="">订单号</label></th>
				<td>
					<?php  echo $item['ordersn'];?>
				</td>
			</tr>
			<tr>
				<th><label for="">订单类型</label></th>
				<td><?php  if($item['order_type']==2) { ?><span class="label label-success">[店内]</span>
					<?php  } else if($item['order_type']==3) { ?><span class="label label-red">[自提]</span>
					<?php  } else { ?><span class="label label-info">[外卖]</span><?php  } ?>
				</td>
			</tr>
			<tr>
				<th><label for="">价钱</label></th>
				<td>
					<?php  echo $item['totalprice'];?> 元
				</td>
			</tr>
			<tr>
				<th><label for="">配送方式</label></th>
				<td>
					<span class="label label-info"><?php  echo $express[$item['sendtype']]['express_name']?></span> <?php  if($item['sendstatus']==1) { ?><span class="label label-success">已配送</span><?php  } else { ?><span class="label label-info">未配送</span><?php  } ?>
				</td>
			</tr>
			<tr>
				<th><label for="">付款方式</label></th>
				<td>
					<?php  if($item['paytype'] == 1) { ?>余额支付
					<?php  } else if($item['paytype'] == 2) { ?>在线支付
					<?php  } else if($item['paytype'] == 3) { ?>货到付款<?php  } ?>
					<?php  if($item['ispay']==1) { ?><span class="label label-success">已支付</span><?php  } else { ?><span class="label label-error">未支付</span><?php  } ?>
					
				</td>
			</tr>
			<tr>
				<th><label for="">订单状态</label></th>
				<td>
					<?php  if($item['status'] == 0) { ?><span class="label label-info">客户未处理</span><?php  } ?>
					<?php  if($item['status'] == 1) { ?><span class="label label-info">未确认</span><?php  } ?>
					<?php  if($item['status'] == 2) { ?><span class="label label-info">已确认</span><?php  } ?>
					<?php  if($item['status'] == 3) { ?><span class="label label-success">已完成</span><?php  } ?>
					<?php  if($item['status'] == -1) { ?><span class="label label-error">已关闭</span><?php  } ?>
				</td>
			</tr>
			<tr>
				<th><label for="">打印状态</label></th>
				<td>
					<?php  if($item['print_sta'] == -1) { ?><span class="label label-success">未打印</span>
					<?php  } else { ?><span class="label label-info">已打印</span><?php  } ?>
					(<?php  echo $item['sms_sta'];?>)
				</td>
			</tr>			
			<tr>
				<th><label for="">下单日期</label></th>
				<td>
					<?php  echo date('Y-m-d H:i:s', $item['createtime'])?>
				</td>
			</tr>
			<tr>
				<th><label for="">预约时间</label></th>
				<td>
					<?php  echo $item['time_day'];?> <?php  echo $item['time_hour'];?>:<?php  echo $item['time_second'];?>
				</td>
			</tr>			
			<tr>
				<th>备注</th>
				<td>
					<textarea style="height:50px;" class="span7" name="remark" cols="70"><?php  echo $item['remark'];?></textarea>
				</td>
			</tr>
		</table>
		<h4>用户信息</h4>
		<table class="tb">
			<tr>
				<th><label for="">姓名</label></th>
				<td>
					<?php  echo $item['guest_name'];?>（<?php  if($item['sex']==1) { ?>先生<?php  } else { ?>女士<?php  } ?>）
				</td>
			</tr>
			<tr>
				<th><label for="">手机</label></th>
				<td>
					<?php  echo $item['tel'];?>
				</td>
			</tr>
			<?php  if($item['order_type']==2) { ?>
			<tr>
				<th><label for="">桌号</label></th>
				<td>
					<?php  echo $item['desk'];?>（人数：<?php  echo $item['nums'];?>）
				</td>
			</tr>
			<?php  } else if($item['order_type']==1) { ?>
			<tr>
				<th><label for="">地址</label></th>
				<td>
					<?php  echo $item['guest_address'];?>
				</td>
			</tr>
			<?php  } ?>
		</table>
		<h4>商品信息</h4>
		<table class="table table-hover">
			<thead class="navbar-inner">
				<tr>
					<th style="width:30px;">ID</th>
					<th style="min-width:150px;">商品标题</th>
 					<th style="width:100px;">销售价/成本价</th>
					<th style="width:100px;">属性</th>
					<th style="width:100px;">数量</th>
					<th style="width:100px;">备注</th>					
					<th style="text-align:right; min-width:60px;">操作</th>
				</tr>
			</thead>
			<?php  if(is_array($item['goods'])) { foreach($item['goods'] as $row) { ?>
			<tr>
				<td><?php  echo $row['id'];?></td>
				<td><?php  if($category[$row['pcate']]['name']) { ?><span class="text-error">[<?php  echo $category[$row['pcate']]['name'];?>] </span><?php  } ?><?php  if($children[$row['pcate']][$row['ccate']]['1']) { ?><span class="text-info">[<?php  echo $children[$row['pcate']][$row['ccate']]['1'];?>] </span><?php  } ?><?php  echo $row['title'];?></td>
 				<!--td><?php  echo $category[$row['pcate']]['name'];?> - <?php  echo $children[$row['pcate']][$row['ccate']]['1'];?></td-->
				<td style="background:#f2dede;"><?php  echo $row['marketprice'];?>元 / <?php  echo $row['productprice'];?>元</td>
				<td><?php  if($row['status']) { ?><span class="label label-success">上架</span><?php  } else { ?><span class="label label-error">下架</span><?php  } ?>&nbsp;</td>
				<td><?php  echo $goodsid[$row['id']]['total'];?></td>
				<td><?php  echo $goodsid[$row['id']]['description'];?></td>
				<td style="text-align:right;">
					<a href="<?php  echo $this->createWebUrl('goods', array('id' => $row['id'], 'op' => 'post'))?>">编辑</a>&nbsp;&nbsp;<a href="<?php  echo $this->createWebUrl('goods', array('id' => $row['id'], 'op' => 'delete'))?>" onclick="return confirm('此操作不可恢复，确认删除？');return false;">删除</a>
				</td>
			</tr>
			<?php  } } ?>
		</table>
		<table class="tb">
			<tr>
				<th></th>
				<td>
				
					<?php  if($item['status']==1) { ?>
					<button type="submit" class="btn btn-primary span2" onclick="return confirm('确认此订单吗？'); return false;" name="confrim" onclick="" value="2">确认订单</button>
					<?php  } else if($item['status'] == 2) { ?>
					<button type="submit" class="btn btn-primary span2" onclick="return confirm('确认完成此订单吗？'); return false;" name="finish" onclick="" value="3">完成订单</button>
					<?php  } else if($item['status'] == 3) { ?>
 					<button type="submit" class="btn btn-primary span2" name="cancel" value="正常" onclick="return confirm('确认取消完成此订单吗？'); return false;">取消完成</button>
					<?php  } else { ?>
					<button class="btn  span2" name="cancel" value="正常">未生成订单</button>
					<?php  } ?>
					
					<?php  if($item['ispay']==1) { ?>
						<button type="submit" class="btn span2" name="cancelpay" value="正常" onclick="return confirm('确认取消付款此订单吗？'); return false;">取消付款</button>
					<?php  } else { ?>
						<button type="submit" class="btn btn-primary span2" name="confrimpay" value="正常" onclick="return confirm('确认此订单已付款吗？'); return false;">确认付款</button>
					<?php  } ?>
					<?php  if($item['status'] != -1) { ?>
					<button type="submit" class="btn span2" name="close" onclick="return confirm('确认关闭此订单吗？'); return false;" value="关闭">关闭订单</button>
					<?php  } else { ?>
					<button type="submit" class="btn span2 btn-primary" name="cancelpay" onclick="return confirm('确认开启此订单吗？'); return false;" value="关闭">开启订单</button>
					<?php  } ?>
					 <?php  if($item['sendstatus']==1) { ?> 
						<button type="submit" class="btn span2" name="cancelsend" onclick="return confirm('确认取消配送订单吗？'); return false;" value="关闭">取消配送</button>
					 <?php  } else { ?>
						<button type="submit" class="btn span2 btn-primary" name="send" onclick="return confirm('此订单已经确认配送？'); return false;" value="关闭">确认配送</button>
					 <?php  } ?>
					 
					<?php  if($item['print_sta']==-1) { ?>
						<button type="submit" class="btn span2" name="cancelprint" value="正常" onclick="return confirm('确认取消GPRS打印机打印？'); return false;">取消打印</button>
					<?php  } else { ?>
						<button type="submit" class="btn btn-primary span2" name="confrimprint" value="正常" onclick="return confirm('确认重新打印此单吗？'); return false;">重新打印</button>
					<?php  } ?>					 
					<input type="hidden" name="token" value="<?php  echo $_W['token'];?>" />
				</td>
			</tr>
		</table>
	</form>
</div>
<?php  } ?>
<?php  include $this->template('common/footer', TEMPLATE_INCLUDEPATH);?>