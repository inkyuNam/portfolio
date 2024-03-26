<?php
include_once $_SERVER['DOCUMENT_ROOT']."/include/common.php";
include_once $_SERVER['DOCUMENT_ROOT']."/lib/siteProperty.php";
include_once $_SERVER['DOCUMENT_ROOT']."/lib/util/function.php";
include_once $_SERVER['DOCUMENT_ROOT']."/lib/util/codeUtil.php";
include_once $_SERVER['DOCUMENT_ROOT']."/lib/util/dateUtil.php";
include_once $_SERVER['DOCUMENT_ROOT']."/lib/util/page.php";

include_once $_SERVER['DOCUMENT_ROOT']."/lib/design/Design.class.php";

$design = new Design(0, 'design', $_POST);

if(!$loginCheck){
    echo "<script>alert('로그인이 필요한 서비스입니다.'); location.href = '/member/login.php?url='+encodeURIComponent(location.href);</script>";
    exit;
}

$total_design_count = $design -> get_design_count($_SESSION['member_no']);
$design_name = "AI 안심건축 설계권 " . ($total_design_count + 1) . "회";

include "config.php";
$root = $_SERVER['DOCUMENT_ROOT'];
$p = "academy";
$sp = 0;
include_once $root."/header.php";
?>

    <script>
        $(function(){
            //운영서버 반영하면 바뀔 헤더 배너
            $('.banner.existing').hide();
            $('.banner.safety').show();
            //$('#header .top.index').hide();
            //$('#header .top.safety').show();
            //$('#header .btm').hide();
            //$('.all_menu .inner.index').hide();
            //$('.all_menu .inner.safety').show();
        });
    </script>
    <script type="text/javascript" src="<?php echo PAYMENT_SERVER ?>/resources/js/v1/SettlePG_v1.2.js"></script>
    <script>
        $(document).on('click', '#frm #paymentInfo input[type="radio"]', function(){
            //console.log($(this).prop('checked'));

            //입금자명 활성화
            if($('#account').prop('checked')){
                //console.log(1);
                displayAction('depositer', 1);
                displayAction('receiptInfo', 1);
            }else{
                displayAction('depositer', 0);
                displayAction('receiptInfo', 0);
                displayAction('taxBill', 0);
                $('#depositerName').val('');
                $('#receiptInfo input[type="radio"]').prop('checked', false);
                $('#taxBill input[type="radio"]').prop('checked', false);
                $('#busiId input[type="file"]').val('');
                $('#busiId input[type="text"]').val('');
            }

            //현금영수증
            if($('#r_noapply').prop('checked')){
                //console.log(1)
                displayAction('taxBill', 1);
                $('.receipt .sel_box select').prop('disabled', true);
                $('.receipt .sel_box select option:eq(0)').prop('selected', true);
                $('.receipt .receipt_num input[type="text"]').prop('disabled', true).val('');

            }else if($('#deduction').prop('checked') || $('#proof').prop('checked')){
                displayAction('taxBill', 0);
                $('#taxBill input[type="radio"]').prop('checked', false);
                $('.receipt .sel_box select').prop('disabled', false);
                $('.receipt .receipt_num input[type="text"]').prop('disabled', false);
            }

            //소득공제용 or 지출증빙용
            if($('#deduction').prop('checked')){
                $('#deductionSel').css('display', 'block');
                $('#proofSel').css('display', 'none');
                $('#proofSel select option:eq(0)').prop('selected', true);
                $('.receipt .receipt_num input[type="text"]').val('');
            }

            if($('#proof').prop('checked')){
                $('#proofSel').css('display', 'block');
                $('#deductionSel').css('display', 'none');
                $('#deductionSel select option:eq(0)').prop('selected', true);
                $('.receipt .receipt_num input[type="text"]').val('');
            }

            //세금계산서
            if($('#aplytax').prop('checked')){
                //console.log(1)
                displayAction('busiId', 1);
            }else{
                displayAction('busiId', 0);
                $('#busiId input[type="file"]').val('');
                $('#busiId input[type="text"]').val('');
            }

        });

        function displayAction(id , i){
            var $windowWidth = $(window).width();

            if(i == 1){
                if($windowWidth > 769){
                    $('#' + id).css('display', 'table-row');
                }else{
                    $('#' + id).css('display', 'block');
                }
            }else{
                $('#' + id).css('display', 'none');
            }
        }

        $(function(){

            //header변경
            $('#header .top.index').hide();
            $('#header .top.safety').show();
            $('#header .btm').hide();
            $('.all_menu .inner.index').hide();
            $('.all_menu .inner.safety').show();

        });


        //goSave 함수
        function goSave () {
            if($("#name").val() == ""){
                alert("이름을 입력해 주세요.");
                $("#name").focus();
                return false;
            }

            if($("#cell").val() == ""){
                alert("휴대폰번호를 입력해 주세요.");
                $("#cell").focus();
                return false;
            }

            if($("#email1").val().trim() == ""){
                alert("이메일을 입력해 주세요.");
                $("#email1").focus();
                return false;
            }

            if($("#email2").val().trim() == ""){
                alert("이메일을 입력해 주세요.");
                $("#email2").focus();
                return false;
            }

            $("#email").val( $("#email1").val().trim() + "@" + $("#email2").val().trim() );

            if($("#email").val().trim() == ""){
                alert("이메일을 입력해 주세요.");
                return false;
            }

            if ($("#email").val().trim() != "") {
                if(!isValidEmail(getObject("email"))) {
                    alert("잘못된 이메일 형식입니다.\n올바로 입력해 주세요.\nex) abc123@email.com");
                    $("#email1").focus();
                    return false;
                }
            }

            if($("input:radio[name=payment]").is(":checked") != true) {
                alert("결제 방법을 선택해 주세요.");
                return false;
            }

            var payment = $("input:radio[name=payment]:checked").val();

            // 무통장입금
            if(payment == 3 || payment == 4 ){
                $("#cmd").val("bank");

                if($("#depositerName").val().trim() == ""){
                    $("#depositerName").focus();
                    alert("입금자명을 입력해 주세요.");
                    return false;
                }

                if($("input[name=bill]:checked").val() == undefined){
                    alert("현금영수증 여부를 선택해 주세요.");
                    $("#r_noapply").focus();
                    return false;

                } else {
                    if($("input[name=bill]:checked").val() == 1 || $("input[name=bill]:checked").val() == 2){
                        if($("#bill_number").val() == ""){
                            alert("현금영수증 처리 정보를 입력해 주세요.");
                            $("#bill_number").focus();
                            return false;
                        }
                    } else {
                        if($("input[name=tax]:checked").val() == undefined){
                            alert("세금계산서 여부를 선택해 주세요.");
                            $("#notaxbill").focus();
                            return false;
                        } else {
                            if($("input[name=tax]:checked").val() == 1){
                                if($("#filename").val() == ""){
                                    alert("사업자등록증을 등록해 주세요.");
                                    $("#filename").focus();
                                    return false;
                                }
                            }
                        }
                    }
                }


                // 신용카드 OR 가상계좌
            } else if (payment == 1 || payment == 2) {

                if (payment == 1){
                    pay('card');
                }else if(payment == 2){
                    pay('vbank');
                }

            }

            if(payment == 3 || payment == 4){
                $("#frm").submit();
            }


        }


        /** 날짜 및 주문정보 재설정 */
        function init(type){

            var curr_date = new Date();
            var year = curr_date.getFullYear().toString();
            var month = ("0" + (curr_date.getMonth() + 1)).slice(-2).toString();
            var day = ("0" + (curr_date.getDate())).slice(-2).toString();
            var hours = ("0" + curr_date.getHours()).slice(-2).toString();
            var mins = ("0" + curr_date.getMinutes()).slice(-2).toString();
            var secs = ("0" + curr_date.getSeconds()).slice(-2).toString();
            var random4 = ("000" + Math.random() * 10000 ).slice(-4).toString();

            $('#STPG_payForm [name="custIp"]').val("<?php echo getRealClientIp() ?>"); //고객 IP 세팅
            $('#STPG_payForm [name="trdDt"]').val(year + month + day);  //요청일자 세팅
            $('#STPG_payForm [name="trdTm"]').val(hours + mins + secs); //요청시간 세팅
            $('#STPG_payForm [name="mchtTrdNo"]').val("PAYMENT" + year + month + day + hours + mins + secs + random4);//주문번호 세팅

            //회원 추가 정보 세팅
            var member_no = "<?= $_SESSION['member_no'] ?>";
            var member_id = "<?= $_SESSION['member_id'] ?>";
            var member_name = $('#name').val();
            var member_cell = $('#cell').val();
            var member_email = $('#email1').val() + '@' + $('#email2').val();

            var jsonData = {
                member_fk : member_no,
                name : member_name,
                phone : member_cell,
                mail : member_email,
            }
            var mchtData = JSON.stringify(jsonData);

            $('#STPG_payForm [name="plainMchtCustId"]').val(member_id);
            $('#STPG_payForm [name="plainMchtCustNm"]').val(member_name);
            $('#STPG_payForm [name="plainCphoneNo"]').val(member_cell);
            $('#STPG_payForm [name="plainEmail"]').val(member_email);

            $('#STPG_payForm [name="mchtParam"]').val(mchtData);
            //$('#STPG_payForm [name="mchtParam"]').val('mem_fk='+member_no+'&mem_name='+member_name+'&mem_id='+member_id+'&mem_cell='+member_cell+'&mem_email='+member_email);

            $('#STPG_payForm [name="method"]').val(type);

        }

        /** 결제 버튼 동작 */
        function pay(type){

            //날짜 및 결제수단 등 재설정
            init(type);

            //용도 : SHA256 해쉬 처리 및 민감정보 AES256암호화
            $.ajax({
                type : "POST",
                url : "/lib/hecto/pay_encryptParams.php",
                dataType : "json",
                data : $("#STPG_payForm").serialize(),
                success : function(rsp){
                    //암호화 된 파라미터 세팅
                    for(name in rsp.encParams) {
                        $('#STPG_payForm [name='+name+']').val( rsp.encParams[name] );
                    };

                    //가맹점 -> 세틀뱅크로 결제 요청
                    SETTLE_PG.pay({
                        env : "<?php echo PAYMENT_SERVER ?>",   //결제서버 URL
                        mchtId : $('#STPG_payForm [name="mchtId"]').val(),
                        method : $('#STPG_payForm [name="method"]').val(),
                        trdDt : $('#STPG_payForm [name="trdDt"]').val(),
                        trdTm : $('#STPG_payForm [name="trdTm"]').val(),
                        mchtTrdNo : $('#STPG_payForm [name="mchtTrdNo"]').val(),
                        mchtName : $('#STPG_payForm [name="mchtName"]').val(),
                        mchtEName : $('#STPG_payForm [name="mchtEName"]').val(),
                        pmtPrdtNm : $('#STPG_payForm [name="pmtPrdtNm"]').val(),
                        trdAmt : $('#STPG_payForm [name="trdAmt"]').val(),
                        mchtCustNm : $('#STPG_payForm [name="mchtCustNm"]').val(),
                        custAcntSumry : $('#STPG_payForm [name="custAcntSumry"]').val(),
                        expireDt : $('#STPG_payForm [name="expireDt"]').val(),
                        notiUrl : $('#STPG_payForm [name="notiUrl"]').val(),
                        nextUrl : $('#STPG_payForm [name="nextUrl"]').val(),
                        cancUrl : $('#STPG_payForm [name="cancUrl"]').val(),
                        mchtParam : $('#STPG_payForm [name="mchtParam"]').val(),
                        cphoneNo : $('#STPG_payForm [name="cphoneNo"]').val(),
                        email : $('#STPG_payForm [name="email"]').val(),
                        telecomCd : $('#STPG_payForm [name="telecomCd"]').val(),
                        prdtTerm : $('#STPG_payForm [name="prdtTerm"]').val(),
                        mchtCustId : $('#STPG_payForm [name="mchtCustId"]').val(),
                        taxTypeCd : $('#STPG_payForm [name="taxTypeCd"]').val(),
                        taxAmt : $('#STPG_payForm [name="taxAmt"]').val(),
                        vatAmt : $('#STPG_payForm [name="vatAmt"]').val(),
                        taxFreeAmt : $('#STPG_payForm [name="taxFreeAmt"]').val(),
                        svcAmt : $('#STPG_payForm [name="svcAmt"]').val(),
                        cardType : $('#STPG_payForm [name="cardType"]').val(),
                        chainUserId : $('#STPG_payForm [name="chainUserId"]').val(),
                        cardGb : $('#STPG_payForm [name="cardGb"]').val(),
                        clipCustNm : $('#STPG_payForm [name="clipCustNm"]').val(),
                        clipCustCi : $('#STPG_payForm [name="clipCustCi"]').val(),
                        clipCustPhoneNo : $('#STPG_payForm [name="clipCustPhoneNo"]').val(),
                        certNotiUrl : $('#STPG_payForm [name="certNotiUrl"]').val(),
                        skipCd : $('#STPG_payForm [name="skipCd"]').val(),
                        multiPay : $('#STPG_payForm [name="multiPay"]').val(),
                        autoPayType : $('#STPG_payForm [name="autoPayType"]').val(),
                        linkMethod : $('#STPG_payForm [name="linkMethod"]').val(),
                        appScheme : $('#STPG_payForm [name="appScheme"]').val(),
                        custIp : $('#STPG_payForm [name="custIp"]').val(),
                        corpPayCode : $('#STPG_payForm [name="corpPayCode"]').val(),
                        corpPayType : $('#STPG_payForm [name="corpPayType"]').val(),
                        cashRcptUIYn : $('#STPG_payForm [name="cashRcptUIYn"]').val(),

                        pktHash : rsp.hashCipher,   //SHA256 처리된 해쉬 값 세팅

                        ui :{
                            type:"popup",   //popup, iframe, self, blank
                            width: "430",   //popup창의 너비
                            height: "660"   //popup창의 높이
                        }
                    }, function(rsp){
                        //iframe인 경우 전달된 결제 완료 후 응답 파라미터 처리
                        console.log(rsp);
                    });
                },
                error : function(){
                    alert("에러 발생");
                },
            });
        }

        function goResult(){
            $('#STPG_payForm').attr("action", "<?php echo COMPANY_URL ?>mypage/safety/");
            $('#STPG_payForm').attr("method", "post");
            $('#STPG_payForm').attr("target", "");
            $('#STPG_payForm').submit();
        }


    </script>

    <div id="sub" class="sub e_regist safety">
        <?include_once $root."/include/sub_visual.php";?>
        <div class="sub_wrap">
            <div class="sec_wrap">
                <div class="section section1">
                    <div class="size">
                        <div class="inner">
                            <div class="tit_area bgr">
                                <div class="tit_box tit_box2">
                                    <h3 class="gd-dot">AI설계권 구매</h3>
                                </div>
                            </div>
                            <div class="cont_area">
                                <form action="<?=getSslCheckUrl($_SERVER['REQUEST_URI'], 'process.php')?>" method="post" id="frm" name="frm" enctype="multipart/form-data">
                                    <div class="form_area form_typeB form_typeB_a">
                                        <div class="wte_tit">
                                            <h4>AI건축설계 설계권 정보</h4>
                                        </div>
                                        <div class="wte_cont">
                                            <table>
                                                <caption>AI건축설계 설계권 정보</caption>
                                                <colgroup>
                                                    <col width="150px" class="col-th" />
                                                    <col width="*" />
                                                </colgroup>
                                                <tbody>
                                                <tr>
                                                    <th>상품명</th>
                                                    <td>AI 안심건축 설계권</td>
                                                </tr>
                                                <!--
                                                <tr>
                                                    <th>정상가</th>
                                                    <td>60,000원</td>
                                                </tr>
                                                <tr>
                                                    <th>할인</th>
                                                    <td>20,000원</td>
                                                </tr>
                                                -->
                                                <tr>
                                                    <th>가격</th>
                                                    <td><b>7,000원</b></td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="form_area form_typeB">
                                        <div class="wte_tit">
                                            <h4>회원정보</h4>
                                            <!-- <p class="msg">회원정보를 다시한번 확인하시기 바랍니다.</p> -->
                                            <p class="msg">필수 입력 사항입니다.</p>
                                        </div>
                                        <div class="wte_cont">
                                            <table>
                                                <caption>회원정보</caption>
                                                <colgroup>
                                                    <col width="150px" />
                                                    <col width="*" />
                                                </colgroup>
                                                <tbody>
                                                <tr>
                                                    <th>이름</th>
                                                    <td>
                                                        <div class="ipt_box">
                                                            <input type="text" id="name" name="name" value="<?=$_SESSION['member_name']?>" placeholder="성함을 입력해주세요." />
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>휴대폰번호</th>
                                                    <td>
                                                        <div class="ipt_box fontsize">
                                                            <input type="text" id="cell" name="cell" value="<?=$_SESSION['member_cell']?>" placeholder="“-” 제외하고 숫자만 입력" maxlength="15" onkeyup="isNumberOrHyphen(this);cvtPhoneNumber(this);"/>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>이메일 주소</th>
                                                    <td class="email">
                                                        <div class="ipt_box clear">
                                                            <div class="wte_email clear">
                                                                <div class="wr">
                                                                    <?
                                                                    $email = explode('@', $_SESSION['member_email']);
                                                                    ?>
                                                                    <input type="text" name="email1" id="email1" value="<?=$email[0]?>" class="required" title="이메일" autocomplete="off">
                                                                    <span class="at">@</span>
                                                                    <input type="text" name="email2" id="email2" value="<?=$email[1]?>" class="required" title="이메일" autocomplete="off">
                                                                </div>
                                                            </div>
                                                            <div class="sel_box">
                                                                <select onchange="document.getElementById('email2').value = this.value; this.style.color = '#222';">
                                                                    <option value="">직접입력</option>
                                                                    <option value="naver.com">naver.com</option>
                                                                    <option value="gmail.com">gmail.com</option>
                                                                    <option value="nate.com">nate.com</option>
                                                                    <option value="daum.net">daum.net</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="form_area form_typeB">
                                        <div class="wte_tit">
                                            <h4>결제정보</h4>
                                        </div>
                                        <div id="paymentInfo" class="wte_cont">
                                            <table>
                                                <caption>결제정보</caption>
                                                <colgroup>
                                                    <col width="150px" />
                                                    <col width="*" />
                                                </colgroup>
                                                <tbody>
                                                <tr>
                                                    <th>결제방법</th>
                                                    <td class="">
                                                        <div class="chk_wrap clear">
                                                            <div class="radio_box1">
                                                                <input type="radio" id="credit" name="payment" value="1">
                                                                <label for="credit">신용카드</label>
                                                            </div>
                                                            <div class="radio_box1">
                                                                <input type="radio" id="account" name="payment" value="3">
                                                                <label for="account">무통장입금</label>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr id="depositer" style="display:none;">
                                                    <th class="pt_20">입금자 명</th>
                                                    <td class="bank_info pt_20">
                                                        <div class="ipt_box">
                                                            <input type="text" id="depositerName" name="pay_name" value="" placeholder="입금자 성함을 입력해주세요" />
                                                        </div>
                                                        <div class="txt">
                                                            <span>하나은행</span>
                                                            <p>257-910064-66104</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr id="receiptInfo" style="display:none;">
                                                    <th>현금영수증</th>
                                                    <td class="receipt">
                                                        <div class="ipt_con clear">
                                                            <div class="chk_wrap clear">
                                                                <div class="radio_box1">
                                                                    <input type="radio" id="r_noapply" name="bill" value="0">
                                                                    <label for="r_noapply">신청안함</label>
                                                                </div>
                                                                <div class="radio_box1">
                                                                    <input type="radio" id="deduction" name="bill" value="1">
                                                                    <label for="deduction">소득공제용</label>
                                                                </div>
                                                                <div class="radio_box1">
                                                                    <input type="radio" id="proof" name="bill" value="2">
                                                                    <label for="proof">지출증빙용</label>
                                                                </div>
                                                            </div>
                                                            <div class="ipt_box clear">
                                                                <div id="deductionSel" class="sel_box">
                                                                    <select name="bill_type" onchange="this.style.color = '#222';" disabled>
                                                                        <option value="1">휴대폰 번호</option>
                                                                        <option value="2">주민등록 번호</option>
                                                                        <option value="3">현금영수증카드 번호</option>
                                                                    </select>
                                                                </div>
                                                                <div id="proofSel" class="sel_box" style="display:none;" disabled>
                                                                    <select onchange="this.style.color = '#222';">
                                                                        <option value="">사업자 번호</option>
                                                                    </select>
                                                                </div>
                                                                <div class="receipt_num">
                                                                    <input type="text" name="bill_number" id="bill_number" value="" class="required" title="현금영수증" placeholder="“-” 제외하고 숫자만 입력" autocomplete="off" disabled>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr id="taxBill" style="display:none;">
                                                    <th class="pt_20">세금계산서 신청</th>
                                                    <td class="pt_20">
                                                        <div class="chk_wrap clear">
                                                            <div class="radio_box1">
                                                                <input type="radio" id="notaxbill" name="tax" value="0">
                                                                <label for="notaxbill">신청안함</label>
                                                            </div>
                                                            <div class="radio_box1">
                                                                <input type="radio" id="aplytax" name="tax" value="1">
                                                                <label for="aplytax">세금계산서 신청</label>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr id="busiId" style="display:none;">
                                                    <th class="pt_20">사업자 등록증 첨부</th>
                                                    <td class="fileCheck pt_20">
                                                        <div class="ipt_box">
                                                            <input type="file" id="addFile" name="filename" value="" class="hidden" onchange="document.getElementById('filename').value = this.files[0].name;" />
                                                            <label for="addFile" class="sBtn2 bg-bbl">첨부</label>
                                                            <input type="text" id="filename" value="" readonly class="filename" />
                                                        </div>
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <!--
                                    <div class="fagree_area">
                                        <div class="check_box">
                                            <input type="checkbox" id="agree_1" name="agree" value="1" />
                                            <label for="agree_1">개인정보 수집 및 이용에 동의</label>
                                        </div>
                                    </div>
                                    -->
                                    <div class="btn_area safety-btn">
                                        <div class="fbtn_box">
                                            <?php
                                            $targetUrl = "index.php";
                                            ?>
                                            <a href="<?=$targetUrl?>" class="sBtn2 bg-gr">이전</a>
                                            <input type="button" id="" name="" value="결제하기" onclick="goSave();" class="gd1 sBtn2">
                                        </div>
                                    </div>
                                    <input type="hidden" name="cmd" id="cmd" value="bank"/>
                                    <input type="hidden" name="member_fk" value="<?=$_SESSION['member_no']?>"/>
                                    <input type="hidden" name="email" id="email" value=""/>
                                    <input type="hidden" name="price" id="price" value="7000"/>
                                    <!-- <input type="hidden" name="design_name" value="<?= $design_name ?>"/> -->

                                    <!-- 신용카드 OR 가상계좌 -->
                                    <input type="hidden" name="title" id="title" value="AI건축설계"/>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form id="STPG_payForm" name="STPG_payForm">
        <!-- 승인 요청 파라미터(필수) -->
        <input type="hidden" name="method" value="" />                                          <!-- 결제수단 -->
        <input type="hidden" name="trdDt" value="" />                                           <!-- 요청일자(yyyyMMdd) -->
        <input type="hidden" name="trdTm" value="" />                                           <!-- 요청시간(HHmmss)-->
        <input type="hidden" name="mchtTrdNo" value="" />                                       <!-- 상점주문번호 -->
        <input type="hidden" name="mchtName" value="매경부동산" />                                 <!-- 상점한글명 -->
        <input type="hidden" name="mchtEName" value="mkbiz" />									<!-- 상점영문명 -->
        <input type="hidden" name="pmtPrdtNm" value="AI건축설계권" />								<!-- 상품명 -->
        <input type="hidden" name="notiUrl" value="<?php echo COMPANY_URL ?>lib/hecto/aiReceiveNoti.php" />			<!-- 결과처리 URL -->
        <input type="hidden" name="nextUrl" value="<?php echo COMPANY_URL ?>lib/hecto/ai_receiveResult.php" />		<!-- 결과화면 URL -->
        <input type="hidden" name="cancUrl" value="<?php echo COMPANY_URL ?>lib/hecto/ai_receiveResult.php" />		<!-- 결제취소 URL -->
        <input type="hidden" name="plainTrdAmt" value="7000" />
        <input type="hidden" name="mchtId" value="<?php echo PG_MID ?>" />

        <!-- 승인 요청 파라미터(옵션) -->
        <input type="hidden" name="plainMchtCustNm" value="" />					<!-- 고객명(평문) -->
        <input type="hidden" name="custAcntSumry" value="세틀뱅크" />				<!-- 통장인자내용 -->
        <input type="hidden" name="expireDt" value="" />                        <!-- 입금만료일시(yyyyMMddHHmmss) -->
        <input type="hidden" name="mchtParam" value="상점 예약 필드" />				<!-- 상점예약필드 -->
        <input type="hidden" name="plainCphoneNo" value="" />                   <!-- 핸드폰번호(평문) -->
        <input type="hidden" name="plainEmail" value="" />						<!-- 이메일주소(평문) -->
        <input type="hidden" name="telecomCd" value="" />                       <!-- 통신사코드 -->
        <input type="hidden" name="prdtTerm" value="20221231235959" />          <!-- 상품제공기간 -->
        <input type="hidden" name="plainMchtCustId" value="" />					<!-- 상점고객아이디(평문) -->
        <input type="hidden" name="taxTypeCd" value="" />                       <!-- 면세여부(Y:면세, N:과세, G:복합과세) -->
        <input type="hidden" name="plainTaxAmt" value="" />                     <!-- 과세금액(평문)(복합과세인 경우 필수) -->
        <input type="hidden" name="plainVatAmt" value="" />                     <!-- 부가세금액(평문)(복합과세인 경우 필수) -->
        <input type="hidden" name="plainTaxFreeAmt" value="" />                 <!-- 비과세금액(평문)(복합과세인 경우 필수) -->
        <input type="hidden" name="plainSvcAmt" value="" />                     <!-- 봉사료(평문) -->
        <input type="hidden" name="cardType" value="" />                        <!-- 카드결제타입 -->
        <input type="hidden" name="chainUserId" value="" />                     <!-- 현대카드Payshot ID -->
        <input type="hidden" name="cardGb" value="" />                          <!-- 특정카드사코드 -->
        <input type="hidden" name="plainClipCustNm" value="" />                 <!-- 클립포인트고객명(평문) -->
        <input type="hidden" name="plainClipCustCi" value="" />                 <!-- 클립포인트CI(평문) -->
        <input type="hidden" name="plainClipCustPhoneNo" value="" />            <!-- 클립포인트고객핸드폰번호(평문) -->
        <input type="hidden" name="certNotiUrl" value="" />                     <!-- 인증결과URL -->
        <input type="hidden" name="skipCd" value="" />                          <!-- 스킵여부 -->
        <input type="hidden" name="multiPay" value="" />                        <!-- 포인트복합결제 -->
        <input type="hidden" name="autoPayType" value="" />                     <!-- 자동결제 타입(공백:일반결제, M:월자동 1회차) -->
        <input type="hidden" name="linkMethod" value="" />                      <!-- 연동방식 -->
        <input type="hidden" name="appScheme" value="" />                       <!-- 앱스키마 -->
        <input type="hidden" name="custIp" value="" />                          <!-- 고객IP주소 -->
        <input type="hidden" name="corpPayCode" value="" />                     <!-- 간편결제 코드 -->
        <input type="hidden" name="corpPayType" value="" />                     <!-- 간편결제 타입(CARD:카드, POINT:포인트) -->
        <input type="hidden" name="cashRcptUIYn" value="" />                    <!-- 현금영수증 발급 여부 -->

        <!-- 응답 파라미터 -->
        <input type="hidden" name="respMchtId" />           <!-- 상점아이디 -->
        <input type="hidden" name="respOutStatCd" />        <!-- 거래상태 -->
        <input type="hidden" name="respOutRsltCd" />        <!-- 거절코드 -->
        <input type="hidden" name="respOutRsltMsg" />       <!-- 결과메세지 -->
        <input type="hidden" name="respMethod" />           <!-- 결제수단 -->
        <input type="hidden" name="respMchtTrdNo" />        <!-- 상점주문번호 -->
        <input type="hidden" name="respMchtCustId" />       <!-- 상점고객아이디 -->
        <input type="hidden" name="respTrdNo" />            <!-- 세틀뱅크 거래번호 -->
        <input type="hidden" name="respTrdAmt" />           <!-- 거래금액 -->
        <input type="hidden" name="respMchtParam" />        <!-- 상점예약필드 -->
        <input type="hidden" name="respAuthDt" />           <!-- 승인일시 -->
        <input type="hidden" name="respAuthNo" />           <!-- 승인번호 -->
        <input type="hidden" name="respReqIssueDt" />       <!-- 채번요청일시 -->
        <input type="hidden" name="respIntMon" />           <!-- 할부개월수 -->
        <input type="hidden" name="respFnNm" />             <!-- 카드사명 -->
        <input type="hidden" name="respFnCd" />             <!-- 카드사코드 -->
        <input type="hidden" name="respPointTrdNo" />       <!-- 포인트거래번호 -->
        <input type="hidden" name="respPointTrdAmt" />      <!-- 포인트거래금액 -->
        <input type="hidden" name="respCardTrdAmt" />       <!-- 신용카드결제금액 -->
        <input type="hidden" name="respVtlAcntNo" />        <!-- 가상계좌번호 -->
        <input type="hidden" name="respExpireDt" />         <!-- 입금기한 -->
        <input type="hidden" name="respCphoneNo" />         <!-- 휴대폰번호 -->
        <input type="hidden" name="respBillKey" />          <!-- 자동결제키 -->
        <input type="hidden" name="respCsrcAmt" />          <!-- 현금영수증 발급 금액(네이버페이) -->

        <!-- 암호화 처리 후 세팅될 파라미터-->
        <input type="hidden" name="trdAmt" />               <!-- 거래금액(암호문) -->
        <input type="hidden" name="mchtCustNm" />           <!-- 상점고객이름(암호문) -->
        <input type="hidden" name="cphoneNo" />             <!-- 휴대폰번호(암호문) -->
        <input type="hidden" name="email" />                <!-- 이메일주소(암호문) -->
        <input type="hidden" name="mchtCustId" />           <!-- 상점고객아이디(암호문) -->
        <input type="hidden" name="taxAmt" />               <!-- 과세금액(암호문) -->
        <input type="hidden" name="vatAmt" />               <!-- 부가세금액(암호문) -->
        <input type="hidden" name="taxFreeAmt" />           <!-- 면세금액(암호문) -->
        <input type="hidden" name="svcAmt" />               <!-- 봉사료(암호문) -->
        <input type="hidden" name="clipCustNm" />           <!-- 클립포인트고객명(암호문) -->
        <input type="hidden" name="clipCustCi" />           <!-- 클립포인트고객CI(암호문) -->
        <input type="hidden" name="clipCustPhoneNo" />      <!-- 클립포인트고객휴대폰번호(암호문) -->

        <input type="hidden" name="cmd" value="STPG_pay" />
    </form>
