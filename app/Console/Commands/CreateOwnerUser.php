<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateOwnerUser extends Command
{
    protected $signature = 'make:owner-user';

    protected $description = 'Create a new user with role owner';

    public function handle(): void
    {
        $name = $this->ask('Name');
        $email = $this->ask('Email');
        $phone = $this->ask('Phone number');
        $password = $this->secret('Password');

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'phone_number' => $phone,
            'password' => Hash::make($password),
            'role' => 'owner',
        ]);

        $this->info("Owner user created: {$user->email}");
    }
}
