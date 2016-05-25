<!DOCTYPE html>
<html >
    <head>
        <title>Program</title>
        <meta charset="utf-8" />
        <link rel="stylesheet" href="<?php echo base_url('static/css/bootstrap.min.css'); ?>">
    </head>
    <body>
        <nav class="navbar navbar-inverse navbar-fixed-top">
            <div class="container">
                <div class="navbar-header">
              
                    <a href="# " class="navbar-brand">Program</a>
                </div>
            </div>
        </nav>
        <div class="jumbotron">
            <div class="container">
                <h3>Peptide Sequence</h3>
                <pre>
                <span class="k">输入Uniprot ID:</span>
                <?php echo form_open('tools_peptide_seq/work', array('id' => 'form1', 'name' => 'form1', 'class' => 'bs-docs-example', "target" => "_blank")); ?> 
                  <input name="uniprot_val"id="uniprot_id_by_genid" type="text" class="input-medium" placeholder="输入uniprot id…" value="" style="width: 300px; height: 34px;"/> 
                  <input type="radio" name="options" value="1">特定长度中有DP、DG、NG、QG的忽略
                  <input type="radio" name="options" value="2">特定长度中G在第三位忽略
                  <input type="radio" name="options" value="3">特定长度中Q在第一位忽略
                  <button type="submit" name="submit"  class="btn btn-info" value="submit">提交</button>
                </div>
                <?php echo form_close(); ?>
                </pre>
            </div>
        </div>
    </body>
</html>