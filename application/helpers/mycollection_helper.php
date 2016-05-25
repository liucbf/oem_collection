<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
//多行转一行
function mutiple_2_row($path, $save_file_name = 'test')
{
    $export_data = $excel_data = $export_arr = array();
    $types = pathinfo($path, PATHINFO_EXTENSION);
    if (!in_array($types, array('xls', 'xlsx')))
    {
        exit('File type error!');
    }

    $excel_data = reade_excel($path, '.' . $types);
    $cols = $excel_data[0];
    unset($excel_data[0]);
    foreach ($excel_data as $k=>$v )
         {
             if($v[0] !='' )
             {
                 if( stripos($v[0], ',') !== FALSE ) 
                 {
                     $arrs = explode(',', $v[0]);
                     foreach ($arrs as $vs)
                     {
                         if($vs !='')
                         {
                              $product[$vs][$cols[1]][] = $v[1];
                              $product[$vs][$cols[2]][] = $v[2];
                              $product[$vs][$cols[3]][] = $v[3];
                         }
                     }
                 }
                 else{
                    $product[$v[0]][$cols[1]][] = str_replace(',', '', $v[1]);
                 }
                $product[$v[0]][$cols[2]][] = $v[2];
                $product[$v[0]][$cols[3]][] = $v[3];
             }
         }
       $k = 0;
       $proddd = array();
       foreach ($product as $val => $vv)
       {
            $proddd[$k][$cols[0]] = $val;
             $j = 1;
             for($i =0 ;$i<count($vv[$cols[1]]);$i++)
             {
                   $proddd[$k][$cols[1].'_'.$j] = $vv[$cols[1]][$i];
                   $proddd[$k][$cols[2].'_'.$j] = $vv[$cols[2]][$i];
                   $proddd[$k][$cols[3].'_'.$j] = $vv[$cols[3]][$i];
                   $j++;
             }
          //   $proddd[$k] = array_unique($proddd[$k] );
//             $d = 0;
//             foreach($proddd[$k]  as $cc )
//             {
//                  if($cc != '')
//                  {
//                    $proddd[$k][$cols[0].'_'.$d] = $cc;
//                  }
//                  $d++;
//             }
             $k++;
       }  
       
       $export_cols = array( $cols[0] => $cols[0] );
       for($l=1;$l<30;$l++)
        {
           $export_cols[$cols[1].'_'.$l] = $cols[1].'_'.$l ;
           $export_cols[$cols[2].'_'.$l] = $cols[2].'_'.$l ;
           $export_cols[$cols[3].'_'.$l] = $cols[3].'_'.$l ;
        
        }
        export_excel($export_cols,$proddd, __FUNCTION__.time());
}

function set_image_type_from_legend($path, $save_file_name = 'test')
{
    $export_data = $excel_data = $export_arr = array();
    $types = pathinfo($path, PATHINFO_EXTENSION);
    if (!in_array($types, array('xls', 'xlsx')))
    {
        exit('File type error!');
    }

    $excel_data = reade_excel($path, '.' . $types);
    $cols = $excel_data[0];
    unset($excel_data[0]);
    foreach ($excel_data as $k => $item)
    {
        $j = 1;
        foreach ($item as $kk => $v)
        {
            if ($v)
            {
                $type = chk_get_type($v);
                $export_data[($k - 1)]['type_' . $j] = $type;
                $j++;
            }
        }
    }
    for ($i = 1; $i < (count($cols) + 1); $i++)
    {
        $export_arr['type_' . $i] = 'type_' . $i;
    }
    export_excel($export_arr, $export_data, $save_file_name);
}

