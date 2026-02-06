<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Activity;
use App\Models\User;
use App\Models\Project;
use App\Models\Employee;
use App\Models\Client;
use Carbon\Carbon;

class ActivityLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إنشاء بعض الأنشطة التجريبية
        $this->createSampleActivities();
    }

    private function createSampleActivities(): void
    {
        $users = User::all();
        $projects = Project::all();
        $employees = Employee::all();
        $clients = Client::all();

        if ($users->isEmpty() || $projects->isEmpty() || $employees->isEmpty() || $clients->isEmpty()) {
            $this->command->info('Skipping activity log seeding - required models not found.');
            return;
        }

        $activities = [
            [
                'description' => 'Created Project: Sample Project 1',
                'event' => 'created',
                'log_name' => 'projects',
                'subject_type' => Project::class,
                'subject_id' => $projects->first()->id,
                'causer_type' => User::class,
                'causer_id' => $users->first()->id,
                'properties' => [
                    'name' => 'Sample Project 1',
                    'status' => 'active',
                    'client_id' => $clients->first()->id ?? null,
                ],
                'created_at' => Carbon::now()->subDays(5),
            ],
            [
                'description' => 'Updated Project: Sample Project 1',
                'event' => 'updated',
                'log_name' => 'projects',
                'subject_type' => Project::class,
                'subject_id' => $projects->first()->id,
                'causer_type' => User::class,
                'causer_id' => $users->first()->id,
                'properties' => [
                    'status' => [
                        'old' => 'pending',
                        'new' => 'active'
                    ],
                ],
                'created_at' => Carbon::now()->subDays(3),
            ],
            [
                'description' => 'Created Employee: John Doe',
                'event' => 'created',
                'log_name' => 'employees',
                'subject_type' => Employee::class,
                'subject_id' => $employees->first()->id,
                'causer_type' => User::class,
                'causer_id' => $users->first()->id,
                'properties' => [
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'position' => 'Developer',
                ],
                'created_at' => Carbon::now()->subDays(7),
            ],
            [
                'description' => 'Created Client: ABC Company',
                'event' => 'created',
                'log_name' => 'clients',
                'subject_type' => Client::class,
                'subject_id' => $clients->first()->id,
                'causer_type' => User::class,
                'causer_id' => $users->first()->id,
                'properties' => [
                    'name' => 'ABC Company',
                    'email' => 'contact@abc.com',
                    'phone' => '+1234567890',
                ],
                'created_at' => Carbon::now()->subDays(10),
            ],
            [
                'description' => 'Updated Client: ABC Company',
                'event' => 'updated',
                'log_name' => 'clients',
                'subject_type' => Client::class,
                'subject_id' => $clients->first()->id,
                'causer_type' => User::class,
                'causer_id' => $users->first()->id,
                'properties' => [
                    'phone' => [
                        'old' => '+1234567890',
                        'new' => '+1987654321'
                    ],
                ],
                'created_at' => Carbon::now()->subDays(2),
            ],
            [
                'description' => 'Project Status Changed',
                'event' => 'custom',
                'log_name' => 'projects',
                'subject_type' => Project::class,
                'subject_id' => $projects->first()->id,
                'causer_type' => User::class,
                'causer_id' => $users->first()->id,
                'properties' => [
                    'action' => 'status_change',
                    'old_status' => 'active',
                    'new_status' => 'completed',
                    'reason' => 'Project delivered successfully',
                ],
                'created_at' => Carbon::now()->subDay(),
            ],
        ];

        foreach ($activities as $activityData) {
            Activity::create($activityData);
        }

        $this->command->info('Sample activity logs created successfully!');
    }
}
