<?php
namespace App\Http\Middleware;
use Closure;
class RequireAuth {
  public function handle($request, Closure $next) {
    if (! session('user')) {
      return redirect()->route('login.form');
    }
    return $next($request);
  }
}
