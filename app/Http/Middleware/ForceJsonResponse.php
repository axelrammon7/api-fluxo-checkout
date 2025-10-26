<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    /**
     * Handle an incoming request.
     * 
     * Este middleware força que as respostas da API sejam em JSON,
     * evitando que erros de validação retornem HTML ao invés de JSON.
     * Isso garante consistência nas respostas da API.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Força o Accept header para garantir resposta em JSON
        // Útil para Postman/Insomnia que podem não enviar esse header por padrão
        $request->headers->set('Accept', 'application/json');
        
        $response = $next($request);
        
        return $response;
    }
}
