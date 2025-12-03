<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = ['topic','question','user_id','uuid','active','explanation'];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function options(){
        return $this->hasMany(Option::class);
    }

    public function userSelectedAnswer(){
        return $this->hasOne(Option::class)->where('user_answer', 1);
    }

    public function correctAnswer(){
        return $this->hasOne(Option::class)->where('is_correct', 1);
    }

    public function userScore(){
        return $this->hasMany(Option::class)->where('is_correct', 1)->where('user_answer', 1);
    }
}
