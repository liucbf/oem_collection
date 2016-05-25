<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Export_uniprot extends CI_Controller {

    const TBL_UNP_UNIPORT = 'p_uniprot_genid';
    const TBL_UNIPORT = 'p_uniprot';

    function __construct()
    {
        error_reporting(0);
        set_time_limit(0);
        parent::__construct();
         $this->load->database();
    }

    public function ready()
    {
        $this->db->empty_table(self::TBL_UNP_UNIPORT);
        if ($this->query("INSERT INTO `p_uniprot_genid`( `accession`, `geneid`)  select accession,geneid from p_uniprot"))
        {
            exit("执行完毕");
        }
    }

    //根据genid 以@隔开&&@在前,无genid的用null替代
    public function work($fileName)
    {
        $genid_arr = $export_data_unipro = array();
        $contents = file_get_contents(base64_decode($fileName));
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
                $export_data_unipro[] = self::_get_uniproid_by_genid($each_genid);
            }
        }

        $export_arr = array(
            'geneid' => 'geneid',
            'uniproid' => 'uniproid',
        );
        export_excel($export_arr, $export_data_unipro, __FUNCTION__ . time());
    }

    private function _get_uniproid_by_genid($genid = '', $type = false)
    {
        $unipr_arr = $rows = array();
        if ($genid)
        {
            if (is_array($genid))
            {
                foreach ($genid as $v)
                {
                    $rows = $this->db->select('accession')->get_where(self::TBL_UNP_UNIPORT, array('geneid' => trim($v)))->row_array(1);
                    $unipros .= $rows['accession'] ? $rows['accession'] . '|' : " null |";
                }
                $genids = implode(',', $genid);
            }
            elseif ($genid)
            {
                $rows = $this->db->query("select accession from " . self::TBL_UNP_UNIPORT . " where geneid regexp '" . trim($genid) . "' limit 1")->row_array();
                $genids = $genid;
                $unipros .= $rows['accession'];
            }
            $unipros = rtrim($unipros, "|");
        }
        if ($type == 'txt')
        {
            return "geneid:" . $genid . '@@@uniprotid:' . $unipros . "\r\n";
        }
        return $unipr_arr = array('geneid' => $genids, 'uniproid' => $unipros);
    }

    public function select_name()
    {
        $datas = file_get_contents('d:/1.txt');
        $data = explode("\r\n", $datas);

        foreach ($data as $k => $uniprot)
        {
            if ($uniprot)
            {
                $row = $this->db->query("SELECT name
                                            FROM `p_uniprot_single`
                                            WHERE `accession` ='" . trim($uniprot) . "' ")->row_array();
                $name = 'null';
                if ($row)
                {
                    $name = $row['name'];
                }
                file_put_contents('d:/a.txt', $name . "\r\n", FILE_APPEND);
                echo $k . "=";
                //  $export_data[$k]['uniprot'] = $uniprot;
            }
        }
    }

    //uniprot 导出
    public function index()
    {
        $this->load->view("export_uniprot");
    }

    public function search_seq_and_uniprot()
    {
        $data = reade_excel('C:\Users\li_hao\Desktop/1.xlsx', '.xlsx');
        unset($data[0]);
        $res = array();

        foreach ($data as $k => $v)
        {
            $res[$k]['danbaihao'] = $v[1];
            if ($v[2] != '')
            {
                $resss = $this->db->select('accession')->like('sequence', trim($v[2]))->get(self::TBL_UNIPORT)->row_array(1);
                if (count($resss))
                {
                    $res[$k]['accession'] = $resss['accession'];
                }
                else
                {
                    $res[$k]['accession'] = '0';
                }
            }
        }
        export_excel(array('danbaihao' => 'danbaihao', 'accession' => 'accession'), $res, time());
    }

    //根据uniprot id导出uniprot id 的信息
    public function get_entry_name_by_uniprot($filename)
    {
        $datas = file_get_contents('C:\Users\li_hao\Desktop/'.$filename.'.txt');
        $data = explode("@", $datas);
        foreach ($data as $ky => $v1)
        {
            if (strlen(trim($v1)))
            {
                $product[$ky]['uniprot_id'] = trim($v1);
                // $row = $this->db->select( '`accession`,antigen_source,is_reviewed,function,reference,sequence,other_name' )->where( '', trim($v1) )->get( self:: TBL_UNIPORT )->row_array();
                $row = $this->db->select('geneid,`accession`,antigen_source,is_reviewed,function ,reference ,other_name,`entry_name`,`name`')->like('accession', trim($v1))->get(self:: TBL_UNIPORT)->row_array();
                
                if (count($row))
                {
                    $product[$ky]['antigen_source'] = $row['antigen_source'];
                    $product[$ky]['function'] = $row['function'];
                    $product[$ky]['reference'] = $row['reference'];
                    $product[$ky]['entry_name'] = $row['entry_name'];
                    $product[$ky]['name'] = $row['name'];
                    
                    $product[$ky]['other_name'] = $row['other_name'];
                    $product[$ky]['geneid'] = $row['geneid'];
                    $product[$ky]['is_reviewed'] = $row['is_reviewed'];
                    if ($row['reference'])
                    {
                        $refer_arr = @unserialize($row['reference']);
                        $str = '';
                        $i = 0;
                        if ($refer_arr)
                        {
                            foreach ($refer_arr as $v)
                            {
                                if (!$v['AUTHORS'])
                                    continue;
                                if ($i < 5)
                                {
                                    $str.= $v['AUTHORS'][0] . ',et al.' . $v['JOURNAL'] . "\r\n";
                                    $i++;
                                }
                            }
                        }
                        $product[$ky]['reference'] = $str;
                    }
                }
            }
        }
        $exprot_cols = array( 'uniprot_id' => 'uniprot_id','name' => 'name','entry_name' => 'entry_name','geneid' => 'geneid', 'antigen_source' => 'antigen_source', 'is_reviewed' => 'is_reviewed', 'function' => 'function', 'reference' => 'reference', 'sequence' => 'sequence', 'other_name' => 'other_name');
        
        export_excel($exprot_cols, $product, '__specices','C:\Users\li_hao\Desktop/');
    }

    public function uniprot_collection()
    {
        $data = file_get_contents("C:\Users\li_hao\Desktop/1.txt");
        $data_arr = explode('@', $data);
        $c = 1;
        $product = array();
        foreach ($data_arr as $uniprot)
        {
            if (!empty($uniprot))
            {

                $arr = array();
                $url = "http://www.uniprot.org/uniprot/" . trim($uniprot);
                //read content
                $content = format_html($url);
                $arr['uniprot'] = trim($uniprot);
                preg_match('#<ul class="noNumbering subcellLocations">(.*)</ul#iUs', $content, $step1_arr);
                if (count($step1_arr))
                {
                    preg_match_all('#<li>(.*)</li>#iUs', $step1_arr[1], $step1_arr_all);
                    if ($step1_arr_all)
                    {
                        foreach ($step1_arr_all[1] as $k => $v)
                        {
                            $arr['li_' . $k] = strip_tags($v);
                        }
                    }
                    $step2_arr = array();
                    preg_match('#<span property="schema:text">(.*)</span>#iUs', $step1_arr[1], $step2_arr);
                    if ($step2_arr)
                    {
                        $arr['note'] = strip_tags($step2_arr[1]);
                    }
                }
                $product[$i] = $arr;
            }
            $i++;
        }

        $excel_cols['uniprot'] = "uniprot";
        for ($i = 0; $i < 10; $i++)
        {
            $excel_cols['li_' . $i] = "li_" . $i;
        }
        $excel_cols['note'] = "note";
        export_excel($excel_cols, $product, 'uniprot_info_location_' . time());
    }

    public function lsbio_collection()
    {
        $data = file_get_contents("C:\Users\li_hao\Desktop/lsbio.txt");
        $data_arr = explode('http', $data);
        $product = array();
        foreach ($data_arr as $cc => $url)
        {
            //read content
            if ($url)
            {
                $product[$cc]['url'] = trim('http' . $url);
                $content = format_html($product[$cc]['url']);
                preg_match_all('#<table class="srcSitecss_AntibodyDetailsRightColumn_ImageCaption">(.*)<\/table>#iUs', $content, $step1_arr);
                if (count($step1_arr))
                {
                    foreach ($step1_arr[1] as $k => $v)
                    {
                        $step1_arr_all = array();
                        preg_match_all('#<img src="/image2/(.*)"alt="(.*)"class="(.*)"/>#iUs', $v, $step1_arr_all);

                        if (count($step1_arr_all))
                        {
                            $product[$cc]['image_' . $k] = $step1_arr_all[1][0];
                        }
                    }
                }
            }
        }

        $cols_arr = array('url' => 'url');
        for ($i = 0; $i < 10; $i++)
        {
            $cols_arr['image_' . $i] = 'image_' . $i;
        }
        export_excel($cols_arr, $product, '--');
    }

}