//根据字符串，输出他对应的图片类型以第一次匹配的结果为主
function chk_get_type($string = 'wbsss')
{
    if ($string == '')
    {
        return '';
    }
    $type_arr = array(
        'wb' => "western|wb|immunoblot|western blot|westernblot",
        'elisa' => "elisa",
        'if' => "immunofluorescence",
        'ip' => 'immunoprecipitation',
        'ihc' => "ihc|immunohistochemi",
        'fc' => "flow|flow cytometry|flow cyt|flow-cyt|fc",
        'icc' => "immunocytochemi|icc",
        'pa' => "protein assay|protein array",
        'block' => "block|inhibition|inhibition|neu",
        'iem' => "iem|electron microscopy|immunoelectron microscopy",
        'funcs' => "functional|funcs",
        'ria' => "radioimmunoassay",
        'gs' => "gel shift|emsa",
        'chip' => "chromatin immunoprecipitation|chip",
        'rppa' => "rppa",
        'db' => 'dot blot|dotblot',
        'other' => "other",
    );
    foreach ($type_arr as $type_nam => $type_names)
    {
        $type_v = explode('|', $type_names);
        foreach ($type_v as $v)
        {
            if (stripos($string, $v, 0) !== FALSE)
            {
                return strtoupper($type_nam);
            }
        }
    }
    return 'N/A';
}

//g根据type和catalog给图片命名，模板为rename_image_name_by_catalog_and_type.xls
function rename_image_name_by_catalog_and_type($path, $save_file_name = 'test')
{
    
    $types = pathinfo($path, PATHINFO_EXTENSION);
    if (!in_array($types, array('xls', 'xlsx')))
    {
        exit('File type error!');
    }
    $excel_data = reade_excel($path, '.' . $types);

    $cols = $excel_data[0];
    unset($excel_data[0]);
    foreach ($excel_data as $k => $item)
    {
        $catalog = $item[0];
        unset($item[0]);
        $type = get_type_num($item);
        $type_arr = array();
        foreach ($item as $kk => $item_val)
        {
            if (( $item_val != '' ) && ( in_array($item_val, array_keys($type)) ))
            {
                $type_arr['image_' . $kk] = $catalog . '_' . str_replace(array($item_val), array_values($type[$item_val]), $item_val) . ".jpg";
                array_shift($type[$item_val]);
            }
        }
        $export_data[($k - 1)] = $type_arr;
    }
    for ($i = 1; $i < (count($cols)); $i++)
    {
        $export_arr['image_' . $i] = 'image_' . $i;
    }
    
    export_excel($export_arr, $export_data, $save_file_name.time());
}

//获取每个type的数目，并且加上catalog_type_i(i为自然数).jpg
function get_type_num($item)
{
    $ip = $pa = $icc = $ihc = $block = $iem = $funcs = $ria = $gs = $chip = $rppa = $other = $fc = $wb = $elisa = $if = $ha = $ihc_f = $ihc_p=0;
    $type_num = array();
    foreach ($item as $item_v)
    {
        switch ($item_v)
        {
            case 'WB': $wb ++;
                $type_num['WB'][] = $item_v . "_" . ($wb);
                break;
            case 'ELISA':
            case 'E':    
                $elisa ++;
                $type_num['E'][] = $item_v . "_" . ( $elisa);
                break;
            case 'IF':$if ++;
                $type_num['IF'][] = $item_v . "_" . ($if);
                break;
            case 'IP':$ip ++;
                $type_num['IP'][] = $item_v . "_" . ($ip);
                break;
            case 'IHC':$ihc ++;
                $type_num['IHC'][] = $item_v . "_" . ($ihc);
                break;
             case 'IHC-P':$ihc_p ++;
                $type_num['IHC-P'][] = $item_v . "_" . ($ihc_p);
                break;
             case 'IHC-F':$ihc_f ++;
                $type_num['IHC-F'][] = $item_v . "_" . ($ihc_f);
                break;
            case 'FC':$fc ++;
                $type_num['FC'][] = $item_v . "_" . ($fc);
                break;
            case 'ICC':$icc ++;
                $type_num['ICC'][] = $item_v . "_" . ($icc);
                break;
            case 'PA':$pa ++;
                $type_num['PA'][] = $item_v . "_" . ($pa);
                break;
            case 'BLOCK':$block ++;
                $type_num['BLOCK'][] = $item_v . "_" . ($block);
                break;
            case 'IEM':$iem ++;
                $type_num['IEM'][] = $item_v . "_" . ($iem);
                break;
            case 'FUNCS':$funcs ++;
                $type_num['FUNCS'][] = $item_v . "_" . ( $funcs);
                break;
            case 'RIA':$ria ++;
                $type_num['RIA'][] = $item_v . "_" . ( $ria);
                break;
            case 'GS':$gs ++;
                $type_num['GS'][] = $item_v . "_" . ( $gs);
                break;
            case 'CHIP':$chip ++;
                $type_num['CHIP'][] = $item_v . "_" . ( $chip);
                break;
            case 'RPPA':$rppa ++;
                $type_num['RPPA'][] = $item_v . "_" . ( $rppa);
                break;
            case 'HA':
            case 'Hemagglutinin':$ha ++;
                $type_num['HA'][] = $item_v . "_" . ($ha);
                break;
            default :$other ++;
                $type_num['OTHER'][] = $item_v . "_" . ($other);
                break;
        }
    }
    return $type_num;
}

