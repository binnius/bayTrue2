<?php defined('IN_IA') or exit('Access Denied');?>		<div id="footer_menu" class="footer footer_menu">
            <ul class="clear">
                <li><a href="<?php  echo  $this->createMobileUrl('wlhome')?>"     <?php  if($_GPC['do']=='wlhome') { ?>class="onactive"<?php  } ?>><span class="icons icons_1"></span><label>店铺首页</label></a></li>
				 <?php  if(($_GPC['do']!='wlmember' && $_GPC['do']!='wlorder'&& $_GPC['do']!='wlmylike' && $_GPC['do']!='wlhome'  && $_GPC['do']!='wlgenius' )) { ?>
                <li><a href="<?php  echo  $this->createMobileUrl('wlindex')?>" class="onactive" ><span class="icons icons_2"></span><label>选购</label></a></li>
				<?php  } else { ?>
				<li><a  href="<?php  echo  $this->createMobileUrl('wlindex')?>" ><span class="icons icons_2"></span><label>选购</label></a></li>
				<?php  } ?>
				<li><a href="<?php  echo  $this->createMobileUrl('wlgenius')?>" <?php  if($_GPC['do']=='wlgenius') { ?>class="onactive"<?php  } ?>><span class="icons icons_1"></span><label>智能推荐</label></a></li>					
                <?php  if($_GPC['do']=='wlmember'||$_GPC['do']=='wlorder') { ?>
				<li><a href="javascript:;" class="onactive" ><span class="icons icons_3"></span><label>我的订单</label></a></li>
				<?php  } else { ?>
				<li><a href="<?php  echo  $this->createMobileUrl('wlmember',array('status'=>1))?>" ><span class="icons icons_3"></span><label>我的订单</label></a></li>
				<?php  } ?>
                 			
                <li><a href="javascript:void(0);" id="my_menu"><span class="icons icons_5"><label id="num" class="num"><?php  echo intval($totalnum)?></label></span></a></li>
            </ul>
        </div>
        <script>
            var footer = document.getElementById("footer_menu");
            var evtObj = {
                handleEvent: function(evt){
                    if("A" == evt.target.tagName){
                        evt.target.classList.toggle("on");
                    }
                }
            }
            footer.addEventListener("touchstart", evtObj, false);
            footer.addEventListener("touchend", evtObj, false);
			
			$("a").click(function(){
				MLoading.show('加载中');
			});	
        </script>
 