<?php

//**************************************************************************************************************
//NICE평가정보 Copyright(c) KOREA INFOMATION SERVICE INC. ALL RIGHTS RESERVED

//서비스명 :  체크플러스 - 안심본인인증 서비스
//페이지명 :  체크플러스 - 결과 페이지

//보안을 위해 제공해드리는 샘플페이지는 서비스 적용 후 서버에서 삭제해 주시기 바랍니다.
//인증 후 결과값이 null로 나오는 부분은 관리담당자에게 문의 바랍니다.
//**************************************************************************************************************

session_start();

$sitecode = "사이트코드";                // NICE로부터 부여받은 사이트 코드
$sitepasswd = "패스워드";                // NICE로부터 부여받은 사이트 패스워드

// Linux = /절대경로/ , Window = D:\\절대경로\\ , D:\절대경로\
$cb_encode_path = "/usr/lib64/php/modules/CPClient.so";

$enc_data = $_REQUEST["EncodeData"];        // 암호화된 결과 데이타

//////////////////////////////////////////////// 문자열 점검///////////////////////////////////////////////
if (preg_match('~[^0-9a-zA-Z+/=]~', $enc_data, $match)) {
    echo "입력 값 확인이 필요합니다 : " . $match[0];
    exit;
} // 문자열 점검 추가.
if (base64_encode(base64_decode($enc_data)) != $enc_data) {
    echo "입력 값 확인이 필요합니다";
    exit;
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($enc_data != "") {

    $plaindata = `$cb_encode_path DEC $sitecode $sitepasswd $enc_data`;        // 암호화된 결과 데이터의 복호화
    /*
    echo "[plaindata]  " . $plaindata . "<br>";
    */

    if ($plaindata == -1) {
        $returnMsg = "암/복호화 시스템 오류";
    } else if ($plaindata == -4) {
        $returnMsg = "복호화 처리 오류";
    } else if ($plaindata == -5) {
        $returnMsg = "HASH값 불일치 - 복호화 데이터는 리턴됨";
    } else if ($plaindata == -6) {
        $returnMsg = "복호화 데이터 오류";
    } else if ($plaindata == -9) {
        $returnMsg = "입력값 오류";
    } else if ($plaindata == -12) {
        $returnMsg = "사이트 비밀번호 오류";
    } else {
        // 복호화가 정상적일 경우 데이터를 파싱합니다.
        $ciphertime = `$cb_encode_path CTS $sitecode $sitepasswd $enc_data`;    // 암호화된 결과 데이터 검증 (복호화한 시간획득)

        $requestnumber = GetValue($plaindata, "REQ_SEQ");
        $responsenumber = GetValue($plaindata, "RES_SEQ");
        $authtype = GetValue($plaindata, "AUTH_TYPE");
        //$name = GetValue($plaindata , "NAME");
        $name = GetValue($plaindata, "UTF8_NAME"); //charset utf8 사용시 주석 해제 후 사용
        $birthdate = GetValue($plaindata, "BIRTHDATE");
        $gender = GetValue($plaindata, "GENDER");
        $nationalinfo = GetValue($plaindata, "NATIONALINFO");    //내/외국인정보(사용자 매뉴얼 참조)
        $dupinfo = GetValue($plaindata, "DI");
        $conninfo = GetValue($plaindata, "CI");
        $mobileno = GetValue($plaindata, "MOBILE_NO");
        $mobileco = GetValue($plaindata, "MOBILE_CO");

        /*
        if(strcmp($_SESSION["REQ_SEQ"], $requestnumber) != 0)
        {
            echo "세션값이 다릅니다. 올바른 경로로 접근하시기 바랍니다.<br>";

            $requestnumber = "";
            $responsenumber = "";
            $authtype = "";
            $name = "";
            $birthdate = "";
            $gender = "";
            $nationalinfo = "";
            $dupinfo = "";
            $conninfo = "";
    $mobileno = "";
    $mobileco = "";

        }
        */


    }
}

$addHyphen_no = addHyphenToPhoneNumber($mobileno);

// 인증 휴대전화 번호로 가입한 내역이 있는지 체크
//$duplication = $member->checkCell($addHyphen_no);
$duplication = 0;

// 데이터를 연관 배열로 저장
$data = array(
    "cell" => $mobileno,
    "name" => URLDecode($name),
    "birth" => $birthdate,
    "gender" => $gender,
    "veri" => $duplication
);

// 연관 배열을 json으로 인코딩하여 세션에 저장
$_SESSION['nice_data'] = json_encode($data);

//********************************************************************************************
//해당 함수에서 에러 발생 시 $len => (int)$len 로 수정 후 사용하시기 바랍니다. (하기소스 참고)
//********************************************************************************************

function GetValue($str, $name)
{
    $pos1 = 0;  //length의 시작 위치
    $pos2 = 0;  //:의 위치

    while ($pos1 <= strlen($str)) {
        $pos2 = strpos($str, ":", $pos1);
        $len = substr($str, $pos1, $pos2 - $pos1);
        $key = substr($str, $pos2 + 1, $len);
        $pos1 = $pos2 + $len + 1;
        $pos2 = strpos($str, ":", $pos1);
        $len = substr($str, $pos1, $pos2 - $pos1);
        if ($key === $name) {
            return substr($str, $pos2 + 1, $len);
        } else {
            // 다르면 스킵한다.
            $pos1 = $pos2 + $len + 1;
        }
    }
}

?>
<script>

    function closePopupAndSendData() {
        opener.document.getElementById("name-input").value = '<?= URLDecode($name) ?>';
        opener.document.getElementById("birth-input").value = '<?= $birthdate ?>';
        opener.document.getElementById("cell-input").value = '<?= $mobileno ?>';
        opener.document.getElementById("gender-input").value = '<?= $gender ?>';
        opener.document.getElementById("veri-input").value = '<?= $duplication ?>';
        // 팝업창 닫기
        window.close();
        // 부모창으로 정보 전달
    }

    function closePopUp() {
        // 세션에서 데이터 받아오기
        const niceData = {
            cell: '<?= $mobileno ?>',
            name: '<?= URLDecode($name) ?>',
            birth: '<?= $birthdate ?>',
            gender: '<?= $gender ?>',
            veri: '<?= $duplication ?>'
        };

        // 부모창의 updateData 함수 호출하여 입력값 전달
        window.opener.updateData(niceData);

        // 팝업창 닫기
        self.close();
    }

    window.onload = function () {
        closePopupAndSendData();
    }

</script>

<html>
<head>
    <title>NICE평가정보 - CheckPlus 본인인증 테스트</title>
    <style>
        body {
            background: #f7f7f7;
        }

        table {
            width: 1px;
            height: 1px;
            clip: rect(0, 0, 0, 0);
            position: absolute;
            overflow: hidden;
            margin-top: -1px;
        }

        .ct_wrapper {
            width: 90%;
            margin-top: 200px;
            max-width: 480px;
            background: #fff;
            padding: 20px;
        }

        input[type="button"] {
            width: 100%;
            height: 40px;
            background: #ff3a4b;
            border: none;
            outline: none;
            color: #fff;
            font-weight: 700;
            font-size: 18px;
            cursor: pointer;
        }
    </style>
</head>
<body>
<center>
    <div class="ct_wrapper">
        <h2>본인 확인</h2>
        <p>본인 인증이 처리되었습니다.</p>
        <input type="button" value="확인" onclick="closePopUp()">
    </div>
    <br>
    <input type="hidden" name="name" value="<?= URLDecode($name) ?>"/>
    <input type="hidden" name="cell" value="<?= $mobileno ?>"/>
    <input type="hidden" name="birth" value="<?= $birthdate ?>"/>
    <input type="hidden" name="gender" value="<?= $gender ?>"/>

    <table border=1>
        <tr>
            <td>복호화한 시간</td>
            <td><?= $ciphertime ?> (YYMMDDHHMMSS)</td>
        </tr>
        <tr>
            <td>요청 번호</td>
            <td><?= $requestnumber ?></td>
        </tr>
        <tr>
            <td>나신평응답 번호</td>
            <td><?= $responsenumber ?></td>
        </tr>
        <tr>
            <td>인증수단</td>
            <td><?= $authtype ?></td>
        </tr>
        <tr>
            <td>성명</td>
            <td><?= URLDecode($name) ?></td>
        </tr>
        <tr>
            <td>생년월일(YYYYMMDD)</td>
            <td><?= $birthdate ?></td>
        </tr>
        <tr>
            <td>성별</td>
            <td><?= $gender ?></td>
        </tr>
        <tr>
            <td>내/외국인정보</td>
            <td><?= $nationalinfo ?></td>
        </tr>
        <tr>
            <td>DI(64 byte)</td>
            <td><?= $dupinfo ?></td>
        </tr>
        <tr>
            <td>CI(88 byte)</td>
            <td><?= $conninfo ?></td>
        </tr>
        <tr>
            <td>휴대폰번호</td>
            <td><?= $mobileno ?></td>
        </tr>
        <tr>
            <td>통신사</td>
            <td><?= $mobileco ?></td>
        </tr>
        <tr>
            <td>중복확인</td>
            <td><?= $duplication ?></td>
        </tr>
        <tr>
            <td colspan="2">인증 후 결과값은 내부 설정에 따른 값만 리턴받으실 수 있습니다. <br>
                일부 결과값이 null로 리턴되는 경우 관리담당자 또는 계약부서(02-2122-4615)로 문의바랍니다.
            </td>
        </tr>
    </table>
</center>
</body>
</html>