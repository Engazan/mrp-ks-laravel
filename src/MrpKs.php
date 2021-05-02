<?php

namespace Engazan\MrpKs;

use Illuminate\Http\JsonResponse;

/**
 * Class MrpKs
 * https://faq.mrp.sk/K-S-vseobecne/Autonomny-rezim-uctovneho-systemu-MRP-K-S-497
 *
 * @package Engazan\MrpKs
 * @author Engazan <Engazan.eu@icloud.com>
 */
class MrpKs
{

    private string $command;
    private array $filters;

    public function __construct(string $command = '', array $filters = [])
    {
        $this->command = $command;
        $this->filters = $filters;
    }

    public function setCommand(string $command): MrpKs
    {
        $this->command = $command;
        return $this;
    }

    public function setFilters(array $filters): MrpKs
    {
        $this->filters = $filters;
        return $this;
    }

    public function sendRequest(): JsonResponse
    {
        return MrpKsRequest::sendGetRequest($this->command, $this->filters);
    }



    public static function EXPEO0(array $filter = []): JsonResponse
    {
        return MrpKsRequest::sendGetRequest('EXPEO0', $filter);
    }

    public static function EXPEO1(array $filter = []): JsonResponse
    {
        return MrpKsRequest::sendGetRequest('EXPEO1', $filter);
    }

    public static function ADREO0(array $filter = []): JsonResponse
    {
        return MrpKsRequest::sendGetRequest('ADREO0', $filter);
    }

    public static function CENEO0(array $filter = []): JsonResponse
    {
        if (!isset($filter['cenovaSkupina'])) {
            $filter['cenovaSkupina'] = '1';
        }
        return MrpKsRequest::sendGetRequest('CENEO0', $filter);
    }

}
