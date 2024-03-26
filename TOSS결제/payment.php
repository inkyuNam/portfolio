<?php include_once $_SERVER['DOCUMENT_ROOT']."/include/common.php";
include_once $_SERVER['DOCUMENT_ROOT']."/lib/siteProperty.php";
include_once $_SERVER['DOCUMENT_ROOT']."/lib/util/function.php";
include_once $_SERVER['DOCUMENT_ROOT']."/lib/util/codeUtil.php";
include_once $_SERVER['DOCUMENT_ROOT']."/lib/util/dateUtil.php";
include_once $_SERVER['DOCUMENT_ROOT']."/lib/util/page.php";

include_once $_SERVER['DOCUMENT_ROOT']."/lib/member/Member.class.php";

$member = new Member(9999, "member", $_REQUEST);
if(!$loginCheck){
    mloginConfirmURL();
}else{
    $data = $member->getData($_SESSION['member_no']);
}

$p = "mypage";
$sp = 1;
$root = $_SERVER['DOCUMENT_ROOT'];

include_once $root."/header.php";
?>

<script>

    function hideRemove(){
        var val = document.getElementById('telNumber').value;
        var mcell = document.getElementById('memberCell').value;

        if(mcell == '') {
            alert('등록된 전화번호가 없습니다.');
            return false;
        } else {

            $.ajax({
                url : 'sendSms.php',
                type : "post",
                data : {
                    "cmd" : "sms",
                    "cell" : val
                },
                success : function(data){
                    var r = data.trim();
                    //console.log(r);
                    if(r == "1"){
                        alert('성공적으로 발송되었습니다.');
                        $('.telConfirm').removeClass('hide');
                        confirm_interval = setInterval(msg_time, 1000);
                    }else{
                        alert('전송 요청에 실패하였습니다.');
                    }
                }
            });

        }
    }

    //인증번호 유효시간 5분
    var setTime = 299;
    function msg_time() {
        m = Math.floor(setTime / 60) + "분 " + (setTime % 60) + "초"
        var msg = m;
        $('.conf_time').text(m);
        setTime--;
        if(setTime == -1){
            alert('인증시간이 만료되었습니다.');
            $('.limit_txt').text('인증시간이 만료되었습니다.');
            $('input[name=timeSet]').attr('value',"1");
            location.reload();

        }
    }

    function goCertification(frm){
        if(frm.pay_name.value.trim()==''){
            alert('입금자명을 입력해 주세요.');
            return false;
        }

        if(frm.telConfirm.value.trim()==''){
            alert('인증번호를 입력해 주세요.');
            frm.telConfirm.focus();
            return false;
        } else {

            var val = document.getElementById('telConfirm').value;
            var rtn = false;

            $.ajax({
                url : 'numchk.php',
                type : "post",
                data : {
                    "code" : val
                },
                async:false,
                success : function(data){
                    var r = data.trim();

                    if(r == "1"){
                        rtn = true;
                    }else{

                        alert('인증번호가 일치하지 않습니다.');
                        rtn = false;

                    }
                }
            });
            return rtn;
        }

    }
</script>

<!-- 1. 스크립트 추가 -->
<script src="https://js.tosspayments.com/v1/payment-widget"></script>

