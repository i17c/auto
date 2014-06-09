<?php

/**
 * User: duxing
 * Email: duxing@taobao.com
 * Date: 2013-1-22
 */

include_once("../include/common.php");
include_once("SqlAnaly.php");
include_once("DaoGenerator.php");
$columns ="";
$columns_posted ="";
if(isset($_POST["submit"]) && $_POST["submit"]=="anally"){
    $columns = $_POST["columns"];
    $columns_posted = $columns;


    $columns = str_replace("\r","",$columns);
    $columns = str_replace("\n\n","\n",$columns);
    $columns = SqlAnaly::fixComment($columns);
    $columns = str_replace(","," \n",$columns);

}
?>
<html>
<head>
    <script src="../assets/js/jquery-1.7.1.min.js"></script>
    <script src="../assets/zeroclipboard/ZeroClipboard.js"></script>
    <style>
        TD{
            border-left:1px solid #ccc;
            border-top:1px solid #ccc;
        }
        TABLE{
            width:700px;
            border-right:1px solid #ccc;
            border-bottom:1px solid #ccc;
        }
        TR.title TD{
            font-weight:bold;
            background: #eee;
        }
        INPUT[type="text"]{
            width:180px;
        }
        INPUT.middle{
            width:140px;
        }
        INPUT.short{
            width:100px;
        }
        INPUT.checkbox{
            width:20px;
        }
    </style>
</head>
<body>
<form action="" method="post">
    <span style="color:red;">注意: 本工具需要mysql数据库字段命名符合规范,单词间用下划线分割或用驼峰式.java类属性统一使用驼峰式命名. (本工具独家支持chrome,mysql)</span><br>
    使用本工具流程: 填入建表语句 - > Anally - > 生成XXX <br/>
    输入数据库的建表语句:<input type="button" id="autoInput" value="填入测试数据">
    <textarea name="columns" id="columns" style="width:100%;height:120px;"><?php echo str_replace("</textarea>","",$columns_posted)?></textarea>
    <input type="submit" name="submit" value="anally">
<!--    <div id="copy_container" style="float:left;position:relative;">
        <input type="submit" value="copy" id="copy">
    </div>-->
    <span id="result"></span>
</form>


<?php
$matches = SqlAnaly::analy($columns);
$print = "";
$primary="";

