<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

$file = $_FILES['excel_file'];
// 업로드된 파일의 정보 확인
$fileTmp = $file["tmp_name"];

// 파일 타입 검사
$fileNameCmps = explode(".", $file['name']);
$fileExtension = strtolower(end($fileNameCmps));
$allowedFileExtensions = array('xls', 'xlsx');
if (!in_array($fileExtension, $allowedFileExtensions)){
    // redirection 설정
    exit();
}

$spreadsheet = IOFactory::load($fileTmp);

$row = $spreadsheet->getActiveSheet()->toArray();
$maxRows=count($row);//최대로우 구하기

$success_count = 0;
$fail_count = 0;

try {

    for ($i = 2; $i < $maxRows; $i++) {    // 실제 데이터는 2번째 줄부터 시작
        /*
        | ----------------------------------------------------------------------------------------
        | 엑셀파일 읽고 DB 에 저장하기
        | ----------------------------------------------------------------------------------------
        */
        $row[$i][1];
    }

    if ($fail_count === 0) {
        // 정상 업로드 처리
    }else{
        // 업로드 실패 처리
        $fail_count . '개의 데이터가 업로드 실패했습니다.';
    }

} catch (exception $e) {
    echo '엑셀파일을 읽는도중 오류가 발생하였습니다.!';
    exit();
}