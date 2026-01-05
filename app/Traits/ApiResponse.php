<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

trait ApiResponse
{
    /**
     * Return a success JSON response.
     */
    protected function successResponse(string $message, mixed $data = null, int $status = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $status);
    }

    /**
     * Return an error JSON response.
     */
    protected function errorResponse(string $message, mixed $errors = null, int $status = 400): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }


    /**
 * This will return a standard json response when a request successful
 * @param mixed $response 
 * @param string $message 
 */
function successfulResponse($response, $message = 'Process successful', $statusCode = 200)
{

    $responseObject = [
        'data' => $response['data'] ?? $response,
        'meta_data' => $response['meta_data'] ?? null,
        'message' => $message,
        'success' => true
    ];

    return response()->json($responseObject, $statusCode);
}


/**
 * Transform the paginated data response to have a uniform response 
 */

function transformPaginatedData(LengthAwarePaginator $paginatedData, $resourceClass = null)
{
    $items = $resourceClass
        ? $resourceClass::collection($paginatedData->items())
        : $paginatedData->items();
    return [
        'data' => $items,
        'meta_data' => [
            'total' => $paginatedData->total(),
            'per_page' => $paginatedData->perPage(),
            'current_page' => $paginatedData->currentPage(),
            'last_page' => $paginatedData->lastPage(),
            'next_page_url' => $paginatedData->nextPageUrl(),
            'prev_page_url' => $paginatedData->previousPageUrl(),
        ],
    ];
}

/**
 * This will return a standard json response when a request failed
 * @param mixed $response 
 * @param string $message 
 * @param string $error 
 */
function failedResponse($response = null, $message = 'Process failed', int $statusCode = 400, ?Throwable $e = null)
{
    $responseObject = [
        'data' => $response,
        'success' => false,
        'message' => $message,
    ];

    if ($e) {
        if ($e instanceof HttpExceptionInterface) {
            // Use the HTTP status code from the exception
            $statusCode = $e->getStatusCode();
        } else {
            // Ensure a valid HTTP status code: default to 500 if invalid
            $statusCode = is_int($e->getCode()) && $e->getCode() >= 100 && $e->getCode() <= 599
                ? $e->getCode()
                : 500;
        }
    }

    // Make sure status code is always valid
    if ($statusCode < 100 || $statusCode > 599) {
        $statusCode = 500;
    }

    return response()->json($responseObject, $statusCode);
}


}



