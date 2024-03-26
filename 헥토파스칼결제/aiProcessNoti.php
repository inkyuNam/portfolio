<?php
/** 노티를 성공적으로 수신한 경우 처리할 로직을 작성하여 주세요. */
function noti_success($noti){
    /* TODO : 관련 로직 추가 */
    $hecto = new Hecto(0, 'orders', $_REQUEST);
    $order = new AiOrders(1, 'ai_orders', $_POST);
    $design = new Design(0, 'design', $_POST);

    $jsonData = json_encode($noti, JSON_UNESCAPED_UNICODE);
    $hecto -> insert_payment_log(330, $jsonData);

    /*============================================================================================================================================
     * DB 저장
     *============================================================================================================================================ */
    if($noti['trdStatus'] == '0021'){
        $req = array();
        /** 공통파라미터 */
        $req['member_fk'] = $noti['mem_fk'];
        $req['oid'] = $noti['mchtNo'];
        $req['name'] = $noti['mem_name'];
        $req['cell'] = $noti['mem_phone'];
        $req['email'] = $noti['mem_email'];
        $req['price'] = ltrim($noti['trdAmount'], '0');
        $req['order_at'] = date("Y-m-d H:i:s");
        $req['device'] = $order->getMobileChk();
        $req['order_ip'] = $_SERVER["REMOTE_ADDR"];
        $req['tid'] = $noti['trdNo'];

        /** 신용카드 */
        if($noti['method'] == 'CA'){
            $req['payment'] = 1;
            $req['state'] = 1;
            $req['pay_at'] = date("Y-m-d H:i:s");
            $req['cardCode'] = $noti['cardCd'];
            $req['cardName'] = $noti['cardName'];
            $req['cardQuota'] = $noti['cardQuota'];
            /** 가상계좌 */
        }else if($noti['method'] == 'VA'){
            $req['payment'] = 2;
            $req['state'] = 0;
            $req['vbankNum'] = $noti['가상계좌번호'];
            $req['vbankExpDate'] = date('Y-m-d H:i:s', strtotime($noti['가상계좌 입금만료일시']));
        }

        $r = $order->insert($req);

        if($r > 0){

            /*=========================================================================================
             * ai 건축 설계권 발급
             *========================================================================================= */
            $issuance = $design->insert($r,$req);

            if($issuance) {

                $cell = $noti['phone'];
                $msg = "[".COMPANY_NAME."]\n";
                $msg .= "<".$noti['productName']."> 결제가 완료되었습니다.";

            }else{

                $cell = $noti['phone'];
                $msg = "[".COMPANY_NAME."]\n";
                $msg .= "<".$noti['productName']."> 설계권이 정상적으로 발급되지 않았습니다.\n";
                $msg .= "관리자에게 문의하세요.";

            }

            sendSms($cell, $msg, "SMS");

        }

    }

    return true;
}

/** 입금대기시 처리할 로직을 작성하여 주세요. */
function noti_waiting_pay($noti){
    $hecto = new Hecto(0, 'orders', $_REQUEST);
    /* TODO : 관련 로직 추가 */
    $jsonData = json_encode($noti, JSON_UNESCAPED_UNICODE);
    $hecto -> insert_payment_log(1, $jsonData);

    return true;
}

/** 노티 수신중 해시 체크 에러가 생긴 경우 처리할 로직을 작성하여 주세요. */
function noti_hash_error($noti){
    $hecto = new Hecto(0, 'orders', $_REQUEST);
    /* TODO : 관련 로직 추가 */
    $hecto -> insert_payment_log(1, '수신에러');

    return false;
}
?>