/**
 * 重新命名图片以catalog_type_1.jpg的格式，模板文件：change_image_name_by_catalog | 默认是前面20列，后面20列，中间隔一列
 * @param string $path 模板的路径，$origin_img_file_path未改名字的图片所在的上级目录，$change_img_file_path需要放到的最新的修改名称后的img地址
 * @param return  导出excel,失败的会输出在页面
 * @return object	 
 */
function change_image_name_by_catalog($path, $origin_img_file_path, $change_img_file_path)
{
    $type = pathinfo($path, PATHINFO_EXTENSION);
    if (!in_array($type, array('xls', 'xlsx')))
    {
        exit('File type error!');
    }
    $excel_data = reade_excel($path, '.' . $type);
    $file_path = $origin_img_file_path;
    $file_change_path = $change_img_file_path;
    unset($excel_data[0]);
    $i = 0;
    foreach ($excel_data as $k => $v)
    {

        if ($v[21])
        {
            for ($jj = 21; $jj < 40; $jj++)
            {
                if ($v[$jj])
                {
                    $file = pathinfo( $v[$jj],PATHINFO_BASENAME );
                    $file_arr =  explode('.', $file);
                    $local_name = $file_arr[0].'.'.$file_arr[1];//需要去除此时ps产生的空格被横线替换
                   
                    if (!@copy($file_path . $local_name, $file_change_path . $local_name))
                    {
                        echo '(' . $v[$jj] . ') copy failed- ' . $jj . ' end !'.$v[$jj - 21].'<br>';
                    }
                    else
                    {
                        if (rename($file_change_path . $local_name, $file_change_path . $v[$jj - 21]) == FALSE)
                        {
                            echo '(' . $v[$jj] . ')  rename to'.$v[$jj - 21].' failed!'.$v[$jj - 21].'<br>';
                        }
                    }
                }
            }
        }
    }
}

/**
 * 替换一些字段里面原有的catalog信息replace_catalog
 * @param string $path 模板的路径，$origin_img_file_path未改名字的图片所在的上级目录，$change_img_file_path需要放到的最新的修改名称后的img地址
 * @param return  导出excel,失败的会输出在页面
 * @return object	 
 */
function replace_catalog($path,$save_file_name = 'your-file')
{
    $type = pathinfo($path, PATHINFO_EXTENSION);
    if (!in_array($type, array('xls', 'xlsx')))
    {
        exit('File type error!');
    }
    $excel_data = reade_excel($path, '.' . $type);
    $field_arr = $excel_data[0];
    unset($excel_data[0]);
    foreach ($excel_data as $k => $row)
    {
        $rows = array();
        $rows[$field_arr[0]] = $row[0];
        $rows[$field_arr[1]] = $row[1];
        for ($i = 2; $i < count($row); $i++)
        {
            if (strlen($row[$i]))
            {
                $rows[$field_arr[$i]] = str_replace($row[1], $row[0], $row[$i]);
            }
        }

        $export_data[($k - 1)] = $rows;
    }

    $export_arr = array_combine(array_values($field_arr), $field_arr);
    export_excel($export_arr, $export_data, $save_file_name);
}

