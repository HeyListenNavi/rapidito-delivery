<?php

namespace App\Filament\Resources\ServiceZones;

use App\Filament\Resources\ServiceZones\Pages\CreateServiceZone;
use App\Filament\Resources\ServiceZones\Pages\EditServiceZone;
use App\Filament\Resources\ServiceZones\Pages\ListServiceZones;
use App\Filament\Resources\ServiceZones\RelationManagers\DeliveryZonesRelationManager;
use App\Filament\Resources\ServiceZones\Schemas\ServiceZoneForm;
use App\Filament\Resources\ServiceZones\Tables\ServiceZonesTable;
use App\Models\ServiceZone;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ServiceZoneResource extends Resource
{
    protected static ?string $model = ServiceZone::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::GlobeAmericas;

    protected static \UnitEnum|string|null $navigationGroup = 'Logística';

    protected static ?string $modelLabel = 'Zona de Servicio';

    protected static ?string $pluralModelLabel = 'Zonas de Servicio';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    public static function form(Schema $schema): Schema
    {
        return ServiceZoneForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServiceZonesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            DeliveryZonesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServiceZones::route('/'),
            'create' => CreateServiceZone::route('/create'),
            'edit' => EditServiceZone::route('/{record}/edit'),
        ];
    }
}
