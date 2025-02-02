<?php namespace BB\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use GuzzleHttp\Client as HttpClient;

class Handler extends ExceptionHandler {
	/**
	 * A list of the exception types that should not be reported.
	 *
	 * @var array
	 */
	protected $dontReport = [
        HttpException::class,
        NotFoundHttpException::class,
        ModelNotFoundException::class,
        MethodNotAllowedHttpException::class,
        FormValidationException::class,
        AuthenticationException::class,         //These are logged separately below
	];

	/**
	 * Report or log an exception.
	 *
	 * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
	 *
	 * @param  \Exception  $e
	 * @return void
	 */
	public function report(Exception $e)
	{
        try{
            $level = 'error';
            $title = 'Error';
            $suppress = false;
            $ignore = false;
            
            $has_status_code = method_exists($e, 'getStatusCode');
            if($has_status_code){
                $statusCode = $e->getStatusCode();
                
                if($statusCode == 404){
                    $level = 'warn';
                    $title = 'Not Found - ' . \Request::path();
                    $suppress = true;
                    $ignore = true;
                }
            }

            if($e instanceof NotImplementedException){
                $level = 'info';
                $title = 'Not Implemented';
            }

            if(!$ignore) $this->notifyTelegram($level, $title, $suppress, $e);
        } catch (Exception $e) {
        }

        //The parent will log exceptions that aren't of the types above
		parent::report($e);
	}


    protected function notifyTelegram($level = 'error', $title = 'Exception', $suppress = false, $error)
    {
        $botKey = config('telegram.bot_key');
        $botChat = config('telegram.bot_chat');

        if (empty($botKey) || empty($botChat)) {
            return;
        }

        $icon = array(
            'error'=> '🛑',
            'warn' => '⚠️',
            'info' => 'ℹ️'
        );

        $userString = \Auth::guest() ? "A guest": \Auth::user()->name . "(#" .  \Auth::user()->id . ")";

        $notification = $suppress ? 'Trace suppressed' :
            "Path: <b>/" . \Request::path() . "</b> \n" .
            "User: <b>" . $userString . "</b> \n" . 
            "Message: <b>" . $error->getMessage() . "</b> \n"  . 
            "File: <b>" . $error->getFile() . "</b> \n"  .
            "Line: <b>" . $error->getLine() . "</b> \n"  . 
            "More Info: https://members.hacman.org.uk/logs";
        
        // TODO: Use TelegramHelper??
        (new HttpClient)->get(
            "https://api.telegram.org/bot{$botKey}/sendMessage" .
            "?parse_mode=HTML&chat_id=${botChat}". 
            "&text=" . $icon[$level] . urlencode("<b>{$title}</b> \n \n " . $notification)
        );
    }

	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Exception  $e
	 * @return \Illuminate\Http\Response
	 */
	public function render($request, Exception $e)
	{
        if ($e instanceof FormValidationException) {
            if ($request->wantsJson()) {
                return \Response::json($e->getErrors(), 422);
            }
            \Notification::error("Something wasn't right, please check the form for errors", $e->getErrors());
            return redirect()->back()->withInput();
        }

        if ($e instanceof ValidationException) {
            if ($request->wantsJson()) {
                return \Response::json($e->getMessage(), 422);
            }
            \Notification::error($e->getMessage());
            return redirect()->back()->withInput();
        }

        if ($e instanceof NotImplementedException) {
            \Notification::error("NotImplementedException: ".$e->getMessage());
            \Log::warning($e);
            return redirect()->back()->withInput();
        }

        if ($e instanceof AuthenticationException) {
            if ($request->wantsJson()) {
                return \Response::json(['error' => $e->getMessage()], 403);
            }
            $userString = \Auth::guest() ? "A guest": \Auth::user()->name;
            \Log::warning($userString." tried to access something they weren't supposed to.");

            return \Response::view('errors.403', [], 403);
        }

        if ($e instanceof ModelNotFoundException) {
            $e = new HttpException(404, $e->getMessage());
        }

        if (config('app.debug') && $this->shouldReport($e) && !$request->wantsJson())
        {
            return $this->renderExceptionWithWhoops($e);
        }

        if ($request->wantsJson()) {
            if ($this->isHttpException($e)) {
                return \Response::json(['error' => $e->getMessage()], $e->getStatusCode());
            }
        }
		return parent::render($request, $e);
	}

    /**
     * Render an exception using Whoops.
     *
     * @param  \Exception $e
     * @return \Illuminate\Http\Response
     */
    protected function renderExceptionWithWhoops(Exception $e)
    {
        $whoops = new \Whoops\Run;
        $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());

        return new \Illuminate\Http\Response($whoops->handleException($e), $e->getCode());
    }

}
