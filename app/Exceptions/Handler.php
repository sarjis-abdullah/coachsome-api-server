<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;

use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Throwable
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function render($request, Throwable $exception)
    {
        return $this->handleApiException($request, $exception);
    }

    private function handleApiException($request, \Exception $exception)
    {
        $exception = $this->prepareException($exception);

        if ($exception instanceof AuthorizationException) {
            return response()->json((['status' => 403, 'message' => 'Insufficient privileges to perform this action.']),
                403);
        }

        elseif ($exception instanceof MethodNotAllowedHttpException) {
            return response()->json((['status' => 405, 'message' => 'Method Not Allowed.']), 405);
        }

        if ($exception instanceof ModelNotFoundException) {
            return response()->json((['status' => 404, 'message' => 'Resource not found with the specific id.']), 404);
        }

        elseif ($exception instanceof NotFoundHttpException || $exception instanceof RouteNotFoundException) {
            return response()->json((['status' => 404, 'message' => 'The requested resource was not found.']), 404);
        }

        elseif ($exception instanceof AccessDeniedHttpException) {
            return response()->json((['status' => 403, 'message' => "Access Denied."]),
                403);
        }

        elseif ($exception instanceof \InvalidArgumentException) {
            return response()->json((['status' => 403, 'message' => $exception->getMessage()]),
                403);
        }
        elseif ($exception instanceof ValidationException) {
            return response()->json((['status' => 422, 'message' => $exception->getMessage()]),
                403);
        }
        elseif ($exception instanceof ThrottleRequestsException) {
            return response()->json((['status' => 429, 'message' => "Limit Exceeded for today."]),
                403);
        }
        elseif ($exception instanceof AuthenticationException) {
            return response()->json((['status' => 401, 'message' => "Un-Authenticated."]),
                401);
        }
        return parent::render($request, $exception);
    }
}
