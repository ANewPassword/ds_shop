<?php
/**
 * 收支明细
**/
include("../includes/common.php");
$title='收支明细';
include './head.php';
if($islogin==1){}else exit("<script language='javascript'>window.location.href='./login.php';</script>");
?>
    <div class="col-md-12 center-block" style="float: none;">
<?php
$link = '';
$sql = " 1";
$zid = 0;
if(isset($_GET['zid'])){
	$zid = intval($_GET['zid']);
	$sql .= " AND zid=$zid";
	$link .= '&zid='.$zid;
}
if(isset($_GET['kw']) && !empty($_GET['kw'])) {
	$type=intval($_GET['type']);
	$kw=daddslashes($_GET['kw']);
	if($type == 1) $sql .= " AND `action`='$kw'";
	elseif($type == 2) $sql .= " AND `point`='$kw'";
	elseif($type == 3) $sql .= " AND `bz` LIKE '%$kw%'";
	elseif($type == 4) $sql .= " AND `orderid`='$kw'";
	$link .= '&type='.$type.'&kw='.$kw;
}

$thtime=date("Y-m-d").' 00:00:00';
$lastday=date("Y-m-d",strtotime("-1 day")).' 00:00:00';
$income_today=$DB->getColumn("SELECT sum(point) FROM pre_points WHERE action='提成' AND{$sql} AND addtime>'$thtime'");
$outcome_today=$DB->getColumn("SELECT sum(point) FROM pre_points WHERE action='消费' AND{$sql} AND addtime>'$thtime'");
$income_lastday=$DB->getColumn("SELECT sum(point) FROM pre_points WHERE action='提成' AND{$sql} AND addtime<'$thtime' AND addtime>'$lastday'");
$outcome_lastday=$DB->getColumn("SELECT sum(point) FROM pre_points WHERE action='消费' AND{$sql} AND addtime<'$thtime' AND addtime>'$lastday'");
if(isset($_GET['zid'])){
$income_all=$DB->getColumn("SELECT sum(point) FROM pre_points WHERE action='提成' AND{$sql}");
$outcome_all=$DB->getColumn("SELECT sum(point) FROM pre_points WHERE action='消费' AND{$sql}");
}

?>
<div class="block">
     <div class="block-title"><h2><?php echo ($zid>0?'分站ZID:<b>'.$zid.'</b> ':'全部分站')?>收支明细</h2></div>
		  <div class="table-responsive">
<table class="table table-bordered">
<tbody>
<tr height="25">
<td align="center"><font color="#808080"><b><span class="glyphicon glyphicon-tint"></span>今日收益</b></br><?php echo round($income_today,2)?>元</font></td>
<td align="center"><font color="#808080"><b><i class="glyphicon glyphicon-check"></i>今日消费</b></br></span><?php echo round($outcome_today,2)?>元</font></td>
<td align="center"><font color="#808080"><b><span class="glyphicon glyphicon-tint"></span>昨日收益</b></br><?php echo round($income_lastday,2)?>元</font></td>
<td align="center"><font color="#808080"><b><i class="glyphicon glyphicon-check"></i>昨日消费</b></br></span><?php echo round($outcome_lastday,2)?>元</font></td>
<?php if(isset($_GET['zid'])){?>
<td align="center"><font color="#808080"><b><span class="glyphicon glyphicon-tint"></span>总计收益</b></br><?php echo round($income_all,2)?>元</font></td>
<td align="center"><font color="#808080"><b><i class="glyphicon glyphicon-check"></i>总计消费</b></br></span><?php echo round($outcome_all,2)?>元</font></td>
<?php }?>
</tr>
</tbody>
</table>
<form action="./record.php" method="GET" class="form-inline">
  <?php if(isset($_GET['zid'])){?><input type="hidden" name="zid" value="<?php echo $_GET['zid']?>"><?php }?>
  <div class="form-group">
    <label><b>搜索</b></label>
	<select name="type" class="form-control" default="<?php echo @$_GET['type']?>"><option value="1">类型</option><option value="2">金额</option><option value="3">详情</option><option value="4">订单号</option></select>
    <input type="text" class="form-control" name="kw" placeholder="输入搜索内容" value="<?php echo @$_GET['kw']?>">
	<button type="submit" class="btn btn-primary"><i class="fa fa-search"></i>&nbsp;搜索</button>
  </div>
</form>
      <div class="table-responsive">
        <table class="table table-striped">
          <thead><tr><th>ID</th><th>站点ID</th><th>类型</th><th>金额</th><th>详情</th><th>时间</th><th>订单号</th></tr></thead>
          <tbody>
<?php
$numrows=$DB->getColumn("SELECT count(*) from pre_points WHERE{$sql}");

$pagesize=30;
$pages=ceil($numrows/$pagesize);
$page=isset($_GET['page'])?intval($_GET['page']):1;
$offset=$pagesize*($page - 1);

$rs=$DB->query("SELECT * FROM pre_points WHERE{$sql} order by id desc limit $offset,$pagesize");
while($res = $rs->fetch())
{
echo '<tr><td><b>'.$res['id'].'</b></td><td><a href="sitelist.php?zid='.$res['zid'].'">'.$res['zid'].'</a></td><td>'.$res['action'].'</td><td><font color="'.(in_array($res['action'],array('提成','奖励','赠送','退款','退回','充值','加款'))?'red':'green').'">'.$res['point'].'</font></td><td>'.$res['bz'].'</td><td>'.$res['addtime'].'</td><td>'.($res['orderid']?'<a href="./list.php?id='.$res['orderid'].'" target="_blank">'.$res['orderid'].'</a>':'无').'</td></tr>';
}
?>
          </tbody>
        </table>
      </div>
<?php
echo'<ul class="pagination">';
$first=1;
$prev=$page-1;
$next=$page+1;
$last=$pages;
if ($page>1)
{
echo '<li><a href="record.php?page='.$first.$link.'">首页</a></li>';
echo '<li><a href="record.php?page='.$prev.$link.'">&laquo;</a></li>';
} else {
echo '<li class="disabled"><a>首页</a></li>';
echo '<li class="disabled"><a>&laquo;</a></li>';
}
$start=$page-10>1?$page-10:1;
$end=$page+10<$pages?$page+10:$pages;
for ($i=$start;$i<$page;$i++)
echo '<li><a href="record.php?page='.$i.$link.'">'.$i .'</a></li>';
echo '<li class="disabled"><a>'.$page.'</a></li>';
for ($i=$page+1;$i<=$end;$i++)
echo '<li><a href="record.php?page='.$i.$link.'">'.$i .'</a></li>';
if ($page<$pages)
{
echo '<li><a href="record.php?page='.$next.$link.'">&raquo;</a></li>';
echo '<li><a href="record.php?page='.$last.$link.'">尾页</a></li>';
} else {
echo '<li class="disabled"><a>&raquo;</a></li>';
echo '<li class="disabled"><a>尾页</a></li>';
}
echo'</ul>';
#分页
?>
    </div>
  </div>
 </div>
</div>