<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\User;

class UserRepository
{
    public User $user;
    
    public function __construct(User $user){
        $this->user = $user;
    }
}
