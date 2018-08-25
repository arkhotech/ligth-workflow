<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Actions\ProcessVariable;

class ActivityVariable extends Model implements ProcessVariable
{
    public function getName() {
        return $this->name;
    }

    public function getValue() {
        return $this->value;
    }

    public function saveVar() {
        $this->save();
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function setValue($value) {
        $this->value = $value;
    }

//
}
