<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

Class Tools_peptide_seq extends CI_Controller 
{
    private $_baseurl = "http://www.uniprot.org/uniprot/";
    private $_tbl_uniprot = 'uniprot';
    private $_tbl_word = 'peptide_word';
    
    private $_length = 15;
    private $_cur_position = 0;
    private $_limit_residue_begin = array();
    private $_limit_residue_end = array();
    private $_limit_chain_begin ;
    private $_limit_chain_end ;
    private $_subcellular_location = array();
    private $_option_set ;
    private $_word_arr = array(
                'W'=>'7.16',
                'F'=>'13',
                'Y'=>'31.13',
                'I'=>'18.29',
                'M'=>'14.98',
                'L'=>'26.91',
                'V'=>'28.19',
                'N'=>'55.89',
                'C'=>'28.12',
                'T'=>'51.04',
                'A'=>'45.1',
                'G'=>'63.24',
                'R'=>'56.62',
                'D'=>'74.13',
                'H'=>'35.94',
                'Q'=>'51.06',
                'S'=>'68.59',
                'K'=>'78.81',
                'E'=>'69.33',
                'P'=>'73.65'
    );
    public function __construct()
    {
        error_reporting(0);
        set_time_limit(0);
        parent::__construct();
    }
    private function _link_uniprot()
    {
        return $this->load->database( 'uniprot' , TRUE );
    }
    
    public function index()
    {
        $this->load->view( 'main_1' );
    }
    public function work()
    {
        $post = $this->input->post( NULL ,TRUE );
        
        if( $post['uniprot_val'] )
        {   
            $this->_option_set = $post['options']? $post['options']:'';
            $uniprot = trim($post['uniprot_val']);
            unset($post);
        }
        $arr = $this->_collection_seq_and_ptm( $uniprot ); 
        $str_length =  strlen( trim($arr['sequence']));
        if( $str_length == 0 )
        {
            exit( 'Uniprot db cannot find the sequence!');
        }
        
        $this->_subcellular_location = $arr['subcellular_location']?$arr['subcellular_location']: array();
        $this->_limit_chain_begin = $arr['chain_begin'] >0 ? ($arr['chain_begin']-1): 0 ;
        $this->_limit_chain_end = $arr['chain_end'] >0 ? $arr['chain_end']: $str_length ;
        $this->_limit_residue_begin = count($arr['residue_begin']) >0 ? $arr['residue_begin']: array() ;
        $this->_limit_residue_end = count($arr['residue_end']) >0 ? $arr['residue_end']: array() ;
        
        $sequence_tp = trim($arr['sequence']);
         //取符合条件的字符串的截取位置
        $limit_length_arr = $this->_remove_amino_acid();
        
        if( count($limit_length_arr)<1 )
        {
           $sequence_tmp = $this->chain_work($sequence_tp, $this->_limit_chain_begin, $this->_limit_chain_end );
           $sequence = $this->only_remove_step4( $sequence_tmp );
        }
        else
        {
           $sequence = $this->_get_sequences_by_subcellular( $sequence,$limit_length_arr );
        }
        
        $calu_arr_tmp = $this->calu( $sequence );
        $chars_arr = $this->_format_first_add_c($calu_arr_tmp);
     
        $chars_arr_result = $this->_option_set_q_g($chars_arr);
        var_dump($chars_arr_result);
    }
    private function _option_set_q_g($chars_arr)
    {
        
        if( $this->_option_set == '2')
        {
            foreach ($chars_arr as $k=>$cur_seq )
            {
                    $num_g = substr_count( $cur_seq, 'G' );
                    if( $num_g >0 && substr( $cur_seq, 2,1 ) == 'G')
                    {
                        unset( $chars_arr[$k]);
                    }
                    
            }
        }
          if( $this->_option_set == '3')
        {
            foreach ($chars_arr as $k=>$cur_seq )
            {
                    $num_q = substr_count( $cur_seq, 'Q' );
                    if($num_q>0 && substr( $cur_seq, 0,1 ) == 'Q')
                    {
                          unset( $chars_arr[$k]);
                    }
                    
            }
        }
        
        return $chars_arr;
    }
    //满足条件但是头部没有c的字段加入c
    private function _format_first_add_c( $calu_arr )
    {
        $format_arr = array();
        foreach ( $calu_arr as $key => $word )
        {
            if( substr($word, 0,1) != 'C')
            {
                $format_arr[$key] = 'C'.$word;
            }
        }
        return $format_arr;
    }

    //如果有subcellular数字，则执行此函数
    private function _get_sequences_by_subcellular( $sequence,$limit_length_arr )
    {
        $tmp_seq = '';
        foreach($limit_length_arr as $item )
        {
            if(count($item))
            {
                $tmp_seq .= substr($sequence, ($item[0]-1),($item[1]-$item[0]+1));
            }
        }
        return $tmp_seq;
    }

    //4 PTM / Processing->Amino acid modifications中 所有值去掉
    private function _remove_amino_acid()
    {
         $limt_arr = array();
         if( count($this->_limit_residue_begin ) && count($this->_limit_residue_end ) && count( $this->_subcellular_location))
         {
            foreach ( $this->_limit_residue_begin as $v )
            {
                 foreach( $this->_subcellular_location as $v1 )
                 {
                     if( $v >= $v1[0] && $v <= $v1[1] )
                     {
                         $limt_arr[] = array( $v1[0],$v-1 );
                         $limt_arr[] = array( $v-1,$v1[1] );
                     }
                     else
                     {
                         $limt_arr[] = $v1;
                     }
                 }
            }
        }
        if( count($this->_limit_chain_begin ) && count($this->_limit_chain_end ) && count( $this->_subcellular_location))
        {
                  foreach( $this->_subcellular_location as $v2 )
                 {
                     if( $this->_limit_chain_begin > $v2[0] && $this->_limit_chain_begin < $v2[1] )
                     {
                         $limt_arr[] = array( $v1[0],$this->_limit_chain_begin );
                         $limt_arr[] = array( $this->_limit_chain_begin,$v1[1] );
                     }
                     if( $this->_limit_chain_end > $v2[0] && $this->_limit_chain_end < $v2[1] )
                     {
                         $limt_arr[] = array( $v2[0],$this->_limit_chain_end );
                         $limt_arr[] = array( $this->_limit_chain_end,$v2[1] );
                     }
                 }
        }
        return $limt_arr;
    }
    
    //2 PTM / Processing-》Molecule processing中如果有Chain，只在这个范围内查找
    public function chain_work( $sequence ,$limit_chain_begin ,$limit_chain_end )
    {
        $chain_sequence = substr( $sequence, $limit_chain_begin, $limit_chain_end );
        return $chain_sequence;
    }
    public function only_remove_step4( $seqstr )
    {
        //先去除residue数据的长度
//        foreach ( $this->_limit_residue_begin as $v )
//        {
//            $remove_str[] = substr( $seqstr,  ($v - $this->_length-1),$this->_length);
//        }
//          //去除residue end
//         foreach ( $this->_limit_residue_end as $v2 )
//        {
//            $remove_str[] = substr( $seqstr,  ($v2 - $this->_length-1),$this->_length);
//        }
          //去除residue begin
        foreach ($remove_str as $v)
        {
           $seqstr = str_replace( $v, "",$seqstr );
        }
        return $seqstr;
    }

        //按照特定的长度计算
    public function calu( $seqstr )
    {
        $cur_seq = '';
        $seqstr_arr = array();
        
        $seqstr_length = strlen( $seqstr );
        //其他条件操作
        for( $i = $this->_cur_position ; $i< ( $seqstr_length - $this->_length ); $i++ )
        {
           $cur_seq = substr( $seqstr,$i,$this->_length );
           //特定长度中有连续3个相同字母的忽略| 从当前向下继续截取
           $cur_position_step1 = $this->_remove_3samechars( $cur_seq,$i );
           if( $cur_position_step1 >0 )
           {
                $this->_cur_position = $cur_position_step1;
                continue;
            }
            if($this->_option_set == '1')
            {
                //特定长度中有DP、DG、NG、QG的忽略
                $cur_position_step2 = $this->_remove_dpdgngqg( $cur_seq ,$i);
                if( $cur_position_step2 >0 )
                {
                    $this->_cur_position = $cur_position_step2;
                    continue;
                }
            }
//            if($this->_option_set == '2')
//            {
//                //特定长度中G在第三位或Q在第一位的忽略
//                $cur_position_step3 = $this->_remove_g( $cur_seq ,$i);
//                if( $cur_position_step3 >0 )
//                {
//                    $this->_cur_position = $cur_position_step3;
//                    continue;
//                } 
//            }
//            if($this->_option_set == '3')
//            {
//                //特定长度中G在第三位或Q在第一位的忽略
//                $cur_position_step5 = $this->_remove_q( $cur_seq ,$i);
//                if( $cur_position_step5 >0 )
//                {
//                    $this->_cur_position = $cur_position_step5;
//                    continue;
//                } 
//            }
            //特定长度中如果有C，C在首位或末位的以外的都不要
            $cur_position_step4 = $this->_remove_c( $cur_seq ,$i);
            if( $cur_position_step4 >0 )
            { 
                $this->_cur_position = $cur_position_step4;
                continue;
            } 
            
            $seqstr_arr[] = $cur_seq;
        }
        return $seqstr_arr;
    }
    
     //特定长度中有连续3个相同字母的忽略| 从当前向下继续截取
    private function _remove_3samechars( $cur_seq,$i )
    {
        $three_chars_arr = array(
             'AAA','BBB','CCC','DDD','EEE','FFF','GGG',
             'HHH','III','JJJ','KKK','LLL','MMM','NNN',
             'OOO','PPP','QQQ','RRR','SSS','TTT','UUU',
             'VVV','WWW','XXX','YYY','ZZZ'
        );
        $cur_position = FALSE;
        foreach( $three_chars_arr as $item )
        {
             if( strripos( $cur_seq , $item ) !== FALSE )
             {
                 $cur_position[] = $i+ intval( strripos( $cur_seq , $item ))+3;
             }
        }
        if( $cur_position && count( $cur_position ) )
        {
            rsort($cur_position);    
            return $cur_position[0];
        }
         return $cur_position;
    }
    
    //特定长度中有DP、DG、NG、QG的忽略
    public function _remove_dpdgngqg( $cur_seq , $cur_position )
    {
         $cur_tmp_position = FALSE;
         if( strripos($cur_seq, 'DP') !== FALSE ||  strripos($cur_seq, 'DG') !== FALSE || strripos($cur_seq, 'NG') !== FALSE || strripos($cur_seq, 'QG') !== FALSE  )
         {
            if( strripos($cur_seq, 'DP') !== FALSE )
            {
                $cur_tmp_position[] = strripos($cur_seq, 'DP')+$cur_position;
            }
             if( strripos($cur_seq, 'DG') !== FALSE )
            {
                $cur_tmp_position[] = strripos($cur_seq, 'DG')+$cur_position;
            }
             if( strripos($cur_seq, 'NG') !== FALSE )
            {
                $cur_tmp_position[] = strripos($cur_seq, 'NG')+$cur_position;
            }
             if( strripos($cur_seq, 'QG') !== FALSE )
            {
                $cur_tmp_position[] = strripos($cur_seq, 'QG')+$cur_position;
            }
         }
         if( $cur_tmp_position && count( $cur_tmp_position ) )
         {
            rsort($cur_tmp_position);    
            return $cur_tmp_position[0];
         }
         return $cur_tmp_position;
    }
    
     //特定长度中G在第三位忽略
    private function _remove_g( $cur_seq , $cur_position )
    {
        $cur_tmp_position = FALSE;
        $num_g = substr_count( $cur_seq, 'G' );
        if( ($num_g ==1 && stripos( $cur_seq,'G') === 2))
        {
                $cur_tmp_position[] = 3 + $cur_position;
        }
        else if($num_g>1 && substr( $cur_seq, 2,1 ) == 'G')
        {
             $cur_tmp_position[] = 3 + $cur_position;
        }
         if( $cur_tmp_position && count( $cur_tmp_position ) )
        {
            rsort($cur_tmp_position);    
            return $cur_tmp_position[0];
        }
        return $cur_tmp_position;
    }
     //特定长度中Q在第一位的忽略
     private function _remove_q( $cur_seq , $cur_position )
    {
        $cur_tmp_position = FALSE;
        $num_q = substr_count( $cur_seq, 'Q' );
        if( ($num_q ==1 && stripos( $cur_seq,'Q') === 0) )
        {
             $cur_tmp_position[] = 1 + $cur_position;
        }
         else if($num_q>1 && substr( $cur_seq, 0,1 ) == 'Q')
        {
             $cur_tmp_position[] = 1 + $cur_position;
        }
         if( $cur_tmp_position && count( $cur_tmp_position ) )
        {
            rsort($cur_tmp_position);    
            return $cur_tmp_position[0];
        }
        return $cur_tmp_position;
    }
    //特定长度中G在第三位或Q在第一位的忽略
    public function _remove_c( $cur_seq , $cur_position )
    {
        $cur_tmp_position = FALSE;
        if( strpos( $cur_seq , 'C' ) !== FALSE  )
        {  
             $c_postion_begin = strpos( $cur_seq ,'C' );
             $c_postion_end = strripos($cur_seq, 'C');
             if( $c_postion_begin < $c_postion_end )
             {
                 return ( $c_postion_end+ $cur_position) ;
             }
             else if( $c_postion_begin == $c_postion_end ){
                if( ($c_postion_begin > 0) && ( $c_postion_begin < ($this->_length -1 ) ) )
                { 
                    $cur_tmp_position = $c_postion_end + $cur_position ;
                    return $cur_tmp_position;
                }
             }
             return $c_postion_end;
             
        }
        return $cur_tmp_position;
    }
    
    //采集seq 和ptm
    private function _collection_seq_and_ptm( $uniprot )
    {
        $chain_begin = $chain_end = 0 ;
        $residue_begin = $residue_end = array();
        $url = $this->_baseurl . $uniprot;
        //read content
        $content = file_get_contents($url);
        
        // 1.Subcellular location->Topology->Extracellular，Cytoplasmic 每个字母都在这里面 -- initing
        preg_match( '#<table class="featureTable" id="topology_section">(.*)</table>#iUs', $content ,$step1_arr );
        preg_match_all( '#<td class="numeric"><a class="position tooltipped" title="BLAST subsequence" href="\/blast\/\?about=(.*)\[(.*)]&amp\;key=(.*)">(.*)</a></td>#iUs', $step1_arr[1] ,$step1_arr_all );
        if( isset($step1_arr_all) &&count($step1_arr_all))
        {
            foreach ( $step1_arr_all[3] as $k_step1 =>$v1 )
            {
              if( trim($v1) == 'Topological domain')
              {
                  $subcellular_location_arr[] = $step1_arr_all[2][$k_step1]; 
              }
            }
            foreach( $subcellular_location_arr as $v_tmp )
            {
                  $tmp_location = explode('-', $v_tmp);
                  $subcellular_location[] =array( intval($tmp_location[0]), intval($tmp_location[1]) );
            }
        }
         // 2.PTM / Processing->Molecule processing中如果有Chain，只在这个范围内查找 --over
        preg_match('#<table class="featureTable" id="peptides_section">(.*)</table>#iUs', $content,$step2_arr);
        preg_match_all( '#<a class="position tooltipped" title="BLAST subsequence" href="\/blast\/\?about=(.*)\[(.*)\]\&amp;key=(.*)">(.*)</a>#iUs', $step2_arr[1] ,$step2_arr_all );
        if(count($step2_arr_all) )
        {
            $tmp_arr = explode('-', $step2_arr_all[2]);
            $chain_begin = $tmp_arr[0];
            $chain_end = $tmp_arr[1];
        }
         // 4.PTM / Processing->Amino acid modifications中 所有值去掉(多行 &多种名称)---over
        preg_match('#<table class="featureTable" id="aaMod_section">(.*)</table>#iUs', $content,$step4_arr);
       //要改为多个 preg_match_all( '#<td class="numeric"><a class="position tooltipped" title="BLAST subsequence" href="\/blast\/\?about=(.*)\[(.*)\]\&amp\;key=(.*)">(.*)</a></td>#iUs', $step4_arr[1] ,$step4_arr_all );
        if( isset($step4_arr_all) &&count($step4_arr_all))
        {
            foreach ( $step4_arr_all[2] as $v )
            {
               $residue_begin[] = intval($v);
               $residue_end[] = intval($v);
            }
        }
        //get sequence
        preg_match('#<pre class="sequence"\>(.*)<\/pre>#iUs', $content,$seq_arr);
        if($seq_arr)
        {
             $sequence =  preg_replace(array('/[0-9]+/','/\s/'), array( '',''), strip_tags($seq_arr[1]) ) ;
        }
        else
        {
             //页面获取失败则从数据库取值
            $row = $this->_link_uniprot()->select('sequence')->like('accession',$uniprot)->get( $this->_tbl_uniprot )->row_array(1);
            if( count($row) )
            {
                $sequence = $row['sequence'];
            }
            else
            {
                $sequence = FALSE;
            }
        }
       
        return array( 'subcellular_location'=>$subcellular_location,'chain_begin' => intval($chain_begin),'chain_end' => intval($chain_end),'residue_begin' =>$residue_begin,'residue_end' => $residue_end,'sequence' => $sequence);
    }
    
    public function get_each_items_value( $arr )
    {
        $row = $word_arr = array();
        foreach( $arr as $k=>$item_word )
        {
          for($i =0;$i<$this->_length ;$i++ )
          {      
                $word = substr($item_word, $i,1);
                $row = $this->_link_uniprot()->where( 'word_name',$word )->get( $this->_tbl_word )->row_array(1);
                $word_arr[$k][] = $row ? $row : array();
          }
        }
        return $word_arr;
    }
    
    //计算seques的rank
    public function rank_val( $seqstr = '' )
    {
        $seqstr = "MKALWAVLLVTLLTGCLAEGEPEVTDQLEWQSNQPWEQALNRFWDYLRWVQTLSDQVQEELQSSQVTQELTALMEDTMTEVKAYKKELEEQLGPVAEETRARLGKEVQAAQARLGADMEDLRNRLGQYRNEVHTMLGQSTEEIRARLSTHLRKMRKRLMRDAEDLQKRLAVYKAGAREGAERGVSAIRERLGPLVEQGRQRTANLGAGAAQPLRDRAQAFGDRIRGRLEEVGNQARDRLEEVREHMEEVRSKMEEQTQQIRLQAEIFQARLKGWFEPIVEDMHRQWANLMEKIQASVATNPIITPVAQENQ";
        if( $seqstr == '' )
        {
            exit('No sequence !');
        }
        $word = self::formt_to_array($seqstr);
        $length = count($word);
        $word_arr = $this->_word_arr;
        $k = 0 ;
        for( $i = ($this->_length-1); $i< $length;$i++ )
        {  
            for($j = $i;$j>=$k;$j--)
            { 
                $arr[$i] += $word_arr[$word[$j]]; 
            }
             $rand_arr[$i] = round($arr[$i]/($this->_length),3);
             $rand_char_arr[$i] = $word[$i];
             $k++;
        }
        echo "<pre>";
        print_r($rand_char_arr);
        print_r($rand_arr);
    }
    private function formt_to_array($seqstr)
    {
        $word_arr = array();
        $legth = 0;
        $legth = strlen( $seqstr );
        for( $i= 0 ; $i< $legth;$i++ )
        {
           $word_arr[$i] = substr($seqstr, $i,1);
        }
        return $word_arr;
    }
      public function draw_chart()
    {
        draw_chart();
    }
}
