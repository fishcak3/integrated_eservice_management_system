<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ChatbotFaq; 

class ChatbotFaqSeeder extends Seeder
{
    public function run(): void
    {
        // 1. CLEARANCE
        ChatbotFaq::create([
            'keyword' => 'clearance',
            'response_auth' => 'Since you are logged in, you can request a Barangay Clearance instantly by clicking on the "Certificate Requests" tab on your sidebar!',
            'response_guest' => 'To request a Barangay Clearance online, please register for an account and log in. Alternatively, you can visit the Barangay Hall during office hours.'
        ]);

        // 2. BLOTTER / COMPLAINT
        ChatbotFaq::create([
            'keyword' => 'blotter',
            'response_auth' => 'To file a blotter or complaint, please navigate to the "Security Services" or "Complaints" section in your dashboard.',
            'response_guest' => 'For security incidents and blotter reports, please log in to file a report online, or proceed immediately to the Barangay Hall.'
        ]);

        // 3. REQUEST STATUS
        ChatbotFaq::create([
            'keyword' => 'status',
            'response_auth' => 'You can check the status of your requests right on your dashboard. It will show if it is pending, processing, or ready for pickup.',
            'response_guest' => 'To check your request status, please log into your account.'
        ]);

        // 4. OFFICE HOURS
        ChatbotFaq::create([
            'keyword' => 'hours',
            'response_auth' => 'Our Barangay Hall is open Monday to Friday, from 8:00 AM to 5:00 PM.',
            'response_guest' => 'Our Barangay Hall is open Monday to Friday, from 8:00 AM to 5:00 PM.'
        ]);

        // 5. REGISTER
        ChatbotFaq::create([
            'keyword' => 'register',
            'response_auth' => 'You are already registered and logged in! How can I help you today?',
            'response_guest' => 'To register for an online portal account, click "Register" at the top. You need to enter your full name exactly as it is recorded at the Barangay Hall so our system can verify you are an actual resident, and wait for the admin to verify your account.'
        ]);

        // 6. PAYMENT METHODS
        ChatbotFaq::create([
            'keyword' => 'pay',
            'response_auth' => 'You can pay for your requested documents at the Barangay Hall cashier upon pickup. Just present your request reference number!',
            'response_guest' => 'Payments are made directly at the Barangay Hall cashier. Please log in to see the specific fees for each document.'
        ]);

        // 7. EMERGENCIES / CONTACT
        ChatbotFaq::create([
            'keyword' => 'emergency',
            'response_auth' => 'For immediate emergencies, please contact the Barangay Patrol hotline or use the "Emergency Alert" button on your dashboard.',
            'response_guest' => 'For emergencies, please contact our Barangay hotline immediately or visit the Barangay Hall. Stay safe!'
        ]);

        // 8. LOCATION / ADDRESS
        ChatbotFaq::create([
            'keyword' => 'location',
            'response_auth' => 'The Barangay Hall is located at the center of the barangay, near the main plaza.',
            'response_guest' => 'The Barangay Hall is located at the center of the barangay. You can check the "About Us" page for the exact map and address.'
        ]);
        
        // 9. GENERAL REQUIREMENTS FALLBACK
        // (If they ask "what are the requirements" without specifying the document)
        ChatbotFaq::create([
            'keyword' => 'requirement',
            'response_auth' => 'Most documents require a valid ID. To get specific requirements, try asking me about the document directly (e.g., "clearance requirements" or "business permit").',
            'response_guest' => 'Most requests require a valid ID and proof of residency. Please log in or ask me about a specific document to see its exact requirements.'
        ]);

        // 10. ACCOUNT CREATION / REGISTRATION
        ChatbotFaq::create([
            'keyword' => 'account',
            'response_auth' => 'You already have an account and are currently logged in! You have full access to our online barangay services.',
            'response_guest' => 'To create an account, click the "Register" button. Please note: you MUST already be registered in the physical Barangay records. Make sure to type your First, Middle, and Last name exactly as they appear on your barangay ID or records, otherwise the system will not recognize you.'
        ]);

        // 11. TWO FACTOR AUTHENTICATION (2FA)
        ChatbotFaq::create([
            'keyword' => '2fa',
            'response_auth' => 'You can protect your account by enabling Two-Factor Authentication (2FA) in your Account Settings. You will need an authenticator app like Google Authenticator or Authy to scan the QR code.',
            'response_guest' => 'We offer Two-Factor Authentication (2FA) to keep your account highly secure! Once you create an account and log in, you can easily enable it in your Account Settings using any standard authenticator app.'
        ]);

        // 11.B TWO FACTOR AUTHENTICATION (Alternate Keyword)
        ChatbotFaq::create([
            'keyword' => 'two-factor',
            'response_auth' => 'You can protect your account by enabling Two-Factor Authentication (2FA) in your Account Settings. You will need an authenticator app like Google Authenticator or Authy to scan the QR code.',
            'response_guest' => 'We offer Two-Factor Authentication (2FA) to keep your account highly secure! Once you create an account and log in, you can easily enable it in your Account Settings using any standard authenticator app.'
        ]);
    }
}