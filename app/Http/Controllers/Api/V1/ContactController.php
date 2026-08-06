<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\IpBlacklist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        // IP blacklist check
        if (IpBlacklist::isBlocked($request->ip())) {
            return response()->json(['message' => 'دسترسی شما مسدود شده است.'], 403);
        }

        // Spam protection — حداکثر ۵ پیام در ساعت از یه IP
        $spamKey = 'contact_limit:' . $request->ip();
        $count = Cache::get($spamKey, 0);
        if ($count >= 5) {
            return response()->json(['message' => 'تعداد پیام‌های شما بیش از حد مجاز است.'], 429);
        }

        $data = $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email|max:200',
            'phone'   => 'nullable|string|max:20',
            'company' => 'nullable|string|max:100',
            'subject' => 'required|string|max:200',
            'message' => 'required|string|max:2000',
        ]);

        $contact = Contact::create($data + [
            'ip_address' => $request->ip(),
            'status'     => 'unread',
        ]);

        // spam counter
        Cache::put($spamKey, $count + 1, now()->addHour());
        \App\Models\Webhook::dispatch('contact.created', $contact->toArray());

        // notification به ادمین
        try {
            Mail::to(config('mail.admin_address', 'admin@toolmaster.com'))
                ->queue(new \App\Mail\ContactNotification($contact));
        } catch (\Throwable $e) {
            \Log::warning('Contact mail failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'data'    => ['id' => $contact->id],
            'message' => 'پیام شما با موفقیت ثبت شد.',
        ], 201);
    }
}
