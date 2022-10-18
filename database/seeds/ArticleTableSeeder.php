<?php
/*
 * File name: EServicesTableSeeder.php
 * Last modified: 2022.02.15 at 16:47:17
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

use App\Models\Article;
use Illuminate\Database\Seeder;

class ArticleTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {

        DB::table('article')->truncate();

        factory(Article::class, 40)->create();
    }
}
