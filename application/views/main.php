<!DOCTYPE html>
<html >
    <head>
        <title>OEM Company Collection Program</title>
        <meta charset="utf-8" />
        <link rel="stylesheet" href="<?php echo base_url('static/css/bootstrap.min.css'); ?>">
        <script  src="<?php echo base_url('static/js/jquery.js'); ?>"></script>
    </head>
    <body>
        <nav class="navbar navbar-inverse navbar-fixed-top">
            <div class="container">
                <div class="navbar-header">
                    <button aria-controls="navbar" aria-expanded="false" data-target="#navbar" data-toggle="collapse" class="navbar-toggle collapsed" type="button">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a href="# " class="navbar-brand">OEM Company Collection Program</a>
                </div>
            </div>
        </nav>
        <div class="jumbotron">
            <div class="container">
                <h3>转换工具 </h3>
                <pre>
                <span class="k">1.把xls或者xlsx文件放在D:\work-2015\caiji_template\temp下面,可以修改control/caiji.php头部配置;（本地地址的D:\work-2015\caiji_template\）</span>
                <span class="k">2.输入完整的文件名附带后缀，点击每行后面的提交按钮即可;</span>
                <span class="k">3.获取导出的excel，然后需要校对数据.</span>
                </pre>
                <?php echo form_open('caiji/work', array('id' => 'form1', 'name' => 'form1', 'class' => 'bs-docs-example', "target" => "_blank")); ?> 
                <pre class="prettyprint linenums">
                <fieldset>
                     <legend>1.根据legend导出type,导出为xls,点击<a class="btn-small" href="<?php echo base_url('static/caiji_template/set_image_type_from_legend.xls'); ?>">这里</a>下载模板，</legend>
                     <input name="set_image_type_from_legend" type="text" placeholder="输入文件名.xls…" class="input-medium"  value="" style="width: 300px;height: 34px;"/>  <button type="submit" name="submit1"  class="btn btn-info"  value="submit1">提交</button>
                     <pre>
                        <span class="k">1.根据legend1，legend2...具体格式按照模板,</span>
                        <span class="k">2.导出对应的每一个legend的对应type;</span>
                        </pre>
                    </fieldset>
                </pre>
                <pre class="prettyprint linenums">
                <fieldset>
                     <legend>2.根据catalog和type对图片命名,导出为xls,点击<a class="btn-small" href="<?php echo base_url('static/caiji_template/rename_image_name_by_catalog_and_type.xls'); ?>">这里</a>下载模板，</legend>
                     <input name="rename_image_name_by_catalog_and_type" type="text" class="input-medium" placeholder="输入文件名.xls…" value=""  style="width: 300px;height: 34px;"/>   <button type="submit" name="submit2"  class="btn btn-info" value="submit2">提交</button>
                        <pre>
                        <span class="k">1.根据catalog和步骤1生成的type,</span>
                        <span class="k">2.计算type，并且对type数量统计排序,</span>
                        <span class="k">3.对应生成ap10231_WB_1这种格式.</span>
                        </pre>
                    </fieldset>

                </pre>
                <pre class="prettyprint linenums">
                <fieldset>
                    <legend>3.根据表格,进行图片文件重新命名存储,点击<a class="btn-small" href="<?php echo base_url('static/caiji_template/change_image_name_by_catalog.xls'); ?>">这里</a>下载模板，</legend>
                    <input name="change_image_name_by_catalog" type="text" class="input-medium" placeholder="输入文件名.xls…" value="" style="width: 300px ;height: 34px;"/>  <button type="submit" name="submit3"  class="btn btn-info" value="submit3">提交</button>
                     <pre>
                        <span class="k">1.origin_image为未改名字前图片的目录,</span>
                        <span class="k">2.change_image为修改后的图片名称文件,</span>
                        <span class="k">3.校验修改后的图片和数据表图片数量;</span>
                        </pre>
                    </fieldset>
                </pre>
                <pre class="prettyprint linenums">
                <fieldset>
                     <legend>4.根据表格,替换某些字段里面的原始catalog,点击<a class="btn-small" href="<?php echo base_url('static/caiji_template/replace_catalog.xls'); ?>">这里</a>下载模板，</legend>
                     <input name="replace_catalog"id="replace_catalog" type="text" class="input-medium" placeholder="输入文件名.xls…" value="" style="width: 300px; height: 34px;"/>  <button type="submit" name="submit4"  class="btn btn-info" value="submit4">提交</button>
                       <pre>
                        <span class="k">1.根据原来的catalog和现在的catalog,</span>
                        <span class="k">2.对需要替换的几个数据段进行catalog替换。</span>
                        </pre>
                    </fieldset>
                </pre>
                 <pre class="prettyprint linenums">
                <fieldset>
                     <legend>5.根据文档由gene id获取uniprot id进行互转,点击<a class="btn-small" href="<?php echo base_url('static/caiji_template/replace_catalog.xls'); ?>">这里</a>下载模板，</legend>
                     <input name="uniprot_id_by_genid"id="uniprot_id_by_genid" type="text" class="input-medium" placeholder="输入文件名.xls…" value="" style="width: 300px; height: 34px;"/>  <button type="submit" name="submit5"  class="btn btn-info" value="submit5">提交</button>
                       <pre>
                        <span class="k">1.@653509,729238 @110893 @283446</span>
                        <span class="k">2.导出对应的uniprot id,同一个单元格的uniprot id用“|”隔开</span>
                       </pre>
                    </fieldset>
                </pre>
                 <pre class="prettyprint linenums">
                <fieldset>
                     <legend>6.把文件夹下面的文件名复制到excel</legend>
                   <button type="submit" name="submit6"  class="btn btn-info" value="submit6">导出我的文件</button>
                       <pre>
                        <span class="k">1.把你的图片放到这个文件file_2_excel下面</span>
                        <span class="k">2.点击上面按钮</span>
                       </pre>
                    </fieldset>
                </pre>
                
                 <pre class="prettyprint linenums">
                <fieldset>
                     <legend>7.多行数据修改为单行数据点击<a class="btn-small" href="<?php echo base_url('static/caiji_template/rows2row.xlsx'); ?>">这里</a>下载模板</legend>
                        <input name="rows_file"id="rows_file" type="text" class="input-medium" placeholder="输入文件名.xls…" value="" style="width: 300px; height: 34px;"/> <button type="submit" name="submit7"  class="btn btn-info" value="submit7">多行数据合并为单行</button>
                       <pre>
                        <span class="k">1.以你的excel第一列为主，将后面1-3列合并一行。</span>
                        <span class="k">2.把你的图片放到这个文件temp下面</span>
                        <span class="k">3.点击上面按钮</span>
                       </pre>
                    </fieldset>
                </pre>
                <?php echo form_close(); ?>
                <?php echo form_open_multipart('collection_abnova', array('name' => 'oem_form', 'id' => 'oem_form', 'method' => 'post', "target" => "_blank")); ?>
                <div class="bs-docs-example" style="background-color:#F5F5F5">
                    <h3>OEM公司</h3>
                    <pre>
                        <span class="k">1.根据OEM公司的完整的url地址（含http）</span>
                        <span class="k">2.可以选择txt文本存储or输入url地址</span>
                        <span class="k">3.每个公司的规则见具体方法，可以先运行几条测试，然后再核对，进行批量操作</span>
                    </pre>
                    <table class="table table-hover">
                        <tbody>
                            <tr>
                                <td colspan="2">
                                    <label>step 1 选公司：</label>
                                    <?php
                                    foreach ($company_arr as $k => $com_val):
                                        ?>
                                        <label class="radio-inline">
                                            <input type="radio" name="company_type" class="company_type" value="<?php echo $com_val; ?>" <?php echo $k == 0 ? 'checked' : ''; ?> />  <?php echo $com_val; ?>
                                        </label>
                                    <?php endforeach; ?>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2"><label>step 2 选方式：</label></td>
                            </tr>
                            <tr class="select_types">
                                <td>
                                    <label class="radio-inline">
                                        <input type="radio" name="select_type"  class="select_type" value="1" checked="checked" /> 上传txt文件
                                    </label>
                                </td>
                                <td>
                                    <label class="radio-inline">
                                        <input type="radio" name="select_type" class="select_type" value="2" /> 2.输入url地址 (以换行分隔)，每次不超过500行
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td><input type="file" name="url_file" id="url_file" /></td>
                                <td colspan="2">
                                    <textarea name="url_input" cols="100"  rows="10" id="url_input">http://everestbiotech.com/product/goat-anti-csmd1-antibody/
