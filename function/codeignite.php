<?php

/*
| ----------------------------------------------------------------------------------------
| 파일 업로드시에 설정파일 불러오는 함수
| ----------------------------------------------------------------------------------------
*/
if ( ! function_exists('set_upload_config'))
{
    function set_upload_config(array $params): array
    {
        $config = array();

        // 기본 설정
        $config['upload_path'] = $params['path'] ?? './uploads/';
        $config['max_size'] = isset($params['size']) ? $params['size'] * 1024 * 1024 : 5 * 1024 * 1024; // 기본값 5MB
        $config['max_width'] = $params['width'] ?? 0;
        $config['max_height'] = $params['height'] ?? 0;
        $config['encrypt_name'] = $params['encrypt_name'] ?? true;

        // allowed_types가 배열인 경우 파이프(|)로 연결
        if (isset($params['allowed_types']) && is_array($params['allowed_types'])) {
            $config['allowed_types'] = implode('|', $params['allowed_types']);
        } else {
            // allowed_types가 설정되지 않았거나 배열이 아닐 때 기본값 사용
            $config['allowed_types'] = '*';
        }

        return $config;
    }
}

/*
| ----------------------------------------------------------------------------------------
| 파일 업로드시에 디렉토리 생성함수
| ----------------------------------------------------------------------------------------
*/
if ( ! function_exists('upload_create_dir'))
{
    function upload_create_dir($file, $dir)
    {
        if(!empty($file)){
            if (is_dir($dir) === false) {
                if (!mkdir($dir, 0707) && !is_dir($dir)) {
                    throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
                }
                $file = $dir . 'index.php';
                $f = @fopen($file, 'w');
                @fwrite($f, '');
                @fclose($f);
                @chmod($file, 0644);
            }
            $dir .= cdate('Y') . '/';
            if (is_dir($dir) === false) {
                if (!mkdir($dir, 0707) && !is_dir($dir)) {
                    throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
                }
                $file = $dir . 'index.php';
                $f = @fopen($file, 'w');
                @fwrite($f, '');
                @fclose($f);
                @chmod($file, 0644);
            }
            $dir .= cdate('m') . '/';
            if (is_dir($dir) === false) {
                if (!mkdir($dir, 0707) && !is_dir($dir)) {
                    throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
                }
                $file = $dir . 'index.php';
                $f = @fopen($file, 'w');
                @fwrite($f, '');
                @fclose($f);
                @chmod($file, 0644);
            }
            return $dir;
        }
        return false;
    }
}