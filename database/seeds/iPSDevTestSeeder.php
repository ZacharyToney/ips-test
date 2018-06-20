<?php

use Illuminate\Database\Seeder;
use App\Module;

class iPSDevTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        for ($i = 1; $i <= 7; $i++){
            Module::insert([
                [
                    'course_key' => 'ipa',
                    'name' => 'IPA Module ' . $i
                ],

                [
                    'course_key' => 'iea',
                    'name' => 'IEA Module ' . $i
                ],

                [
                    'course_key' => 'iaa',
                    'name' => 'IAA Module ' . $i
                ]
            ]);
        }

        DB::table('module_infusionsoft_tags')->insert([
            [
                'id' => 154,
                'name' => 'Module reminders completed',
                'description' => null,
                'category' => null
            ]
        ]);
        for ($i = 0; $i <= 6; $i++){
            DB::table('module_infusionsoft_tags')->insert([
                [
                    'id' => 138 + ($i * 2),
                    'name' => 'Start IAA Module ' . ($i + 1). ' Reminders',
                    'description' => null,
                    'category' => null
                ],
                [
                    'id' => 124 + ($i * 2),
                    'name' => 'Start IEA Module ' . ($i + 1). ' Reminders',
                    'description' => null,
                    'category' => null
                ],
                [
                    'id' => 110 + ($i * 2),
                    'name' => 'Start IPA Module ' . ($i + 1). ' Reminders',
                    'description' => null,
                    'category' => null
                ]
            ]);
        }



    }
}
