<?php

namespace Tests\Feature;

use App\Enums\AccountSettingsEnum;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\Hierarchy;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase, WithFaker, Hierarchy;

    public function test_setting_by_key(): void
    {
        $customerId = 'cus_'.Str::uuid();
        $stripeCustomerIdKey = AccountSettingsEnum::STRIPE_CUSTOMER_ID->value;

        $user = $this->createUser();
        $user->account->settings()->create([
            'key' => $stripeCustomerIdKey,
            'value' => $customerId,
        ]);

        $setting = $user->account->settings()->byKey($stripeCustomerIdKey)->first();
        $this->assertEquals($customerId, $setting->value);
    }

    public function test_setting_by_key_value(): void
    {
        $customerId = 'cus_'.Str::uuid();
        $stripeCustomerIdKey = AccountSettingsEnum::STRIPE_CUSTOMER_ID->value;

        $user = $this->createUser();
        $user->account->settings()->create([
            'key' => $stripeCustomerIdKey,
            'value' => $customerId,
        ]);

        $setting = $user->account->settings()->byKeyValue($stripeCustomerIdKey, $customerId)->first();
        $this->assertEquals($customerId, $setting->value);
    }

    public function test_setting(): void
    {
        $customerId = 'cus_'.Str::uuid();
        $stripeCustomerIdKey = AccountSettingsEnum::STRIPE_CUSTOMER_ID->value;

        $user = $this->createUser();
        $user->account->settings()->create([
            'key' => $stripeCustomerIdKey,
            'value' => $customerId,
        ]);

        $setting = Setting::byKeyValue($stripeCustomerIdKey, $customerId)->first();
        $this->assertEquals($customerId, $setting->value);
    }
}
