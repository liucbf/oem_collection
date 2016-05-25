<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Get_uniproid_by_genid extends CI_Controller {

    const TBL_UNP_UNIPORT = 'p_uniprot';

    function __construct()
    {
        error_reporting(0);
        set_time_limit(0);
        parent::__construct();
        $this->load->database('default', 'uniprot', TRUE);
    }
  
    
    public function ready()
    {
        $this->db->empty_table( self::TBL_UNP_UNIPORT );
        if( $this->query("INSERT INTO `p_uniprot_genid`( `accession`, `geneid`)  select accession,geneid from p_uniprot") )
        {
            exit( "执行完毕" );
        }
    }

        //根据genid 以@隔开&&@在前,无genid的用null替代
    public function work(  )
    {
        $genid_arr = $export_data_unipro = array();
        $contents = file_get_contents( "C:\Users\li_hao\Desktop/script/geneid.txt" );
        $genid_arr = explode('@', $contents);
        unset($genid_arr[0]);
        $file_con = '';
        foreach ($genid_arr as $k => $v)
        {
            if ($v)
            {
                $each_genid = trim($v);
                if (strpos($each_genid, ',') !== false)
                {
                    $each_genid = explode(',', $each_genid);
                
                } 
               $export_data_unipro[] = self::_get_uniproid_by_genid($each_genid );
            }
        }
       
        $export_arr = array(
            'geneid' => 'geneid',
            'uniproid' => 'uniproid',
        );
       export_excel($export_arr, $export_data_unipro, __FUNCTION__  .time(),"C:\Users\li_hao\Desktop\script/");
    }

    private function _get_uniproid_by_genid($genid = '', $type = false)
    {
        $unipr_arr = $rows = array();
        $genids = $unipros =  '';
        if ($genid)
        {
            if (is_array($genid))
            {
                foreach( $genid as $v )
                {
                     $rows = $this->db->select('accession')->get_where(self::TBL_UNP_UNIPORT, array('geneid' => trim($v) ) )->row_array(1);
                     $unipros .= $rows['accession']?$rows['accession']. '|':" null |" ;
                }
                $genids = implode(',', $genid);
            }
            elseif($genid)
            {
                $rows = $this->db->query("select accession from ".self::TBL_UNP_UNIPORT." where geneid = '".trim($genid)."' limit 1")->row_array();
                $genids = $genid;
                $unipros .= $rows['accession'];
            }
            $unipros = rtrim( $unipros, "|" );
        }
        if ($type == 'txt')
        {
            return "geneid:" . $genid . '@@@uniprotid:' . $unipros . "\r\n";
        }
        return $unipr_arr = array('geneid' => $genids, 'uniproid' => $unipros);
    }
    
    
        public  function select_name()
    {
        $datas = file_get_contents('d:/1.txt');
        $data = explode("\r\n", $datas);
        
        foreach ( $data as $k=> $uniprot )
        {
            if($uniprot)
            {
           $row =  $this->db->query("SELECT name
                                            FROM `p_uniprot_single`
                                            WHERE `accession` ='".trim($uniprot)."' ")->row_array();
           $name = 'null';
           if($row)
           {
               $name = $row['name'];
           } 
           file_put_contents('d:/a.txt', $name."\r\n"   ,FILE_APPEND);
           echo $k ."=";
         //  $export_data[$k]['uniprot'] = $uniprot;
          
        }
        }
    }
}
