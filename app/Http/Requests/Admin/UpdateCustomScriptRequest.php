<?php

namespace App\Http\Requests\Admin;

class UpdateCustomScriptRequest extends StoreCustomScriptRequest
{
    public function rules(): array
    {
        return $this->baseRules();
    }
}
