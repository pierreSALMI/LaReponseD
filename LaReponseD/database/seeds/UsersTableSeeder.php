<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role_admin = Role::where('name','Admin')->first();

        factory(App\User::class, 15)->create()->each(function ($user) use ($role_admin) {
            $user->assignRole($role_admin);
        });

        $role_modo = Role::where('name','Modo')->first();

        factory(App\User::class, 35)->create()->each(function ($user) use ($role_modo) {
            $user->assignRole($role_modo);
        });

        $role_user = Role::where('name','User')->first();

        factory(App\User::class, 50)->create()->each(function ($user) use ($role_user) {
            $user->assignRole($role_user);
        });
    }
}
