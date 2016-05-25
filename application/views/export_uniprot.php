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
                    <a href="# " class="navbar-brand">Uniprot Export Program</a>
                </div>
            </div>
        </nav>
        <div class="jumbotron">
            <div class="container">
                <h3>转换工具 </h3>
                <pre>
                <span class="k">批量导出Uniprot数据，类似于lot导出工具，导出字段为Protein Name,Gene Name,AA Protein,Function,Tissue specificity </span>
                <span class="k">&nbsp;&nbsp;&nbsp;&nbsp;Subcellular location,Calc MW (kD)</span>
                </pre>
                <?php echo form_open('caiji/work', array('id' => 'form1', 'name' => 'form1', 'class' => 'bs-docs-example', "target" => "_blank")); ?> 
                <pre class="prettyprint linenums">
                <fieldset>
                     <legend>1.根据legend导出type,导出为xls,点击<a class="btn-small" href="<?php echo base_url('static/caiji_template/set_image_type_from_legend.xls'); ?>">这里</a>下载模板，</legend>
                     <input name="set_image_type_from_legend" type="text" placeholder="输入文件名.xls…" class="input-medium"  value="" style="width: 300px;height: 34px;"/>  <button type="submit" name="submit1"  class="btn btn-info"  value="submit1">提交</button>
                     <pre>
                        <span class="k">1.根据legend1,legend2具体格式按照模板</span>
                        <span class="k">2.导出对应的每一个legend的对应type;</span>
                        </pre>
                    </fieldset>
                </pre>
                <?php echo form_close(); ?>
               
             
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