http://everestbiotech.com/product/goat-anti-rars-antibody/
                                    </textarea>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <button value="提交" class="btn btn-info" name="_submit" type="button" onclick="ck_oem()">提交</button>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <?php echo form_close(); ?>
                <!-- Everest end -->
            </div>
        </div>
    </body>
    <script>
        function  ck_oem()
        {
            var ck = true;
            $('.table-hover').find('input[type="radio"][class="company_type"]').each(function() {
                if ($(this).is(':checked') == true)
                {
                    var base_url = '<?php echo base_url(); ?>';
                    var url = 'collection_' + $(this).attr("value").toLowerCase();
                    $('#oem_form').attr('action', base_url + url);
                }
            });
            $('.select_types').find('input[type="radio"][class="select_type"]').each(function() {
                if ($(this).is(':checked') == true)
                {
                    if ($(this).attr("value") == '2')
                    {
                        if ($("#url_input").val() == '')
                        {
                            alert("请输入地址！");
                            ck = false;
                        }
                    }
                    if ($(this).attr("value") == '1')
                    {
                        if ($("#url_file").val() == '')
                        {
                            alert("请先选择上传文件！");
                            ck = false;
                        }
                    }

                }
            });
            if (ck)
            {
                $('#oem_form').submit();
            }
        }
    </script>
</html>