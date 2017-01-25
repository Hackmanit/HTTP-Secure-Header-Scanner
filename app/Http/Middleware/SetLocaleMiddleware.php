<?php

namespace App\Http\Middleware;

use Closure;
use Validator;

class SetLocaleMiddleware
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

        if($request->has('locale')) {

            $validator = Validator::make( $request->all(), [
                'locale' => 'string|size:2'
            ] );

            if (!$validator->fails()) {
                \App::setLocale( $request->input( 'locale' ) );
            }
        }

        return $next($request);
    }
}
