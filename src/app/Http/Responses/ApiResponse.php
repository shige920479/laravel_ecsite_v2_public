<?php
namespace App\Http\Responses;

trait ApiResponse
{
    protected function success($data = null, $message = null, $meta = null, int $status = 200)
    {
        $response = [
            'success' => true,
            'data' => $data,
            'message' => $message,
        ];

        if (! is_null($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $status);
    }

    protected function error(string $message = 'Error', $errors = null, int $status = 400, string $code = 'API_ERROR')
    {
        $response = [
            'success' => false,
            'code' => $code,
            'message' => $message,
        ];

        if (! is_null($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }
}