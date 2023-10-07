<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use function is_string;

class DisplayCodeController extends Controller
{
    public function __invoke(Request $request): string
    {
        if (config('app.env') !== 'local') {
            abort(404);
        }

        $code = $request->query('code');

        return is_string($code) ? $code : 'invalid';
    }
}
