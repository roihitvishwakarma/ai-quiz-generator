<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\Option;

class OptionRepository
{
    public Option $option;
    
    public function __construct(Option $option){
        $this->option = $option;
    }
}
