<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\AppConfig;
use App\OpenApi\AppTags;
use App\Support\LegacyJson;
use OpenApi\Attributes as OA;

class ConfigController extends Controller
{
    #[OA\Get(
        path: '/config6789.php',
        summary: 'Bootstrap — 3 app đọc API_SERVER',
        tags: [AppTags::CUSTOMER, AppTags::PROJECTOR, AppTags::ADMIN],
        responses: [
            new OA\Response(
                response: 200,
                description: 'JSON: COMPANY_*, API_SERVER, version fields for phone/TV/admin',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'API_SERVER', type: 'string', example: 'http://localhost:8000'),
                            new OA\Property(property: 'COMPANY_NAME', type: 'string'),
                            new OA\Property(property: 'show_payment', type: 'string', example: '1'),
                            new OA\Property(property: 'ACTIVE_FLAG', type: 'integer', example: 1),
                        ],
                        type: 'object'
                    )
                )
            ),
        ]
    )]
    public function show()
    {
        return LegacyJson::send(AppConfig::toLegacyPayload());
    }
}
