<?php
date_default_timezone_set('Etc/GMT-8');
class ExcelService extends Service
{
	public $objPHPExcel;

	public function __construct()
	{
		$this->init();
	}

	protected function init()
	{
		include_once(SITE_PATH.'/addons/libs/PHPExcel/PHPExcel.php');
		$this->objPHPExcel = new PHPExcel();
	}

	public function export($content, $title = '报表', $SavePath = null)
	{
		$objPHPExcel = $this->objPHPExcel;

		// Set properties
		$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
									 ->setLastModifiedBy("Maarten Balliauw")
									 ->setTitle("Office 2003 XLSX Test Document")
									 ->setSubject("Office 2003 XLSX Test Document")
									 ->setDescription("Test document for Office 2003 XLSX, generated using PHP classes.")
									 ->setKeywords("office 2003 openxml php")
									 ->setCategory("Test result file");

		// Set default font
		$objPHPExcel->getDefaultStyle()->getFont()->setName('宋体');
		$objPHPExcel->getDefaultStyle()->getFont()->setSize(12);

		// Add some data, resembling some different data types
		$trid=1;
		foreach($content as $tr){	
			$tdtotal=0;
			foreach($tr as $td){
				$tdid=(intval($tdtotal/26)>=1)?chr(ord('A')+intval($tdtotal/26)-1).chr(ord('A')+$tdtotal%26):chr(ord('A')+$tdtotal%26);
				$objPHPExcel->getActiveSheet()->setCellValue($tdid.$trid,$td);
				$tdtotal++;
			}
			$trid++;
		}

		//Set Width
		for($i=0;$i<$tdtotal;$i++){
			$tdid=(intval($i/26)>=1)?chr(ord('A')+intval($i/26)-1).chr(ord('A')+$i%26):chr(ord('A')+$i%26);
			$objPHPExcel->getActiveSheet()->getColumnDimension($tdid)->setWidth(12);      
		}

		// Rename sheet
		$objPHPExcel->getActiveSheet()->setTitle($title);	

		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel->setActiveSheetIndex(0);	

		// Save Excel 2003 file
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$SavePath  = $SavePath ? $SavePath : ROOT_PATH . '/sites/3i/data/' . date('YmdHis').'.xls';
		$objWriter->save($SavePath);

		return $SavePath;
	}

	public function import()
	{
		return false;
	}

    //运行服务，系统服务自动运行
	public function run(){

	}

	public function _start() {

	}

	public function _stop() {

	}

	public function _install() {

	}

	public function _uninstall() {

	}
}