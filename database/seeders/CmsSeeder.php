<?php

namespace Database\Seeders;

use App\Models\Cms;
use Illuminate\Database\Seeder;

class CmsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pages = [
            [
                'title' => 'Mental Health Resources',
                'alias' => 'mental-health-resources',
                'description' => '
                    <div class="cms-content">
                        <h1>Mental Health & Support Resources</h1>
                        <p class="intro">We care deeply about your well-being. The LGBTQIA+ community can face unique pressures, and you are never alone. If you are experiencing distress, anxiety, or a mental health crisis, please reach out to these dedicated, confidential support lines.</p>
                        
                        <div class="resource-section">
                            <h2>Immediate Crisis Helplines</h2>
                            
                            <div class="resource-card">
                                <h3>988 Suicide & Crisis Lifeline</h3>
                                <p>Free, confidential support for anyone experiencing mental health-related distress or crisis. Available 24 hours a day, 7 days a week.</p>
                                <p><strong>Call or Text:</strong> <a href="tel:988">988</a> (English & Spanish)</p>
                                <p><strong>TTY Users:</strong> Use your preferred relay service or dial 711 then 988.</p>
                            </div>

                            <div class="resource-card">
                                <h3>The Trevor Project</h3>
                                <p>The leading national organization providing crisis intervention and suicide prevention services to LGBTQ young people under 25.</p>
                                <p><strong>Call:</strong> <a href="tel:1-866-488-7386">1-866-488-7386</a> (24/7)</p>
                                <p><strong>Text:</strong> Text <strong>START</strong> to <strong>678-678</strong> (24/7)</p>
                            </div>

                            <div class="resource-card">
                                <h3>Trans Lifeline</h3>
                                <p>A peer support hotline run by and for trans people. It provides direct service, resources, and connections to trans individuals in crisis.</p>
                                <p><strong>Call (US):</strong> <a href="tel:877-565-8860">877-565-8860</a></p>
                                <p><strong>Call (Canada):</strong> <a href="tel:877-330-6366">877-330-6366</a></p>
                            </div>

                            <div class="resource-card">
                                <h3>SAGE LGBTQ+ Elder Hotline</h3>
                                <p>Peer support and crisis counseling for older members of the LGBTQ+ community. Available 24 hours a day, 7 days a week.</p>
                                <p><strong>Call:</strong> <a href="tel:877-360-5428">877-360-5428</a></p>
                            </div>
                        </div>

                        <div class="resource-section pt-4">
                            <h2>Self-Care & Daily Support Tips</h2>
                            <ul>
                                <li><strong>Take a Break:</strong> Step away from social media and news when it feels overwhelming.</li>
                                <li><strong>Connect:</strong> Reach out to trusted friends, family, or support groups in the Bloom community.</li>
                                <li><strong>Mindfulness:</strong> Practice breathing exercises or simple meditation to help ground yourself during stressful moments.</li>
                            </ul>
                        </div>
                    </div>
                ',
            ],
            [
                'title' => 'Community Guidelines',
                'alias' => 'community-guidelines',
                'description' => '
                    <div class="cms-content">
                        <h1>Community Guidelines</h1>
                        <p class="intro">Bloom is designed to be a safe, positive, and affirming space for the LGBTQIA+ community and our allies. To help us preserve this environment, all members must agree to follow these guidelines.</p>
                        
                        <div class="guidelines-list">
                            <div class="guideline-item">
                                <h2>1. Zero Tolerance for Hate Speech & Harassment</h2>
                                <p>We maintain a strict zero-tolerance policy against any form of discrimination, hate speech, bullying, slurs, or targeted harassment. Respect the diverse gender expressions, sexual orientations, ethnicities, and cultures of our community.</p>
                            </div>

                            <div class="guideline-item">
                                <h2>2. Authenticity & Fake Profiles</h2>
                                <p>Honesty builds trust. Do not create fake accounts, impersonate others, or mislead members about your identity. Catfishing or scamming will result in immediate and permanent account ban.</p>
                            </div>

                            <div class="guideline-item">
                                <h2>3. Respect Boundaries & Consent</h2>
                                <p>Consent is critical. Always respect the boundaries set by other members. Do not spam, send unsolicited sexually explicit content, or press individuals for information they are not comfortable sharing.</p>
                            </div>

                            <div class="guideline-item">
                                <h2>4. Safety & Personal Information</h2>
                                <p>Protect your privacy. Never share your home address, financial details, government ID numbers, or passwords publicly in chats or on your profile feed. Be cautious when transitioning from online chats to meeting in person.</p>
                            </div>

                            <div class="guideline-item">
                                <h2>5. Content Restrictions</h2>
                                <p>Do not upload or share content that is illegal, violent, or promotes self-harm. Adult content (NSFW) must comply with our media filters and not be displayed in public profile pictures or primary banners.</p>
                            </div>
                        </div>

                        <div class="reporting-box">
                            <h3>How to Report Violations</h3>
                            <p>If you encounter a profile, post, or message that violates these guidelines, please use the built-in <strong>Report</strong> button or email us at <a href="mailto:support@example.com">support@example.com</a>. We review all reports within 24 hours.</p>
                        </div>
                    </div>
                ',
            ],
            [
                'title' => 'Getting Started',
                'alias' => 'getting-started',
                'description' => '
                    <div class="cms-content">
                        <h1>Getting Started with Bloom</h1>
                        <p class="intro">Welcome to Bloom! We are thrilled to have you in our community. Here is a quick guide to help you set up your profile, adjust your settings, and start making meaningful connections.</p>
                        
                        <div class="steps-section">
                            <h2>3 Steps to Start Your Journey</h2>
                            
                            <div class="step-card">
                                <h3>Step 1: Build an Authentic Profile</h3>
                                <p>Let the community get to know the real you. Go to your Profile settings and fill out your details:</p>
                                <ul>
                                    <li>Upload clear profile pictures and gallery photos.</li>
                                    <li>Select your interests, hobbies, and values.</li>
                                    <li>Add your pronouns and gender identity to help others connect with you respectfully.</li>
                                    <li>Write a brief "About Me" bio sharing your personality or what you are looking for.</li>
                                </ul>
                            </div>

                            <div class="step-card">
                                <h3>Step 2: Configure Your App Toggles</h3>
                                <p>Customize your experience. Head over to <strong>Settings -> App Setting Toggles</strong> to manage your preferences:</p>
                                <ul>
                                    <li><strong>Privacy:</strong> Toggle Stealth Mode or Ghost Mode for extra privacy control.</li>
                                    <li><strong>Discovery:</strong> Choose whether you want to appear in local search lists.</li>
                                    <li><strong>Notifications:</strong> Turn on push and email notifications so you never miss a new chat, friend request, or nearby event.</li>
                                </ul>
                            </div>

                            <div class="step-card">
                                <h3>Step 3: Connect & Explore</h3>
                                <p>Start engaging with the Bloom ecosystem:</p>
                                <ul>
                                    <li><strong>Feed:</strong> Post updates, react, and comment on other members\' posts.</li>
                                    <li><strong>Chats:</strong> Initiate conversations with friends or join local community group chats.</li>
                                    <li><strong>Events:</strong> Keep an eye out for pride and LGBTQIA+ events happening in your local area.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                ',
            ],
            [
                'title' => 'Contact Support',
                'alias' => 'contact-support',
                'description' => '
                    <div class="cms-content">
                        <h1>Contact Support</h1>
                        <p class="intro">Need help? Whether you are having technical issues, have questions about your account, or want to report a concern, the Bloom support team is here to assist you.</p>
                        
                        <div class="support-channels">
                            <h2>Ways to Reach Us</h2>
                            
                            <div class="channel-card">
                                <h3>Email Support</h3>
                                <p>Send us an email detailing your issue. We strive to reply to all tickets within 24 hours.</p>
                                <p><strong>Email Address:</strong> <a href="mailto:support@example.com">support@example.com</a></p>
                            </div>

                            <div class="channel-card">
                                <h3>In-App Support Form</h3>
                                <p>Navigate to <strong>Account -> Help & Support</strong> in the app to submit a ticket directly. This allows us to collect device diagnostic details to resolve technical issues faster.</p>
                            </div>
                        </div>

                        <div class="support-info">
                            <h3>Tips for Faster Assistance:</h3>
                            <ul>
                                <li>State your registered email address and username.</li>
                                <li>Describe the problem clearly, including steps to reproduce the error.</li>
                                <li>Mention your device model (e.g., iPhone 15, Samsung S24) and OS version.</li>
                            </ul>
                            <p><strong>Support Operating Hours:</strong> Monday to Friday, 9:00 AM - 6:00 PM EST.</p>
                        </div>
                    </div>
                ',
            ],
            [
                'title' => 'Report a Bug',
                'alias' => 'report-bug',
                'description' => '
                    <div class="cms-content">
                        <h1>Report a Bug</h1>
                        <p class="intro">Help us make Bloom better! If you run into a glitch, crash, or unexpected behavior in the application, please let us know so we can fix it.</p>
                        
                        <div class="bug-guidelines">
                            <h2>How to File a Bug Report</h2>
                            <p>Please send your reports to <a href="mailto:bugs@example.com">bugs@example.com</a> or use the bug reporter in settings. Please include the following details where possible:</p>
                            
                            <ol>
                                <li><strong>Summary:</strong> A quick one-sentence description of the issue.</li>
                                <li><strong>Steps to Reproduce:</strong> What buttons did you click to trigger the bug? (e.g., 1. Open Profile, 2. Tap Setup Profile, 3. Select Gender -> App Crashes).</li>
                                <li><strong>Expected vs Actual Behavior:</strong> What did you expect to happen, and what actually happened?</li>
                                <li><strong>Device & Version:</strong> Device model (e.g., iPhone 13), app version (e.g., v2.4.0), and OS version (e.g., iOS 17.4).</li>
                                <li><strong>Screenshots/Recordings:</strong> Attach visual proof showing the bug in action.</li>
                            </ol>
                        </div>

                        <div class="bug-thankyou">
                            <p>Thank you for your help in keeping Bloom stable and smooth for the entire community!</p>
                        </div>
                    </div>
                ',
            ],
            [
                'title' => 'Rate the App',
                'alias' => 'rate-app',
                'description' => '
                    <div class="cms-content text-center">
                        <h1>Rate the App</h1>
                        <p class="intro">Enjoying your time on Bloom?</p>
                        <p>We work tirelessly to build a safe, feature-rich space for the LGBTQIA+ community. Your feedback helps us grow and lets other community members know what they can expect.</p>
                        
                        <div class="rating-stars pt-4">
                            <span class="star-icon" style="font-size: 3rem; color: #FFD700;">★ ★ ★ ★ ★</span>
                        </div>

                        <div class="rating-actions pt-4">
                            <p>Take 30 seconds to rate us on the official stores:</p>
                            <div class="d-grid gap-2 col-6 mx-auto">
                                <a href="https://apps.apple.com" target="_blank" class="btn btn-outline-dark btn-lg">Rate on iOS App Store</a>
                                <a href="https://play.google.com" target="_blank" class="btn btn-outline-dark btn-lg">Rate on Google Play Store</a>
                            </div>
                        </div>

                        <p class="feedback-note pt-4">Have constructive criticism instead? Please send it directly to <a href="mailto:feedback@example.com">feedback@example.com</a> so we can address it directly in the next release.</p>
                    </div>
                ',
            ],
            [
                'title' => 'Privacy Policy',
                'alias' => 'privacy-policy',
                'description' => '
                    <div class="cms-content">
                        <h1>Privacy Policy</h1>
                        <p class="last-updated">Last Updated: June 11, 2026</p>
                        <p>Bloom ("we", "our", or "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and protect your information when you use our mobile application and related services.</p>
                        
                        <h2>1. Information We Collect</h2>
                        <p>We collect information you provide directly to us when setting up your profile, such as:</p>
                        <ul>
                            <li>Registration data (email address, birth date, phone number).</li>
                            <li>Profile information (pronouns, interests, values, gallery photos, location preferences).</li>
                            <li>Messages, comments, posts, and engagement details in our chats and feed.</li>
                        </ul>

                        <h2>2. How We Use Your Information</h2>
                        <p>We use the collected data to provide the core services of the app, including:</p>
                        <ul>
                            <li>Connecting you with other members according to matching preferences.</li>
                            <li>Enabling chats, notifications, posts, and event sharing.</li>
                            <li>Ensuring safety, security, and moderation compliance across the platform.</li>
                            <li>Optimizing and improving application features and performance.</li>
                        </ul>

                        <h2>3. Device Permissions & Settings</h2>
                        <p>Bloom may request access to your location, notifications, camera, and photo gallery. You can customize these permissions at any time through your device settings and manage your profile visibility using the privacy settings in the app.</p>

                        <h2>4. Data Sharing & Security</h2>
                        <p>We do not sell your personal data. We implement industry-standard encryption and safety protocols to protect your personal details from unauthorized access, loss, or disclosure.</p>

                        <h2>5. Contact Us</h2>
                        <p>If you have any questions or concerns regarding our privacy practices, please contact our Data Protection Officer at <a href="mailto:privacy@example.com">privacy@example.com</a>.</p>
                    </div>
                ',
            ],
            [
                'title' => 'Terms of Service',
                'alias' => 'terms-service',
                'description' => '
                    <div class="cms-content">
                        <h1>Terms of Service</h1>
                        <p class="last-updated">Last Updated: June 11, 2026</p>
                        <p>Welcome to Bloom. By registering an account and using our mobile application and services, you agree to comply with and be bound by the following Terms of Service. Please review them carefully.</p>
                        
                        <h2>1. Eligibility</h2>
                        <p>You must be at least 18 years old to create an account and use Bloom. By using the app, you represent and warrant that you meet this requirement and have the legal capacity to enter into this agreement.</p>

                        <h2>2. Code of Conduct</h2>
                        <p>You agree to use Bloom in a respectful, lawful manner. You will not:</p>
                        <ul>
                            <li>Harass, stalk, intimidate, or discriminate against other members.</li>
                            <li>Upload explicit, adult content as your public profile photo.</li>
                            <li>Perform commercial activities, scamming, or spamming.</li>
                        </ul>

                        <h2>3. Content Rights & Licensing</h2>
                        <p>You retain ownership of the content you post on Bloom. However, by posting content, you grant us a worldwide, royalty-free license to host, store, and display that content within the application to perform the services.</p>

                        <h2>4. Account Termination</h2>
                        <p>We reserve the right to suspend, restrict, or terminate your account at any time, without notice, if we believe you have violated these terms or our community standards.</p>

                        <h2>5. Limitation of Liability</h2>
                        <p>Bloom is provided on an "as-is" basis. We do not guarantee uninterrupted, secure, or error-free operations. We are not liable for any direct or indirect damages resulting from your use of our application.</p>
                    </div>
                ',
            ],
            [
                'title' => 'Cookie Policy',
                'alias' => 'cookie-policy',
                'description' => '
                    <div class="cms-content">
                        <h1>Cookie Policy</h1>
                        <p class="last-updated">Last Updated: June 11, 2026</p>
                        <p>Bloom uses cookies, web beacons, and local storage technologies to enhance and personalize your experience on our platform. This policy explains what these technologies are and how we use them.</p>
                        
                        <h2>1. What Are Cookies?</h2>
                        <p>Cookies are small text files stored on your browser or device when you visit certain pages. They help websites and apps remember details about your visit, keep you logged in, and analyze traffic patterns.</p>

                        <h2>2. How We Use Cookies</h2>
                        <p>We use cookies for the following purposes:</p>
                        <ul>
                            <li><strong>Essential Cookies:</strong> Required to keep you authenticated in session, load your security tokens, and operate basic app features.</li>
                            <li><strong>Functionality Cookies:</strong> Used to remember your customized preferences (such as language choice or dark mode).</li>
                            <li><strong>Analytical Cookies:</strong> Help us monitor performance, identify bugs, and understand how users navigate through the app.</li>
                        </ul>

                        <h2>3. Managing Cookie Preferences</h2>
                        <p>Most web browsers allow you to control cookie settings in their configuration panel. If you decide to disable or block cookies, please note that some features of Bloom may not work as intended.</p>
                    </div>
                ',
            ],
            [
                'title' => 'Community Standards',
                'alias' => 'community-standards',
                'description' => '
                    <div class="cms-content">
                        <h1>Community Standards</h1>
                        <p class="intro">Bloom is built by and for the LGBTQIA+ community. To preserve our core value of providing a safe, affirming sanctuary for all identities, we enforce the following community standards.</p>
                        
                        <h2>1. Inclusivity & Respect</h2>
                        <p>Respect gender identities, sexual orientations, pronouns, backgrounds, and personal choices of all users. We do not tolerate bigotry, sexism, transphobia, homophobia, racism, or xenophobia.</p>

                        <h2>2. Safety & Moderation</h2>
                        <p>We actively moderate public content and profile feeds. Reports of harassment, threatening behavior, doxxing, or self-harm encouragement will be acted on immediately, and violators will be banned.</p>

                        <h2>3. Boundaries & Respectful Communication</h2>
                        <p>Respect boundaries. Unsolicited explicit texts or requests for private information are prohibited. Let all connections develop naturally and consensually.</p>

                        <h2>4. Reporting Procedure</h2>
                        <p>If you encounter behavior that violates these standards, tap on the options icon on the user\'s profile and select <strong>Report</strong>, or write directly to <a href="mailto:moderators@example.com">moderators@example.com</a>.</p>
                    </div>
                ',
            ],
            [
                'title' => 'Open Source Licenses',
                'alias' => 'open-source-licenses',
                'description' => '
                    <div class="cms-content">
                        <h1>Open Source Licenses</h1>
                        <p class="intro">Bloom is made possible by open source software. We express our gratitude to the creators, maintainers, and contributors of the libraries we use.</p>
                        
                        <h2>Major Open Source Components Used</h2>
                        
                        <div class="license-item">
                            <h3>Laravel Framework</h3>
                            <p>License: MIT License</p>
                            <p>Copyright (c) Taylor Otwell</p>
                        </div>

                        <div class="license-item">
                            <h3>Laravel Passport</h3>
                            <p>License: MIT License</p>
                            <p>Copyright (c) Taylor Otwell</p>
                        </div>

                        <div class="license-item">
                            <h3>Pusher PHP Server</h3>
                            <p>License: MIT License</p>
                            <p>Copyright (c) Pusher</p>
                        </div>

                        <div class="license-item">
                            <h3>CKEditor 5</h3>
                            <p>License: GNU General Public License (GPL) v2 or later</p>
                            <p>Copyright (c) CKSource</p>
                        </div>

                        <div class="license-item">
                            <h3>FontAwesome Icons</h3>
                            <p>License: SIL OFL 1.1 / MIT License</p>
                            <p>Copyright (c) Fonticons, Inc.</p>
                        </div>

                        <div class="license-item">
                            <h3>Bootstrap</h3>
                            <p>License: MIT License</p>
                            <p>Copyright (c) Twitter, Inc.</p>
                        </div>
                    </div>
                ',
            ],
        ];

        foreach ($pages as $page) {
            Cms::updateOrCreate(
                ['alias' => $page['alias']],
                [
                    'title' => $page['title'],
                    'description' => trim($page['description']),
                    'is_active' => 1,
                    'for_home' => 0,
                ]
            );
        }
    }
}
