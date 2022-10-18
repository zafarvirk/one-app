<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanArticle extends Model
{
    public $fillable = [
        'plan_id',
        'article_id',
    ];
}
