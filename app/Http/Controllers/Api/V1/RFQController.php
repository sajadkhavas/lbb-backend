<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Mail\RfqConfirmation;
use App\Mail\RfqNotification;
use App\Models\IpBlacklist;
use App\Models\Product;
use App\Models\RfqRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class RFQController extends Controller
{
    public function store(Request $request)
    {
        // IP blacklist check
        if (IpBlacklist::isBlocked($request->ip())) {
            return response()->json(['message' => 'دسترسی شما مسدود شده است.'], 403);
        }

        // Spam protection — حداکثر ۳ RFQ در ساعت از یه IP
        $spamKey = 'rfq_limit:' . $request->ip();
        $count = Cache::get($spamKey, 0);
        if ($count >= 3) {
            return response()->json(['message' => 'تعداد درخواست‌های شما بیش از حد مجاز است. لطفاً بعداً امتحان کنید.'], 429);
        }

        $validator = Validator::make($request->all(), [
            'name'             => 'required|string|max:100',
            'email'            => 'required|email|max:200',
            'phone'            => 'nullable|string|max:20',
            'company'          => 'nullable|string|max:100',
            'position'         => 'nullable|string|max:100',
            'notes'            => 'nullable|string|max:1000',
            'items'            => 'required|array|min:1|max:50',
            'items.*.id'       => 'required|string|max:200',
            'items.*.quantity' => 'required|integer|min:1|max:9999',
            'items.*.notes'    => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در اعتبارسنجی',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $rfq = RfqRequest::create([
            'name'       => $request->name,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'company'    => $request->company,
            'position'   => $request->position,
            'notes'      => $request->notes,
            'ip_address' => $request->ip(),
            'status'     => 'pending',
        ]);

        foreach ($request->items as $item) {
            $product = Product::where('slug', $item['id'])->first();
            $rfq->items()->create([
                'product_id'    => $product?->id,
                'product_name'  => $product?->name ?? $item['id'],
                'product_model' => $product?->model,
                'quantity'      => $item['quantity'],
                'notes'         => $item['notes'] ?? null,
            ]);
            if ($product) {
                Product::withoutTimestamps(fn() =>
                    Product::where('id', $product->id)->increment('rfq_count')
                );
            }
        }

        // spam counter رو update کن
        Cache::put($spamKey, $count + 1, now()->addHour());
        \App\Models\Webhook::dispatch('rfq.created', $rfq->toArray());

        // ایمیل‌ها رو بفرست
        try {
            Mail::to($rfq->email)->queue(new RfqConfirmation($rfq));
            Mail::to(config('mail.admin_address', 'admin@toolmaster.com'))
                ->queue(new RfqNotification($rfq));
        } catch (\Throwable $e) {
            \Log::warning('RFQ mail dispatch failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'reference_number' => $rfq->reference_number,
                'status'           => $rfq->status,
            ],
            'message' => 'درخواست استعلام با موفقیت ثبت شد.',
        ], 201);
    }

    public function show(string $reference)
    {
        $rfq = RfqRequest::with('items')
            ->where('reference_number', $reference)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data'    => [
                'reference'  => $rfq->reference_number,
                'status'     => $rfq->status,
                'name'       => $rfq->name,
                'itemCount'  => $rfq->items->count(),
                'created_at' => $rfq->created_at->toIso8601String(),
                'items'      => $rfq->items->map(fn($i) => [
                    'productName'  => $i->product_name,
                    'productModel' => $i->product_model,
                    'quantity'     => $i->quantity,
                    'notes'        => $i->notes,
                ]),
            ],
        ]);
    }
}
