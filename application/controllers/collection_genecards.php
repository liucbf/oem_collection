<?php

/* 
 * Genecards rules control
 * 
 * 
 */
class Collection_genecards extends CI_Controller
{
    function __construct()
    {
        error_reporting(0);
        set_time_limit(0);
        parent::__construct();
    }
   //genecards gene id 
    public function geneid()
    {
        $url = 'http://www.genecards.org/hotGenes.shtml';
        $content = format_html( $url );
        preg_match("#<divclass='oldwebsite'>(.*)<\/div>#iUs",$content,$arr );
        preg_match_all( '#<li><ahref="http://www.genecards.org/cgi-bin/carddisp.pl\?gene=(.*)">(.*)</a>#iUs', $arr[1],$arr_tmp);
        foreach ( $arr_tmp[2] as $k =>$v )
        {
                $product[$k]['geneid'] = $v ;
                $product[$k]['url'] = "http://www.genecards.org/cgi-bin/carddisp.pl?gene=".$v;
                $product[$k]['uniprot'] = $this->get_uniprot($product[$k]['url']);
               // file_put_contents('C:\Users\li_hao\Desktop/1.txt', $v.'@'.$product[$k]['url']."@".$product[$k]['uniprot'].PHP_EOL,FILE_APPEND);
        }
        $export_arr = array(
            'geneid' => 'geneid',
            'url' => 'url',
            'uniprot' => 'uniprot',
        );
       export_excel($export_arr, $product, 'hotGenes Data_' . $name . '__' . time());
    }
    public function read_file( )
    {
        $content = file_get_contents( "C:\Users\li_hao\Desktop\geneid/1.txt");
        $arr = explode("http:", $content);
        foreach ( $arr as $k =>$v )
        {
            if($v)
            {       
                   $product[$k]['url'] = "http:".$v;
                   echo $this->get_uniprot("http:".$v )."==<br>";
            }
        }
          $export_arr = array(
            'url' => 'url',
            'uniprot' => 'uniprot',
        );
         //  export_excel($export_arr, $product, 'hotGenes__' . time());
    }

    public function get_uniprot( $url = 'http://www.genecards.org/cgi-bin/carddisp.pl?gene=GGH' )
    {
         $content1 = format_html( $url );
         preg_match_all('#<ahref="http://www.uniprot.org/uniprot/(.*)\#section_comments"(.*)>(.*)</a>#iUs',$content1,$arr1 );
         if(count($arr1) )
         {
             return $arr1[1][0];
         }
         else
         {   
             return '';
         }
         
    }
    
    private function _select_web_db()
    {
        return $this->load->database('query_web',TRUE);
    }
    
    public function query_row()
    {
        $content = file_get_contents( "C:\Users\li_hao\Desktop\geneid/uniprot.txt");
        $arr = explode("@", $content);
     
        foreach ( $arr as $k =>$v )
        {
            if($v)
            {       
               $catalog = self::query_by_accessnum( trim($v));
               $product[$k]['uniprot'] = trim($v);
               $product[$k]['catalog'] = implode(',',$catalog);
            }
        }
           $export_arr = array(
            'uniprot' => 'uniprot',
            'catalog' => 'catalog',
        );
        export_excel($export_arr,$product,'AZ');
    }
    private function query_by_accessnum( $uniprot )
    {
        $arr = array();
        $db_dr = $this->_select_web_db();
        $row = $db_dr->query("SELECT distinct(catalog)  FROM `abgent_products` WHERE `is_allow` = 1 and  `is_backorder` = 0 and `is_discontinue` ='0000-00-00' and  catalog regexp '^AZ|AX' and `primary_accession` like '%".$uniprot."%' order by `is_stellar` desc ")->result_array();
        if(count($row))
        {
            foreach ($row as $v)
            {
                $arr[] = $v['catalog'];
            }
        }
        return $arr ;
    }
    
    public function read11(){
        $c=reade_excel('C:\Users\li_hao\Desktop/Book1.xlsx','.xlsx');
        unset($c[0]);
        foreach ($c as $k=>$v )
        {
            $aa= $v[1];
            $aa = trim($aa,',');
             $product[$k]['u'] = $v[0]; 
            if($aa&& stripos($aa ,',')!==FALSE )
            {
                $arr= explode(',', $aa);
                $product[$k]['catalog'] = $arr[0].",".$arr[1]; 
            }
            else{
               $product[$k]['catalog'] = $aa; 
            }
        }
         $export_arr = array(
            'u' => 'u',
            'catalog' => 'catalog',
        );
        export_excel($export_arr,$product,'AZ');
    }
}