//对路径简单64编码加密
function encode_path($path)
{
    if (!file_exists($path))
    {
        exit("Error path txt:" . $path);
    }
    return base64_encode($path);
}

//读取txt文件的url数据，返回数组
function my_get_txt_data($path)
{
    $product_arr = array();
    $contents = file_get_contents($path);
    $product_arr = explode('http', $contents);
    unset($product_arr[0]);
    return $product_arr;
}
function format_html($url)
{
         $content = file_get_contents($url);
        $content = compress_html($content);
        return $content;
}
/**
 * 清除网页的特殊标记
 * @param type $string
 * @return type
 */
function compress_html($string)
{
    $string = str_replace("\r\n", '', $string); //清除换行符
    $string = str_replace("\n", '', $string); //清除换行符
    $string = str_replace("\t", '', $string); //清除制表符
   
    $pattern = array(
        "/> *([^ ]*) *</", 
        "/[\s]+/",
      //  "/<!--[^!]*-->/",//去掉注释标记
        "/\" /",
        "/ \"/",
       "'/\*[^*]*\*/'"
    );
    $replace = array(
        ">\\1<",
        " ",
     //   "",
        "\"",
        "\"",
        "",
    );
    return preg_replace($pattern, $replace, $string);
}

//把网址图片转为相对图片地址
function image_url_format($file_name)
{
    $file_name_arr = explode('/', $file_name);
    $count = count($file_name_arr) - 1;
    return $file_name_arr[$count];
}

//创建和写入csv头部标题
function csv_ck($file_output, $csv_title_row)
{
    $file = fopen($file_output, 'w'); //追加
    flock($file, LOCK_EX);
    fputcsv($file, $csv_title_row);
    flock($file, LOCK_UN);
    fclose($file);
}

//创建错误日志  
function write_csv_log($filename, $data)
{

    if (!file_exists($filename))
    {
        $head_log = "**********************************************\r\n";
        $head_log .= "\t\t\t" . date('Y-m-d') . "\t\t\t\r\n";
        $head_log .= "********************************************\r\n";
        file_put_contents($filename, $head_log);
    }
    $CI = &get_instance();
    $CI->load->helper("file");
    write_file($filename, $data, 'a+');
}

//replace html 
function replace_to_html($str)
{
    return str_replace(array(" &amp;", "&quot;", "&lt;", "&gt;", "&micro;"), array("&", "”", "<", ">", "µ"), strip_tags($str));
}

//
function rename_image($excel_path, $file_origin_path, $file_change_path, $type = '.xls')
{
    $CI = &get_instance();
    $CI->load->helper('common');
    $file_path = $file_origin_path;
    $file_change_path = $file_change_path;
    $excel_data = reade_excel($excel_path, $type);
    unset($excel_data[0]);
    $i = 0;
    foreach ($excel_data as $k => $v)
    {
        if ($v[1])
        {
            if (!file_exists($file_path . $v[1]))
            {
                echo '[' . $v[0] . '] no image<br>';
            }
            else
            {

                if ($v[2])
                {

                    for ($jj = 1; $jj < (count($v)); $jj++)
                    {
                        if ($v[$jj])
                        {
                            if (!@copy($file_path . $v[$jj], $file_change_path . $v[$jj]))
                            {
                                echo '(' . $v[0] . ') copy failed- ' . $jj . ' end !<br>';
                            }
                            else
                            {
                                if (rename($file_change_path . $v[$jj], $file_change_path . $v[0] . '_' . $jj . '.jpg') == FALSE)
                                {
                                    echo '(' . $v[0] . ')  rename failed!<br>';
                                }
                            }
                        }
                    }
                }
                else
                {
                    if (!@copy($file_path . $v[1], $file_change_path . $v[1]))
                    {
                        echo '(' . $v[0] . ') copy failed';
                    }
                    else
                    {
                        if (rename($file_change_path . $v[1], $file_change_path . $v[0] . '.jpg') == FALSE)
                        {
                            echo '(' . $v[0] . ')  rename failed!<br>';
                        }
                    }
                }
            }
        }
    }
}

