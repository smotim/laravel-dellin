<?php

declare(strict_types=1);

namespace SergeevPasha\Dellin\DTO;

use Carbon\Carbon;
use SergeevPasha\Dellin\Enum\DeliveryType;
use Spatie\DataTransferObject\DataTransferObject;

class DellinTrack extends DataTransferObject
{
    /**
     * @var string|null
     */
    public ?string $status;

    /**
     * @var float
     */
    public float $price;

    /**
     * @var string
     */
    public string $link;

    /**
     * @var \Carbon\Carbon|null
     */
    public ?Carbon $startDate;

    /**
     * @var \Carbon\Carbon|null
     */
    public ?Carbon $receiveDate;

    /**
     * @var \Carbon\Carbon|null
     */
    public ?Carbon $warehousing;

    /**
     * @var int|null
     */
    public ?int $derivalTerminalId;

    /**
     * @var string|null
     */
    public ?string $derivalTerminalAddress;

    /**
     * @var string|null
     */
    public ?string $derivalAddress;

    /**
     * @var int|null
     */
    public ?int $arrivalTerminalId;

    /**
     * @var string|null
     */
    public ?string $arrivalTerminalAddress;

    /**
     * @var string|null
     */
    public ?string $arrivalAddress;

    /**
     * @var string|null
     */
    public ?string $deliveryType;

    /**
     * @var int|null
     */
    public ?int $deliveryDays;

    /**
     * @var bool
     */
    public bool $derivalIsTerminal;

    /**
     * @var bool
     */
    public bool $arrivalIsTerminal;

    /**
     * From Array.
     *
     * @param array $data
     *
     * @return self
     * @throws \Spatie\DataTransferObject\Exceptions\UnknownProperties
     */
    public static function fromArray(array $data): self
    {
        $data = $data['orders'][0] ?? [];
        $derivalDate = null;
        $arrivalDate = null;
        $warehousing = null;
        $orderId = $data['orderId'] ?? null;
        $price = $data['totalSum'] ?? 0;
        $derivalTerminalId = $data['derival']['terminalId'] ?? null;
        $derivalTerminalAddress = $data['derival']['terminalAddress'] ?? null;
        $derivalAddress = $data['derival']['address'] ?? null;
        $arrivalTerminalAddress = $data['arrival']['terminalAddress'] ?? null;
        $arrivalTerminalId = $data['arrival']['terminalId'] ?? null;
        $arrivalAddress = $data['arrival']['address'] ?? null;
        
        $deliveryDays = isset($data['orderTimeInDays']['delivery']) ? (int) $data['orderTimeInDays']['delivery'] : null;
        
        $deliveryType = null;
        $docs = $data['documents'] ?? [];
        foreach ($docs as $doc) {
            if (($doc['type'] ?? '') === 'shipping' && !empty($doc['serviceKind'])) {
                $deliveryType = DeliveryType::fromServiceKind($doc['serviceKind'])?->key;
                if ($deliveryType) {
                    break;
                }
            }
        }

        $derivalIsTerminal = !($data['orderedDeliveryFromAddress'] ?? false);
        $arrivalIsTerminal = !($data['orderedDeliveryToAddress'] ?? false);

        $link = $orderId ? 'https://www.dellin.ru/tracker/orders/' . $orderId . '/' : '';

        if (isset($data['orderDates']['derivalFromOspSender'])) {
            $derivalDate = Carbon::parse($data['orderDates']['derivalFromOspSender']);
        } elseif (isset($data['orderDates']['arrivalToOspReceiver'])) {
            $derivalDate = Carbon::parse($data['orderDates']['arrivalToOspReceiver']);
        }

        if (isset($data['orderDates']['giveoutFromOspReceiver'])) {
            $arrivalDate = Carbon::parse($data['orderDates']['giveoutFromOspReceiver']);
        } elseif (isset($data['orderDates']['arrivalToOspReceiver'])) {
            $arrivalDate = Carbon::parse($data['orderDates']['arrivalToOspReceiver']);
        }

        if (isset($data['orderDates']['warehousing'])) {
            $warehousing = Carbon::parse($data['orderDates']['warehousing']);
        }

        return new self(
            [
                'status'                 => $data['stateName'] ?? null,
                'price'                  => (float)$price,
                'link'                   => $link,
                'startDate'              => $derivalDate,
                'receiveDate'            => $arrivalDate,
                'warehousing'            => $warehousing,
                'derivalTerminalId'      => $derivalTerminalId,
                'derivalTerminalAddress' => $derivalTerminalAddress,
                'derivalAddress'         => $derivalAddress,
                'arrivalTerminalId'      => $arrivalTerminalId,
                'arrivalTerminalAddress' => $arrivalTerminalAddress,
                'arrivalAddress'         => $arrivalAddress,
                'derivalIsTerminal'      => $derivalIsTerminal,
                'arrivalIsTerminal'      => $arrivalIsTerminal,
                'deliveryType'           => $deliveryType,
                'deliveryDays'           => $deliveryDays,
            ]
        );
    }
}
