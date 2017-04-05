<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {

    public function run()
    {
        $this->call('UserTableSeeder');

        $this->command->info('User table seeded!');
    }

}

class UserTableSeeder extends Seeder {

    public function run()
    {
        // DB::table('users')->delete();
        //App\User::create(['email' => 'foo@bar.com', 'password' => 'asdfasdffd']);

        DB::table('publishers')->delete();
        App\Publisher::create(['name' => '新华网']);
    }

}