<script>

    function tossOpen() {

        const paymentType = $("input[name='pay_select']:checked").val();

        var payment_type = 0;

        if (paymentType == '카드')
        {
            var payment_type = 2;
        } else if (paymentType == '계좌이체')
        {
            var payment_type = 5;
        } else if (paymentType == '상품권')
        {
            var payment_type = 6;
        } else if (payment_type == '가상계좌')
        {
            var payment_type = 7;
        }
        console.log(paymentType);

        // ------ 클라이언트 키로 객체 초기화 ------
        var clientKey = '<?= TOSS_CLIENT_KEY ?>'
        var tossPayments = TossPayments(clientKey)
        var price = $('#coco_price').val();
        var point = $('#coco_point').val();
        var customer_id = '<?= $data['id'] ?>'
        var customer_name = '<?= $data['name'] ?>'
        var customer_cell = '<?= $data['cell'] ?>'
        var customer_no = '<?= $data['no'] ?>'
        var order_no = Date.now().toString() + Math.floor(Math.random() * 1000).toString();

        var arr_data = {
            'order' : order_no,
            'id' : customer_id,
            'name' : customer_name,
            'cell' :  customer_cell,
            'no' : customer_no,
            'point' : point,
            'price' : price,
            'payment' : payment_type,
            'device' : 'pc',
            'display' : 1
        };

        var jsonStr = JSON.stringify(arr_data);
        var d = new Date();
        d.setTime(d.getTime() + (10 * 60 * 1000)); // 현재 시간에서 10분 뒤
        document.cookie = "toss_payment_info=" + jsonStr + "; expires=" + d.toUTCString() + "; path=/;";

        // ------ 결제창 띄우기 ------
        tossPayments.requestPayment(paymentType, { // 결제수단 파라미터 (카드, 계좌이체, 가상계좌, 휴대폰 등)
            // 결제 정보 파라미터
            // 더 많은 결제 정보 파라미터는 결제창 Javascript SDK에서 확인하세요.
            // https://docs.tosspayments.com/reference/js-sdk
            amount: price, // 결제 금액
            orderId: order_no, // 주문 ID(주문 ID는 상점에서 직접 만들어주세요.)
            orderName: point + '코코넛', // 주문명
            customerName: customer_name, // 구매자 이름
            flowMode: "DEFAULT",
            successUrl: 'https://www.cocotoon.co.kr/mypage/shop/toss_success.php', // 결제 성공 시 이동할 페이지(이 주소는 예시입니다. 상점에서 직접 만들어주세요.)
            failUrl: 'https://www.cocotoon.co.kr/mypage/shop/toss_fail.php', // 결제 실패 시 이동할 페이지(이 주소는 예시입니다. 상점에서 직접 만들어주세요.)
        })
            // ------결제창을 띄울 수 없는 에러 처리 ------
            // 메서드 실행에 실패해서 reject 된 에러를 처리하는 블록입니다.
            // 결제창에서 발생할 수 있는 에러를 확인하세요.
            // https://docs.tosspayments.com/reference/error-codes#결제창공통-sdk-에러
            .catch(function (error) {
                if (error.code === 'USER_CANCEL') {
                    // 결제 고객이 결제창을 닫았을 때 에러 처리
                } else if (error.code === 'INVALID_CARD_COMPANY') {
                    // 유효하지 않은 카드 코드에 대한 에러 처리
                }
            });
    }

    $(function(){

        // ------ 클라이언트 키로 객체 초기화 ------
        const clientKey = '<?= TOSS_CLIENT_KEY ?>'
        var price = $('#coco_price').val();
        var point = $('#coco_point').val();
        var customer_id = '<?= $data['id'] ?>'
        var customer_name = '<?= $data['name'] ?>'
        var customer_cell = '<?= $data['cell'] ?>'
        var customer_no = '<?= $data['no'] ?>'
        var order_no = Date.now().toString() + Math.floor(Math.random() * 1000).toString();

        var arr_data = {
            'order' : order_no,
            'id' : customer_id,
            'name' : customer_name,
            'cell' :  customer_cell,
            'no' : customer_no,
            'point' : point,
            'price' : price,
            'device' : 'pc',
            'display' : 1
        };

        var jsonStr = JSON.stringify(arr_data);
        var d = new Date();
        d.setTime(d.getTime() + (10 * 60 * 1000)); // 현재 시간에서 10분 뒤
        document.cookie = "toss_payment_info=" + jsonStr + "; expires=" + d.toUTCString() + "; path=/;";

        const customerKey = customer_no // 내 상점의 고객을 식별하는 고유한 키
        const button = document.getElementById("pg_pay")
        // ------  결제위젯 초기화 ------
        // 비회원 결제에는 customerKey 대신 ANONYMOUS를 사용하세요.
        const paymentWidget = PaymentWidget('결제키 등록', customerKey) // 회원 결제
        // const paymentWidget = PaymentWidget(clientKey, PaymentWidget.ANONYMOUS) // 비회원 결제

        // ------  결제위젯 렌더링 ------
        // 결제위젯이 렌더링될 DOM 요소를 지정하는 CSS 선택자 및 결제 금액을 넣어주세요.
        // https://docs.tosspayments.com/reference/widget-sdk#renderpaymentmethods선택자-결제-금액-옵션
        paymentWidget.renderPaymentMethods("#payment-method", price)

        // ------  이용약관 렌더링 ------
        // 이용약관이 렌더링될 DOM 요소를 지정하는 CSS 선택자를 넣어주세요.
        // https://docs.tosspayments.com/reference/widget-sdk#renderagreement선택자
        paymentWidget.renderAgreement('#agreement')

        // ------ '결제하기' 버튼 누르면 결제창 띄우기 ------
        // 더 많은 결제 정보 파라미터는 결제위젯 SDK에서 확인하세요.
        // https://docs.tosspayments.com/reference/widget-sdk#requestpayment결제-정보
        button.addEventListener("click", function () {
            paymentWidget.requestPayment({
                orderId: order_no,            // 주문 ID(직접 만들어주세요)
                orderName: point,			  // 주문명
                successUrl: 'https://www.cocotoon.co.kr/mypage/shop/toss_success.php',  // 결제에 성공하면 이동하는 페이지(직접 만들어주세요)
                failUrl: 'https://www.cocotoon.co.kr/mypage/shop/toss_fail.php',        // 결제에 실패하면 이동하는 페이지(직접 만들어주세요)
                customerEmail: customer_id,
                customerName: customer_name
            })
        })
    })

