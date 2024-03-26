<?php
session_start();

$sitecode = "CA720";			// NICE로부터 부여받은 사이트 코드
$sitepasswd = "i0mAUmxnekvV";			// NICE로부터 부여받은 사이트 패스워드

// Linux = /절대경로/ , Window = D:\\절대경로\\ , D:\절대경로\
$cb_encode_path = "/usr/lib64/php/modules/CPClient.so";

$authtype = "";      		// 없으면 기본 선택화면, M(휴대폰), X(인증서공통), U(공동인증서), F(금융인증서), S(PASS인증서), C(신용카드)

$customize 	= "";		//없으면 기본 웹페이지 / Mobile : 모바일페이지 (default값은 빈값, 환경에 맞는 화면 제공)

$reqseq = "REQ_0123456789";     // 요청 번호, 이는 성공/실패후에 같은 값으로 되돌려주게 되므로
// 업체에서 적절하게 변경하여 쓰거나, 아래와 같이 생성한다.

// 실행방법은 백틱(`) 외에도, 'exec(), system(), shell_exec()' 등등 귀사 정책에 맞게 처리하시기 바랍니다.
// 위의 실행함수를 통해 아무런 값도 출력이 안될 경우 쉘 스크립트 오류출력(2>&1)을 통해 오류 확인 부탁드립니다.
$reqseq = `$cb_encode_path SEQ $sitecode`;

// CheckPlus(본인인증) 처리 후, 결과 데이타를 리턴 받기위해 다음예제와 같이 http부터 입력합니다.
// 리턴url은 인증 전 인증페이지를 호출하기 전 url과 동일해야 합니다. ex) 인증 전 url : http://www.~ 리턴 url : http://www.~
$returnurl = "https://www.cocotoon.co.kr/nice/success.php";	// 성공시 이동될 URL
$errorurl = "https://www.cocotoon.co.kr/nice/fail.php";		// 실패시 이동될 URL

// reqseq값은 성공페이지로 갈 경우 검증을 위하여 세션에 담아둔다.

$_SESSION["REQ_SEQ"] = $reqseq;

// 입력될 plain 데이타를 만든다.
$plaindata = "7:REQ_SEQ" . strlen($reqseq) . ":" . $reqseq .
    "8:SITECODE" . strlen($sitecode) . ":" . $sitecode .
    "9:AUTH_TYPE" . strlen($authtype) . ":". $authtype .
    "7:RTN_URL" . strlen($returnurl) . ":" . $returnurl .
    "7:ERR_URL" . strlen($errorurl) . ":" . $errorurl .
    "9:CUSTOMIZE" . strlen($customize) . ":" . $customize;

$enc_data = `$cb_encode_path ENC $sitecode $sitepasswd $plaindata`;

$returnMsg = "";

