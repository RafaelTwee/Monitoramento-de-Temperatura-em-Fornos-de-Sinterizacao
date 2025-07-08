<?php
namespace App\Http\Middleware;
use Closure;
class RequireAdmin {
  public function handle($request, Closure $next) {
    $user = session('user');
    if (! $user || ! $user['Adm']) {
      abort(403, 'Acesso negado');
    }
    return $next($request);
  }
}
