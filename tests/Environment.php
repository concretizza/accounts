<?php

namespace Tests;

trait Environment
{
    public function runOnlyIntegrations()
    {
        if (config('app.env') != 'integrations') {
            $this->markTestSkipped('only for third party integrations');
        }
    }
}
