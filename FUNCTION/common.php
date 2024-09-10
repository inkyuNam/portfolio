<?php

/*
| ----------------------------------------------------------------------------------------
| 원하는 형태로 날짜 자르기 (날짜, 나눌 문자 (ex '.', '-', ...))
| ----------------------------------------------------------------------------------------
*/
if ( ! function_exists('getYMD')) {
    function getYMD($datetime, $delimiter = '-'): string
    {
        $result = '';
        if ($datetime) {
            $result = substr($datetime, 0, 10);
            if ($delimiter !== '-') {
                $result = str_replace('-', $delimiter, $result);
            }
        }
        return $result;
    }
}

/*
| ----------------------------------------------------------------------------------------
| 배열형태의 체크박스 비교하기
| ----------------------------------------------------------------------------------------
*/
if ( ! function_exists('get_checked_array')) {
    function get_checked_array($s, $w): string
    {
        $r = "";
        $w_array = is_array($w) ? array_map('urldecode', $w) : explode(',', $w);

        $s = trim($s); // 입력된 $s 값도 공백 제거

        if ($s === '') {
            $s = -1;
        }
        foreach ($w_array as $val) {
            $val = trim($val);
            if ($s === $val) { // 완전한 일치 확인
                $r = "checked";
                break;
            }
        }
        return $r;
    }
}

/*
| ----------------------------------------------------------------------------------------
| api 통신 (json)
| ----------------------------------------------------------------------------------------
*/
if ( ! function_exists('callRestApi')) {
    function callRestApi($api, $data)
    {
        $headers = [
            'Content-Type: application/json',
        ];

        $url = "엔드포인트" . $api;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}

/*
| ----------------------------------------------------------------------------------------
| 멀티플 업로드 파일 분류
| ----------------------------------------------------------------------------------------
*/
if ( ! function_exists('get_multiple_upload')) {
    function get_multiple_upload($fileInputName): array
    {
        // 파일 배열을 매핑하여 정리된 배열을 반환
        return array_map(static function ($name, $type, $tmp_name, $error, $size) {
            if (!$name) {
                return null;
            }
            return array(
                'name' => $name,
                'type' => $type,
                'tmp_name' => $tmp_name,
                'error' => $error,
                'size' => $size
            );
        },
            $_FILES[$fileInputName]['name'],
            $_FILES[$fileInputName]['type'],
            $_FILES[$fileInputName]['tmp_name'],
            $_FILES[$fileInputName]['error'],
            $_FILES[$fileInputName]['size']);
    }
}