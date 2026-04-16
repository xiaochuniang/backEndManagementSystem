<?php
/**
 * Response — 统一 JSON 响应封装
 *
 * 所有接口均通过此类返回，保证响应结构一致：
 * {
 *   "code":    200,
 *   "message": "success",
 *   "data":    { ... }
 * }
 */
class Response
{
    public static function success(mixed $data = null, string $message = 'success'): void
    {
        echo json_encode([
            'code'    => 200,
            'message' => $message,
            'data'    => $data,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function error(string $message = 'error', int $code = 400, mixed $data = null): void
    {
        echo json_encode([
            'code'    => $code,
            'message' => $message,
            'data'    => $data,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function unauthorized(string $message = '请先登录'): void
    {
        http_response_code(401);
        echo json_encode([
            'code'    => 401,
            'message' => $message,
            'data'    => null,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function notFound(string $message = '资源不存在'): void
    {
        http_response_code(404);
        echo json_encode([
            'code'    => 404,
            'message' => $message,
            'data'    => null,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
