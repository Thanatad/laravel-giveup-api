<?php

use Illuminate\Database\Seeder;

class ObjectCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('object_categories')->insert([
            [
                'id' => 1,
                'name' => 'ขอเล่น สินค้าแม่และเด็ก'
            ],
            [
                'id' => 2,
                'name' => 'ของสะสม'
            ],
            [
                'id' => 3,
                'name' => 'ของโบราณ'
            ]
        ]);
    }
}
