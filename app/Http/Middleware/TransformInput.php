<?php

namespace App\Http\Middleware;

use App\Core\Base\Traits\LogCache;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class TransformInput
{
    use LogCache;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $transformer)
    {
        $t1 = round(microtime(true) * 1000);
        $transformedInput = [];
        foreach ($request->request->all() as $input => $value) {
            if($transformer::originalAttribute($input)){
                $transformedInput[$transformer::originalAttribute($input)] = $value;
            }else{
                $transformedInput[$input] = $value;
            }
        }
        $request->replace($transformedInput);
        $response = $next($request);
        if (isset($response->exception) && $response->exception instanceof ValidationException) {
            if(!$response instanceof RedirectResponse){
                $data = $response->getData();
                $transformedErrors = [];
                foreach ($data->error as $field => $error) {
                    $transformedField = $transformer::transformedAttribute($field) ? $transformer::transformedAttribute($field) : $field;
                    $field = str_replace('_',' ',strtolower(preg_replace('/\p{Lu}(?<=\p{L}\p{Lu})/u', '_\0', $field)));
                    $transformedErrors[$transformedField] = str_replace( $field , $transformedField, $error);
                }
                $data->error = $transformedErrors;
                $response->setData($data);
            }
        }
        return $response;
    }
}