//return content
function get_content_html_format($url)
{
    $content = file_get_contents($url);
    return $content = compress_html($content);
}

/* * ******pdf helper******* */
if (!function_exists('export_pdf'))
{

    function export_pdf($prod_title, $subject, $html, $file_name)
    {
        $CI = &get_instance();
        $CI->load->add_package_path(APPPATH . 'third_party/tcpdf/');
        $CI->load->library('tcpdf');
        $CI->tcpdf->SetCreator(PDF_CREATOR);
        $CI->tcpdf->SetAuthor('The Wuxibiosciences Team');
        $CI->tcpdf->SetTitle($prod_title);
        $CI->tcpdf->SetSubject($subject);
        $CI->tcpdf->SetKeywords('Wuxibiosciences');
        //remove default header/footer
        $CI->tcpdf->setPrintHeader(false);
        $CI->tcpdf->setPrintFooter(false);
        // set default monospaced font
        $CI->tcpdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        //set margins
        $CI->tcpdf->SetMargins(20, 10, 20);

        //set auto page breaks
        $CI->tcpdf->SetAutoPageBreak(TRUE, 15);

        //set image scale factor
        $CI->tcpdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set font
        $CI->tcpdf->SetFont('dejavusans', '', 9);

        // add a page
        $CI->tcpdf->AddPage();

        // output the HTML content
        $CI->tcpdf->writeHTML($html, true, false, true, false, '');

        // REMOVE TAG TOP AND BOTTOM MARGINS
        $tagvs = array('p' => array(0 => array('h' => 0, 'n' => 0), 1 => array('h' => 0, 'n' => 0)));
        $CI->tcpdf->setHtmlVSpace($tagvs);

        // SET LINE HEIGHT
        $CI->tcpdf->setCellHeightRatio(1.25);

        // CHANGE THE PIXEL CONVERSION RATIO
        $CI->tcpdf->setImageScale(0.47);

        // reset pointer to the last page
        $CI->tcpdf->lastPage();

        //Close and output PDF document
        $CI->tcpdf->Output($file_name . '.pdf', 'I');
    }

}

/**
 * reade file contents
 * @param type $file file path and name
 * @param type $type file type xls,xlsx
 * @return type 
 */
function reade_excel($file, $type)
{
    
    
    $CI = &get_instance();
    $CI->load->add_package_path(APPPATH . 'third_party/phpexcel/');
    $CI->load->library('phpexcel2');
    $_excel = $CI->phpexcel2->get_excel_instance($type);
    $_excel->setReadDataOnly(true); // Not read styles
    $excel = $_excel->load($file); //excel
    $data = $excel->getActiveSheet()->toArray();
    return $data;
}

/**
 * export to excel
 * @param type $fields .export fields
 * @param type $datas .exoprt data
 * @param type $file_name . file name
 */
