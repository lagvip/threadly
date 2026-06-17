<?php

namespace App\Enums;

enum GhnOrderStatus: string
{
    case ReadyToPick = 'ready_to_pick';
    case Picking = 'picking';
    case MoneyCollectPicking = 'money_collect_picking';
    case Picked = 'picked';
    case Storing = 'storing';
    case Transporting = 'transporting';
    case Sorting = 'sorting';
    case Delivering = 'delivering';
    case MoneyCollectDelivering = 'money_collect_delivering';
    case Delivered = 'delivered';
    case DeliveryFail = 'delivery_fail';
    case WaitingToReturn = 'waiting_to_return';
    case Return = 'return';
    case ReturnTransporting = 'return_transporting';
    case ReturnSorting = 'return_sorting';
    case Returning = 'returning';
    case ReturnFail = 'return_fail';
    case Returned = 'returned';
    case Cancel = 'cancel';
    case Exception = 'exception';
    case Damage = 'damage';
    case Lost = 'lost';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function terminalValues(): array
    {
        return [
            self::Delivered->value,
            self::Cancel->value,
            self::Returned->value,
            self::Lost->value,
            self::Damage->value,
        ];
    }

    public static function safeSimulationValues(): array
    {
        return [
            self::ReadyToPick->value,
            self::Picking->value,
            self::Picked->value,
            self::Storing->value,
            self::Transporting->value,
            self::Sorting->value,
            self::Delivering->value,
            self::MoneyCollectDelivering->value,
            self::DeliveryFail->value,
            self::Delivered->value,
        ];
    }

    public static function simulationOptions(): array
    {
        return [
            self::ReadyToPick->value => 'Chờ bàn giao',
            self::Picked->value => 'Đã lấy hàng',
            self::Delivering->value => 'Đang giao',
            self::DeliveryFail->value => 'Giao thất bại',
            self::Delivered->value => 'Hoàn tất',
        ];
    }

    public static function allowedTransitions(): array
    {
        return [
            self::ReadyToPick->value => [self::Picking->value, self::Picked->value, self::Cancel->value],
            self::Picking->value => [self::Picked->value, self::Cancel->value],
            self::MoneyCollectPicking->value => [self::Picked->value, self::Cancel->value],
            self::Picked->value => [self::Storing->value, self::Delivering->value, self::Cancel->value, self::Lost->value, self::Damage->value],
            self::Storing->value => [self::Transporting->value, self::Sorting->value, self::Delivering->value, self::Lost->value, self::Damage->value],
            self::Transporting->value => [self::Sorting->value, self::Delivering->value, self::Lost->value, self::Damage->value],
            self::Sorting->value => [self::Delivering->value, self::Lost->value, self::Damage->value],
            self::Delivering->value => [self::Delivered->value, self::DeliveryFail->value, self::WaitingToReturn->value, self::Lost->value, self::Damage->value],
            self::MoneyCollectDelivering->value => [self::Delivered->value, self::DeliveryFail->value, self::WaitingToReturn->value],
            self::DeliveryFail->value => [self::Delivering->value, self::WaitingToReturn->value, self::Cancel->value],
            self::WaitingToReturn->value => [self::Return->value, self::ReturnTransporting->value, self::Returning->value],
            self::Return->value => [self::ReturnTransporting->value, self::ReturnSorting->value, self::Returning->value],
            self::ReturnTransporting->value => [self::ReturnSorting->value, self::Returning->value],
            self::ReturnSorting->value => [self::Returning->value],
            self::Returning->value => [self::Returned->value, self::ReturnFail->value],
            self::ReturnFail->value => [self::Returning->value, self::Returned->value],
            self::Delivered->value => [],
            self::Cancel->value => [],
            self::Returned->value => [],
            self::Lost->value => [],
            self::Damage->value => [],
            self::Exception->value => [],
        ];
    }

