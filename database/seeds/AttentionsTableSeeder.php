<?php

use Illuminate\Database\Seeder;

class AttentionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('attentions')->insert([
            [
                'id' => 1,
                'name' => 'ความรัก'
            ],
            [
                'id' => 3,
                'name' => 'กีฬา'
            ],
            [
                'id' => 4,
                'name' => 'ของสะสม'
            ],
            [
                'id' => 5,
                'name' => 'มือถือและอุปกรณ์อิเล็กทรอนิกส์'
            ],
        ]);
    }
}
