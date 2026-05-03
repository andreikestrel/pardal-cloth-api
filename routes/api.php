<?php

use Illuminate\Support\Facades\Route;

// Webhook routes — no session auth, signature-validated
// POST /api/webhooks/payment

// Internal WS notification route — secret header validated
// POST /api/internal/notify (proxied to pardal-cloth-ws)
