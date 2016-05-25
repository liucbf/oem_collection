<!DOCTYPE html>
<html >
    <head>
        <title>Peptide Sequences Design Tool</title>
        <meta charset="utf-8" />
        <link rel="stylesheet" href="<?php echo base_url('static/css/bootstrap.min.css'); ?>">
        <script src="<?php echo base_url('static/js/jquery.js'); ?>" type="text/javascript" charset="utf-8"></script>
    </head>
    <body>
        <nav class="navbar navbar-inverse navbar-fixed-top">
            <div class="container">
                <div class="navbar-header">
                    <a href="http://58.210.77.50:20034/ptd" class="navbar-brand">Peptide Sequences Design Tool</a>
                </div>
            </div>
        </nav>
        <div class="jumbotron" >
            <div class="container">
                <pre>
                    <?php echo form_open('tools_peptide_sequence/work', array('id' => 'form1', 'name' => 'form1', 'class' => 'bs-docs-example', "target" => "_self")); ?> 
                  Please enter a Uniprot ID : <input name="uniprot_val"id="uniprot_val" type="text" class="input-medium" placeholder=" Please enter a Uniprot ID …" value="<?php echo set_value('uniprot_val') ?>" style="width: 300px; height: 32px;"/> (exp: Q9NP60)<font style=" color:red"><?php echo str_replace(array('<p>', '</p>'), '', form_error('uniprot_val'));
                    if (isset($error)):echo " Notice:" . $error;
                    endif; ?></font>
                  Please enter a length :  <input name="changdu"  class="input-medium" placeholder=" Please enter a number …" style="width: 60px; height: 30px;" value="<?php echo set_value('changdu') ? set_value('changdu') : 15 ?>"/> (exp: 15 )<font style=" color:red"><?php echo str_replace(array('<p>', '</p>'), '', form_error('changdu')); ?></font>
                  <br>
                   Requirements Recommended Rules :
                        <input type="checkbox" name="limit[]" value="1" <?php echo !isset($limit) || (isset($limit) && in_array(1, $limit)) ? 'checked' : ''; ?>>1.Subcellular location->Topology->Extracellular,Cytoplasmic(inside)
                        <input type="checkbox" name="limit[]" value="2" <?php echo !isset($limit) || (isset($limit) && in_array(2, $limit)) ? 'checked' : ''; ?>>2.PTM / Processing->Molecule processing->Chain(inside)
                        <input type="checkbox" name="limit[]" value="3" <?php echo !isset($limit) || (isset($limit) && in_array(3, $limit))? 'checked' : ''; ?>>3.PTM / Processing->Molecule processing->Signal(remove)
                        <input type="checkbox" name="limit[]" value="4" <?php echo !isset($limit) || (isset($limit) && in_array(4, $limit)) ? 'checked' : ''; ?>>4.PTM / Processing->Amino acid modifications(remove)
                   Options selection：
                        <input type="checkbox" name="option[]" value="0" <?php echo !isset($option)|| (isset($option) && in_array(0, @$option)) ? 'checked' : ''; ?>>1.Remove sequence results which have three specific length of consecutive identical letters
                        <input type="checkbox" name="option[]" value="1" <?php echo !isset($option)|| (isset($option) && in_array(1, @$option))  ? 'checked' : ''; ?>>2.Remove sequence results contain ‘DP’, ‘DG’, ‘NG’, ‘QG’
                        <input type="checkbox" name="option[]" value="2" <?php echo !isset($option)|| (isset($option) && in_array(2, @$option))  ? 'checked' : ''; ?>>3.Remove sequence results have ‘G’ in the 3rd location
                        <input type="checkbox" name="option[]" value="3" <?php echo !isset($option)|| (isset($option) && in_array(3, @$option))  ? 'checked' : ''; ?>>4.Remove sequence results have ‘Q’ in the 1st location
                        <input type="checkbox" name="option[]" value="4" <?php echo !isset($option)|| (isset($option) && in_array(4, @$option))  ? 'checked' : ''; ?>>5.Remove sequence results have ‘H’ in the last location

                   Blast Sequence : <input type="checkbox" name="blast" value="1" <?php echo  !isset($blast)||(isset($blast) && $blast) ? 'checked' : ''; ?>> (This will cost some time from other sequences.)

                    <button type="submit" name="submit" class="btn btn-info" value="submit" id='submit' onclick='show_lock(1)'> Submit </button>  <button type="reset" name="reset" class="btn btn-info" value="Reset" id='reset' onclick='show_lock(0)'> Reset </button><span id='submit_wait' style="display: none;color:blue">  ( Please wait for the results.....) </span>
                <?php echo form_close(); ?>
                </pre>
                    <?php if ($cost_set): ?>
                    <pre><?php
                        foreach ($cost_set as $c_k => $c_v):
                            echo $cost_set[$c_k] . $cost_val[$c_k] . " ";
                        endforeach;
                        ?></pre>
                    <?php endif; ?>
                    <?php if (isset($seques) && count($seques)) : ?>
                    <pre id="show_div"> 
                    <h4>ID "<a href="http://www.uniprot.org/uniprot/<?php echo $uniprot_val; ?>" target="_blank"><?php echo $uniprot_val; ?></a>"designed <?php echo count($seques); ?>  peptide sequences:</h4> 
                    <table class="table table-striped">
                          <thead>
                            <tr>
                              <th>ID</th>
                              <th>Top 5</th>
                            </tr>
                          </thead>
                          <tbody>
                                <?php
                                foreach ($seques as $k => $v):
                                    ?>       
                                <tr>
                                  <th scope="row"><?php echo $k + 1; ?></th>
                                  <td><?php echo $v; ?></td>
                                </tr>
                                    <?php
                            endforeach;
                            ?>
                          </tbody>
                        </table>
                        <?php
                        foreach ($blast_arr as $k_b => $v_b):
                        ?>
                         <table class="table" id="blast_<?php echo $k_b;?>">
                              <thead>
                                <tr>
                                    <th class="toggle-faq"><a title="单击显示/隐藏" href="#blast_<?php echo $k_b;?>">Blast result : <?php echo $k_b; ?> Lists</a></th>
                                </tr>
                              </thead>
                              <tbody class="con hidden">
                                    <?php
                                    foreach ($v_b as $kk => $vv):
                                    ?>
                                    <tr>
                                      <td> <?php echo ($kk + 1) . ". " . $vv;  
                                                  if(isset($blast_uniprot_arr[$item])):
                                                  $item = preg_replace("/\[.*\]$/", "", $vv);
                                                  echo "   [blast uniprot ids : ".$blast_uniprot_arr[$item]." ]";
                                                  endif;?>
                                      </td>
                                    </tr>
                                    <?php
                                    endforeach;
                                    ?>
                              </tbody>
                            </table>
                            <?php
                        endforeach;
                        ?>
                    </pre>
                    <?php elseif (isset($seques) && count($seques) == 0): ?>
                    <pre> 
                    <h4>ID  "<a href="http://www.uniprot.org/uniprot/<?php echo $uniprot_val; ?>" target="_blank"><?php echo $uniprot_val; ?></a>"designed 0 peptide sequences:</h4> 
                    <span class="k" style="color:red" >
                        no data! maybe network timeout, please click the reset button.
                    </span>
                    </pre>
            <?php endif; ?>
            </div>
        </div>
        <script type="text/javascript">
            $(function() {
                $(".toggle-faq").click(function() {
                    var next_obj = $(this).parents(".table").find(".con");
                    if (next_obj.hasClass('hidden'))
                    {
                        next_obj.removeClass('hidden');
                    }
                    else
                    {
                        next_obj.addClass('hidden');
                    }


                });
            });
            function show_lock(obj)
            {
                if (obj == 1)
                {
                    document.getElementById("submit").disabled = true;
                    document.getElementById('submit_wait').style.display = '';
                    if (document.getElementById('show_div'))
                    {
                        document.getElementById('show_div').style.display = 'none';
                    }
                }
                else {
                    window.location.href = "<?php echo site_url("tools_peptide_sequence/index") ?>"
                }
            }


//        <!--关闭浏览器 -->  
//        var flag = true;  
//        window.onbeforeunload = function () {  
//            if (flag) {  
//               var evt = window.event || arguments[0];  
//               var userAgent = navigator.userAgent;  
//               if (userAgent.indexOf("MSIE") > 0) {  
//                   var n = window.event.screenX - window.screenLeft;  
//                   var b = n > document.documentElement.scrollWidth - 20;  
//                   if (b && window.event.clientY < 0 || window.event.altKey) {  
//                       window.event.returnValue = ("该操作将会导致非正常停止脚本，您是否确认?");  
//                   }else {  
//                       return ("该操作将会导致非正常停止脚本，您是否确认?");  
//                   }  
//               }else if (userAgent.indexOf("Firefox") > 0) {  
//                       return ("该操作将会导致非正常停止脚本，您是否确认?");  
//               }  
//            }  
//        }  
        </script>
    </body>
</html>