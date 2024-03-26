<?php
include_once $_SERVER['DOCUMENT_ROOT']."/lib/siteProperty.php";
include_once $_SERVER['DOCUMENT_ROOT']."/lib/util/function.php";
include_once $_SERVER['DOCUMENT_ROOT']."/lib/order/Orders.class.php";
include_once $_SERVER['DOCUMENT_ROOT']."/lib/point/Point.class.php";

$order = new Orders(0, "orders", $_REQUEST);

$credential = base64_encode(TOSS_SECRET_KEY . ':');

$payment_key = $_REQUEST['paymentKey']; // PAYMENT_KEY 변수에는 실제 값을 넣어주셔야 합니다.
$amount = $_REQUEST['amount'];
$orderId = $_REQUEST['orderId'];
// 주문 내용 order 테이블에 저장

// 쿠키 값 읽어오기
$toss_payment_info = isset($_COOKIE['toss_payment_info']) ? $_COOKIE['toss_payment_info'] : null;

// JSON 문자열을 PHP 배열로 변환
$order_info = json_decode($toss_payment_info, true);

// 주문 금액이 일치하는지 확인
if($amount != $order_info['price']){
    echo returnHistory('[시스템에러]주문 금액이 일치하지 않습니다.');
}

// order 테이블에 insert 하기
$return_no = $order->insert_toss_payment($order_info);

// order 테이블에 데이터 유효성 검사
if (!$return_no) {
    echo returnHistory('요청처리중 장애가 발생하였습니다.');
}

$data = array(
    'paymentKey' => $payment_key,
    'amount' => $amount,
    'orderId' => $orderId
);

$data_string = json_encode($data);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.tosspayments.com/v1/payments/confirm');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Basic ' . $credential,
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data_string))
);

$response = curl_exec($ch);
$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if($status_code == 200) {
    $response_array = json_decode($response, true);

    // 결제처리 완료 및 포인트 지급 처리
    $price = $response_array['totalAmount']; // 결제금액
    $payment = $response_array['method']; // 결제수단

    switch($payment){
        case '가상계좌': $payment_type = 1; break;
        case '카드': $payment_type = 2; break;
        case '계좌이체': $payment_type = 3; break;
        case '문화상품권': $payment_type = 4; break;
        case '도서문화상품권': $payment_type = 4; break;
        case '게임문화상품권': $payment_type = 4; break;
    }

    // 입금상태 업데이트
    if($payment_type !== ''){
        $order->update_payment_type($return_no, $payment_type);
    }


    if($response_array['method'] === '가상계좌'){
        echo returnURLMsg('complete.php?price='.$price.'&name='.$order_info['name'], '주문처리 되었습니다.');
        exit();
    }

    /*
    | ----------------------------------------------------------------------------------------
    | Default Parameters
    | ----------------------------------------------------------------------------------------
    */
    $orderno		= $return_no;				//결제번호
    $memberno		= $order_info['no'];			//회원번호
    $changestate	= 1;		//변경주문상태 0:입금대기 1:결제완료 2:결제취소

    $toplace = 2;
    $toadmin = "코코웹툰";

    /*
    | ----------------------------------------------------------------------------------------
    | 충전/차감 처리
    | ----------------------------------------------------------------------------------------
    */

    $dbobj = new Point(1,'','');

    $result = $dbobj->setOrderStateChange($orderno,$memberno,$changestate,$toplace,$toadmin);

    $jsonarray = array("code" => $result);
    // echo json_encode($jsonarray);

    /*
    | ----------------------------------------------------------------------------------------
    | 에러처리
    | ----------------------------------------------------------------------------------------
    */
    function error_disp($errorcode){
        $jsonarray = array("code" => $errorcode);

        $error_log = json_encode($jsonarray);

        $order->error_log_insert($error_log, $orderno);

        echo returnHistory('포인트 지급 에러 오류내용 : '.$jsonarray .'관리자에게 문의해주세요.');
        exit;
    }


    echo returnURLMsg('complete2.php?price='.$price.'&payment='.$payment , '결제가 완료되었습니다.');
} else {
    echo returnHistory('요청처리중 장애가 발생하였습니다.');
}


