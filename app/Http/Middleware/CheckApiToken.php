<?php

namespace App\Http\Middleware;

use Closure;

class CheckApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $api_token = $request->headers->get('VKAPP-API-TOKEN');

        if($api_token !== env('API_TOKEN')){
            return response()->json([
                'code' => 403,
                'error' => [
                    'message' => 'Unauthorized access',
                    'exception' => ''
                ]
            ]);
        }

        return $next($request);
    }
}
