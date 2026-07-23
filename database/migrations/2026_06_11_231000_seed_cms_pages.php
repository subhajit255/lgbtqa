<?php

use App\Models\Cms;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $pages = [
            [
                'title' => 'Mental Health Resources',
                'alias' => 'mental-health-resources',
                'description' => '<h1>Mental Health Resources</h1><p>We understand that the LGBTQIA+ community faces unique challenges. Here are crisis lines and support resources available to help you:</p><ul><li><strong>National Suicide Prevention Lifeline:</strong> Call 988 for free, confidential support 24/7.</li><li><strong>The Trevor Project:</strong> Call 1-866-488-7386 or text START to 678-678 for LGBTQ youth crisis support.</li><li><strong>Trans Lifeline:</strong> Call 877-565-8860 for peer support run by and for trans individuals.</li></ul>',
            ],
            [
                'title' => 'Community Guidelines',
                'alias' => 'community-guidelines',
                'description' => '<h1>Community Guidelines</h1><p>Welcome to Bloom! To ensure a safe, inclusive, and welcoming environment for everyone, please adhere to the following rules:</p><ol><li><strong>Be Respectful:</strong> Harassment, bullying, and hate speech are strictly prohibited.</li><li><strong>Stay Safe:</strong> Do not share sensitive personal information publicly.</li><li><strong>Be Authentic:</strong> Misrepresentation or impersonation will lead to account suspension.</li></ol>',
            ],
            [
                'title' => 'Getting Started',
                'alias' => 'getting-started',
                'description' => '<h1>Getting Started with Bloom</h1><p>Welcome to Bloom! Here is how to make the most of your experience:</p><ol><li><strong>Complete Your Profile:</strong> Add photos, write an about section, and choose your hobbies/interests.</li><li><strong>Configure Settings:</strong> Adjust your privacy, notifications, and discovery preferences in settings.</li><li><strong>Connect:</strong> Explore the feed, match with like-minded individuals, and join community chats.</li></ol>',
            ],
            [
                'title' => 'Contact Support',
                'alias' => 'contact-support',
                'description' => '<h1>Contact Support</h1><p>Have questions, issues, or feedback? Our support team is here to help you.</p><p>You can email us directly at <strong>support@example.com</strong> or fill out the support form in the app. We aim to respond to all inquiries within 24 hours.</p>',
            ],
            [
                'title' => 'Report a Bug',
                'alias' => 'report-bug',
                'description' => '<h1>Report a Bug</h1><p>Found something that isn\'t working right? Help us improve Bloom by reporting it!</p><p>Please send an email to <strong>bugs@example.com</strong> with a description of the bug, steps to reproduce it, and screenshots if possible.</p>',
            ],
            [
                'title' => 'Rate the App',
                'alias' => 'rate-app',
                'description' => '<h1>Rate the App</h1><p>Love using Bloom? Please share your experience and rate us in the App Store!</p><p>Your ratings and reviews help other members of the community discover Bloom and help us grow.</p>',
            ],
            [
                'title' => 'Privacy Policy',
                'alias' => 'privacy-policy',
                'description' => '<h1>Privacy Policy</h1><p>Your privacy is our highest priority. This Privacy Policy details how we collect, use, and protect your personal information:</p><p>We collect information you provide during registration, profile setup, and app usage. We use this data to provide and improve services, show relevant content, and keep the platform secure. We do not sell your personal data to third parties.</p>',
            ],
            [
                'title' => 'Terms of Service',
                'alias' => 'terms-service',
                'description' => '<h1>Terms of Service</h1><p>By using Bloom, you agree to comply with these Terms of Service. Please read them carefully:</p><p>You must be at least 18 years old to use Bloom. You agree to use the platform in compliance with all applicable laws and our community standards. We reserve the right to suspend accounts that violate these terms.</p>',
            ],
            [
                'title' => 'Cookie Policy',
                'alias' => 'cookie-policy',
                'description' => '<h1>Cookie Policy</h1><p>Bloom uses cookies and similar technologies to improve your experience on our platform.</p><p>Cookies help us remember your login sessions, analyze traffic, and personalize your experience. You can manage cookie preferences in your browser settings, though disabling some cookies may affect app functionality.</p>',
            ],
            [
                'title' => 'Community Standards',
                'alias' => 'community-standards',
                'description' => '<h1>Community Standards</h1><p>Bloom is dedicated to providing a safe, positive space for the LGBTQIA+ community. Our community standards are built around inclusivity, respect, and mutual support.</p><p>We have zero tolerance for discrimination, bigotry, or exclusion. Respect the identities, pronouns, and boundaries of all members.</p>',
            ],
            [
                'title' => 'Open Source Licenses',
                'alias' => 'open-source-licenses',
                'description' => '<h1>Open Source Licenses</h1><p>Bloom is built with the help of open source software. We would like to thank the developers and contributors of the libraries we use.</p><p>Detailed license text and copyright notices for third-party software are available upon request or in the official repository documentation.</p>',
            ],
        ];

        foreach ($pages as $page) {
            // Using updateOrCreate to prevent duplicate entries if run multiple times
            Cms::updateOrCreate(
                ['alias' => $page['alias']],
                [
                    'title' => $page['title'],
                    'description' => $page['description'],
                    'is_active' => 1,
                    'for_home' => 0,
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $aliases = [
            'mental-health-resources',
            'community-guidelines',
            'getting-started',
            'contact-support',
            'report-bug',
            'rate-app',
            'privacy-policy',
            'terms-service',
            'cookie-policy',
            'community-standards',
            'open-source-licenses',
        ];

        Cms::whereIn('alias', $aliases)->forceDelete();
    }
};
