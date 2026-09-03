<?php

namespace App\Filament\Resources\Transactions\Schemas;

use App\Filament\Support\MoneyFormat;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TransactionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Giao dịch')
                    ->icon('heroicon-o-banknotes')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('transaction_id')->label('ID'),
                        TextEntry::make('reg_number')->label('Mã đơn')->copyable(),
                        MoneyFormat::infolistEntry(
                            TextEntry::make('amount')->label('Số tiền')->weight('bold')
                        ),
                        TextEntry::make('name_packet')->label('Gói')->columnSpan(2),
                        TextEntry::make('payment_date')->label('Ngày TT')->date('d/m/Y'),
                        TextEntry::make('customer_id')->label('Customer ID'),
                        TextEntry::make('paid_id')->label('Paid ID'),
                        TextEntry::make('ref_transaction_id')->label('Ref')->placeholder('—'),
                        TextEntry::make('created_date')->label('Tạo lúc')->dateTime('d/m/Y H:i'),
                    ]),
            ]);
    }
}
