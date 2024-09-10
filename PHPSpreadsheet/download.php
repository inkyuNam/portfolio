<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$center = [
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
];

// 왼쪽 정렬 적용
$left = [
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_LEFT,
    ],
];

// 오른쪽 정렬 적용
$right = [
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_RIGHT,
    ],
];

// 테두리 적용
$border = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '000000'], // 테두리 색상을 지정합니다.
        ],
    ],
];

$current_date = (new DateTime())->format("Ymd");
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(7);            // 해당 셀 가로값 설정

$row = 1;
$spreadsheet->getActiveSheet()->mergeCells('A' . $row . ':J' . $row);                   // 셀 병합
$sheet->setCellValue('A'.$row, "제목");

$row ++;
$sheet->setCellValue('A'.$row, "column1");
$sheet->setCellValue('B'.$row, "column2");
$sheet->setCellValue('C'.$row, "column3");
$sheet->setCellValue('D'.$row, "column4");
$sheet->setCellValue('E'.$row, "column5");
$sheet->setCellValue('F'.$row, "column6");
$sheet->setCellValue('G'.$row, "column7");
$sheet->setCellValue('H'.$row, "column8");
$sheet->setCellValue('I'.$row, "column9");
$sheet->setCellValue('J'.$row, "column10");

try {
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="제목_' . $current_date . '.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save('php://output');
} catch (\PhpOffice\PhpSpreadsheet\Writer\Exception $e) {
    echo $e->getMessage();
}