<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Models\Payment;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class PaymentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                /*
                 * ============================================================
                 * PAYMENT INFORMATION
                 * ============================================================
                 */

                Section::make('Payment Information')
                    ->description('Customer, order, payment method and transaction amount.')
                    ->icon(Heroicon::OutlinedCreditCard)
                    ->schema([
                        TextEntry::make('order.id')
                            ->label('Order')
                            ->prefix('#')
                            ->icon(Heroicon::OutlinedShoppingBag)
                            ->iconColor('primary'),

                        TextEntry::make('user.name')
                            ->label('Customer')
                            ->icon(Heroicon::OutlinedUser)
                            ->iconColor('gray'),

                        TextEntry::make('payment_method')
                            ->label('Payment Method')
                            ->badge()
                            ->icon(Heroicon::OutlinedWallet),

                        TextEntry::make('provider')
                            ->label('Payment Provider')
                            ->badge()
                            ->color('gray')
                            ->placeholder('Not specified'),

                        TextEntry::make('amount')
                            ->label('Amount')
                            ->money(
                                fn (Payment $record): string => $record->currency
                            )
                            ->weight('bold'),

                        TextEntry::make('currency')
                            ->label('Currency')
                            ->badge(),
                    ])
                    ->columns(2)
                    ->columnSpan(['lg' => 3]),

                /*
                 * ============================================================
                 * PAYMENT STATUS
                 * ============================================================
                 */

                Section::make('Payment Status')
                    ->description('Current state and any recorded failure information.')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->schema([
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge(),

                        TextEntry::make('failure_reason')
                            ->label('Failure Reason')
                            ->placeholder('No failure reason recorded')
                            ->columnSpanFull()
                            ->visible(fn (Payment $record): bool => filled($record->failure_reason)
                            ),
                    ])
                    ->columns(2)
                    ->columnSpan(['lg' => 3]),

                /*
                 * ============================================================
                 * TRANSACTION REFERENCES
                 * ============================================================
                 */

                Section::make('Transaction References')
                    ->description('Identifiers generated during payment processing.')
                    ->icon(Heroicon::OutlinedFingerPrint)
                    ->schema([
                        TextEntry::make('reference')
                            ->label('Payment Reference')
                            ->placeholder('Not available')
                            ->copyable()
                            ->icon(Heroicon::OutlinedHashtag),

                        TextEntry::make('merchant_request_id')
                            ->label('Merchant Request ID')
                            ->placeholder('Not available')
                            ->copyable(),

                        TextEntry::make('checkout_request_id')
                            ->label('Checkout Request ID')
                            ->placeholder('Not available')
                            ->copyable(),
                    ])
                    ->columns(2)
                    ->columnSpan(['lg' => 3]),

                /*
                 * ============================================================
                 * PAYMENT TIMELINE
                 * ============================================================
                 */

                Section::make('Payment Timeline')
                    ->description('System-recorded timestamps for the payment lifecycle.')
                    ->icon(Heroicon::OutlinedClock)
                    ->schema([
                        TextEntry::make('initiated_at')
                            ->label('Initiated')
                            ->dateTime()
                            ->placeholder('Not recorded')
                            ->icon(Heroicon::OutlinedPlay),

                        TextEntry::make('completed_at')
                            ->label('Completed')
                            ->dateTime()
                            ->placeholder('Not completed')
                            ->icon(Heroicon::OutlinedCheckCircle)
                            ->iconColor('success'),

                        TextEntry::make('failed_at')
                            ->label('Failed')
                            ->dateTime()
                            ->placeholder('Not failed')
                            ->icon(Heroicon::OutlinedXCircle)
                            ->iconColor('danger'),
                    ])
                    ->columns(3)
                    ->columnSpan(['lg' => 3]),

                /*
                 * ============================================================
                 * AUDIT INFORMATION
                 * ============================================================
                 */

                Section::make('Audit Information')
                    ->description('Record lifecycle information maintained by the system.')
                    ->icon(Heroicon::OutlinedClipboardDocument)
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime()
                            ->icon(Heroicon::OutlinedPlusCircle),

                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime()
                            ->icon(Heroicon::OutlinedArrowPath),

                        TextEntry::make('deleted_at')
                            ->label('Deleted')
                            ->dateTime()
                            ->icon(Heroicon::OutlinedTrash)
                            ->iconColor('danger')
                            ->visible(fn (Payment $record): bool => $record->trashed()
                            ),
                    ])
                    ->columns(3)
                    ->collapsed()
                    ->columnSpan(['lg' => 3]),
            ]);
    }
}
