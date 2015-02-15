<?php defined('IN_IA') or exit('Access Denied');?><?php  include $this->template('common/header', TEMPLATE_INCLUDEPATH);?>
<link rel="stylesheet" type="text/css" href="./source/modules/shopping3/style/css/uploadify_t.css" media="all" />
<ul class="nav nav-tabs">
	<li <?php  if($operation == 'post') { ?>class="active"<?php  } ?>><a href="<?php  echo $this->createWebUrl('goods', array('op' => 'post'))?>">添加商品</a></li>
	<li <?php  if($operation == 'display') { ?>class="active"<?php  } ?>><a href="<?php  echo $this->createWebUrl('goods', array('op' => 'display'))?>">管理商品</a></li>
</ul>
<?php  if($operation == 'post') { ?>
<div class="main">
	<form action="" method="post" class="form-horizontal form" enctype="multipart/form-data">
		<input type="hidden" name="id" value="<?php  echo $item['id'];?>" />
		<h4>添加商品</h4>
		<table class="tb">
			<tr>
				<th><label for="">排序</label></th>
				<td>
					<input type="text" name="displayorder" class="span2" value="<?php  echo $item['displayorder'];?>" />
					<!--<input type="checkbox" name="isindex" value="1" <?php  if($item['isindex']==1) { ?>checked<?php  } ?> >是否首页推荐-->
				</td>
			</tr>
			<tr>
				<th>分类</th>
				<td>
					<select class="span3" style="margin-right:15px;" name="pcate"    autocomplete="off">
						<option value="0">请选择一级分类</option>
						<?php  if(is_array($category)) { foreach($category as $row) { ?>
						<?php  if($row['parentid'] == 0) { ?>
						<option value="<?php  echo $row['id'];?>" <?php  if($row['id'] == $item['pcate']) { ?> selected="selected"<?php  } ?>><?php  echo $row['name'];?></option>
						<?php  } ?>
						<?php  } } ?>
					</select>
					 
				</td>
			</tr>
			<tr>
				<th><label for="">商品名称</label></th>
				<td>
					<input type="text" name="goodsname" class="span6" value="<?php  echo $item['title'];?>" />
				</td>
			</tr>
			<tr>
				<th><label for="">商品单位</label></th>
				<td>
					<input type="text" name="unit" class="span3" value="<?php  echo $item['unit'];?>" />
					<span class="help-block">一个字，例如，份，只，个，两，瓶。</span>										
				</td>
			</tr>
			<tr>
				<th><label for="">自定义标签</label></th>
				<td>
					<input type="text" name="label" class="span3" value="<?php  echo $item['label'];?>" />
					<span class="help-block">最多2个字，例如：特价，推荐，新品等。可为空</span>					
				</td>
			</tr>
			 
			<tr>
				<th><label for="">是否上架</label></th>
				<td>
					<label for="isshow1" class="radio inline"><input type="radio" name="status" value="1" id="isshow1" <?php  if($item['status'] == 1) { ?>checked="true"<?php  } ?> /> 是</label>
					&nbsp;&nbsp;&nbsp;
					<label for="isshow2" class="radio inline"><input type="radio" name="status" value="0" id="isshow2"  <?php  if($item['status'] == 0) { ?>checked="true"<?php  } ?> /> 否</label>
					<span class="help-block"></span>
				</td>
			</tr>
			<tr>
				<th>商品图片</th>
				<td>
					<div id="upimg_main">
						<div id="file_upload" class="uploadify">
						<span class="btn btn-file"><span class="fileupload-new" id="file_upload-button">选择图片</span></span><span class="maroon">*</span></div>
						<div id="file_upload-queue" class="uploadify-queue"></div>
						<ul class="ipost-list ui-sortable" id="fileList">
							<?php  if(is_array($item['thumbArr'])) { foreach($item['thumbArr'] as $row) { ?>
							<li class="imgbox"><a class="thumb_close" href="javascript:void(0)" title="删除"></a>  
							<input type="hidden" value="<?php  echo $row;?>" name="thumb_url[]">
							<span class="item_box"><img src="<?php  echo $row;?>"></span>
							</li>
							<?php  } } ?>
						</ul>
						<div id="file_upload_queue" class="uploadifyQueue"></div>
					</div>
 					<span class="help-block" style="clear:both">商品展示图片，第一张默认为商品缩略图(尺寸建议：300*230px)</span>					
				</td>
			</tr>	
			<!--tr>
				<th><label for="">商品编号</label></th>
				<td>
					<input type="text" name="goodssn" class="span6" value="<?php  echo $item['goodssn'];?>" />
				</td>
			</tr>
			<tr>
				<th><label for="">商品条码</label></th>
				<td>
					<input type="text" name="productsn" class="span6" value="<?php  echo $item['productsn'];?>" />
				</td>
			</tr-->
			<tr>
				<th><label for="">销售价</label></th>
				<td>
					<input type="text" name="marketprice" class="span3" value="<?php  echo $item['marketprice'];?>" /> 元 <span style="color:red">*</span>
				</td>
			</tr>
			<tr>
				<th><label for="">原价</label></th>
				<td>
					<input type="text" name="productprice" class="span3" value="<?php  echo $item['productprice'];?>" /> 元 <span style="color:red">*</span>
				</td>
			</tr>
			<tr>
				<th><label for="">库存</label></th>
				<td>
					<input type="text" name="total" class="span2" value="<?php  echo $item['total'];?>" />
					<span class="help-block">当前商品的库存数量，设置-1则表示不限制。</span>
				</td>
			</tr>
			<tr>
				<th><label for="">销量</label></th>
				<td>
					<input type="text" name="sellnums" class="span2" value="<?php  echo $item['sellnums'];?>" />
					<span class="help-block">可以虚拟，订单完成后，菜品数量+1,不建议修改</span>
				</td>
			</tr>			
			<tr>
				<th>简介</th>
				<td>
					<textarea style="height:150px;" class="span7" name="description" cols="70"><?php  echo $item['description'];?></textarea>
				</td>
			</tr>
			<!--
			<tr>
				<th>内容</th>
				<td>
					<textarea style="height:400px; width:100%;" class="span7 richtext-clone" name="content" cols="70"><?php  echo $item['content'];?></textarea>
				</td>
			</tr>
			-->
		<tr>
			<th></th>
			<td>
				<input name="submit" type="submit" value="提交" class="btn btn-primary span3">
				<input type="hidden" name="token" value="<?php  echo $_W['token'];?>" />
			</td>
		</tr>
		</table>
	</form>
</div>
<script type="text/javascript">
<!--
	var category = <?php  echo json_encode($children)?>;
	kindeditor($('.richtext-clone'));
//-->
</script>
<script type="text/javascript" src="./resource/script/kindeditor/kindeditor-min.js"></script>
<script type="text/javascript" src="./resource/script/kindeditor/lang/zh_CN.js"></script>
<link type="text/css" rel="stylesheet" href="./resource/script/kindeditor/themes/default/default.css" />
 <script type="text/javascript">
var editor = KindEditor.editor({
	allowFileManager : true,
	uploadJson : "./index.php?act=attachment&do=upload",
	fileManagerJson : "./index.php?act=attachment&do=manager",
	 
	});
	$("#file_upload-button").click(function() {
		editor.loadPlugin("image", function() {
		editor.plugin.imageDialog({
			imageUrl : $("#upload-image-url-thumb").val(),
			clickFn : function(url) {
				editor.hideDialog();
				var filename = /images(.*)/.exec(url);
					html='<li class="imgbox"><a class="thumb_close" href="javascript:void(0)" title="删除"></a><input type="hidden" value="'+url+'" name="thumb_url[]"><span class="item_box"><img src="'+url+'"></span></li>';
					$("#fileList").append(html);
			}
		});
	});
});
        $("a.thumb_close").live("click ", function (n) {
           $(this).parent().remove();
        });

</script>			
<?php  } else if($operation == 'display') { ?>
<div class="main">
	<div class="search">
		<form action="site.php" method="get">
		<input type="hidden" name="act" value="module" />
		<input type="hidden" name="do" value="goods" />
		<input type="hidden" name="op" value="display" />
		<input type="hidden" name="name" value="shopping3" />
		<table class="table table-bordered tb">
			<tbody>
				<tr>
					<th>关键字</th>
					<td>
						<input class="span6" name="keyword" id="" type="text" value="<?php  echo $_GPC['keyword'];?>">
					</td>
				</tr>
				<tr>
					<th>状态</th>
					<td>
						<select name="status">
							<option value="1" <?php  if(!empty($_GPC['status'])) { ?> selected<?php  } ?>>上架</option>
							<option value="0" <?php  if(empty($_GPC['status'])) { ?> selected<?php  } ?>>下架</option>
						</select>
					</td>
				</tr>
				<tr>
					<th>分类</th>
					<td>
						<select class="span3" style="margin-right:15px;" name="cate_1" >
							<option value="0">请选择一级分类</option>
							<?php  if(is_array($category)) { foreach($category as $row) { ?>
							<?php  if($row['parentid'] == 0) { ?>
							<option value="<?php  echo $row['id'];?>" <?php  if($row['id'] == $_GPC['cate_1']) { ?> selected="selected"<?php  } ?>><?php  echo $row['name'];?></option>
							<?php  } ?>
							<?php  } } ?>
						</select>
						 
					</td>
				</tr>
				<tr>
				 <tr class="search-submit">
					<td colspan="2"><button class="btn pull-right span2"><i class="icon-search icon-large"></i> 搜索</button></td>
				 </tr>
			</tbody>
		</table>
		</form>
	</div>
	<div style="padding:15px;">
		<form action="" method="post" onsubmit="return formcheck(this)">				
		<table class="table table-hover">
			<thead class="navbar-inner">
				<tr>
					<th style="width:10px;"></th>
					<th style="width:60px;">显示顺序</th>
					<th style="width:30px;">ID</th>
					<th style="min-width:150px;">商品标题</th>
 					<th style="width:100px;">销售价/原价</th>
					<th style="width:100px;">销售量/库存</th>
					<th style="width:100px;">属性</th>
					<th style="text-align:right; min-width:60px;">操作</th>
				</tr>
			</thead>
			<tbody>
				<?php  if(is_array($list)) { foreach($list as $item) { ?>
				<tr>
					<td></td>
					<td><input type="text" class="span1" name="displayorder[<?php  echo $item['id'];?>]" value="<?php  echo $item['displayorder'];?>"></td>				
					<td><?php  echo $item['id'];?></td>
					<td><?php  if(!empty($category[$item['pcate']])) { ?><span class="text-error">[<?php  echo $category[$item['pcate']]['name'];?>] </span><?php  } ?><?php  if(!empty($children[$item['pcate']])) { ?><span class="text-info">[<?php  echo $children[$item['pcate']][$item['ccate']]['1'];?>] </span><?php  } ?><?php  echo $item['title'];?></td>
 					<td style="background:#f2dede;"><?php  echo $item['marketprice'];?>元 / <?php  echo $item['productprice'];?>元</td>
					<td style="background:#a2dede;"><?php  echo $item['sellnums'];?>/ <?php  if($item['total']<0) { ?>无限<?php  } else { ?><?php  echo $item['total'];?><?php  } ?></td>					
					<td><?php  if($item['status']) { ?><span class="label label-success">上架</span><?php  } else { ?><span class="label label-error">下架</span><?php  } ?>&nbsp;<?php  if($item['isindex'] == 1) { ?><span class="label label-info">首页</span><?php  } ?></td>
					<td style="text-align:right;">
						<a href="<?php  echo $this->createWebUrl('goods', array('id' => $item['id'], 'op' => 'post'))?>">编辑</a>&nbsp;&nbsp;<a href="<?php  echo $this->createWebUrl('goods', array('id' => $item['id'], 'op' => 'delete'))?>" onclick="return confirm('此操作不可恢复，确认删除？');return false;">删除</a>
					</td>
				</tr>
				<?php  } } ?>
			</tbody>
			<tr>
				<td></td>
				<td colspan="3">
					<input name="token" type="hidden" value="<?php  echo $_W['token'];?>" />
					<input type="submit" class="btn btn-primary" name="submit" value="提交" />
				</td>
			</tr>
		</table>
		</form>
		<?php  echo $pager;?>
	</div>
</div>
<script type="text/javascript">
<!--
	var category = <?php  echo json_encode($children)?>;
	
//-->
</script>
<?php  } ?>
<?php  include $this->template('common/footer', TEMPLATE_INCLUDEPATH);?>