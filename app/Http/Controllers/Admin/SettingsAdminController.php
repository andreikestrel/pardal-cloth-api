<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateGeneralSettingsRequest;
use App\Http\Requests\Admin\UpdatePaymentSettingsRequest;
use App\Models\PaymentSetting;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class SettingsAdminController extends Controller
{
    public function general(): Response
    {
        return Inertia::render('Admin/Settings/General', [
            'settings' => Setting::first(),
        ]);
    }

    public function updateGeneral(UpdateGeneralSettingsRequest $request): RedirectResponse
    {
        $settings = Setting::firstOrNew([]);
        $settings->fill($request->safe()->except('logo'));
        $settings->save();

        if ($request->hasFile('logo')) {
            $settings->clearMediaCollection('logo');
            $settings->addMediaFromRequest('logo')->toMediaCollection('logo');
        }

        // Bust the cached settings shared via HandleInertiaRequests
        Cache::forget('app_settings');

        return back()->with('success', 'Settings updated successfully.');
    }

    public function payment(): Response
    {
        // Mask credential values so sensitive keys never reach the frontend
        $gateways = PaymentSetting::all()->map(fn ($g) => [
            'id'          => $g->id,
            'gateway'     => $g->gateway,
            'active'      => $g->active,
            'credentials' => collect($g->getDecryptedCredentials())->mapWithKeys(
                fn ($v, $k) => [$k => filled($v) ? '••••••••' : '']
            ),
        ]);

        return Inertia::render('Admin/Settings/Payment', [
            'gateways' => $gateways,
        ]);
    }

    public function updatePayment(UpdatePaymentSettingsRequest $request): RedirectResponse
    {
        $setting = PaymentSetting::where('gateway', $request->validated('gateway'))->firstOrNew([
            'gateway' => $request->validated('gateway'),
        ]);

        $setting->active = $request->validated('active', false);
        $setting->credentials = $request->validated('credentials');
        $setting->save();

        return back()->with('success', 'Payment settings updated successfully.');
    }
}
