<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

trait AccessTrait
{
    public function authorized(Request $request): bool
    {
        $apiAccessKey = $request->get('api_access_key') ?? null;
        if (isset($apiAccessKey)) {
            $apiAccessKey = decrypt($apiAccessKey);
        }

        if ($apiAccessKey !== config('app.api_access_key')) {
            return false;
        }
        return true;
    }
}