if($matches!="" && $matches[0]==true && count($matches['columns'])>0){
    $print .= "<table cellpadding='0' cellspacing='0'>\n";
    $print .="<tr class='title'>\n";
    $print .="<td>数据表</td>\n";
    $print .="<td>DO</td>\n";
    $print .="<td>DAO</td>\n";
    $print .="<td>DAOImpl</td>\n";
    $print .="<td>SQLMAP</td>\n";
    $print .="</tr>\n";
    $print .="<tr>\n";
    $print .="<td><input type=\"input\" name=\"table\" value=\"".$matches['table']."\"></td>\n";
    $print .="<td><input type=\"input\" name=\"class\" value=\"".SqlAnaly::getClassName($matches['table'])."\"></td>\n";
    $print .="<td><input type=\"input\" name=\"classDAO\" value=\"".SqlAnaly::getClassDAOName($matches['table'])."\"></td>\n";
    $print .="<td><input type=\"input\" name=\"classDAOImpl\" value=\"".SqlAnaly::getClassDAOImplName($matches['table'])."\"></td>\n";
    $print .="<td><input type=\"input\" name=\"fileSqlMap\" value=\"".SqlAnaly::getSqlMapName($matches['table'])."\"></td>\n";
    $print .="</tr>\n";
    $print .="</table>";
    $print .="<br/>";
    $print .= "<table cellpadding='0' cellspacing='0'>\n";
    $print .="<tr class='title'>\n";
    $print .="<td></td>\n";
    $print .="<td><input id=\"allCheck\" class=\"checkbox\" type=\"checkbox\" checked value=\"checked\"></td>\n";
    $print .="<td>字段</td>\n";
    $print .="<td>数据库类型</td>\n";
    $print .="<td>java类型</td>\n";
    $print .="<td>类属性</td>\n";
    $print .="<td>属性说明</td>\n";
    if(isset($matches['primaryKey'])&&$matches['primaryKey']!=""){
        $print .="<td>主键</td>\n";
    }
    $print .="</tr>\n";
    $i=0;
    foreach($matches['columns'] as $line){

        $print .="<tr id=\"tr".$i."\">\n";
        $print .="<td align=\"center\">".($i+1)."</td>\n";
        $print .="<td align=\"center\"><input id=\"check[".$i."]\" class=\"checkbox line\" type=\"checkbox\" checked value=\"$i\"></td>\n";
        $print .="<td><input type=\"text\" name=\"name[]\" class=\"name\" value=\"".$line['name']."\"></td>\n";
        $print .="<td><input class=\"middle\" type=\"text\" name=\"sqlType[]\" class=\"sqlType\"  value=\"".$line['typeStr']."\"></td>\n";
        $print .="<td><input class=\"short type\" type=\"text\" name=\"type[]\" value=\"".$line['type']."\"></td>\n";
        $print .="<td><input type=\"text\" name=\"property[]\" class=\"property\" value=\"".SqlAnaly::getPropertyName($line['name'])."\"></td>\n";
        $print .="<td><input type=\"text\" name=\"comment[]\" class=\"comment\" value=\"".(isset($line['comment'])?$line['comment']:"")."\"></td>\n";
        if(isset($matches['primaryKey'])&&$matches['primaryKey']!=""){
            $print .="<td>".($line['name']==$matches['primaryKey']?"■":"")."</td>\n";
            $primary="<input type=\"hidden\" name=\"primaryKey\" value=\"".$matches['primaryKey']."\">";
        }

        $print .="</tr>\n";
        $i++;
    }
    $print .="</table>";
}
echo "\n<div id=\"links\">";
echo $print;
echo "\n修改复选框以选择对象中的属性,修改值以修改生成的各种内容.";
echo "\n<form id=\"formGenerator\" target='_blank' action='dao.php' method='post'>\n";
echo $primary."\n";
echo "<input type=\"hidden\" name=\"tableName\" value=\"\" id=\"tableName\">\n";
echo "<input type=\"hidden\" name=\"sqlMapName\" value=\"\" id=\"sqlMapName\">\n";
echo "<input type=\"hidden\" name=\"className\" value=\"\" id=\"className\">\n";
echo "<input type=\"hidden\" name=\"classDAOName\" value=\"\" id=\"classDAOName\">\n";
echo "<input type=\"hidden\" name=\"classDAOImplName\" value=\"\" id=\"classDAOImplName\">\n";
echo "<input type=\"hidden\" name=\"columnData\" value=\"\" id=\"columnData\">\n";
echo "<input type=\"button\" value=\"生成DO\" id=\"autoDO\">\n";
echo "<input type=\"button\" value=\"生成DAO\" id=\"autoDAO\">\n";
echo "<input type=\"button\" value=\"生成DAOImpl\" id=\"autoDAOImpl\">\n";
echo "<input type=\"button\" value=\"生成Manager\" id=\"autoManager\">\n";
echo "<input type=\"button\" value=\"生成ManagerImpl\" id=\"autoManagerImpl\">\n";
echo "<input type=\"button\" value=\"生成SqlMap\" id=\"autoSqlMap\">\n";
echo "</form>\n</div>";
echo "<input type=\"button\" value=\"查看BaseDAO\" id=\"baseDAO\">\n";
?>


