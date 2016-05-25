<?php

/**
 * Tools_peptide_sequence Controller
 *
 * @package Abgent
 * @author  l h
 */
Class Tools_peptide_sequence extends CI_Controller {

    private $_baseurl = "http://www.uniprot.org/uniprot/";
    private $_length ;
    private $_sequence;
    private $_blast = 0;
    private $_cur_position = 0;
    private $_limit_residue = array();
    private $_limit_residue_signal = array();
    private $_limit_chain = array();
    private $_subcellular_location = array();
    private $_option_set = array();
    private $_limit_set = array();
    private $_limit_residue_disu = array();
    private $_seq_blast_uniprot_arr = array();
    private $_char_quanzhong = array(
        'F'=>8.151017,
        'M'=>10.90495,
        'I'=>15.52741,
        'L'=>27.56202,
        'C'=>29.25434,
        'V'=>29.35204,
        'Y'=>33.45546,
        'H'=>40.16888,
        'P'=>50,
        'A'=>52.95021,
        'T'=>61.24429,
        'Q'=>61.26522,
        'N'=>68.01354,
        'R'=>69.0359,
        'G'=>78.26512,
        'S'=>85.7427,
        'E'=>86.41613,
        'D'=>93.46802,
        'K'=>100,
        'W'=>0
    );
    protected $_cost_time = array(
        'collection_cost' =>'Collection cost : ',
        'rule_cost' =>' --Rule cost : ',
        'option_cost' =>'--Option cost : ',
        'blast_cost' => '--Human blast cost : '
    );
    public function __construct()
    {
        error_reporting(0);
        set_time_limit(0);
        parent::__construct();
        $this->load->model('uniprot_model','uniprot',TRUE);
        $this->load->library('session');
    }
    public function index()
    {
        $this->session->unset_userdata('uniprot_val');
        $this->session->unset_userdata('arr');
        $this->load->view('main_peptide');
    }
    public function work()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('uniprot_val', 'uniprot id', 'trim|required|xss_clean');
        $this->form_validation->set_rules('changdu', 'sequence length', 'trim|integer|required|xss_clean');
        $this->form_validation->set_rules('option[]', '', 'trim|xss_clean');
        if ($this->form_validation->run() === FALSE)
        {
            $data['error'] = 'Fill in the input';
            $this->load->view('main_peptide', $data);
            return;
        }
        else
        {
            $uniprot_session = $this->session->userdata('uniprot_val');
            $arr_session = $this->session->userdata('arr');
            $post = $this->input->post(NULL, TRUE);

            if ($post['uniprot_val'])
            {
                $this->_option_set = isset($post['option']) ? $post['option'] : array();
                $this->_limit_set = isset($post['limit']) ? $post['limit'] : array();
                $this->_length = isset($post['changdu']) ? intval($post['changdu']) : 15;
                $this->_blast = isset($post['blast']) ? $post['blast'] : 0;
                $uniprot = trim($post['uniprot_val']);
                unset($post);
            }
             $this->benchmark->mark("collection_start");
            if ($uniprot_session == $uniprot )
            {
                $arr = count($arr_session)? $arr_session:array();
            }
            else
            {
                //get limit range value
                $this->session->set_userdata('uniprot_val', $uniprot);
                $arr = $this->_collection_seq_and_ptm($uniprot);
                $this->session->set_userdata('arr', $arr);
            }
            $this->benchmark->mark("collection_end");
            //begin cal .......
            $sequence = trim($arr['sequence']);
            $str_length = strlen($sequence);
            if ($str_length == 0)
            {   
                $data['error'] = 'Uniprot not exists or it has no sequence!';
                $this->load->view('main_peptide', $data);
                return;
            }
             $this->_sequence = $sequence;
            //0字符转为数组
            $seq_arr = $this->_string_to_arr($sequence);
            //1根据选项设置范围
            $seq_arr_assioc = array(implode("",$seq_arr));
            $tmp_arr = $this->_set_format_range($arr);
            //2根据设置的范围去除特定范围的字符
            $this->benchmark->mark("rule_start");
            if( count($this->_limit_set) && count($tmp_arr) )
            {   
                $seq_arr_assioc = array();
                $seq_arr_assioc = $this->_set_chars_by_remove_arr($seq_arr, $tmp_arr);//满足限制条件的多条字符串
            }
            $this->benchmark->mark("rule_end");
            //3根据选项执行
           //4特定长度中如果有C，C在首位或末位的以外的都不要
           $seq_tmp_arr = array();
           foreach ($seq_arr_assioc as $seq_str)
           {
               $tmp_arr = array();
               $tmp_arr = $this->_base_work($seq_str);
               $seq_tmp_arr = array_merge($seq_tmp_arr,$tmp_arr);
           }
            //5执行选项操作
            $this->benchmark->mark("option_start");
            if (count($this->_option_set))
            {
                $seq_tmp_arr = $this->_option_set_job($seq_tmp_arr);
            }
             $this->benchmark->mark("option_end");
            //6blast同源比对
            if($this->_blast)
            {
                 $this->benchmark->mark("blast_start");
                 $seq_blast_arr = $this->_blast_tongyuan($seq_tmp_arr);
                 $seq_top_five = $seq_blast_arr['blast_top_5'];
                 $data['blast_arr'] = $seq_blast_arr['blast_arr'];
                 $this->benchmark->mark("blast_end");
            }
             else{
                 //7计算权限值，取前5个值最大。
                    $seq_top_five = $this->_get_weight_max5($seq_tmp_arr);
             }
            //8如果特定长度中没有C，加C在最左边（例如15个长度，会变成16个）
            $seq_tmp_arrs = $this->_add_c($seq_top_five);
            //test on
            //$this->output->enable_profiler(TRUE);
            //end calu....
            $data['seques'] = $seq_tmp_arrs;
            $data['uniprot_val'] = $uniprot;
            $data['option'] = $this->_option_set;
            $data['limit'] = $this->_limit_set;
            $data['blast'] = $this->_blast;
            
            $data['blast_uniprot_arr'] = $this->_seq_blast_uniprot_arr;
            //get mark time 
            $collection_cost = $this->benchmark->elapsed_time("collection_start","collection_end");
            $rule_cost = $this->benchmark->elapsed_time("rule_start","rule_end");      
            $option_cost = $this->benchmark->elapsed_time("option_start","option_end");
            $blast_cost = $this->benchmark->elapsed_time("blast_start","blast_end");
            $data['cost_val']['collection_cost'] = $collection_cost > 0.0001?$collection_cost." s":"< 0.0001 s";
            $data['cost_val']['rule_cost'] = $rule_cost > 0.0001?$rule_cost." s":"< 0.0001 s";
            $data['cost_val']['option_cost'] = $option_cost > 0.0001?$option_cost." s":"< 0.0001 s";
            $data['cost_val']['blast_cost'] = $blast_cost > 0.0001?$blast_cost." s":"< 0.0001 s";
            $data['cost_set'] = $this->_cost_time;
            $this->load->view('main_peptide', $data);
        }
    }
    
    //获取权重平均值前5
   private function _get_weight_max5( $tmp_arr )
   {
       $weight_arr = $weight_arrs = array();
       $char_weight =  $this->_char_quanzhong;
       foreach ($tmp_arr as $k=>$v )
       {
           for( $i=0;$i<$this->_length;$i++ )
           {
                 $char_tmp = substr($v, $i,1);
                 if( array_key_exists( $char_tmp, $char_weight )!==FALSE)
                 {
                    $weight_arrs[$k] += $char_weight[$char_tmp];
                 }
           } 
           $weight_arr[$k.$v] = $weight_arrs[$k]/$this->_length;
           
       }
       arsort($weight_arr);
       $i=0;
       if(count($weight_arr)>5)
       {
              return array_slice( array_keys($weight_arr),0,5);   
       }
       else
       {
            return  array_keys($weight_arr);
       }
       return $result_arr;
    }
    //同源比对:1全部匹配2部分匹配
    private function _blast_tongyuan( $seq_tmp_arr )
    {
        if(!$seq_tmp_arr)
        {
            return FALSE;
        }
        $blast_arr = array();
        foreach ($seq_tmp_arr as $key => $v )
        {
            $result = $this->uniprot->query_rows( $v ,$this->session->userdata('uniprot_val'));
            $this_result = count($result)>0 ?count($result) : 0;
            if($this_result)
            {
                $v_uniprot_str = '';
                foreach ($result as $v_uniprot)
                {
                    $v_uniprot_str .= $v_uniprot['accession'].';';
                }
                $this->_seq_blast_uniprot_arr[$v] = $v_uniprot_str;
            }
            //加入位置
            $tmp_char = '';
            $tmp_char = preg_replace("/\d/","", $v);
            $num_start = stripos($this->_sequence, $tmp_char)+1;
            $num_end = $num_start + $this->_length-1;
            $char_range = "[".$num_start."--".$num_end."]";
            $blast_arr[$this_result][] = $v.$char_range;
            ksort($blast_arr);
        }
        //同源匹配可能的结果数目：0,1,2,多个
        $blast_top_5 = $this->blast_combine($blast_arr);
        return array('blast_arr'=>$blast_arr,'blast_top_5'=>$blast_top_5);
    }
    //同源匹配筛选前五个
    function blast_combine($blast_arr)
    {
           $blast_top = array();
           for( $i =0; $i<10;$i++ )
           {
               if( !isset($blast_arr[$i]) )
               {
                   continue;
               }
             $blast_top += $this->_get_weight_max5($blast_arr[$i]);
             if(count($blast_top)>4)    
             {
                   return $blast_top;
             }
           }
            return $blast_top;
    }
    private function _string_to_arr($sequence)
    {
        $seq_arr = array();
        $str_length = strlen($sequence);
        //字符串转为数组
        $j = 1;
        for ($i = 0; $i < $str_length; $i++)
        {
            $seq_arr[$j] = substr($sequence, $i, 1);
            $j++;
        }
        if (!count($seq_arr))
        {
            exit('Error:sequence format to array');
        }
        return $seq_arr;
    }

    //5如果特定长度中没有C，加C在最左边（例如15个长度，会变成16个）
    private function _add_c($seq_tmp_arr)
    {
        $seq_arr = array();
        foreach ($seq_tmp_arr as $key => $v)
        {
            $tmp_char = '';
            $num_start = $num_end = 0 ;
            
            $tmp_char = preg_replace( "/\d/","", $v);
            $tmp_char = str_replace( array("[","]","--"), "", $tmp_char);
            $num_start = stripos($this->_sequence, $tmp_char)+1;
            $num_end = $num_start + $this->_length-1;
            $char_range = "[".$num_start."--".$num_end."]";
            if (substr_count($v, 'C') == 0)
            {
                $seq_arr[] = "C" . $tmp_char.$char_range;
            }
            else
            {
                $seq_arr[] = $tmp_char.$char_range;
            }
        }
        return $seq_arr;
    }

    //6根据option的选项设置 
    private function _option_set_job($seq_tmp_arr)
    {
        foreach ($seq_tmp_arr as $key => $v)
        {
            if (in_array('0', $this->_option_set))
            {
                //0 .连续3个相同字母的忽略
                if ($this->_remove_3samechars($v))
                {
                    unset($seq_tmp_arr[$key]);
                }
            }
            if (in_array('1', $this->_option_set))
            {
                //1 .特定长度中有DP、DG、NG、QG的忽略
                if ($this->_remove_dpdgngqg($v))
                {
                    unset($seq_tmp_arr[$key]);
                }
            }
            if (in_array('2', $this->_option_set))
            {
                //2.特定长度中G在第三位忽略（C也要算）
                if ($this->_remove_g($v))
                {
                    unset($seq_tmp_arr[$key]);
                }
            }
            if (in_array('3', $this->_option_set))
            {
                // 3.特定长度中Q在第一位忽略（C也要算）
                if ($this->_remove_q($v))
                {
                    unset($seq_tmp_arr[$key]);
                }
            }
            if (in_array('4', $this->_option_set))
            {
                // 3.特定长度中Q在第一位忽略（C也要算）
                if ($this->_remove_h($v))
                {
                    unset($seq_tmp_arr[$key]);
                }
            }
            if (isset($seq_tmp_arr[$key]))
            {
                $seq_arr[] = $v;
            }
        }
        return $seq_arr;
    }

    //4根据特定长度获取字符串
    private function _base_work($seqstr)
    {
        $cur_seq = '';
        $seqstr_arr = array();
        $seqstr_length = strlen($seqstr);
        //其他条件操作
        $this->_cur_position = 0;
        for ($i = $this->_cur_position; $i < ( $seqstr_length - $this->_length ); $i++)
        {
            $cur_seq = substr($seqstr, $i, $this->_length);
            //特定长度中如果有C，C在首位或末位的以外的都不要
            $cur_position_step4 = $this->_remove_c($cur_seq, $i);
            if ($cur_position_step4 )
            {
                $this->_cur_position = $cur_position_step4;
                continue;
             }
            $seqstr_arr[] = $cur_seq;
        }
        return $seqstr_arr;
    }

    //特定长度中有连续3个相同字母的忽略| 从当前向下继续截取
    private function _remove_3samechars($cur_seq )
    {
        $three_chars_arr = array(
            'AAA', 'BBB', 'CCC', 'DDD', 'EEE', 'FFF', 'GGG',
            'HHH', 'III', 'JJJ', 'KKK', 'LLL', 'MMM', 'NNN',
            'OOO', 'PPP', 'QQQ', 'RRR', 'SSS', 'TTT', 'UUU',
            'VVV', 'WWW', 'XXX', 'YYY', 'ZZZ'
        );
       
        foreach ($three_chars_arr as $item)
        {
            if (strripos($cur_seq, $item) !== FALSE)
            {
                return TRUE;
            }
        }
       return FALSE;
    }

    //特定长度中有DP、DG、NG、QG的忽略
    public function _remove_dpdgngqg($cur_seq)
    {
        if (strripos($cur_seq, 'DP') !== FALSE || strripos($cur_seq, 'DG') !== FALSE || strripos($cur_seq, 'NG') !== FALSE || strripos($cur_seq, 'QG') !== FALSE)
        {
            return TRUE;
        }
        return FALSE;
    }

    //特定长度中G在第三位忽略
    private function _remove_g($cur_seq)
    {
        if (stripos($cur_seq, 'G') === 2)
        {
            return TRUE;
        }
        return FALSE;
    }

    //特定长度中Q在第一位的忽略
    private function _remove_q($cur_seq)
    {
        if (stripos($cur_seq, 'Q') === 0)
        {
            return TRUE;
        }
        return FALSE;
    }
  //特定长度中h在最后一位的忽略
    private function _remove_h($cur_seq)
    {
        if (stripos($cur_seq, 'h') == (strlen($cur_seq)-1))
        {
            return TRUE;
        }
        return FALSE;
    }
    //特定长度中G在第三位或Q在第一位的忽略
    public function _remove_c($cur_seq, $cur_position)
    {
        if (strpos($cur_seq, 'C') !== FALSE)
        {
            $c_postion_begin = strpos($cur_seq, 'C');
            $c_postion_end = strripos($cur_seq, 'C');
            if ($c_postion_begin < $c_postion_end)
            {
                return $c_postion_end;
            }
            else if ($c_postion_begin == $c_postion_end)
            {
                if (($c_postion_begin == 0) ||( $c_postion_begin == ($this->_length - 1 ) ))
                {
                    return false ;
                }
            }
          return $c_postion_end;
        }
        return FALSE;
    }
 //根据范围获取符合的字符
    private function _set_chars_by_remove_arr($seq_arr, $limit_arr)
    {
        $seq_tmp_arr = $seq_char = $remove_location = array();
        if ($limit_arr['limit_arr'])
        {
            $seq_tmp_arr = array_intersect_key($seq_arr, $limit_arr['limit_arr']);
        }
      
       //amino去除位置
        if (count($limit_arr['remove_arr']))
        {
                foreach ($limit_arr['remove_arr'] as $k => $v)
                {
                    $remove_location[] = $v;
                }
        } 
        //去除该位置前7位 后7位
         if (count($limit_arr['remove_disu_arr']))
        {
                foreach ($limit_arr['remove_disu_arr'] as $k1 => $v1)
                {
                    $remove_location += array($v1-7,$v1+7,$v1+7,$v1-7);
                }
        } 
        //signal去除一段
        if (count($limit_arr['remove_signal_arr']))
        {
                foreach ($limit_arr['remove_signal_arr'] as $k_signal => $v_signal)
                {
                   $remove_location[]=$v_signal;
                }
        } 
        
          $remove_location = array_unique($remove_location);
          sort($remove_location);//去除重复->升序
         $arr = $this->get_limit_chars( $seq_tmp_arr ,$remove_location );
         return $arr;
    }
    
    private function get_limit_chars( $seq_tmp_arr ,$remove_location )
    {
         $i= 0;
         foreach ($seq_tmp_arr as $s_tmp_location =>$s_tmp_char )
         {
              $chars = "";
             if( !in_array( $s_tmp_location,$remove_location) &&($seq_tmp_arr[$s_tmp_location+1]))
             {
                 $seq_char[$i] .= $s_tmp_char;
             }
             else
             {   
                   if(strlen($seq_char[$i])<$this->_length)
                    {
                            unset($seq_char[$i]);
                     }
                 $i++;
                 continue;
             }
         }
           return $seq_char;
    }

    //初始化范围值
    private function _set_format_range($arr)
    {
        $remove_arr = $limit_arr = $subcellular_arr = $remove_signal_arr = $remove_disu_arr = array();
        $this->_subcellular_location = $arr['subcellular_location'] ? $arr['subcellular_location'] : array();
        $this->_limit_chain = count($arr['chain']) ? $arr['chain'] : array();
        $this->_limit_residue = count($arr['residue']) ? $arr['residue'] : array();
        $this->_limit_residue_signal = count($arr['residue_signal']) ? $arr['residue_signal'] : array();
        $this->_limit_residue_disu = count($arr['residue_disu']) ? $arr['residue_disu'] : array();
        if(count($this->_limit_set) == 0 )
        {
            return array();
        }
        if (count($this->_subcellular_location) && (in_array('1',$this->_limit_set)))
        {
            foreach ($this->_subcellular_location as $sub_arr)
            {
                if (count($sub_arr))
                {
                    for ($i = $sub_arr[0]; $i <= $sub_arr[1]; $i++)
                    {
                        $subcellular_arr[$i] = 1;
                    }
                }
            }
        }

        if (count($this->_limit_chain) && in_array('2',$this->_limit_set))
        {
            foreach ($this->_limit_chain as $chan_arr1)
            {
                if (count($chan_arr1))
                {
                    for ($j = $chan_arr1[0]; $j <= $chan_arr1[1]; $j++)
                    {
                        $chain_arr[$j] = 1;
                    }
                }
            }
        }
        
       if (count($this->_limit_residue_signal)&& ( in_array('3',$this->_limit_set)) ) 
        {
            foreach ($this->_limit_residue_signal as $residue_signal_arr1)
            {
                if (count($residue_signal_arr1))
                {
                    foreach ($residue_signal_arr1 as $r_v1 )
                    {
                         $remove_signal_arr[] = $r_v1;
                    }
                }
            }
        }
        
        if (count($this->_limit_residue)&& ( in_array('4',$this->_limit_set)) ) 
        {
            foreach ($this->_limit_residue as $residue_arr1)
            {
                if (count($residue_arr1))
                {
                    foreach ($residue_arr1 as $r_v )
                    {
                         $remove_arr[] = $r_v;
                    }
                }
            }
            $remove_arr = array_unique($remove_arr);
        }
         if (count($this->_limit_residue_disu)&& ( in_array('4',$this->_limit_set)) ) 
        {
            foreach ($this->_limit_residue_disu as $residue_disu_arr1)
            {
                if (count($residue_disu_arr1))
                {
                    foreach ($residue_disu_arr1 as $r_v2 )
                    {
                         $remove_disu_arr[] = $r_v2;
                    }
                }
            }
        }
        
        if (count($subcellular_arr))
        {
            if (count($chain_arr))
            {
                $limit_arr = array_intersect_key($subcellular_arr, $chain_arr);
            }
            else
            {
                $limit_arr = $subcellular_arr;
            }
        }
        else if (count($chain_arr))
        {
            $limit_arr = $chain_arr;
        }
        return array('limit_arr' => $limit_arr, 'remove_arr' => $remove_arr,'remove_signal_arr' =>$remove_signal_arr,'remove_disu_arr'=>$remove_disu_arr);
    }

    //采集seq 和ptm
    private function _collection_seq_and_ptm($uniprot)
    {
        $chain =  $subcellular_location = array();
        $residue = $residue_signal = array();
         

        //从数据库取值
        $row = $this->uniprot->query_one_row( $uniprot);
        if (count($row))
        {
           $sequence = $row['sequence'];
        }
        else
        {
            $sequence = FALSE;
//            $url = $this->_baseurl . $uniprot;
//              //read content
//            $content = file_get_contents($url);
//            preg_match('#<pre class="sequence"\>(.*)<\/pre>#iUs', $content, $seq_arr);
//            if ($seq_arr)
//            {
//                $sequence = preg_replace(array('/[0-9]+/', '/\s/'), array('', ''), strip_tags($seq_arr[1]));
//            }
           return array('sequence' => $sequence);
        }
        $url = $this->_baseurl . $uniprot;
        //read content
        $content = file_get_contents($url);
        // $content = file_get_contents("C:\Users\li_hao\Desktop/work.htm"); test html
        // 1.Subcellular location->Topology->Extracellular，Cytoplasmic 每个字母都在这里面 -- initing
        preg_match('#<table class="featureTable" id="topology_section">(.*)</table>#iUs', $content, $step1_arr);
        preg_match_all('#<td class="numeric"><a class="position tooltipped" title="BLAST subsequence" href="\/blast\/\?about=(.*)\[(.*)]&amp\;key=(.*)">(.*)</a></td>#iUs', @$step1_arr[1], $step1_arr_all);
        if (isset($step1_arr_all) && count($step1_arr_all))
        {
            foreach ($step1_arr_all[3] as $k_step1 => $v1)
            {
                if (trim($v1) == 'Topological domain')
                {
                    $subcellular_location_arr[] = $step1_arr_all[2][$k_step1];
                }
            }
            if (isset($subcellular_location_arr))
            {
                foreach ($subcellular_location_arr as $v_tmp)
                {
                    $tmp_location = explode('-', $v_tmp);
                    $subcellular_location[] = array(intval($tmp_location[0]), intval($tmp_location[1]));
                }
            }
        }

        // 2.PTM / Processing->Molecule processing中如果有Chain，只在这个范围内查找 --over
        preg_match('#<table class="featureTable" id="peptides_section">(.*)</table>#iUs', $content, $step2_arr);
        preg_match_all('#<a class="position tooltipped" title="BLAST subsequence" href="\/blast\/\?about=(.*)\[(.*)\]\&amp;key=(.*)">(.*)</a>#iUs', $step2_arr[1], $step2_arr_all);
        if (count($step2_arr_all))
        {
            foreach ($step2_arr_all[2] as $k2 => $v2)
            {
                //chain限制范围
                if (strripos($step2_arr_all[3][$k2], 'Chain') !== FALSE)
                {
                    if (strripos($v2, "-") !== FALSE)
                    {
                        $tmp_arr2 = explode('-', $v2);
                        $chain[] = array(intval($tmp_arr2[0]), intval($tmp_arr2[1]));
                    }
                    else
                    {
                        $chain[] = array(intval($v2[0]), intval($v2[0]));
                    }
                }
                //Signal peptidei去掉范围
                if (strripos($step2_arr_all[3][$k2], 'Signal') !== FALSE)
                {
                    if (strripos($v2, "-") !== FALSE)
                    {
                        $tmp_arr2 = explode('-', $v2);
                        $residue_signal[] = array(intval($tmp_arr2[0]), intval($tmp_arr2[1]));
                    }
                    else
                    {
                        $residue_signal[] = array(intval($v2[0]), intval($v2[0]));
                    }
                }
            }
        }
        // 4.PTM / Processing->Amino acid modifications中 所有值去掉(多行 &多种名称)---over
        preg_match('#<table class="featureTable" id="aaMod_section">(.*)</table>#iUs', $content, $step4_arr);
        preg_match_all('#<td class="numeric"><a class="position tooltipped" title="BLAST subsequence" href="\/blast\/\?about=(.*)\[(.*)\]\&amp\;key=(.*)">(.*)</a></td>#iUs', $step4_arr[1], $step4_arr_all);

        if (isset($step4_arr_all) && count($step4_arr_all))
        {
            foreach ($step4_arr_all[2] as $s4_key=>$v4)
            {
                //Glycosylationi去掉该位置前10个后10个字母
                if (strripos($step4_arr_all[3][$s4_key], "Glycosylation") !== FALSE)
                {
                    $tmp4_arr = explode('-', $v4);
                   
                    $residue_disu[] = array(intval($tmp4_arr[0]), intval($tmp4_arr[0]));
                }
                //  Modified residuei去掉特定位置 
               else if (strripos($v4, "-") !== FALSE)
                {
                    $tmp4_arr = explode('-', $v4);
                    $residue[] = array(intval($tmp4_arr[0]), intval($tmp4_arr[1]));
                }
                //Disulfide bondi 去掉2端
               else if (strripos($v4, ",") !== FALSE)
                {
                    $tmp44_arr = explode(',', $v4);
                    $residue[] = array(intval($tmp44_arr[0]), intval($tmp44_arr[1]));
                }
                else
                {
                    $residue[] = array(intval($v4), intval($v4));
                }
            }
        }
        
        return array('subcellular_location' => $subcellular_location, 'chain' => $chain, 'residue' => $residue,'residue_disu' => $residue_disu, 'residue_signal'=>$residue_signal,'sequence' => $sequence);
    }

}
