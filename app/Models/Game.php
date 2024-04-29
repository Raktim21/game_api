<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function users(){
        return $this->belongsToMany(User::class, 'game_users', 'game_id', 'user_id')->withPivot('position')->orderBy('pivot_position');
    }

    public function scopeSearch(Builder $q){

        return $q->when(request()->search, function ($q) {
                    $q->where('uuid', 'like', '%' . request()->search . '%')
                        ->orWhere('uuid', 'like', '%' . request()->search . '%');
                })->when(request()->status , function ($q) {
                    if (request()->status == 'active') {
                        $q->where('active', 1);
                    }else {
                        $q->where('active', 0);
                    }
                });
    }
}
