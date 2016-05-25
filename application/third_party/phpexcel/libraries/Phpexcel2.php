<?php

/** PHPExcel root directory */
if (!defined('PHPEXCEL_ROOT')) {
        define('PHPEXCEL_ROOT', dirname(__FILE__) . '/');
        require(PHPEXCEL_ROOT . 'PHPExcel/Autoloader.php');
        require(PHPEXCEL_ROOT . 'Phpexcel.php');
}

/**
 * PHPExcel
 *
 * @category   PHPExcel
 * @package    PHPExcel
 * @copyright  Copyright (c) 2006 - 2012 PHPExcel (http://www.codeplex.com/PHPExcel)
 */
class Phpexcel2 extends Phpexcel {
        public function __construct() {
                parent::__construct();
        }
        public function get_excel_instance($type) {
                if ($type == '.xlsx') {
                        require_once PHPEXCEL_ROOT . 'PHPExcel/Reader/Excel2007.php';
                        return new PHPExcel_Reader_Excel2007();
                } elseif ($type == '.xls') {
                        require_once PHPEXCEL_ROOT . 'PHPExcel/Reader/Excel5.php';
                        return new PHPExcel_Reader_Excel5();
                }
        }

        public function get_write_excel_instance($type,$objExcle) {
                if ($type == '.xlsx') {
                        require_once PHPEXCEL_ROOT . 'PHPExcel/Writer/Excel2007.php';
                        return new PHPExcel_Writer_Excel2007($objExcle);
                } elseif ($type == '.xls') {
                        require_once PHPEXCEL_ROOT . 'PHPExcel/Writer/Excel5.php';
                        return new PHPExcel_Writer_Excel5($objExcle);
                }
        }

}
