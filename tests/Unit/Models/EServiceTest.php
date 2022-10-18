<?php
/*
 * File name: EServiceTest.php
 * Last modified: 2021.02.05 at 13:07:43
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
 */

namespace Models;

use App\Models\Article;
use Tests\TestCase;

class EServiceTest extends TestCase
{

    public function testGetAvailableAttribute()
    {
        $article = Article::find(32);
        self::assertTrue($article->available);
    }
}