function export_excel($fields = array(), $datas = array(), $file_name = '.xlsx',$save_path = '')
{
    $file_name = $file_name . '.xlsx';
    if (!empty($fields))
    {
        $CI = &get_instance();
        $CI->load->add_package_path(APPPATH . 'third_party/phpexcel/');
        $CI->load->library('phpexcel2');
        $writer = $CI->phpexcel2->get_write_excel_instance('.xlsx', $CI->phpexcel2);
        //$writer->setOffice2003Compatibility(TRUE);
        $objProps = $CI->phpexcel2->getProperties();
        $objProps->setCreator("Abgent");
        $objProps->setLastModifiedBy("Abgent");
        $objProps->setTitle($file_name);
        $objProps->setSubject($file_name);
        $objProps->setDescription("Abgent ppd");
        $objProps->setKeywords("Abgent ppd");
        $objProps->setCategory("$file_name");
        $CI->phpexcel2->setActiveSheetIndex(0);
        $objActSheet = $CI->phpexcel2->getActiveSheet();
        $i = 0;
        foreach ($fields as $key => $value)
        {
            $objActSheet->setCellValueByColumnAndRow($i, 1, $value);
            $i++;
        }
        foreach ($datas as $key2 => $row)
        {
            $i = 0;
            foreach ($fields as $key => $value)
            {
                if (isset($row[$key]))
                {
                    $objActSheet->setCellValueByColumnAndRow($i, $key2 + 2, @$row[$key]);
                }
                else
                {
                    $objActSheet->setCellValueByColumnAndRow($i, $key2 + 2, '');
                }
                $i++;
            }
        }
          if(!empty($save_path))
        {
            $writer->save($save_path.$file_name);
        }
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="' . $file_name . '"');
        header("Content-Transfer-Encoding: binary");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $writer->save('php://output');
    }
}

function import_excel($config, $uplod_file_name = 'userfile')
{

    $CI = &get_instance();

    $CI->load->library('upload');
    $CI->upload->initialize($config);
    if ($CI->upload->do_upload($uplod_file_name))
    {

        return $CI->upload->data();
    }
    else
    {
        return $CI->upload->display_errors();
    }
}
/**
 *
 * @staticvar int $i
 * @param type $fileds=array('filed_name'=>'col_header_name')
 * @param type $file_name
 * @param type $datas the data for export
 */
function export_cvs($fileds = array(),$datas = array(),$file_name = 'test') {
    if(!empty($fileds)){


        $f = fopen('php://output', 'w');
        if (isset($f) && is_resource($f)) {
            header("Expires: Mon, 1 Apr 1974 05:00:00 GMT");
            header("Last-Modified: " . gmdate("D,d M Y H:i:s") . " GMT");
            header("Cache-Control: no-store, no-cache");
            header("Content-Type: application/force-download");
            header("Content-type: application/csv");
            header("Content-Disposition: attachment; filename=\"" . $file_name . ".csv\"");
        }
        // create column labels at header
        $row = array();
        foreach ($fileds as $c => $col) {
            $row[] = $col;
        }

        if (isset($f) && is_resource($f))
            fputcsv($f, $row, ',', '"');

        $row_index = 0;
        while ($row = @$datas[$row_index]) {

            // create data cells
            $csv_row = array();
            foreach ($fileds as $c => $col) {

               $out = stripslashes(@$row[$c]);
                // create cell
                if (is_array($out))
                    $out = stripslashes(implode('', $out));

                $csv_row[] = $out;
                unset($out);
            }

            if (isset($f) && is_resource($f)) {
                fputcsv($f, $csv_row, ',', '"');
                flush();
            }

            $row_index++;
        }
        // create table and show
        if (isset($f) && is_resource($f)) {
            fclose($f);
        }
    }
}
/**
 * 获取请求ip
 *
 * @return ip地址
 */