    public static function toOrderStatusValue(string $status): ?string
    {
        return match ($status) {
            self::ReadyToPick->value,
            self::Picking->value,
            self::MoneyCollectPicking->value => OrderStatus::Processing->value,

            self::Picked->value,
            self::Storing->value,
            self::Transporting->value,
            self::Sorting->value,
            self::Delivering->value,
            self::MoneyCollectDelivering->value,
            self::DeliveryFail->value,
            self::WaitingToReturn->value,
            self::Return->value,
            self::ReturnTransporting->value,
            self::ReturnSorting->value,
            self::Returning->value,
            self::ReturnFail->value => OrderStatus::Shipped->value,

            self::Delivered->value => OrderStatus::Delivered->value,

            self::Cancel->value,
            self::Returned->value,
            self::Exception->value,
            self::Damage->value,
            self::Lost->value => OrderStatus::Cancelled->value,

            default => null,
        };
    }

    public static function groupFor(string $status, bool $hasOrderCode = true): string
    {
        return self::tryFrom($status)?->group()
            ?? ($hasOrderCode ? 'Không xác định' : 'Chưa gửi GHN');
    }

    public static function badgeFor(string $status): string
    {
        return self::tryFrom($status)?->badge() ?? 'bg-light text-dark';
    }

    public static function labelFor(string $status): string
    {
        return self::tryFrom($status)?->label() ?? $status;
    }

    public function group(): string
    {
        return match ($this) {
            self::ReadyToPick,
            self::Picking,
            self::MoneyCollectPicking => 'Chờ bàn giao',

            self::Picked,
            self::Storing,
            self::Transporting,
            self::Sorting,
            self::Delivering,
            self::MoneyCollectDelivering => 'Đã bàn giao - Đang giao',

            self::DeliveryFail => 'Chờ xác nhận giao lại',

            self::WaitingToReturn,
            self::Return,
            self::ReturnTransporting,
            self::ReturnSorting,
            self::Returning,
            self::ReturnFail,
            self::Returned => 'Đã bàn giao - đang hoàn hàng',

            self::Delivered => 'Hoàn tất',
            self::Cancel => 'Đơn hủy',

            self::Exception,
            self::Damage,
            self::Lost => 'Hàng thất lạc - hư hỏng',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::ReadyToPick,
            self::Picking,
            self::MoneyCollectPicking => 'bg-primary',

            self::Picked,
            self::Storing,
            self::Transporting,
            self::Sorting,
            self::Delivering,
            self::MoneyCollectDelivering => 'bg-info',

            self::DeliveryFail => 'bg-warning text-dark',

            self::WaitingToReturn,
            self::Return,
            self::ReturnTransporting,
            self::ReturnSorting,
            self::Returning,
            self::ReturnFail,
            self::Returned => 'bg-secondary',

            self::Delivered => 'bg-success',
            self::Cancel => 'bg-danger',

            self::Exception,
            self::Damage,
            self::Lost => 'bg-dark',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::ReadyToPick => 'Mới tạo đơn hàng / Chờ lấy hàng',
            self::Picking => 'Nhân viên đang lấy hàng',
            self::MoneyCollectPicking => 'Nhân viên đang thu tiền người gửi',
            self::Picked => 'Nhân viên đã lấy hàng',
            self::Storing => 'Hàng đang ở kho GHN',
            self::Transporting => 'Đang luân chuyển hàng',
            self::Sorting => 'Đang phân loại hàng hóa',
            self::Delivering => 'Nhân viên đang giao cho người nhận',
            self::MoneyCollectDelivering => 'Nhân viên đang thu tiền người nhận',
            self::Delivered => 'Đã giao hàng thành công',
            self::DeliveryFail => 'Giao hàng thất bại',
            self::WaitingToReturn => 'Đang đợi trả hàng về người gửi',
            self::Return => 'Trả hàng',
            self::ReturnTransporting => 'Đang luân chuyển hàng trả',
            self::ReturnSorting => 'Đang phân loại hàng trả',
            self::Returning => 'Nhân viên đang đi trả hàng',
            self::ReturnFail => 'Trả hàng thất bại',
            self::Returned => 'Trả hàng thành công',
            self::Cancel => 'Đã hủy đơn hàng',
            self::Exception => 'Đơn hàng ngoại lệ',
            self::Damage => 'Hàng bị hư hỏng',
            self::Lost => 'Hàng bị mất',
        };
    }
}
