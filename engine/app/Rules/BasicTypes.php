<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Log;

class BasicTypes implements Rule
{
    private $typeValidation;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($type="integer"){
        $this->typeValidation = $type;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        Log::debug("att:".$attribute);
        Log::debug("val:".$value);
        Log::debug("tipo: ".$this->typeValidation);
        if($this->typeValidation == "integer"){
            return is_numeric($value);
        }else{
            return true;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The validation error message.';
    }
}