function ip()
{
    if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown'))
    {
        $ip = getenv('HTTP_CLIENT_IP');
    }
    elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown'))
    {
        $ip = getenv('HTTP_X_FORWARDED_FOR');
    }
    elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown'))
    {
        $ip = getenv('REMOTE_ADDR');
    }
    elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown'))
    {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return preg_match('/[\d\.]{7,15}/', $ip, $matches) ? $matches [0] : '';
}

function download_imgs( $file_txt,$origin_url,$local_path )
{
    download_remote_file_with_file_put_contents($origin_url, $local_path);
}
function download_remote_file_with_file_put_contents( $origin_url , $local_path )
{
    $image_name = basename($origin_url);
    if( !($data = file_get_contents($origin_url)) )
    {
        exit( $origin_url .' download error!');
    }
    if(! file_put_contents($local_path.$image_name, $data))
    {
        exit( $origin_url .'download error!' );
    }
}

function download_remote_file_with_curl($origin_url, $local_path)
{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_POST, 0); 
		curl_setopt($ch,CURLOPT_URL,$origin_url); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		$file_content = curl_exec($ch);
		curl_close($ch);

        $downloaded_file = fopen($local_path, 'w');
		fwrite($downloaded_file, $file_content);
		fclose($downloaded_file);
 
	}
    
 function  draw_chart()
 {
//  
//     $CI  = &get_instance();
//     $CI->load->add_package_path(APPPATH . 'third_party/pchart/class');
//      /* pChart library inclusions */
//     $CI->load->library('pData');
 include(APPPATH . "third_party/pchart/class/pData.class.php");
 include(APPPATH . "third_party/pchart/class/pDraw.class.php");
 include(APPPATH . "third_party/pchart/class/pRadar.class.php");
 include(APPPATH . "third_party/pchart/class/pImage.class.php"); 
 /* CAT:Polar and radars */
 
  $MyData = new pData();   
$MyData->addPoints(array('37.37','33.91','33.91','36.74','40.48','55.09','49.56'),"ScoreA");  

 $MyData->setSerieDescription("ScoreA","Coverage A");

 /* Define the absissa serie */
 $MyData->addPoints(array(40,80,120,160,200,240,280,320,360),"Coord");
 $MyData->setAbscissa("Coord");

 /* Create the pChart object */
 $myPicture = new pImage(300,300,$MyData);
 $myPicture->drawGradientArea(0,0,300,300,DIRECTION_VERTICAL,array("StartR"=>200,"StartG"=>200,"StartB"=>200,"EndR"=>240,"EndG"=>240,"EndB"=>240,"Alpha"=>100));
 $myPicture->drawLine(0,20,300,20,array("R"=>255,"G"=>255,"B"=>255));
 $RectangleSettings = array("R"=>180,"G"=>180,"B"=>180,"Alpha"=>100);

 /* Add a border to the picture */
 $myPicture->drawRectangle(0,0,299,299,array("R"=>0,"G"=>0,"B"=>0));

 /* Write the picture title */ 
 $myPicture->setFontProperties(array("FontName"=>APPPATH . "third_party/pchart/fonts/Silkscreen.ttf","FontSize"=>6));
 $myPicture->drawText(10,13,"xxxxxxxxxxxxxxxx",array("R"=>255,"G"=>255,"B"=>255));

 /* Set the default font properties 圆弧的字体颜色*/ 
 $myPicture->setFontProperties(array("FontName"=>APPPATH . "third_party/pchart/fonts/Forgotte.ttf","FontSize"=>10,"R"=>0,"G"=>0,"B"=>0));

 /* Enable shadow computing */ 
 $myPicture->setShadow(FALSE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

 /* Create the pRadar object */ 
 $SplitChart = new pRadar();

 /* Draw a radar chart */ 
 $myPicture->setGraphArea(10,25,290,290);
 $Options = array("DrawPoly"=>TRUE,"WriteValues"=>TRUE,"ValueFontSize"=>8,"Layout"=>RADAR_LAYOUT_CIRCLE,"BackgroundGradient"=>array("StartR"=>255,"StartG"=>255,"StartB"=>255,"StartAlpha"=>100,"EndR"=>255,"EndG"=>255,"EndB"=>255,"EndAlpha"=>50));
 $SplitChart->drawPolar($myPicture,$MyData,$Options);

 /* Render the picture (choose the best way) */
 $myPicture->autoOutput("pictures/example.polar.values.png"); 
 }
 
/* End of file excel_helper.php */
/* Location: ./application/helpers/excel_helper.php */