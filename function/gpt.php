<?php
/*
| ----------------------------------------------------------------------------------------
| GPT 통신
| ----------------------------------------------------------------------------------------
*/
if ( ! function_exists('send_gpt')) {
    function send_gpt($keyword,$filePath=''): string
    {
        //$file = file_get_contents('assets/images/social_facebook.png');
        if(!empty($keyword)){
            $data = [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                    ['role' => 'user', 'content' => $keyword]
                ],
                'max_tokens' => 1000
            ];

            $headers = [
                'Content-Type: application/json',
                'Authorization: ' . 'Bearer ' . " API_KEY 작성 "
            ];

            $ch = curl_init("엔드 포인트 URL");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $response = curl_exec($ch);
            curl_close($ch);

            // API 응답 처리
            $decodedResponse = json_decode($response, true);
            // 한글 텍스트 출력
            return nl2br($decodedResponse['choices'][0]['message']['content']);
        }
    }
}