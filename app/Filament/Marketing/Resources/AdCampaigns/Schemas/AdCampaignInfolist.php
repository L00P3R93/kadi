<?php

namespace App\Filament\Marketing\Resources\AdCampaigns\Schemas;

use App\Models\AdCampaign;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;

class AdCampaignInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make(fn (AdCampaign $record) => $record->name)
                    ->description('Campaign overview')
                    ->icon(Heroicon::OutlinedMegaphone)
                    ->schema([
                        TextEntry::make('adProfile.company_name')
                            ->label('Advertiser')
                            ->icon(Heroicon::OutlinedBuildingOffice2)
                            ->weight('medium'),
                        TextEntry::make('adCategory.name')
                            ->label('Category')
                            ->badge()
                            ->color('gray')
                            ->icon(Heroicon::OutlinedTag),
                        TextEntry::make('status')
                            ->badge(),
                        TextEntry::make('priority')
                            ->badge(),
                    ])
                    ->columns(2),

                Section::make('Budget')
                    ->description('Escrow, spend, and what\'s left to allocate')
                    ->icon(Heroicon::OutlinedBanknotes)
                    ->schema([
                        TextEntry::make('total_budget')
                            ->label('Total budget')
                            ->money('KES')
                            ->weight('bold')
                            ->size(TextSize::Large),
                        TextEntry::make('escrowed_budget')
                            ->label('In escrow')
                            ->money('KES')
                            ->color('warning')
                            ->icon(Heroicon::OutlinedLockClosed),
                        TextEntry::make('spent_budget')
                            ->label('Spent')
                            ->money('KES')
                            ->color('danger')
                            ->icon(Heroicon::OutlinedArrowTrendingDown),
                        TextEntry::make('remaining_budget')
                            ->label('Remaining')
                            ->state(fn (AdCampaign $record) => max(
                                0,
                                $record->total_budget - $record->escrowed_budget - $record->spent_budget
                            ))
                            ->money('KES')
                            ->color('success')
                            ->icon(Heroicon::OutlinedWallet)
                            ->weight('bold'),

                        /*TextEntry::make('utilization')
                            ->label('Budget utilized')
                            ->state(function (AdCampaign $record) {
                                if (! $record->total_budget) {
                                    return '0%';
                                }

                                return round(($record->spent_budget / $record->total_budget) * 100).'%';
                            })
                            ->badge()
                            ->color(function (AdCampaign $record) {
                                if (! $record->total_budget) {
                                    return 'gray';
                                }

                                $pct = ($record->spent_budget / $record->total_budget) * 100;

                                return match (true) {
                                    $pct >= 90 => 'danger',
                                    $pct >= 60 => 'warning',
                                    default => 'success',
                                };
                            })
                            ->columnSpanFull(),
                        */
                    ])
                    ->columns(2),

                Section::make('Schedule & Delivery')
                    ->description('When this campaign runs and how often it can show')
                    ->icon(Heroicon::OutlinedCalendarDays)
                    ->schema([
                        TextEntry::make('starts_at')
                            ->label('Starts')
                            ->date()
                            ->icon(Heroicon::OutlinedPlay),
                        TextEntry::make('ends_at')
                            ->label('Ends')
                            ->date()
                            ->icon(Heroicon::OutlinedStop),
                        /*TextEntry::make('frequency_cap')
                            ->label('Frequency cap')
                            ->numeric()
                            ->suffix(' views / user')
                            ->icon(Heroicon::OutlinedArrowPath),*/
                    ])
                    ->columns(2),

                Section::make('Moderation')
                    ->description('Review outcome for restricted categories (e.g. Alcohol, Political)')
                    ->icon(Heroicon::OutlinedShieldCheck)
                    ->visible(fn (AdCampaign $record) => filled($record->reviewed_at) || filled($record->rejection_reason))
                    ->schema([
                        TextEntry::make('reviewedBy.name')
                            ->label('Reviewed by')
                            ->badge()
                            ->color('success')
                            ->icon(Heroicon::OutlinedShieldCheck)
                            ->placeholder('—'),
                        TextEntry::make('reviewed_at')
                            ->label('Reviewed at')
                            ->dateTime()
                            ->placeholder('—'),
                        TextEntry::make('rejection_reason')
                            ->label('Rejection reason')
                            ->placeholder('—')
                            ->color(fn ($state) => filled($state) ? 'danger' : null)
                            ->icon(fn ($state) => filled($state) ? Heroicon::OutlinedExclamationTriangle : null)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Timestamps')
                    ->description('When this campaign was created and last updated')
                    ->icon(Heroicon::OutlinedClock)
                    ->collapsed(false)
                    ->schema([
                        TextEntry::make('created_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->dateTime()
                            ->placeholder('-'),
                    ])
                    ->columns(2),
            ]);
    }
}
