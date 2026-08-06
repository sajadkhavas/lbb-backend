<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate(['email' => 'required|email']);

        $sub = NewsletterSubscriber::firstOrCreate(
            ['email' => $data['email']],
            ['is_active' => true, 'subscribed_at' => now()]
        );

        return response()->json([
            'data'    => ['id' => $sub->id],
            'message' => 'با موفقیت در خبرنامه عضو شدید.',
        ], 201);
    }
}
