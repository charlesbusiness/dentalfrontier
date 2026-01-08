<?php

namespace App\Exception;

use Exception;
use Throwable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Symfony\Component\ErrorHandler\Error\UndefinedMethodError;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use TypeError;

class ExceptionHandler extends Exception
{
    /**
     * Handle API exceptions and return a JSON response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleApiException(Throwable $exception)
    {
        $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        $message = 'An unexpected error occurred';

        $response = [
            'data' => null,
            'success' => false,
            'message' => $message,
        ];
        if ($exception instanceof ValidationException) {
            $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY;
            $response['message'] = is_string($exception) == 'string' ? $exception : array_merge(...array_values($exception->errors()));
            // $response['data'] = array_values( $exception->errors());
        } elseif ($exception instanceof NotFoundHttpException) {
            $statusCode = Response::HTTP_NOT_FOUND;
            $response['message'] = 'Endpoint not found';
        } elseif ($exception instanceof ModelNotFoundException) {
            $statusCode = Response::HTTP_NOT_FOUND;
            $response['message'] = 'Resource not found';
        } elseif ($exception instanceof BadRequestException) {
            $statusCode = Response::HTTP_BAD_REQUEST;
            $response['message'] = $exception->getMessage();
        } elseif ($exception instanceof MethodNotAllowedHttpException) {
            $statusCode = Response::HTTP_METHOD_NOT_ALLOWED;
            $response['message'] = 'Method not allowed';
        } elseif ($exception instanceof HttpResponseException) {
            $statusCode = $exception->getResponse()->getStatusCode();
            $response['message'] = 'HTTP response exception';
        } else if ($exception instanceof QueryException) {
            $response['message'] = 'There was an error. Please contact admin';
        } else if ($exception instanceof UnauthorizedException) {
            $response['message'] = $exception->getMessage();
        } elseif ($exception instanceof AuthenticationException) {
            $statusCode = 401; //Unauthenticated
            $response['message'] = 'Please log in to access this resource';
        } elseif ($exception instanceof InvalidArgumentException) {
            $statusCode = Response::HTTP_BAD_REQUEST;
            $response['message'] = $exception->message;
        } elseif ($exception instanceof TypeError) {
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            $response['message'] = "There was a server error. Please contact admin";
        } elseif ($exception instanceof UndefinedMethodError) {
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            $response['message'] = "There was a server error. Please contact admin";
        } elseif ($exception instanceof HttpException) {
            $statusCode = Response::HTTP_BAD_REQUEST;
            $response['message'] = $exception->getMessage();
        } else {

            $response['message'] = method_exists($exception, 'getMessage') ? $exception->getMessage() : 'An unexpected error occurred';
        }

        try {
            // Don't log full stack traces for expected authentication errors
            if ($exception instanceof AuthenticationException) {
                Log::info('Authentication failed: ' . $exception->getMessage());
            } else {
                Log::error($exception);
            }
        } catch (Throwable $e) {
            Log::error($e);
        }

        return response()->json($response, $statusCode);
    }


    /**
 
 
 
     * Default handler for non-JSON requests.
     *
     * @param Throwable $exception
     * @param \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function defaultExceptionHandler(Throwable $exception)
    {
        // Generate an appropriate response for non-JSON requests
        $statusCode = $exception instanceof HttpException
            ? $exception->getStatusCode()
            : 500;

        // Example: Return a simple HTML error page
        return response()->view('errors.generic', [
            'message' => $exception->getMessage(),
            'status' => $statusCode,
        ], $statusCode);
    }
}
