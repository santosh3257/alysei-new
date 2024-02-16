<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use League\OAuth2\Server\Exception\OAuthServerException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, \Exception $e)
    {
        if ($e instanceof MethodNotAllowedHttpException) {
            return response()->json(['success' => 405,'errors' =>['exception' => $e->getMessage()]], 405);
        }
        if ($e instanceof FatalThrowableError) {
            return response()->json(['success' => 500,'errors' =>['exception' => $e->getMessage()]], 500);
        }
        if ($e instanceof NotFoundHttpException) {
            if ($request->is('api/*')) {
                return response()->json(['success' => 404,'errors' =>'This api endpoint does not exist'], 404);
            };
        }
        if ($e instanceof OAuthServerException || $e instanceof AuthenticationException) {

            if(isset($e->guards) && isset($e->guards()[0]) ==='api')
            return response()->json(['success' => 401,'errors' =>['exception' => $e->getMessage()]], 401);
            else if ($e instanceof OAuthServerException)
            return response()->json(['success' => 401,'errors' =>['exception' => $e->getMessage()]], 401);
        }

        return parent::render($request, $e);
    }


    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' =>  $exception->getMessage()], 401);
        }

        return redirect()->guest(route('login'));
    }
}
