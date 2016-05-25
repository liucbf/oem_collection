<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Collection_merdianlifescience extends CI_Controller {

    function __construct()
    {
       error_reporting(0);
        set_time_limit(0);
        parent::__construct();
        $this->load->helper('mycollection');
    }

  public function index(  )
    {
      $post = $this->input->post();
       
        $type = $post['select_type'];
        if( $type == '1' )
        {
            $config['allowed_types'] = 'txt';
            $config['upload_path'] = './static/uploads/';
            $this->load->library('upload', $config);
            if ( ! $this->upload->do_upload('url_file'))
            {
                exit( $this->upload->display_errors());
            }
            $file = $this->upload->data();
            $contents = file_get_contents( $file['full_path'] );
        }
        if( $type == '2' )
        {
           $contents = trim($post['url_input']);
        }
        $product_arr = explode('https', $contents);
        unset( $product_arr[0] );
        foreach ($product_arr as $k => $v)
        {
           $each_one = 'https' . trim($v);
           $export_data = array_merge( $this->get_base_data_from($each_one),$export_data );
        }
        $export_arr = array(
                    'child_url' => 'child_url',
                    'name' => 'name',
                    'format' => 'format',
                    'host' => 'host',
                    'isotype' => 'isotype',
                    'apps' => 'apps',
                    'unit' => 'unit',
                    'catalog' => 'catalog',
                    'pdf' => 'pdf'
        );
       export_excel($export_arr , $export_data, 'merdianlifescience_'.date('YmdHis'));
    }

    public function get_base_data_from($url = "https://meridianlifescience.com/products/catalog.aspx?product=Polyclonal+Antibodies+to+Cardiac+Markers")
    {
        $product = array();
        $content = get_content_html_format($url);
        $name_arr = $this->set_base_rules('name', $content);
        $format_arr = $this->set_base_rules('format', $content);
        $host_arr = $this->set_base_rules('host', $content);
        $isotype_arr = $this->set_base_rules('isotype', $content);
        $apps_arr = $this->set_base_rules('apps', $content);
        $unit_arr = $this->set_base_rules('unit', $content);
        $catalog_arr = $this->set_base_rules('catalog', $content);
        $pdf_arr = $this->set_base_rules('pdf', $content);
        if( $name_arr && count($name_arr) )
        {
            foreach ( $name_arr[1] as $k=>$v )
            {
                $product[$k]['child_url'] = $url;
                $product[$k]['name'] = $v;
                $product[$k]['format'] = strip_tags($format_arr[1][$k]);
                $product[$k]['host'] = strip_tags($host_arr[1][$k]);
                $product[$k]['isotype'] = strip_tags($isotype_arr[1][$k]);
                $product[$k]['apps'] = strip_tags($apps_arr[1][$k]);
                $product[$k]['unit'] = strip_tags($unit_arr[1][$k]);
                $product[$k]['catalog'] = strip_tags($catalog_arr[1][$k]);
                $product[$k]['pdf'] = $pdf_arr[1][$k];
            }
        }
        return $product;
    }

    public function set_base_rules($param, $content)
    {
        $data = $preg_unique = $preg = $pre_arr = array();
        $preg['name'] = '#<div style=\"font-family\:Verdana\, Arial\, Helvetica\, sans-serif\; color\:\#FFFFFF\; background-color\:\#00337F\; width\:950px\; padding\:2px\; font-weight\:bold\; font-size\:10px;\">(.*)</div>#iUs';
        $preg['format'] = '#<font size="1"><div><strong>Format\:</strong>(.*)</div></font>#iUs';
        $preg['host'] = '#<font size="1"><div><strong>Host\/Source:</strong>(.*)</div></font>#iUs';
        $preg['isotype'] = '#<font size="1"><div><strong>Isotype\:</strong>(.*)</div></font>#iUs';
        $preg['apps'] = '#<font size="1"><div><strong>Tested Apps\: </strong>(.*)</div></font>#iUs';
        $preg['unit'] = '#<font size="1"><div><strong>Unit\:</strong>(.*)</div></font>#iUs';
        $preg['catalog'] = '#<font size="1"><div><strong>Catalog\:</strong>(.*)</div></font>#iUs';
        $preg['pdf'] = '#<font size="1"><div><input type=\'button\' value=\'SDS\' title=\'Safety Data Sheets\'(.*)class=\'bttn_sds\'/></div>#iUs';
                                             
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
    // type 1:category 2:product url
    public function get_first_url($url = 'https://meridianlifescience.com/products/products.aspx',$type = 1)
    {
        $product = $pre_arr = $pre_arr2 = array(); 
        $content = '';
        if ( $url  )
        {
            if($type ==1)
            {
                 $content = get_content_html_format($url= "https://meridianlifescience.com/products/products.aspx ");
                  preg_match_all('#<li><font face="Verdana, Arial, Helvetica, sans-serif"size="2"><b><a href="(.*)"><font class="bullets">(.*)</font></a></b></font></li>#iUs', $content, $pre_arr);
                  foreach ( $pre_arr[1] as $k=> $url1 )
                  {
                       $content2 = '';
                       $export_data[$k]['category'] = $pre_arr[2][$k];
                       $export_data[$k]['category_url'] = $pre_arr[1][$k];

                  }
        
                  $export_arr = array(
                    'category' => 'category',
                    'category_url' => 'category_url',

                );
                  export_excel($export_arr , $export_data, '--meridianlifescience_Data_'.$name);
                  exit;
            }
            else{
               
                 $content = get_content_html_format( $url );
                 preg_match_all('#<p><span ><a href="(.*)">(.*)</a>(.*)</span></p>#iUs', $content, $pre_arr);
                return $pre_arr;
              
             }
        }
    }

    //获取链接
    public function get_child_url_data()
    {
        $name = 'category_url';
        $fileName = 'D:/work-2014/collection/meridianlifescience/' . $name . '.txt';
        $product_arr = $export_data = array();
        //open file
        $contents = file_get_contents($fileName);
        $product_arr = explode('active=', $contents);
        unset($product_arr[0]);
        $j = 0;
        foreach ($product_arr as $k => $v)
        {
            $each_one = 'https://meridianlifescience.com/products/products3.aspx?active=' . urlencode(trim($v)) ;
            $data_arr = $this->get_first_url($each_one,2);
            if($data_arr)
            {
                foreach ( $data_arr[2] as $ck=>$cc)
                {
                    $export_data[$j]['url']  = 'https://meridianlifescience.com/products/products3.aspx?active='.trim($v);
                    $export_data[$j]['child_name']  = $cc;
                    $export_data[$j]['child_url'] = "https://meridianlifescience.com".$data_arr[1][$ck];
                    $export_data[$j]['product_num'] = strip_tags( $data_arr[3][$ck] );
                    $j++;
                }
            }
        else
            {
                $j++;
            }
        }
        $export_arr = array(
            'url' => 'url',
            'child_name' => 'child_name',
            'child_url' => 'child_url',
            'product_num' => 'product_num',
        );
       export_excel($export_arr , $export_data, 'meridianlifescience_product'.$name);
    }
    
    
    //采集pdf->html数据
    public function get_data_from_html()
    {
        $this->load->helper( "file" );
        $real_path = "C:/Users/li_hao/Desktop/meridian/html/";
        $export_data = array();
        $file = get_filenames( $real_path );
        foreach( $file as $k => $filename )
        {
            
            
           $content = format_html($real_path.$filename);
           $export_data[$k] = self::get_pro_info( $content,rtrim($filename,'.htm') );
           
           $export_arr = array(
                'catalog'=>'catalog',
               'storage'=>'storage',
               'purification'=>'purification',
               'immunogen'=>'immunogen',
               'clone'=>'clone',
               'isotype'=>'isotype',
               'concentration' =>'concentration'
               
           );
        }
       
        export_excel($export_arr ,$export_data,'abc_'.date('ymdHis'));
    }
    
    public function get_pro_info( $content ='',$catalog )
    {    
//         $real_path = "C:/Users/li_hao/Desktop/meridian/html/";
//         $content = format_html( $real_path."A24108H.htm");
        $product['catalog'] = $catalog;
      //  $product['important_note'] = self::_get_pro_info('important_note' ,$content);
       // $product['description'] = self::_get_pro_info('description_1',$content).self::_get_pro_info('description_2',$content);

  
//        $product['format'] = self::_get_pro_info('format',$content);
//        $product['purification'] = self::_get_pro_info('purification',$content);
//        $product['concentration'] = self::_get_pro_info('concentration',$content);
//        $product['buffer'] = (self::_get_pro_info('buffer_1',$content)) .(self::_get_pro_info('buffer_2',$content)); 
//        $product['preservative'] = self::_get_pro_info('preservative',$content);  
//        $product['application'] = self::_get_pro_info('application',$content);  
//        $product['storage'] = self::_get_pro_info('storage',$content);  
//        $product['inactivation'] = self::_get_pro_info('inactivation',$content);
//        //下面有的有有的没有
//        $product['warnings'] = self::_get_pro_info('warnings',$content);
       
        /*
         $immunogen = self::_get_pro_info('immunogen',$content);
         $product['immunogen'] = strip_tags( $immunogen[1] );
         $arr = self::_get_pro_info('purification',$content);
         $product['purification']= strip_tags($arr[1]);
         $concentration = self::_get_pro_info('concentration',$content);
         $product['concentration'] = ltrim(strip_tags( $concentration[1] ),':');
         $storage = self::_get_pro_info('storage',$content);
         $product['storage'] = ltrim(strip_tags( $storage[1] ),':');
         * $clone = self::_get_pro_info('clone',$content);
         $product['clone'] = ltrim(strip_tags( $clone[1] ),':');
         $storage = self::_get_pro_info('storage',$content);
         $product['storage'] = ltrim(strip_tags( $storage[1] ),':');
         * 
         */
          $clone = self::_get_pro_info('clone',$content);
          $product['clone'] = ltrim(strip_tags( $clone[1] ),':');
//        $arr = self::_get_pro_info('immunogen_1',$content);
//        $product['immunogen'] = self::_get_pro_info('immunogen',$content) .$arr[4][0];
//       
//        $product['host_animal'] = self::_get_pro_info('host_animal',$content);
//        $product['specificity'] = self::_get_pro_info('specificity',$content);
//        $product['note'] = self::_get_pro_info('note',$content);
//        $product['affinity_constant'] = self::_get_pro_info('affinity_constant',$content);
//        $product['clone'] = self::_get_pro_info('clone',$content);
//        $product['isotype'] = self::_get_pro_info('isotype',$content);
//        $product['exp_date'] = self::_get_pro_info('exp_date',$content);
        return $product;
    }
   
    private function _get_pro_info($param, $content)
    {
        $data = $preg_unique = $preg = $pre_arr = array();
        $preg_unique['important_note'] = '#<h2 style="padding-left: 5pt;text-indent: 0pt;line-height: 273%;text-align: left;">(.*)\.#iUs';
        //$preg_unique['description_1'] = '#Description+:?<span class="p">(.*)</span>#iUs';
       // $preg_unique['description_2'] = '#Description<span class="h3">:</span><span class="p">(.*)</span></h2>#iUs';
        //Immunogen 	Purification	Concentration	Storage	Precautions Original	Search Clonality	Clone Names	Bio References	Clonality	Antigen Region
       //  $preg_unique['source_1'] = '#<p style="padding-left: 5pt;text-indent: 0pt;text-align: left;"><span class="h2">Source:</span>(.*).</p>(.*)#iUs';
      // $preg_unique['source_2'] = '#Source+:?<span class="p">(.*)</span>#iUs';
      //  $preg['source_2'] = '# <p class="s2"style="padding-left: 2pt;text-indent: 0pt;text-align: left;">Source<span class="s3">:</span></p></td><td><p style="text-indent: 0pt;line-height: 13pt;text-align: left;"><br/></p><p class="s3"style="padding-left: 15pt;text-indent: 0pt;text-align: left;">(.*)</p>#iUs';
       
      // $preg_unique['format'] = '#Format+:?<span class="p">(.*)</span>#iUs';
      //  $preg_unique['exp_date'] = '#Exp\. Date+:?<span class="p">(.*)</span>#iUs';
        $preg['immunogen'] = '#Immunogen(.*)Format#iUs';
        //$preg['immunogen_1'] = '#Immunogen:</p></td><td><p class="s(.*)"style="padding-top: (.*)pt;padding-left: (.*)pt;text-indent: 0pt;text-align: left;">(.*)</p>#iUs';
       
        
        $preg['purification'] = '#Purification(.*)Concentration#iUs';
        $preg['concentration'] = '#Concentration(.*)Buffer#iUs';
        $preg['storage'] = '#Storage(.*)<img#iUs';
        
        
        $preg['clone'] = '#Host Animal:</p>(.*)Mouse#iUs';
       // $preg_unique['buffer_1'] = '#<span class="h2">Buffer:</span>(.*)<span class="h2">#iUs';
        //$preg_unique['buffer_2'] = '#Buffer+:?<span class="p">(.*)</span>#iUs';
        //$preg_unique['preservative'] = '#Preservative+:?<span class="p">(.*)</span>#iUs';
       // $preg_unique['application'] = '#Application+s?:?<span class="p">(.*)</span>#iUs';
       // $preg_unique['storage'] = '#Storage+:?<span class="p">(.*)</span>#iUs';
        //$preg_unique['inactivation'] = '#Inactivation+:?<span class="p">(.*)</span>#iUs';
        
        //下面的是可能存在的一种情况：
      //  $preg_unique['warnings'] = '#Warning+s?:?<span class="p">(.*)</span>#iUs';
      //  $preg_unique['immunogen'] = '#Immunogen+:?<span class="p">(.*)</span>#iUs';
      //  $preg_unique['host_animal'] = '#Host Animal+:?<span class="p">(.*)</span>#iUs';
       // $preg_unique['specificity'] = '#Specificity+:?<span class="p">(.*)</span>#iUs';
        //$preg_unique['note'] = '#Note+:?<span class="p">(.*)</span>#iUs';
      //  $preg_unique['affinity_constant'] = '#Affinity Constant+:?<span class="p">(.*)</span>#iUs';
       // $preg_unique['clone'] = '#Clone(.*)Host Animal#iUs';
       // $preg_unique['isotype'] = '#Isotype+:?<span class="p">(.*)</span>#iUs';
        if ($param && $content)
        {
            if (isset($preg_unique[$param]))
            {
                preg_match($preg_unique[$param], $content, $pre_arr);
                return $pre_arr[1];
            }
            else
            {
                preg_match($preg[$param], $content, $pre_arr);
                return $pre_arr;
            }
        }
        return $pre_arr;
    }
     public function get_web_data_title()
    {
        $real_path = "C:/Users/li_hao/Desktop/meridian/1501-2260.txt";
        $export_data = array();
        $file_data = file_get_contents( $real_path );
        $arr = explode('@',$file_data );
       
        foreach( $arr as $k => $v )
        {
            if($v !='')
            {
                $catalog= trim($v);

                $url = 'https://meridianlifescience.com/products/results_2.aspx?searchbox='.$catalog.'&page=1&group=0';
                $export_data[$k] = self::get_title($url);
                $export_data[$k]['catalog'] = $catalog;
                $export_arr = array(
                     'catalog'=>'catalog',
                     'title' => 'title',
                     'isotype'=>'isotype'
               );
            }
        }
      
        export_excel($export_arr ,$export_data,'abc_'.date('ymdHis'));
    }
    
    public function get_title( $url = "https://meridianlifescience.com/products/results_2.aspx?searchbox=Z86107M&page=1&group=0" )
    {$product = array();
       $content = format_html($url);
       $preg = '#<div style=\"font-family:Verdana\, Arial, Helvetica\, sans-serif\; color\:\#FFFFFF\; background-color\:\#00337F\; width\:950px\; padding\:2px\; font-weight\:bold; font-size\:10px\;">(.*)</div>#iUs';
       preg_match($preg, $content,$arr );
       $preg_isotype = '#<div><strong>Isotype:</strong>(.*)</div>#iUs';
       preg_match($preg_isotype, $content,$arr1 );
       $product['isotype'] = @$arr1[1];
       $product['title'] = $arr[1];
       return $product;
    }
    
    public function ck_file(){

        $arr = reade_excel("C:\Users\li_hao\Desktop\meridian/Book2.xlsx", ".xlsx");
        unset($arr[0]);
       
         foreach( $arr as $k => $v )
        {
             $file = "C:/Users/li_hao/Desktop/meridian/pdf/".$v[0].".pdf";
             if( !file_exists($file) )
             {
                 echo $v[0]."<br>";
             }
        }
        
    }
}
