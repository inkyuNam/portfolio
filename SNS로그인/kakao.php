<?php
use environment\MemberConfig;
include_once $_SERVER['DOCUMENT_ROOT']."/lib/environment/MemberConfig.php";
include_once $_SERVER['DOCUMENT_ROOT']."/lib/member/Member.class.php";
include_once $_SERVER['DOCUMENT_ROOT']."/lib/util/function.php";
$config = new MemberConfig();
$obj = $config->getList();

$sns_type="kakao";
$protocol = "http://";
if(SSL_USE){
    $protocol = "https://";
}
/*
| ----------------------------------------------------------------------------------------
| 카카오토큰발급 (access, refresh)
| ----------------------------------------------------------------------------------------
*/
$kakao_callback_url = urlencode($protocol.$_SERVER['HTTP_HOST']."/api/sns/login/kakao.php");
$code = $_GET["code"];
$state = $_GET["state"];
$url = "https://kauth.kakao.com/oauth/token?grant_type=authorization_code&client_id=".$obj->kakao_client_id."&redirect_uri=".$kakao_callback_url."&code=".$code."&state=".$state."&client_secret=".$obj->kakao_client_secret;

$is_post = false;
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, $is_post);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // php curl 설정
$headers = array(
    "Content-Type: application/x-www-form-urlencoded"
);
$response = curl_exec ($ch);
$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close ($ch);

if($status_code == 200){
    /*
    | ----------------------------------------------------------------------------------------
    | 토큰으로 유저 정보 조회
    | ----------------------------------------------------------------------------------------
    */
    $res = json_decode($response, true);
    $access_token = $res['access_token'];
    $refresh_token = $res['refresh_token'];
    $header = "Bearer ".$access_token; // Bearer 다음에 공백 추가
    $url = "https://kapi.kakao.com/v2/user/me";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, $is_post);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $headers = array(
        "Content-type: application/x-www-form-urlencoded;charset=utf-8",
        "Authorization: " . $header,
    );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec ($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close ($ch);

    if($status_code == 200){
        /*
        | ----------------------------------------------------------------------------------------
        | 로그인 및 DB 저장
        | ----------------------------------------------------------------------------------------
        */
        $member = new Member(0, 'member', $_REQUEST);
        $res = json_decode($response, true);
        $sns_id = $res['id'];
        $mem = $config->get_sns_member($sns_id, $sns_type);
        if(!empty($mem)){
            // 로그인
            $member->updateLoginTime($mem['no']);
            $_SESSION['member_no'] = $mem['no'];
            $_SESSION['member_id'] = $mem['id'];
            $_SESSION['member_name'] = $mem['name'];
            $_SESSION['member_email'] = $mem['email'];
            $_SESSION['member_cell'] = $mem['cell'];
            $_SESSION['member_sns'] = $mem['sns_login_type'];
            echo returnURL(LOGIN_AFTER_PAGE);
        }else{
            // db저장 (저장할 내용 커스터마이징 필요 기본 sns_id)
            $profile = $res['kakao_account']['profile'];
            // insert 데이터 정렬
            $insert_data['id'] = $sns_id;
            $insert_data['name'] = $profile['nickname'];
            $insert_no = $member->insert_sns_member($insert_data, $sns_type, $refresh_token);
            if($insert_no != 0) {
                $mem = $config->get_sns_member($sns_id, $sns_type);
                $member->updateLoginTime($mem['no']);
                $_SESSION['member_no'] = $mem['no'];
                $_SESSION['member_id'] = $mem['id'];
                $_SESSION['member_name'] = $mem['name'];
                $_SESSION['member_email'] = $mem['email'];
                $_SESSION['member_cell'] = $mem['cell'];
                $_SESSION['member_sns'] = $sns_type;
                echo returnURLMsg(LOGIN_AFTER_PAGE, '정상적으로 회원가입되었습니다.');
            }else{
                echo returnHistory('회원가입 오류');
                exit();
            }
        }
    }else{
        echo returnHistory('액세스토큰 인증 실패');
        exit();
    }

}else{
    echo returnHistory('카카오 서비스 장애가 발생했습니다.');
}