<script>
    jQuery(document).ready(function(){

/*        var clip = null;
        clip = new ZeroClipboard.Client();
        clip.setHandCursor( true );
        clip.addEventListener('mouseDown', function (client) {
            clip.setText($('#links').text()+1);
            $('#result').text('copied!')
        });
        clip.glue('copy','copy_container');*/
        $('#autoDO').click(function(){
            var actionPage="do.php";
            var i=0;
            var dataStr = "";
            $("input.checkbox.line").each(function(element) {
                if(this.checked){
                    if(dataStr!="")dataStr+="|";
                    dataStr+="name="+$("#tr"+this.value+" td input.name").val();
                    dataStr+="&type="+$("#tr"+this.value+" td input.type").val();
                    dataStr+="&prop="+$("#tr"+this.value+" td input.property").val();
                    dataStr+="&comm="+encodeURIComponent($("#tr"+this.value+" td input.comment").val());
                    i++;
                }
            });
//            document.getElementById("shows").innerHTML = dataStr
            if(dataStr==""){
                alert('无属性设置!请勾选复选框以至少选择一个属性才能生成.');
                return;
            }
            var form =$('#formGenerator');
            $('#columnData').val(dataStr);
            $('#className').val($("input[name='class']").val());
            form.attr("action",actionPage);
            form.submit();

            /*jQuery.ajax({
                url: "dao.php",
                type: "post",
                dataType: 'json',
                data: {
                    "list":dataStr
                },
                success: function (data) {
                    if (data.result == "succ") {
                        location.reload();
                    } else {
                        alert(data.msg);
                    }
                }
            });*/
        });
        $('#autoDAO').click(function(){
            var actionPage="dao.php";
            var dataStr = "";
            $("input.checkbox.line").each(function(element) {
                if(this.checked){
                    if(dataStr!="")dataStr+="|";
                    dataStr+="name="+$("#tr"+this.value+" td input.name").val();
                    dataStr+="&type="+$("#tr"+this.value+" td input.type").val();
                    dataStr+="&prop="+$("#tr"+this.value+" td input.property").val();
                    dataStr+="&comm="+encodeURIComponent($("#tr"+this.value+" td input.comment").val());
                    i++;
                }
            });
            if(dataStr==""){
                alert('无属性设置!请勾选复选框以至少选择一个属性才能生成.');
                return;
            }
            var i=0;
            var form =$('#formGenerator');
            $('#columnData').val(dataStr);
            $('#className').val($("input[name='class']").val());
            $('#classDAOName').val($("input[name='classDAO']").val());
            form.attr("action",actionPage);
            form.submit();
        });

        $('#autoDAOImpl').click(function(){
            var actionPage="daoImpl.php";
            var dataStr = "";
            $("input.checkbox.line").each(function(element) {
                if(this.checked){
                    if(dataStr!="")dataStr+="|";
                    dataStr+="name="+$("#tr"+this.value+" td input.name").val();
                    dataStr+="&type="+$("#tr"+this.value+" td input.type").val();
                    dataStr+="&prop="+$("#tr"+this.value+" td input.property").val();
                    dataStr+="&comm="+encodeURIComponent($("#tr"+this.value+" td input.comment").val());
                    i++;
                }
            });
            if(dataStr==""){
                alert('无属性设置!请勾选复选框以至少选择一个属性才能生成.');
                return;
            }
            var i=0;
            var form =$('#formGenerator');
            $('#columnData').val(dataStr);
            $('#className').val($("input[name='class']").val());
            $('#classDAOName').val($("input[name='classDAO']").val());
            $('#classDAOImplName').val($("input[name='classDAOImpl']").val());
            form.attr("action",actionPage);
            form.submit();
        });

        $('#autoManager').click(function(){
            var actionPage="manager.php";
            var dataStr = "";
            $("input.checkbox.line").each(function(element) {
                if(this.checked){
                    if(dataStr!="")dataStr+="|";
                    dataStr+="name="+$("#tr"+this.value+" td input.name").val();
                    dataStr+="&type="+$("#tr"+this.value+" td input.type").val();
                    dataStr+="&prop="+$("#tr"+this.value+" td input.property").val();
                    dataStr+="&comm="+encodeURIComponent($("#tr"+this.value+" td input.comment").val());
                    i++;
                }
            });
            if(dataStr==""){
                alert('无属性设置!请勾选复选框以至少选择一个属性才能生成.');
                return;
            }
            var i=0;
            var form =$('#formGenerator');
            $('#columnData').val(dataStr);
            $('#className').val($("input[name='class']").val());
            $('#classDAOName').val($("input[name='classDAO']").val());
            form.attr("action",actionPage);
            form.submit();
        });

        $('#autoManagerImpl').click(function(){
            var actionPage="managerImpl.php";
            var dataStr = "";
            $("input.checkbox.line").each(function(element) {
                if(this.checked){
                    if(dataStr!="")dataStr+="|";
                    dataStr+="name="+$("#tr"+this.value+" td input.name").val();
                    dataStr+="&type="+$("#tr"+this.value+" td input.type").val();
                    dataStr+="&prop="+$("#tr"+this.value+" td input.property").val();
                    dataStr+="&comm="+encodeURIComponent($("#tr"+this.value+" td input.comment").val());
                    i++;
                }
            });
            if(dataStr==""){
                alert('无属性设置!请勾选复选框以至少选择一个属性才能生成.');
                return;
            }
            var i=0;
            var form =$('#formGenerator');
            $('#columnData').val(dataStr);
            $('#className').val($("input[name='class']").val());
            $('#classDAOName').val($("input[name='classDAO']").val());
            $('#classDAOImplName').val($("input[name='classDAOImpl']").val());
            form.attr("action",actionPage);
            form.submit();
        });

        $('#autoSqlMap').click(function(){
            var actionPage="sqlmap.php";
            var i=0;
            var dataStr = "";
            $("input.checkbox.line").each(function(element) {
                if(this.checked){
                    if(dataStr!="")dataStr+="|";
                    dataStr+="name="+$("#tr"+this.value+" td input.name").val();
                    dataStr+="&type="+$("#tr"+this.value+" td input.type").val();
                    dataStr+="&prop="+$("#tr"+this.value+" td input.property").val();
                    dataStr+="&comm="+encodeURIComponent($("#tr"+this.value+" td input.comment").val());
                    i++;
                }
            });
            if(dataStr==""){
                alert('无属性设置!请勾选复选框以至少选择一个属性才能生成.');
                return;
            }
            var form =$('#formGenerator');
            $('#columnData').val(dataStr);
            $('#className').val($("input[name='class']").val());
            $('#classDAOName').val($("input[name='classDAO']").val());
            $('#sqlMapName').val($("input[name='fileSqlMap']").val());
            $('#tableName').val($("input[name='table']").val());
            form.attr("action",actionPage);
            form.submit();


        });

        $('#baseDAO').click(function(){
            window.open('basedao.php');
        });

        $('#autoInput').click(function(){
            $('#columns').val("CREATE TABLE IF NOT EXISTS `transaction` (   `ID` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '序号',   `app` varchar(20) NOT NULL COMMENT '应用',   `model` varchar(20) NOT NULL COMMENT '应用内的模块',   `key` varchar(50) NOT NULL COMMENT '事务自定义标记',   `resume_bean_name` varchar(50) NOT NULL COMMENT '事务补偿当前对象',   `resume_method` varchar(255) NOT NULL COMMENT '事务补偿方法',   `resume_parameter` longtext NOT NULL COMMENT '事务方法参数',   `exception` varchar(50) NOT NULL COMMENT '异常标记串',   `exception_info` varchar(255) NOT NULL COMMENT '异常信息',   `exception_stack` longtext NOT NULL COMMENT '异常堆栈',   `step` tinyint(4) NOT NULL COMMENT '事务进度',   `status` tinyint(4) NOT NULL COMMENT '事务状态',   `mark` varchar(32) NOT NULL COMMENT '当前事务标记,为各种信息md5结果',   `gmt_create` datetime NOT NULL COMMENT '创建时间',   `gmt_modified` datetime NOT NULL COMMENT '修改时间',   `is_deleted` tinyint(4) NOT NULL COMMENT '记录是否逻辑删除',   PRIMARY KEY (`id`),   UNIQUE KEY `mark_2` (`mark`),   KEY `mark` (`mark`),   KEY `app` (`app`,`model`,`key`,`mark`),   KEY `key` (`key`) ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='事务记录表' AUTO_INCREMENT=2 ;");
        });

        $('#allCheck').click(function(){
            $("input.checkbox.line").each(function(element) {
                this.checked=$('#allCheck').attr("checked");
            });
        });
        function allPrpos(obj) {
            // 用来保存所有的属性名称和值
            var props = "";
            // 开始遍历
            for(var p in obj){
                // 方法
                if(typeof(obj[p])=="function"){
                    obj[p]();
                }else{
                    // p 为属性名称，obj[p]为对应属性的值
                    props+= p + "=" + obj[p] + " ";
                }
            }
            // 最后显示所有的属性
            //alert(props);
            document.getElementById("shows").innerHTML = props;
        }



    });



</script>

<div id="shows"></div>
</body>
</html>