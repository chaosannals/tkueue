<?php

use tkueue\exception\TipException;

/**
 * 响应 JSON
 */
function respond($key = 'successful', $data = null)
{
    // 混合参数。
    $result = config("response.$key");
    if (empty($result)) {
        $result = config('response.failed');
    }
    $code = $result['code'];
    $message = $result['message'];
    if (isset($data)) {
        $message = array_merge($message, $data);
    }
    return json($message, $code);
}

/**
 * AES 256 加密。
 *
 * @param string $key
 * @param mixed $data
 * @return string
 */
function aes256_encrypt($key, $data)
{
    $method = 'aes-256-cbc';
    $ivlength = openssl_cipher_iv_length($method);
    $iv = openssl_random_pseudo_bytes($ivlength);
    $text = openssl_encrypt(
        json_encode($data, JSON_UNESCAPED_UNICODE),
        $method,
        $key,
        OPENSSL_RAW_DATA,
        $iv
    );
    $hmac = hash_hmac('sha256', $text, $key, true);
    return base64_encode($iv . $hmac . $text);
}

/**
 * AES 256 解密。
 *
 * @param string $key
 * @param string $data
 * @return mixed
 */
function aes256_decrypt($key, $data)
{
    $method = 'aes-256-cbc';
    $ivlength = openssl_cipher_iv_length($method);
    $raw = base64_decode($data);
    $iv = substr($raw, 0, $ivlength);
    $hmac = substr($raw, $ivlength, 32);
    $text = substr($raw, $ivlength + 32);
    $result = openssl_decrypt(
        $text,
        $method,
        $key,
        OPENSSL_RAW_DATA,
        $iv
    );
    $calcmac  = hash_hmac('sha256', $text, $key, true);
    if ($hmac != $calcmac) {
        return null;
    }
    return json_decode($result, true);
}

/**
 * 蛇皮风格转帕斯卡风格
 *
 * @param string $source
 * @return string
 */
function snake_to_pascal($source)
{
    return preg_replace_callback('/(?:^|_)([a-z])/', function ($matches) {
        return strtoupper($matches[1]);
    }, $source);
}

/**
 * 蛇皮风格转驼峰风格
 *
 * @param string $source
 * @return string
 */
function snake_to_camel($source)
{
    return preg_replace_callback('/_([a-z])/', function ($matches) {
        return strtoupper($matches[1]);
    }, $source);
}

/**
 * 帕斯卡风格转蛇皮风格
 *
 * @param string $source
 * @return string
 */
function pascal_to_snake($source)
{
    return strtolower(preg_replace_callback('/(.)([A-Z])/', function ($matches) {
        return $matches[1] . '_' . $matches[2];
    }, $source));
}

/**
 * 批量条件。
 *
 * @param Query $query
 * @param array $param
 * @param array $fields
 * @return void
 */
function query_batch($query, $param, $conditions)
{
    foreach ($conditions as $k => $v) {
        if (array_key_exists($k, $param)) {
            if ($v[0] == 'or') {
                $query->where(function ($q) use ($param, $k, $v) {
                    foreach ($v[1] as $condition) {
                        $value = strcasecmp($condition[0], 'like') == 0 ? "%{$param[$k]}%" : $param[$k];
                        $q->whereOr($condition[1], $condition[0], $value);
                    }
                });
            } else {
                $value = strcasecmp($v[0], 'like') == 0 ? "%{$param[$k]}%" : $param[$k];
                $query->where($v[1], $v[0], $value);
            }
        }
    }
    return $query;
}

/**
 * 批量范围。
 *
 * @param Query $query
 * @param array $param
 * @param array $fields
 * @return void
 */
function query_batch_range($query, $param, $fields)
{
    foreach ($fields as $key => $field) {
        if (!empty($param[$key])) {
            $value = $param[$key];
            if (!empty($value[0])) {
                $query->where($field, '>=', $value[0]);
            }
            if (!empty($value[1])) {
                $query->where($field, '<=', $value[1]);
            }
        }
    }
    return $query;
}

/**
 * 批量 LIKE
 *
 * @param Query $query
 * @param array $param
 * @param array $fields
 * @return Query
 */
function query_batch_like($query, $param, $fields)
{
    foreach ($fields as $key => $field) {
        if (!empty($param[$key])) {
            $text = trim($param[$key]);
            if (strlen($text) > 0) {
                $query->where($field, 'like', "%{$text}%");
            };
        }
    }
    return $query;
}

/**
 * 批量等于。
 *
 * @param Query $query
 * @param array $param
 * @param array $fields
 * @return Query
 */
function query_batch_equal($query, $param, $fields, $strict = true)
{
    foreach ($fields as $key => $field) {
        if (array_key_exists($key, $param)) {
            $value = $param[$key];
            if (is_string($value)) {
                $text = trim($value);
                if (strlen($text) > 0) {
                    $query->where($field, '=', $text);
                }
            } else {
                if (!($strict and empty($value))) {
                    $operation = is_array($value) ? 'in' : '=';
                    $query->where($field, $operation, $value);
                }
            }
        }
    }
    return $query;
}

/**
 * 排序拼接。
 *
 * @param Query $query
 * @param array $info
 * @param array $fields
 * @param string $default
 * @return string
 */
function query_order($query, $info, $fields, $default = null)
{
    $valid = !empty($info) and is_array($info);
    $key = $valid ? array_keys($info)[0] : $default;

    if (!array_key_exists($key, $fields)) {
        throw new TipException("无效排序");
    }
    $field = $fields[$key];
    $direct = $valid ? strtoupper($info[$key]) : 'ASC';
    if (!in_array($direct, ['ASC', 'DESC'])) {
        throw new TipException("不是有效的排序方向");
    }
    $query->order("$field $direct");
}

/**
 * 数据过滤。
 *
 * @param array $data 数据源
 * @param array $options 可填项
 * @param array $must 必填项
 * @return array
 */
function data_filter(&$data, $options, $must = [])
{
    $keys = array_keys($data);
    $diff = array_diff($must, $keys);
    if (count($diff) > 0) {
        throw new TipException("缺少必填字段", [
            'fields' => $diff,
        ]);
    }
    $fields = array_unique(array_merge($must, $options));
    foreach (array_diff($keys, $fields) as $key) {
        unset($data[$key]);
    }
    return $data;
}

/**
 * 验证分页大小限制是否有效。
 *
 */
function valid_limit($limit)
{
    if (!in_array($limit, config('base.pagination.limit'))) {
        throw new TipException('不是有效的分页大小');
    }
    return $limit;
}
