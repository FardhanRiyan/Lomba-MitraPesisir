<?php

namespace App\Http\Requests;

class AdminCatchLogRequest extends CatchLogRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }
}