</script>

<div id="sub" class="sub mypage payment">
    <div class="con con1">
        <div class="size">
            <div class="inner">
                <?php include_once $root."/mypage/sideQuick.php";?>
                <div class="main_area">
                    <form name="board" id="board" method="post" action="process.php" onsubmit="return goCertification(this);">
                        <input type="hidden" name="cmd" value="order"/>
                        <input type="hidden" name="timeSet" id="timeSet" value=""/>
                        <input type="hidden" name="price" id="coco_price" value="<?=getMoney($_REQUEST['coco'])?>"/>
                        <input type="hidden" name="point" id="coco_point" value="<?=getCoconut($_REQUEST['coco'])?>"/>
                        <input type="hidden" name="id" value="<?=$data['id']?>"/>
                        <input type="hidden" name="member_fk" value="<?=$data['no']?>"/>
                        <input type="hidden" name="device" value="pc"/>
                        <div class="tit_wrap clear">
                            <strong>코코넛 충전</strong>
                        </div>
                        <div class="con_wrap">
                            <div class="con_box">
                                <h4 class="tit">구매내용</h4>
                                <ul class="content">
                                    <li>
                                        <p class="befCharge">코코넛 <b><?=getCoconut($_REQUEST['coco'])?>개</b></p>
                                        <p class="price"><span class="Aggro">&#8361;</span><?=getMoney($_REQUEST['coco'])?></p>
                                    </li>
                                </ul>
                            </div>
                            <div class="con_box">
                                <h4 class="tit sktxt">결제자 정보</h4>
                                <div class="content user_inf ipt_wrap clear">
                                    <div class="ipt_box clear">
                                        <span>이름</span>
                                        <input type="text" name="name" id="name" value="<?=$data['name']?>" placeholder="이름을 입력해주세요." readonly>
                                    </div>
                                    <div class="ipt_box clear">
                                        <span class="sktxt">연락처</span>
                                        <input type="text" name="telNumber" id="telNumber" value="<?=$data['cell']?>" maxlength="13" onkeyup="isNumberOrHyphen(this);cvtPhoneNumber(this);" placeholder="전화번호 입력해주세요." readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="con_box">
                                <h4 class="tit">결제 정보</h4>
                                <div class="content pay_inf ipt_wrap clear">
                                    <div class="ipt_box clear">
                                        <span class="sktxt">결제 금액</span>
                                        <p class="ipt">&#8361; <?=getMoney($_REQUEST['coco'])?></p>
                                    </div>
                                    <div id="payment-method"></div>
                                    <div id="agreement"></div>
                                    <!--
                                                                    <div class="ipt_box clear">
                                                                        <span>결제수단 선택</span>
                                                                        <div class="radio_wrap clear">
                                                                            <div class="radio_box">
                                                                                <input type="radio" name="pay_select" id="payment1" value="카드" checked >
                                                                                <label for="payment1">신용카드</label>
                                                                            </div>
                                                                            <div class="radio_box">
                                                                                <input type="radio" name="pay_select" id="payment2" value="계좌이체">
                                                                                <label for="payment2">실시간 계좌이체</label>
                                                                            </div>
                                                                            <div class="radio_box">
                                                                                <input type="radio" name="pay_select" id="payment4" value="도서문화상품권">
                                                                                <label for="payment4">도서문화상품권</label>
                                                                            </div>
                                                                            <div class="radio_box">
                                                                                <input type="radio" name="pay_select" id="payment5" value="가상계좌">
                                                                                <label for="payment5">가상계좌</label>
                                                                            </div>
                                                                            <div class="radio_box">
                                                                                <input type="radio" name="pay_select" id="payment3">
                                                                                <label for="payment3">무통장 입금</label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                    -->
                                    <div class="ipt_box bank_ipt clear">
                                        <span>입금자명</span>
                                        <input type="text" name="pay_name" id="pay_name" placeholder="이름을 입력해주세요." class="max250">
                                    </div>
                                    <div class="ipt_box bank_ipt clear">
                                        <span>입금은행</span>
                                        <p class="ipt">KB국민은행 291637-04-010395 주식회사 코코미디어</p>
                                    </div>
                                </div>
                            </div>


                            <div class="login_btn btn_box">

                                <?php //if ($_SERVER['REMOTE_ADDR'] === '211.38.48.45') { ?>
                                <!-- 현재 IP가 211.38.48.45 인 경우 실행할 코드 -->
                                <!-- 피지사 연동 버튼 -->
                                <!-- 결제위젯, 이용약관 영역 -->

                                <input type="button" value="결제하기" class="basisBtn3 beLined" id="pg_pay" onclick=""/>

                                <?php //} ?>
                                <!-- 무통장 입금 버튼
                                <input type="submit" value="결제하기" class="basisBtn3 beLined" id="bank_pay" style="display:none"/>-->
                            </div>
                            <!--<div class="btn_wrap btn_box">
                                <a href="javascript:;" class="basisBtn3 beLined sktxt" onclick="hideRemove();">인증번호 전송</a>
                            </div>

                            <div class="telConfirm tel hide">
                                <input type="text" name="telConfirm" id="telConfirm" placeholder="문자로 받으신 인증번호를 입력해주세요." onchange="btn_able()" maxlength="6"/>
                                <div class="limit_txt sktxt">인증번호 유효시간이 <span class="conf_time">5분 0초</span> 남았습니다.</div>

                                <div class="login_btn btn_box">
                                    <input type="submit" value="결제하기" class="basisBtn3 beLined"/>
                                </div>
                            </div>-->
                            <input type="hidden" id="memberCell" value="<?=$data['cell']?>"/>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
include_once $root."/footer.php";
?>
<script>
    $(function(){

        $('input[name="pay_select"]').on('click', function(){
            var _id = $(this).attr('id');
            if(_id == "payment3"){
                $('#pg_pay').css('display', 'none');
                $('#bank_pay').css('display', 'inline-block');
                $('.pay_inf .bank_ipt').css('display', 'block');
            }else{
                $('#pg_pay').css('display', 'inline-block');
                $('#bank_pay').css('display', 'none');
                $('.pay_inf .bank_ipt').css('display', 'none');
            }
        })

    })
    function pgOpen(){
        const popUp = window.open('INIstdpay_pc_req.php', '이니시스 결제', `width=1000, height=700, resizable=no`)

        setTimeout(() => {
            console.log('working')
            const leftPosition = (window.screen.width - 1000) / 2;
            const topPosition = (window.screen.height - 700) / 2 - 700;
            popUp.moveTo(leftPosition, topPosition);
        }, 500)

    }
</script>