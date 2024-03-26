<?php
include_once $_SERVER['DOCUMENT_ROOT']."/lib/siteProperty.php";
include_once $_SERVER['DOCUMENT_ROOT']."/lib/util/function.php";

if (isset($_GET['code']) && isset($_GET['message'])) {
    $errorCode = $_GET['code'];
    $errorMessage = $_GET['message'];

    // 에러 메시지 처리
    echo "에러 코드: " . $errorCode . "<br>";
    echo "에러 메시지: " . $errorMessage;
} else {
    // 에러 정보가 전달되지 않은 경우에 대한 처리
    echo "결제 요청 실패: 에러 정보가 전달되지 않았습니다.";
}
exit();

echo returnHistory('요청처리중 장애가 발생하였습니다.');

