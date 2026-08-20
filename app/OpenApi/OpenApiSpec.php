<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'TS Screen Legacy API',
    description: 'Compatible with 3 Flutter apps. Swagger grouped by app: Customer (Phone), Projector (TV), Admin. Shared routes appear in every app that calls them. App receives JSON string (text/html). Swagger Try it out receives application/json. status=1 success, -2 show msg, -1 OTP error.'
)]
#[OA\Server(url: '/', description: 'Current host')]
#[OA\Tag(name: AppTags::CUSTOMER, description: 'RemoteProjector2024 — phone. Auth plaintext, mua gói, VietQR, notify, media.')]
#[OA\Tag(name: AppTags::PROJECTOR, description: 'remote_projector_tv — TV box. Login customer, check serial, gói active, pairing, lịch chiếu, lệnh.')]
#[OA\Tag(name: AppTags::ADMIN, description: 'RemoteProjectorAdmin — CRUD gói, duyệt đơn, khách, tài khoản MD5.')]
class OpenApiSpec {}
