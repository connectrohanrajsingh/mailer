<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class MailerSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {

        $userSeederFilePath = "static/seeder/users.json";

        $disk = Storage::disk("local");

        if ($disk->exists($userSeederFilePath)) {
            $users = json_decode($disk->get($userSeederFilePath), TRUE);
            foreach ($users as $user) {
                User::create($user);
            }
        }
    }
}
