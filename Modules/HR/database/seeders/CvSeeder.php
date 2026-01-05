<?php

declare(strict_types=1);

namespace Modules\HR\Database\Seeders;

use Modules\HR\Models\Cv;
use Illuminate\Database\Seeder;

class CvSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample CVs with realistic data
        $cvs = [
            [
                'name' => 'Ahmed Hassan',
                'email' => 'ahmed.hassan@example.com',
                'phone' => '+966501234567',
                'country' => 'Saudi Arabia',
                'state' => 'Riyadh',
                'city' => 'Riyadh',
                'address' => 'King Fahd Road, Riyadh',
                'birth_date' => '1990-05-15',
                'gender' => 'male',
                'marital_status' => 'married',
                'nationality' => 'Saudi',
                'religion' => 'Islam',
                'summary' => 'Experienced software developer with 5+ years in web development using Laravel and Vue.js.',
                'skills' => 'Laravel, Vue.js, MySQL, Git, Docker, AWS',
                'experience' => 'Senior Developer at TechCorp (2020-2023)\n- Led development of 3 major web applications\n- Mentored junior developers\n- Implemented CI/CD pipelines',
                'education' => 'Bachelor of Computer Science\nKing Saud University (2014)',
                'projects' => 'E-commerce Platform (Laravel + Vue.js)\nHR Management System (Laravel)\nInventory Management System',
                'certifications' => 'AWS Certified Developer\nLaravel Certified Developer',
                'languages' => 'Arabic (Native), English (Fluent)',
                'interests' => 'Open Source Development, Reading Tech Blogs, Traveling',
                'references' => 'Dr. Mohammed Ali - Professor at KSU\nEng. Sarah Ahmed - Senior Developer at TechCorp',
                'cover_letter' => 'I am passionate about creating innovative web solutions and have a strong track record of delivering high-quality applications.',
                'portfolio' => 'https://ahmed-hassan.dev',
            ],
            [
                'name' => 'Fatima Al-Zahra',
                'email' => 'fatima.alzahra@example.com',
                'phone' => '+966502345678',
                'country' => 'Saudi Arabia',
                'state' => 'Jeddah',
                'city' => 'Jeddah',
                'address' => 'Corniche Road, Jeddah',
                'birth_date' => '1992-08-22',
                'gender' => 'female',
                'marital_status' => 'single',
                'nationality' => 'Saudi',
                'religion' => 'Islam',
                'summary' => 'Creative UI/UX designer with expertise in user-centered design and modern design tools.',
                'skills' => 'Figma, Adobe Creative Suite, Sketch, HTML/CSS, JavaScript, React',
                'experience' => 'UI/UX Designer at DesignStudio (2021-2023)\n- Designed user interfaces for 10+ mobile apps\n- Conducted user research and usability testing\n- Created design systems and style guides',
                'education' => 'Bachelor of Design\nPrincess Nourah University (2016)',
                'projects' => 'Banking App Redesign\nE-learning Platform UI\nRestaurant Ordering App',
                'certifications' => 'Google UX Design Certificate\nFigma Advanced Course',
                'languages' => 'Arabic (Native), English (Fluent), French (Intermediate)',
                'interests' => 'Art, Photography, Travel, Design Conferences',
                'references' => 'Prof. Aisha Mohammed - Design Department Head\nEng. Omar Khalil - Product Manager at DesignStudio',
                'cover_letter' => 'I believe in creating designs that not only look beautiful but also solve real user problems.',
                'portfolio' => 'https://fatima-designs.com',
            ],
            [
                'name' => 'Omar Abdullah',
                'email' => 'omar.abdullah@example.com',
                'phone' => '+966503456789',
                'country' => 'Saudi Arabia',
                'state' => 'Dammam',
                'city' => 'Dammam',
                'address' => 'King Khalid Street, Dammam',
                'birth_date' => '1988-12-10',
                'gender' => 'male',
                'marital_status' => 'married',
                'nationality' => 'Saudi',
                'religion' => 'Islam',
                'summary' => 'Project manager with 8+ years experience in IT project management and team leadership.',
                'skills' => 'Project Management, Agile/Scrum, Jira, Microsoft Project, Risk Management, Stakeholder Management',
                'experience' => 'Senior Project Manager at IT Solutions (2019-2023)\n- Managed 15+ IT projects worth $2M+\n- Led cross-functional teams of 20+ members\n- Improved project delivery time by 25%',
                'education' => 'MBA in Project Management\nKing Fahd University (2018)\nBachelor of Information Technology\nKing Fahd University (2012)',
                'projects' => 'ERP System Implementation\nCloud Migration Project\nDigital Transformation Initiative',
                'certifications' => 'PMP (Project Management Professional)\nPRINCE2 Practitioner\nScrum Master Certified',
                'languages' => 'Arabic (Native), English (Fluent)',
                'interests' => 'Leadership Development, Technology Trends, Golf',
                'references' => 'Dr. Khalid Al-Rashid - MBA Director\nEng. Hassan Mohammed - CTO at IT Solutions',
                'cover_letter' => 'I am committed to delivering projects on time and within budget while ensuring high quality standards.',
                'portfolio' => 'https://omar-pm.com',
            ],
        ];

        // Create the sample CVs
        // استخدام withoutGlobalScopes() لتجاوز BranchScope عند البحث
        foreach ($cvs as $cvData) {
            Cv::withoutGlobalScopes()
                ->firstOrCreate(
                    ['phone' => $cvData['phone']], // استخدام phone فقط لأنه required وليس nullable
                    $cvData
                );
        }
    }
}
