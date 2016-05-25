<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Collection_aviva extends CI_Controller {

    //path
    var $_path = "D:/work-2015/aviva/";
    var $_model_url = "http://www.avivasysbio.com";

    function __construct()
    {
        error_reporting(0);
        set_time_limit(0);
        parent::__construct();
        $this->load->helper('mycollection');
    }

    //++++++++++++++++++++++++++++
    public function index( )
    {
        //$file = "1-500.txt";
        $contents = file_get_contents( $this->_path.$file );
        $product_arr = explode('http', $contents);
        unset($product_arr[0]);
        foreach ($product_arr as $k => $v)
        {
             $each_one = 'http' . trim($v);
             $export_data[] = self::_catch_images($each_one);
        }
        $export_arr = array(
            'url' => 'url',
        );
        for($i=1;$i<20;$i++)
        {
            $export_arr['Image_'.$i] = 'Image_'.$i;
            $export_arr['Legend_'.$i] = 'Legend_'.$i;
        }
        export_excel($export_arr, $export_data, ' aviva_' .date('YmdHis'));
    }
  //采集图片
   private function _catch_images( $url )
   {
            $content = format_html($url);
            $product['url'] = $url;
            preg_match_all('#<a class="mustang-gallery"(.*)"description="(.*)"href="(.*)"><img id="product_image_thumbnail_click"#iUs', $content,$arr );
            if( $arr && count($arr[3] ) )
            {
                $k = 1;
                foreach ( $arr[3] as $i => $img_v )
                {
                    $product['Image_'.$k] = $img_v;
                    $product['Legend_'.$k] = $arr[2][$i];
                            $k ++ ;
                }    
            }
             // fputcsv($export_arr, $export_data, ' aviva_' .date('YmdHis'));
            return $product ;
            
   }
     public function get_queshi_info( )
    {
        $file = "1.txt";
        $contents = file_get_contents( "C:\Users\li_hao\Desktop/".$file  );
        $product_arr = explode('http', $contents);
        unset($product_arr[0]);
        foreach ($product_arr as $k => $v)
        {
             $each_one = 'http' . trim($v);
             $export_data[] = self::get_base_data_from($each_one);
        }
        $export_arr = array(
            'url' => 'url',
            'concentration' => 'concentration',
            'reference'=>'reference',
            'protein_interactions'=>'protein_interactions',
            'protein_accession' => 'protein_accession',
            'nucleotide_accession' => 'nucleotide_accession'
        );
      export_excel($export_arr, $export_data, ' aviva_' .date('YmdHis'));
    }
    public function get_base_data_from($url = '')
    {
        $url = strlen($url) ? $url : $this->_model_url;
        $product_get_url = $url;
        $product = array(); 
        $product['url'] = $url;
        $content = format_html($product_get_url);
        $concentration = $this->set_base_rules('concentration', $content);
        
        $reference  = $this->set_base_rules('reference', $content);
        $protein_interactions  = $this->set_base_rules('protein_interactions', $content);
        
        $nucleotide_accession  = $this->set_base_rules('nucleotide_accession', $content);
        $protein_accession  = $this->set_base_rules('protein_accession', $content);
        
        $product['concentration']  = strip_tags( $concentration[1][0] );
        $product['reference']  = strip_tags( $reference[1][0] );
        $product['protein_interactions'] = strip_tags( $protein_interactions[1][0] );
 
        $product['protein_accession'] = strip_tags( $protein_accession[1][0] );
        $product['nucleotide_accession'] = strip_tags( $nucleotide_accession[1][0] );
        
        return $product;
    }

    //set rules
    public function set_base_rules($param, $content)
    {
        $data = $preg_unique = $preg = $pre_arr = array();
        //Product Info 
        $preg['concentration'] = '#<dt>Concentration:</dt><dd>(.*)</dd>#iUs';
        //Target Reference:
        $preg['reference'] = '#<dt>TargetReference:</dt>(.*)</dl>#iUs';
        //Protein Interactions:
        $preg['protein_interactions'] = '#<dt>ProteinInteractions:</dt><dd>(.*)</dd>#iUs';
        //Protein Accession #:
        $preg['protein_accession'] = '#<dt>ProteinAccession\#\:</dt><dd>(.*)</dd>#iUs';
        $preg['nucleotide_accession'] = '#<dt>NucleotideAccession\#\:</dt><dd>(.*)</dd>#iUs';
        
        if ($param && $content)
        {
            if (isset($preg_unique[$param]))
            {
                preg_match($preg_unique[$param], $content, $pre_arr);
                return $pre_arr[1];
            }
            else
            {
                preg_match_all($preg[$param], $content, $pre_arr);
                return $pre_arr;
            }
        }
        return $pre_arr;
    }
   
    public function rename()
    {
  
         $path = 'C:\Users\li_hao\Desktop\aviva/Book1.xlsx';
         $excel_data = reade_excel($path, ".xlsx");
         unset($excel_data[0]);
         foreach ($excel_data as $i=> $v)
         {
             
              $product[$i]['image_1'] = $this->check($v[0]);
              $product[$i]['image_2'] = $this->check($v[1]);
              $product[$i]['image_3'] = $this->check($v[2]);
              $product[$i]['image_4'] = $this->check($v[3]);
              $product[$i]['image_5'] = $this->check($v[4]);
              $product[$i]['catalog']  = $v[5];
         }
         $export_arr = array(
             'image_1' => 'image_1',
             'image_2' => 'image_2',
             'image_3' => 'image_3',
             'image_4' => 'image_4',
             'image_5' => 'image_5',
             'catalog' => 'catalog',
        );
      
         export_excel( $export_arr ,$product,'-'.date('ymdHis'));
    }
    public function check($str)
   {
        if( $str!='' )
        {
            return basename($str);
        }else{
            return '';
        }
    }
    
    
    //检测遗漏的图片数据
    public function check_left_img_data()
    {
         $path = 'C:\Users\li_hao\Desktop\aviva\2_1374/book2.xlsx';
         $excel_data = reade_excel($path, ".xlsx");
         unset($excel_data[0]);
         $local_path =  'C:\Users\li_hao\Desktop\aviva\2_1374/2_1374image/';
         
         foreach ($excel_data as $k=>$v )
         {
             if($v[0] !='' && !file_exists($local_path.$v[0]))
             {
                 echo $v[0]."<br>";
             }
         }
    }
   public function temp_ok()
   {
         $path = 'C:\Users\li_hao\Desktop/932.xlsx';
         $excel_data = reade_excel($path, ".xlsx");
         
         foreach ($excel_data as $k=>$v )
         {
             $a[$k]['pit'] = substr($v[0],0,strripos($v[0], "/") ).'/'.$v[1];
           
         }
         export_excel( array('pit'=>'pit'),$a,'sss');
    }
}
