<?php
function checkpower($powerId=0){
	if($_SESSION['admin']['power']==1){
		return 0;
	}
	if(in_array($powerId,$_SESSION['admin']['power'])){
		return 0;
	}
}

function loadExcelContent($uploadfile){
	set_time_limit(0);

	$uploadfile=$_SERVER['DOCUMENT_ROOT'].$uploadfile;
	$fileinfo=pathinfo($uploadfile);
	$type=$fileinfo['extension'];

	if (file_exists($uploadfile)) {
		Vendor('PHPExcel.PHPExcel.IOFactory');
		if ($type == 'xlsx') {
			$reader = \PHPExcel_IOFactory::createReader('Excel2007');
		} else if ($type == 'csv') {
            $reader = \PHPExcel_IOFactory::createReader('CSV');
        }else if($type=='xls'){
            $reader = \PHPExcel_IOFactory::createReader('Excel5');
		} else {
			die('Not supported file types!');
		}

		$PHPExcel = $reader->load($uploadfile);

		$objWorksheet = $PHPExcel->getActiveSheet();

		$highestRow = $objWorksheet->getHighestRow();
		$highestColumn = $objWorksheet->getHighestColumn();
		$arr = array(
			0 => 'prodNO',
			1 => 'prodName',
			2 => 'keyword',
			3 => 'prodInfo',
			4 => 'paking',
			5 => 'wbi',
			6 => 'proTypeId',
			7 => 'price1',
			8 => 'mqq',
			9 => 'isNew',
			10 => 'isHot',
			11=>'totalSale'
		);


		$res = array();
		for ($row = 2; $row <= $highestRow; $row++) {
			$data=array();
			$is_empty=false;
			for ($column = 0; $column<count($arr); $column++) {
				$val = $objWorksheet->getCellByColumnAndRow($column, $row)->getValue();
				if(empty($val)&&(in_array($column,array(0,1,4)))){
					$is_empty=true;
					break;
				}
				$data[$arr[$column]] = $val;
			}
			if($is_empty==false) {
				$data['create_time'] = time();
				$res[] = $data;
			}
		}
		return $res;
	}else{
		return false;
	}
}