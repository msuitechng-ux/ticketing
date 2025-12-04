<?php

namespace Database\Seeders;

use App\Models\Ceremony;
use App\Models\Graduate;
use App\Models\User;
use App\Services\TicketAllocationService;
use App\Services\TicketRequestService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles and permissions first
        $this->call(RolePermissionSeeder::class);

        // Create admin user
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@northumbria.ac.uk',
        ]);
        $admin->assignRole('Admin');

        // Create security staff user
        $security = User::factory()->create([
            'name' => 'Security Staff',
            'email' => 'security@northumbria.ac.uk',
        ]);
        $security->assignRole('Security Staff');

        // Create event staff user
        $eventStaff = User::factory()->create([
            'name' => 'Event Staff',
            'email' => 'event@northumbria.ac.uk',
        ]);
        $eventStaff->assignRole('Event Staff');

        // Create ceremonies
        $upcomingCeremony = Ceremony::create([
            'name' => 'Summer Graduation Ceremony 2025',
            'description' => 'Summer graduation for all faculties',
            'ceremony_date' => now()->addMonths(2),
            'venue' => 'Northumbria University London Campus Main Hall',
            'venue_address' => '110 Middlesex Street, London, E1 7HT',
            'total_capacity' => 500,
            'base_tickets_per_graduate' => 2,
            'ticket_request_deadline' => now()->addMonth(),
            'redistribution_deadline' => now()->addMonths(2)->subWeek(),
            'is_active' => true,
        ]);

        $winterCeremony = Ceremony::create([
            'name' => 'Winter Graduation Ceremony 2025',
            'description' => 'Winter graduation for all faculties',
            'ceremony_date' => now()->addMonths(6),
            'venue' => 'Northumbria University London Campus Main Hall',
            'venue_address' => '110 Middlesex Street, London, E1 7HT',
            'total_capacity' => 450,
            'base_tickets_per_graduate' => 2,
            'ticket_request_deadline' => now()->addMonths(5),
            'redistribution_deadline' => now()->addMonths(6)->subWeek(),
            'is_active' => true,
        ]);

        // Create student users and graduates
        $faculties = ['Faculty of Business', 'Faculty of Engineering', 'Faculty of Health Sciences', 'Faculty of Arts'];
        $degreeLevels = ['Undergraduate', 'Masters', 'PhD'];

        $allocationService = app(TicketAllocationService::class);
        $requestService = app(TicketRequestService::class);

        // Create the main test student
        $testStudent = User::factory()->create([
            'name' => 'John Smith',
            'email' => 'student@northumbria.ac.uk',
        ]);
        $testStudent->assignRole('Student');

        $testGraduate = Graduate::create([
            'user_id' => $testStudent->id,
            'ceremony_id' => $upcomingCeremony->id,
            'student_id' => 'NU2025001',
            'student_name' => 'John Smith',
            'degree_level' => 'Undergraduate',
            'faculty' => 'Faculty of Business',
            'department' => 'Business Management',
        ]);

        // Allocate base tickets
        $allocationService->allocateBaseTickets($testGraduate);

        // Create a ticket request for the test student
        $requestService->createRequest($testGraduate, 3, 'I have a large family and would like to invite my parents, siblings, and grandparents.');

        // Create 30 more students with graduates
        for ($i = 2; $i <= 30; $i++) {
            $studentUser = User::factory()->create([
                'name' => fake()->name(),
                'email' => 'student' . $i . '@northumbria.ac.uk',
            ]);
            $studentUser->assignRole('Student');

            $graduate = Graduate::create([
                'user_id' => $studentUser->id,
                'ceremony_id' => $i <= 20 ? $upcomingCeremony->id : $winterCeremony->id,
                'student_id' => 'NU2025' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'student_name' => $studentUser->name,
                'degree_level' => fake()->randomElement($degreeLevels),
                'faculty' => fake()->randomElement($faculties),
                'department' => fake()->randomElement([
                    'Business Management',
                    'Computer Science',
                    'Nursing',
                    'Digital Marketing',
                    'Mechanical Engineering',
                    'Psychology',
                ]),
            ]);

            // Allocate base tickets
            $allocationService->allocateBaseTickets($graduate);

            // 50% chance of requesting extra tickets
            if (rand(0, 1)) {
                $request = $requestService->createRequest(
                    $graduate,
                    rand(1, 4),
                    fake()->sentence(12)
                );

                // 70% chance of being approved
                if (rand(1, 10) <= 7) {
                    $approvedQty = min($request->requested_quantity, rand(1, 3));
                    $requestService->approveRequest($request, $approvedQty, $admin, 'Approved based on availability');
                } elseif (rand(1, 10) <= 2) {
                    $requestService->denyRequest($request, $admin, 'Insufficient capacity');
                }
            }
        }

        $this->command->info('Database seeded successfully!');
        $this->command->info('Test Users Created:');
        $this->command->info('- Admin: admin@northumbria.ac.uk / password');
        $this->command->info('- Student: student@northumbria.ac.uk / password');
        $this->command->info('- Security: security@northumbria.ac.uk / password');
        $this->command->info('- Event Staff: event@northumbria.ac.uk / password');
        $this->command->info('');
        $this->command->info('Created:');
        $this->command->info('- 2 Ceremonies');
        $this->command->info('- 30 Students with Graduates');
        $this->command->info('- Base tickets allocated to all graduates');
        $this->command->info('- Multiple ticket requests with various statuses');
    }
}