if( $enc_data == -1 )
{
    $returnMsg = "암/복호화 시스템 오류입니다.";
    $enc_data = "";
}
else if( $enc_data== -2 )
{
    $returnMsg = "암호화 처리 오류입니다.";
    $enc_data = "";
}
else if( $enc_data== -3 )
{
    $returnMsg = "암호화 데이터 오류 입니다.";
    $enc_data = "";
}
else if( $enc_data== -9 )
{
    $returnMsg = "입력값 오류 입니다.";
    $enc_data = "";
}
?>
<SCRIPT type="text/javascript">

    function goSave(frm) {

        const ch1 = document.getElementById('agree_1');
        const ch2 = document.getElementById('agree_2');

        if(!$(ch1).prop("checked")) {
            alert('이용약관 동의후 회원가입이 가능합니다.');
            return false;
        }

        if(!$(ch2).prop("checked")) {
            alert('개인정보 처리방침 동의후 회원가입이 가능합니다.');
            return false;
        }

        if (frm.email.value.trim() == "") {
            alert("이메일을 입력해 주세요.");
            frm.email.focus();
            return false;
        }
        if (frm.email.value.trim() != "") {
            if(!isValidEmail(getObject("email"))) {
                alert("잘못된 이메일 형식입니다.\\n올바로 입력해 주세요.\\n ex)abcdef@naver.com");
                frm.email.focus();
                return false;
            }
        }

        if(!validPassword($("#password"))) return false;
        if ($("#password").val().trim() != $("#password2").val().trim()) {
            alert("비밀번호를 정확하게 입력해 주세요.");
            $("#password2").focus();
            return false;
        }

        $.ajax({
            type : "POST",
            url: "/include/id_check.php",
            async: false,
            data: {
                id : frm.email.value.trim()
            },
            success: function( data ) {
                var r = data.trim();
                if (r == "false") {
                    alert("중복된 이메일입니다.");
                    frm.email.focus();
                    $("#email_duplication").val("1");
                } else if (r == "true") {
                    $("#email_duplication").val("0");
                }
            },
            error:function(e) {
                alert(e.responseText);
            }
        });
        if ($("#email_duplication").val() == "1") {
            return false;
        }

        if(frm.veri.value.trim() == 1){
            alert("본인 인증확인을 해주세요.");
            return false;
        }

    }

    function checkId(){
        if ($("#id").val().trim() == "") {
            alert("아이디를 입력해 주세요.");
            $("#id").focus();
            return false;
        } else {
            $.ajax({
                type : "POST",
                url: "/include/id_check.php",
                async: false,
                data: {
                    id : $("#id").val()
                },
                success: function( data ) {
                    var r = data.trim();
                    if (r == "false") {
                        alert("중복된 아이디입니다.");
                        $("#id").focus();
                        $("#id_duplication").val("1");
                    } else if (r == "true") {
                        alert("사용가능한 아이디입니다.");
                        $("#id_duplication").val("0");
                    }
                },
                error:function(e) {
                    alert(e.responseText);
                }
            });
        }
    }

    function checkEmail(){
        if($('#email1').val().trim() == ""){
            alert('이메일을 입력해 주세요.');
            $('#email1').focus();
            return false;
        }
        if($('#email2').val().trim() == ""){
            alert('이메일을 입력해 주세요.');
            $('#email2').focus();
            return false;
        }
        $('#email').val( $('#email1').val().trim() + '@' + $('#email2').val().trim() );

        if($('#email').val().trim() == ""){
            alert('이메일을 입력해 주세요.');
            return false;
        }
        if ($("#email").val().trim() != "") {
            if(!isValidEmail(getObject("email"))) {
                alert("잘못된 이메일 형식입니다.\\n올바로 입력해 주세요.\\n ex)abcdef@naver.com");
                $("#email").focus();
                return false;
            }
        }

        $.ajax({
            type : "POST",
            url: "/include/email_check.php",
            async: false,
            data: {
                email : $("#email").val().trim()
            },
            success: function( data ) {
                var r = data.trim();
                if (r == "false") {
                    alert("중복된 이메일입니다.");
                    $("#email").focus();
                    $("#email_duplication").val("1");
                } else if (r == "true") {
                    alert("사용가능한 이메일입니다.");
                    $("#email_duplication").val("0");
                }
            },
            error:function(e) {
                alert(e.responseText);
            }
        });
    }
    function receiveData(ciphertime, requestnumber, responsenumber, authtype, name, birthdate, gender, nationalinfo, dupinfo, conninfo, mobileno, mobileco) {
        // 이름을 입력받는 input 태그의 id가 name-input이라 가정합니다.
        const nameInput = document.getElementById('name-input');
        // URLDecode 함수를 사용하여 디코딩합니다.
        const decodedName = decodeURIComponent(name);
        // input 태그의 값을 업데이트합니다.
        nameInput.value = decodedName;
    }
</SCRIPT>
<script language='javascript'>
    window.name ="Parent_window";
    // 나이스 인증모듈 스크립트
    function fnPopup(){
        window.open('', 'popupChk', 'width=500, height=550, top=100, left=100, fullscreen=no, menubar=no, status=no, toolbar=no, titlebar=yes, location=no, scrollbar=no');
        document.form_chk.action = "https://nice.checkplus.co.kr/CheckPlusSafeModel/checkplus.cb";
        document.form_chk.target = "popupChk";
        document.form_chk.submit();
    }
    $(function(){
        $('input[name="agree_1"]').change(function(){
            if($('input[name="agree_1"]:checked').length == $('input[name="agree_1"]').length){
                console.log('allchecked')
                $('input[name="chkAllAgree"]').prop('checked', true);
            }else{
                $('input[name="chkAllAgree"]').prop('checked', false);
            }
        })
    })
    function selectAll(All){
        const chk = document.querySelectorAll('input[type="checkbox"]');

        chk.forEach((checkbox) => {
            checkbox.checked = All.checked
        })

        const allBox = document.querySelectorAll('input[type="checkbox"]');
        const checked = document.querySelectorAll('input[type="checkbox"]:checked');

        const selectAll = document.querySelector('input[name="chkAllAgree"]');

        if(allBox.length === checked.length) {
            selectAll.checked == true;
        } else {
            selectAll.checked == false;
        }
    }

    function updateData(niceData) {
        // 입력값으로 DOM 업데이트 등의 작업 수행
        document.getElementById("name-input").value = niceData.name;
        document.getElementById("birth-input").value = niceData.birth;
        document.getElementById("cell-input").value = niceData.cell;
        document.getElementById("gender-input").value = niceData.gender;
        document.getElementById("veri-input").value = niceData.veri;
    }

