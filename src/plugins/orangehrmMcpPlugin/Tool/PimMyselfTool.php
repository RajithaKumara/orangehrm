<?php

namespace OrangeHRM\Mcp\Tool;

use OrangeHRM\Core\Api\V2\Request as ApiRequest;
use OrangeHRM\Framework\Http\Request as HttpRequest;
use OrangeHRM\Mcp\Server\ToolInterface;
use OrangeHRM\Pim\Api\MyInfoAPI;

class PimMyselfTool implements ToolInterface
{
    public function getName(): string
    {
        return 'pim_myself';
    }

    public function getDescription(): string
    {
        return 'Get personal information for the currently authenticated employee. '
            . 'Wraps GET /api/v2/pim/myself.';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'model' => [
                    'type' => 'string',
                    'enum' => [
                        MyInfoAPI::MODEL_DEFAULT,
                        MyInfoAPI::MODEL_SUMMARY,
                        MyInfoAPI::MODEL_DETAILED,
                    ],
                    'description' => 'Response model variant.',
                    'default' => MyInfoAPI::MODEL_DEFAULT,
                ],
            ],
            'additionalProperties' => false,
        ];
    }

    public function execute(array $arguments): array
    {
        $query = [];
        if (isset($arguments['model'])) {
            $query[MyInfoAPI::FILTER_MODEL] = $arguments['model'];
        }

        $httpRequest = new HttpRequest($query);
        $apiRequest = new ApiRequest($httpRequest);

        $api = new MyInfoAPI($apiRequest);
        $result = $api->getOne();

        return $result->normalize();
    }
}