</script>
<div id="sub" class="member join join_agree">
    <div class="size">
        <div class="inner">
            <!-- 여기서부터 게시판--->
            <div class="bbs">
                <div class="form_wrap">
                    <form name="board" id="board" method="post" action="process.php" onsubmit="return goSave(this);">
                        <fieldset class="login_form">
                            <input type="hidden" name="url" id="url" value="<?=$url?>"/>
                            <input type="hidden" name="param" id="param" value="<?=$param?>"/>
                            <input type="hidden" name="email_duplication" id="email_duplication" />
                            <input type="hidden" name="device" value="pc">
                            <input type="hidden" name="cmd" value="write" />
                            <!-- <div class="gauge_wrap">
                                <div class="step_gauge"></div>
                            </div> -->
                            <div class="memb_txt">
                                <em>약관동의가 필요해요</em>
                                <p>코코툰 회원가입을 위해 약관동의가 필요해요.</p>
                            </div>
                            <div class="chk_wrap">
                                <div class="chk_tit chk_box1 skin2 sz_up">
                                    <input type="checkbox" name="chkAllAgree" id="chkAllAgree" onclick="selectAll(this)"/>
                                    <label for="chkAllAgree">약관 동의 (전체동의)</label>
                                </div>
                                <ul class="chk_list sktxt">
                                    <li>
                                        <div class="chk_box1 skin2">
                                            <input type="checkbox" name="agree_1" id="agree_1" value="1"/>
                                            <label for="agree_1" class="sktxt">코코툰 이용약관 <span>(필수)</span></label>
                                        </div>
                                        <a href="/member/agree.php" target="_blank">링크페이지 이동</a>
                                    </li>
                                    <li>
                                        <div class="chk_box1 skin2">
                                            <input type="checkbox" name="agree_2" id="agree_2" value="1"/>
                                            <label for="agree_2" class="sktxt">개인정보 처리방침 <span>(필수)</span></label>
                                        </div>
                                        <a href="/member/policy.php" target="_blank">링크페이지 이동</a>
                                    </li>
                                    <li>
                                        <div class="chk_box1 skin2">
                                            <input type="checkbox" name="agree_3" id="agree_3" value="1"/>
                                            <label for="agree_3" class="sktxt">마케팅 동의 <span>(선택)</span></label>
                                        </div>
                                        <a href="javascript:;">링크페이지 이동</a>
                                    </li>
                                </ul>
                            </div>
                            <div class="memb_txt sktxt">
                                <em>정보를 입력해주세요.</em>
                                <p>코코툰 회원가입에 필요한 정보를 입력해주세요.</p>
                            </div>
                            <div class="ipt_box join_box">
                                <div class="email">
                                    <input type="text" name="email" id="email" placeholder="이메일 주소를 입력해주세요." value="<?=$_REQUEST['email']?>"/>
                                </div>
                                <div class="password">
                                    <input type="password" name="password" id="password" placeholder="영문 숫자를 포함한 비밀번호 8~16자리를 입력해주세요."/>
                                </div>
                                <div class="password2 blurEff">
                                    <input type="password" name="password2" id="password2" placeholder="입력하신 비밀번호를 다시한번 입력해주세요."/>
                                </div>
                                <input type="hidden" name="name" id="name-input" value=""/>
                                <input type="hidden" name="birth" id="birth-input" value=""/>
                                <input type="hidden" name="cell" id="cell-input" value=""/>
                                <input type="hidden" name="gender" id="gender-input" value=""/>
                                <input type="hidden" name="veri" id="veri-input" value="1"/>
                            </div>
                            <div class="login_btn btn_box">
                                <input type="submit" value="회원가입" class="basisBtn1 beLined sktxt"/>
                            </div>
                        </fieldset>
                    </form>
                    <form name="form_chk" method="post">
                        <!--나이스 인증모듈-->
                        <input type="hidden" name="m" value="checkplusService">				    <!-- 필수 데이타로, 누락하시면 안됩니다. -->
                        <input type="hidden" name="EncodeData" value="<?= $enc_data ?>">		<!-- 위에서 업체정보를 암호화 한 데이타입니다. -->
                        <!-- ============= -->
                        <div class="btn_box">
                            <a href="javascript:fnPopup();" class="basisBtn2 beLined sktxt">본인인증</a>
                        </div>
                    </form>
                </div>
            </div>
            <!-- //여기까지 게시판--->
        </div>
    </div>
    <!-- //size--->
</div>
<!-- //sub--->
<script>
    $(function(){

        $('#password2').blur(function(){
            if($(this).val() !== $('#password').val()){
                console.log("틀렸을 경우")
                $(this).parent().addClass('on')
            }else{
                console.log("맞았을 경우")
                $(this).parent().removeClass('on')
            }
        })
    });
</script>
