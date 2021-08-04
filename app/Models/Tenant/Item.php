<?php

namespace App\Models\Tenant;
use App\Models\Tenant\Catalogs\AffectationIgvType;
use App\Models\Tenant\Catalogs\CurrencyType;
use App\Models\Tenant\Catalogs\SystemIscType;
use App\Models\Tenant\Catalogs\UnitType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Modules\Account\Models\Account;
use Modules\Digemid\Models\CatDigemid;
use Modules\Inventory\Models\Warehouse;
use Modules\Item\Models\Brand;
use Modules\Item\Models\Category;
use Modules\Item\Models\ItemLot;
use Modules\Item\Models\ItemLotsGroup;
use Modules\Item\Models\WebPlatform;


/**
 * Class Item
 *
 * @package App\Models\Tenant
 * @mixin  ModelTenant
 */
class Item extends ModelTenant
{
    protected $with = ['item_type', 'unit_type', 'currency_type', 'warehouses','item_unit_types', 'tags'];
    protected $fillable = [
        'warehouse_id',
        'name',
        'second_name',
        'description',
        'model',
        'technical_specifications',
        'item_type_id',
        'internal_id',
        'item_code',
        'item_code_gs1',
        'unit_type_id',
        'currency_type_id',
        'sale_unit_price',
        'purchase_unit_price',
        'has_isc',
        'system_isc_type_id',
        'percentage_isc',
        'suggested_price',

        'sale_affectation_igv_type_id',
        'purchase_affectation_igv_type_id',
        'calculate_quantity',
        'has_igv',

        'stock',
        'stock_min',
        'percentage_of_profit',

        'attributes',
        'has_perception',
        'percentage_perception',
        'image',
        'image_medium',
        'image_small',

        'account_id',
        'amount_plastic_bag_taxes',
        'date_of_due',
        'is_set',
        'sale_unit_price_set',
        'apply_store',
        'brand_id',
        'category_id',
        'lot_code',
        'lots_enabled',
        'active',
        'line',
        'series_enabled',
        'purchase_has_igv',
        'web_platform_id',
        'has_plastic_bag_taxes',
        'barcode',
        'sanitary',
        'cod_digemid',
        // 'warehouse_id'
    ];

    protected $casts = [
        'date_of_due' => 'date'
    ];

    /**
     * @return string
     */
    public function getSanitary() {
        return $this->sanitary;
    }

    /**
     * @param string $sanitary
     *
     * @return Item
     */
    public function setSanitary($sanitary) {
        $this->sanitary = $sanitary;
        return $this;
    }

    /**
     * @return string
     */
    public function getCodDigemid() {
        return $this->cod_digemid;
    }

    /**
     * @param string $cod_digemid
     *
     * @return Item
     */
    public function setCodDigemid($cod_digemid) {
        $this->cod_digemid = $cod_digemid;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @param mixed $description
     *
     * @return Item
     */
    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    /*protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('active', 1);
        });
    }*/

    public function getAttributesAttribute($value)
    {
        return (is_null($value))?null:json_decode($value);
    }

    public function setAttributesAttribute($value)
    {
        $this->attributes['attributes'] = (is_null($value))?null:json_encode($value);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function item_type()
    {
        return $this->belongsTo(ItemType::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function unit_type()
    {
        return $this->belongsTo(UnitType::class, 'unit_type_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency_type()
    {
        return $this->belongsTo(CurrencyType::class, 'currency_type_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function system_isc_type()
    {
        return $this->belongsTo(SystemIscType::class, 'system_isc_type_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function kardex()
    {
        return $this->hasMany(Kardex::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function inventory_kardex()
    {
        return $this->hasMany(InventoryKardex::class);
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function cat_digemid()
    {
        return $this->hasOne(CatDigemid::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchase_item()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sale_affectation_igv_type()
    {
        return $this->belongsTo(AffectationIgvType::class, 'sale_affectation_igv_type_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purchase_affectation_igv_type()
    {
        return $this->belongsTo(AffectationIgvType::class, 'purchase_affectation_igv_type_id');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereWarehouse($query)
     {
        $establishment_id = auth()->user()->establishment_id;
        $warehouse = Warehouse::where('establishment_id', $establishment_id)->first();
        if ($warehouse) {
            return $query->whereHas('warehouses', function($query) use($warehouse) {
                            $query->where('warehouse_id', $warehouse->id);
                        })->orWhere('unit_type_id', 'ZZ');
        }
        return $query;
     }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereTypeUser($query)
    {
        $user = auth()->user();
        return ($user->type == 'seller') ? $this->scopeWhereWarehouse($query) : null;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereNotIsSet($query)
    {
        return $query->where('is_set', false);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereIsActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereIsSet($query)
    {
        return $query->where('is_set', true);
    }


    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function scopePharmacy($query){
        return $query
            ->whereNotNull('items.cod_digemid')
            ->select('items.*')
            ->join('cat_digemid','cat_digemid.item_id','=','items.id')
            ;
    }

    /**
     * @return int
     */
    public function getStockByWarehouse()
    {
        if(auth()->user())
        {
            $establishment_id = auth()->user()->establishment_id;
            $warehouse = Warehouse::where('establishment_id', $establishment_id)->first();
            if ($warehouse) {
                $item_warehouse = $this->warehouses->where('warehouse_id',$warehouse->id)->first();
                return ($item_warehouse) ? $item_warehouse->stock : 0;
            }
        }

        return 0;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function warehouses()
    {
        return $this->hasMany(ItemWarehouse::class)->with('warehouse');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function item_unit_types()
    {
        return $this->hasMany(ItemUnitType::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tags()
    {
        return $this->hasMany(ItemTag::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sets()
    {
    return $this->hasMany(ItemSet::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class)->withDefault([
            'id' => '',
            'name' => ''
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class)->withDefault([
            'id' => '',
            'name' => ''
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function item_lots()
    {
        return $this->hasMany(ItemLot::class, 'item_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function lots()
    {
        return $this->morphMany(ItemLot::class, 'item_loteable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public  function images()
    {
        return $this->hasMany(ItemImage::class, 'item_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function lots_group()
    {
        return $this->hasMany(ItemLotsGroup::class, 'item_id');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereNotService($query)
    {
        return $query->where('unit_type_id','!=', 'ZZ');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereService($query)
    {
        return $query->where('unit_type_id', 'ZZ');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public  function document_items()
    {
        return $this->hasMany(DocumentItem::class, 'item_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public  function sale_note_items()
    {
        return $this->hasMany(SaleNoteItem::class, 'item_id');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param                                       $params
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereFilterValuedKardex(Builder $query, $params)
    {

        if($params->establishment_id){

            return $query->with(['document_items'=> function($q) use($params){
                        $q->whereHas('document', function($q) use($params){
                            $q->whereStateTypeAccepted()
                                ->whereTypeUser()
                                ->whereBetween('date_of_issue', [$params->date_start, $params->date_end])
                                ->where('establishment_id', $params->establishment_id);
                        });
                    },
                    'sale_note_items' => function($q) use($params){
                        $q->whereHas('sale_note', function($q) use($params){
                            $q->whereStateTypeAccepted()
                                ->whereNotChanged()
                                ->whereTypeUser()
                                ->whereBetween('date_of_issue', [$params->date_start, $params->date_end])
                                ->where('establishment_id', $params->establishment_id);
                        });
                    }]);

        }

        return $query->with(['document_items'=> function($q) use($params){
                    $q->whereHas('document', function($q) use($params){
                        $q->whereStateTypeAccepted()
                            ->whereTypeUser()
                            ->whereBetween('date_of_issue', [$params->date_start, $params->date_end]);
                    });
                },
                'sale_note_items' => function($q) use($params){
                    $q->whereHas('sale_note', function($q) use($params){
                        $q->whereStateTypeAccepted()
                            ->whereNotChanged()
                            ->whereTypeUser()
                            ->whereBetween('date_of_issue', [$params->date_start, $params->date_end]);
                    });
                }]);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereIsNotActive($query)
    {
        return $query->where('active', false);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereHasInternalId($query)
    {
        return $query->where('internal_id','!=', null);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function web_platform()
    {
        return $this->belongsTo(WebPlatform::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function warehousePrices()
    {
        return $this->hasMany(ItemWarehousePrice::class, 'item_id')->select('id','item_id', 'price', 'warehouse_id');
    }

    public static function getSaleUnitPriceByWarehouse(Item $item, int $warehouseId): string
    {
        $warehousePrice = $item->warehousePrices->where('item_id', $item->id)
            ->where('warehouse_id', $warehouseId)
            ->first();

        $price = $warehousePrice ? $warehousePrice->price : $item->sale_unit_price;
        return number_format($price, 4, ".", "");
    }

    /**
     * Devuelve la esuctura de item para los select correspondientes.
     *
     * @param \App\Models\Tenant\Warehouse|\Modules\Inventory\Models\Warehouse $warehouse
     * @param false $extended
     *
     * @return array
     */
    public function getFullDescription($warehouse, $extended = false) {

        $desc = ($this->internal_id) ? $this->internal_id.' - '.$this->description : $this->description;
        $category = ($this->category) ? "{$this->category->name}" : '';
        $brand = ($this->brand) ? "{$this->brand->name}" : '';
        if ($this->unit_type_id != 'ZZ') {
            if (isset($this['stock'])) {
                $warehouse_stock = number_format($this['stock'], 2);
            } else {
                $warehouse_stock = ($this->warehouses && $warehouse)
                    ?
                    number_format($this->warehouses->where('warehouse_id', $warehouse->id)->first()->stock, 2)
                    :
                    0;
            }
            $stock = ($this->warehouses && $warehouse) ? "{$warehouse_stock}" : '';
        } else {
            $stock = '';
        }
        if($extended == false) {
            $desc = "{$desc} - {$brand}";
        }else {
            $desc = "{$desc} - {$category} - {$brand}";
        }
        return [
            'full_description'      => $desc,
            'brand'                 => $brand,
            'category'              => $category,
            'stock'                 => $stock,
            'warehouse_description' => $warehouse->description,
        ];
    }

    /**
     * Devuelve la relacion con el almacen dado
     * @param int $warehouse_id
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function getCurrentItemWarehouse($warehouse_id){
        return $this->warehouses()->where('warehouse_id',$warehouse_id);
    }

    /**
     * Devuelve un estandar de estructura para items.
     *
     * Es utilizado en :
     * app/Http/Controllers/Tenant/DocumentController.php
     * modules/Order/Http/Controllers/OrderNoteController.php
     *
     * @param \App\Models\Tenant\Warehouse|\Modules\Inventory\Models\Warehouse|null $warehouse
     * @param false                                    $with_lots_has_sale
     * @param false                                    $extended_description
     *
     * @return array
     */
    public function getDataToItemModal($warehouse = null, $with_lots_has_sale = false, $extended_description = false) {

        if ($warehouse == null) {
            $establishment_id = auth()->user()->establishment_id;
            $warehouse = Warehouse::where('establishment_id', $establishment_id)->first();
        }
        $detail = $this->getFullDescription($warehouse, $extended_description);
        $realtion_item_unit_types = $this->item_unit_types;
        $lots_grp = $this->lots_group;
        $lots = [];
        if ($with_lots_has_sale == true) {
            $lots = $this->item_lots->where('has_sale', false)->transform(function ($row) {
                return [
                    'id'           => $row->id,
                    'series'       => $row->series,
                    'date'         => $row->date,
                    'item_id'      => $row->item_id,
                    'warehouse_id' => $row->warehouse_id,
                    'has_sale'     => (bool)$row->has_sale,
                    'lot_code'     => ($row->item_loteable_type)
                        ?
                        (isset($row->item_loteable->lot_code)
                            ?
                            $row->item_loteable->lot_code
                            :
                            null)
                        :
                        null,
                ];
            })->values();
        }
        $stock = $detail['stock'];

        // Obtiene el stock basado el en almacen itemwarehouse
        $stockItemWarehouse = $this->getCurrentItemWarehouse($warehouse->id)->first();
        if(is_object($stockItemWarehouse)) {
            $stock = $stockItemWarehouse->stock;
        }


        $data = [
            'id'                               => $this->id,
            'full_description'                 => $detail['full_description'],
            'model'                            => $this->model,
            'brand'                            => $detail['brand'],
            'warehouse_description'            => $detail['warehouse_description'],
            'category'                         => $detail['category'],
            'stock'                            => $stock,
            'internal_id'                      => $this->internal_id,
            'description'                      => $this->description,
            'currency_type_id'                 => $this->currency_type_id,
            'currency_type_symbol'             => $this->currency_type->symbol,
            'sale_unit_price'                  => self::getSaleUnitPriceByWarehouse($this, $warehouse->id),
            'purchase_unit_price'              => $this->purchase_unit_price,
            'unit_type_id'                     => $this->unit_type_id,
            'sale_affectation_igv_type_id'     => $this->sale_affectation_igv_type_id,
            'purchase_affectation_igv_type_id' => $this->purchase_affectation_igv_type_id,
            'calculate_quantity'               => (bool)$this->calculate_quantity,
            'has_igv'                          => (bool)$this->has_igv,
            'has_plastic_bag_taxes'            => (bool)$this->has_plastic_bag_taxes,
            'amount_plastic_bag_taxes'         => $this->amount_plastic_bag_taxes,
            'item_unit_types'                  => collect($realtion_item_unit_types)->transform(function ($item_unit_types) {
                return [
                    'id'            => $item_unit_types->id,
                    'description'   => "{$item_unit_types->description}",
                    'item_id'       => $item_unit_types->item_id,
                    'unit_type_id'  => $item_unit_types->unit_type_id,
                    'quantity_unit' => $item_unit_types->quantity_unit,
                    'price1'        => $item_unit_types->price1,
                    'price2'        => $item_unit_types->price2,
                    'price3'        => $item_unit_types->price3,
                    'price_default' => $item_unit_types->price_default,
                ];
            }),
            'warehouses' => collect($this->warehouses)->transform(function ($warehouses) use ($warehouse) {
                return [
                    'warehouse_description' => $warehouses->warehouse->description,
                    'stock'                 => (!empty($warehouses->stock)) ? $warehouses->stock : 0,
                    'warehouse_id'          => $warehouses->warehouse_id,
                    'checked'               => ($warehouses->warehouse_id == $warehouse->id) ? true : false,
                ];
            }),
            'attributes'     => $this->attributes ? $this->attributes : [],
            'lots_group'     => collect($lots_grp)->transform(function ($lots_group) {
                return [
                    'id'          => $lots_group->id,
                    'code'        => $lots_group->code,
                    'quantity'    => $lots_group->quantity,
                    'date_of_due' => $lots_group->date_of_due,
                    'checked'     => false,
                ];
            }),
            'lots'           => $lots,
            'lots_enabled'   => (bool)$this->lots_enabled,
            'series_enabled' => (bool)$this->series_enabled,
            'is_set'         => (bool)$this->is_set,

            'lot_code'    => $this->lot_code,
            'date_of_due' => $this->date_of_due,
            'barcode'     => $this->barcode,

        ];

        return $data;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|mixed|\Modules\Digemid\Models\CatDigemid|object|null
     */
    public function getCatDigemid(){
        return CatDigemid::where('item_id',$this->id)->first();
    }

    /**
     * Retorna un standar de nomenclatura para el modelo
     *
     * @param \App\Models\Tenant\Configuration|null $configuration
     *
     * @return array
     */
    public function getCollectionData(Configuration $configuration = null){
        if(empty($configuration)){
            $configuration =  Configuration::first();
        }
        $brand = null;
        if (!empty($this->brand_id)) {
            $brand = $this->brand()->first()->name;
        }
        $has_igv_description = null;
        $purchase_has_igv_description = null;

        $affectation_igv_types_exonerated_unaffected = ['20', '21', '30', '31', '32', '33', '34', '35', '36', '37'];

        if (in_array($this->sale_affectation_igv_type_id, $affectation_igv_types_exonerated_unaffected)) {
            $has_igv_description = 'No';
        } else {
            $has_igv_description = ((bool)$this->has_igv) ? 'Si' : 'No';
        }

        if (in_array($this->purchase_affectation_igv_type_id, $affectation_igv_types_exonerated_unaffected)) {
            $purchase_has_igv_description = 'No';
        } else {
            $purchase_has_igv_description = ((bool)$this->purchase_has_igv) ? 'Si' : 'No';
        }
        $digemid_exportable = false;
        $name_disa = '';
        $laboratory = '';
        if($configuration->isPharmacy()) {
            $digemid = $this->getCatDigemid();
            if (!empty($digemid)) {
                $digemid_exportable = (bool)$digemid->active;
                $name_disa = $digemid->getNomProd();
                $laboratory = $digemid->getNomTitular();
            }
        }

        return [
            'name_disa'                           => $name_disa,
            'laboratory'                           => $laboratory,
            'exportable_pharmacy'                           => $digemid_exportable,
            'id'                           => $this->id,
            'sanitary'                 => $this->sanitary,
            'cod_digemid'                 => $this->cod_digemid,
            'unit_type_id'                 => $this->unit_type_id,
            'description'                  => $this->description,
            'name'                         => $this->name,
            'second_name'                  => $this->second_name,
            'model'                        => $this->model,
            'barcode'                      => $this->barcode,
            'brand'                        => $brand,
            'warehouse_id'                 => $this->warehouse_id,
            'internal_id'                  => $this->internal_id,
            'item_code'                    => $this->item_code,
            'item_code_gs1'                => $this->item_code_gs1,
            'stock'                        => $this->getStockByWarehouse(),
            'stock_min'                    => $this->stock_min,
            'currency_type_id'             => $this->currency_type_id,
            'currency_type_symbol'         => $this->currency_type->symbol,
            'sale_affectation_igv_type_id' => $this->sale_affectation_igv_type_id,
            'purchase_affectation_igv_type_id' => $this->purchase_affectation_igv_type_id,
            'amount_sale_unit_price'       => $this->sale_unit_price,
            'calculate_quantity'           => (bool)$this->calculate_quantity,
            'has_igv'                      => (bool)$this->has_igv,
            'active'                       => (bool)$this->active,
            'has_igv_description'          => $has_igv_description,
            'purchase_has_igv_description' => $purchase_has_igv_description,
            'sale_unit_price'              => "{$this->currency_type->symbol} {$this->sale_unit_price}",
            'purchase_unit_price'          => "{$this->currency_type->symbol} {$this->purchase_unit_price}",
            'created_at'                   => ($this->created_at) ? $this->created_at->format('Y-m-d H:i:s') : '',
            'updated_at'                   => ($this->created_at) ? $this->updated_at->format('Y-m-d H:i:s') : '',
            'warehouses'                   => collect($this->warehouses)->transform(function ($row) {
                return [
                    'warehouse_description' => $row->warehouse->description,
                    'stock'                 => $row->stock,
                ];
            }),
            'apply_store'                  => (bool)$this->apply_store,
            'image_url'                    => ($this->image !== 'imagen-no-disponible.jpg')
                ? asset('storage'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'items'.DIRECTORY_SEPARATOR.$this->image)
                : asset("/logo/{$this->image}"),
            'image_url_medium'             => ($this->image_medium !== 'imagen-no-disponible.jpg')
                ? asset('storage'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'items'.DIRECTORY_SEPARATOR.$this->image_medium)
                : asset("/logo/{$this->image_medium}"),
            'image_url_small'              => ($this->image_small !== 'imagen-no-disponible.jpg')
                ? asset('storage'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'items'.DIRECTORY_SEPARATOR.$this->image_small)
                : asset("/logo/{$this->image_small}"),
            'tags'                         => $this->tags,
            'tags_id'                      => $this->tags->pluck('tag_id'),
            'item_unit_types'              => collect($this->item_unit_types)->transform(function ($row) use ($configuration) {
                return [
                    'id'            => $row->id,
                    'description'   => "{$row->description}",
                    'item_id'       => $row->item_id,
                    'unit_type_id'  => $row->unit_type_id,
                    'quantity_unit' => number_format($this->quantity_unit, $configuration->decimal_quantity, '.', ''),
                    'price1'        => number_format($this->price1, $configuration->decimal_quantity, '.', ''),
                    'price2'        => number_format($this->price2, $configuration->decimal_quantity, '.', ''),
                    'price3'        => number_format($this->price3, $configuration->decimal_quantity, '.', ''),
                    'price_default' => $this->price_default,
                ];
            }),


        ];
    }

    /**
     * Establece un standar para insersion por catalogo DIGEMID
     *
     * Este proviene del excel, debe tener la estructura :
     *
     * $Cod_Prod = $row[0];
     * $Nom_Prod = $row[1];
     * $Concent = $row[2];
     * $Nom_Form_Farm = $row[3];
     * $Nom_Form_Farm_Simplif = $row[4];
     * $Presentac = $row[5];
     * $Fracciones = $row[6];
     * $Fec_Vcto_Reg_Sanitario = $row[7];
     * $Num_RegSan = $row[8];
     * $Nom_Titular = $row[9];
    * $Situacion = $row[10];
     *
     * @param array $data
     *
     * @return $this
     */
    public function fillFormDigemid($data){

        $model = substr($data[5],0,100);
        $line = substr($data[4],0,255);

        $this->cod_digemid = $data[0];

        $active = 1;
        if(strtolower(trim($data[10])) !== 'act'){
            $active = 0;
        }
        $warehouse = auth()->user()->establishment_id;
        $today =  Carbon::now()->format('Y-m-d');
        $this
            ->setInArray('sanitary',$data[8])
            ->setInArray('internal_id',$data[0])
            ->setInArray('description',$data[1])
            ->setInArray('second_name',$data[1]." ".$data[2])
            ->setInArray('name', $data[3].' '. $data[1]." ".$data[2])
            ->setInArray('sale_affectation_igv_type_id',10)
            ->setInArray('purchase_affectation_igv_type_id',$this->sale_affectation_igv_type_id)
            ->setInArray('item_type_id','01')
            ->setInArray('barcode',$this->internal_id)
            ->setInArray('lot_code',$this->internal_id)
            ->setInArray('model',$model)
            ->setInArray('line',$line)
            ->setInArray('lots_enabled',true)
            ->setInArray('stock',0)
            ->setInArray('stock_min',0)
            ->setInArray('currency_type_id','PEN')
            ->setInArray('unit_type_id','NIU')
            ->setInArray('active',$active)
            ->setInArray('sale_unit_price',1)
            ->setInArray('sale_unit_price_set',null)
            ->setInArray('has_igv',true)
            ->setInArray('is_set',false)
            ->setInArray('purchase_has_igv',true)
            ->setInArray('amount_plastic_bag_taxes',0.1)
            ->setInArray('purchase_unit_price',0)
            ->setInArray('percentage_isc',0)
            ->setInArray('suggested_price',0)
            ->setInArray('has_plastic_bag_taxes',false)
            ->setInArray('has_isc',false)
            ->setInArray('has_plastic_bag_taxes',false)
            ->setInArray('warehouse_id',$warehouse)
            ->setInArray('image','imagen-no-disponible.jpg')
            ->setInArray('image_medium','imagen-no-disponible.jpg')
            ->setInArray('image_small','imagen-no-disponible.jpg')
            ->setInArray('date_of_due',$today)
            ->setInArray('item_code',$this->cod_digemid)
            ->setInArray('brand_id',null)
            ->setInArray('category_id',null)

        /*
        'technical_specifications',
        'item_code_gs1',
        'system_isc_type_id',
        'sale_affectation_igv_type_id',
        'purchase_affectation_igv_type_id',
        'calculate_quantity',
        'percentage_of_profit',
        'attributes',
        'has_perception',
        'percentage_perception',
        'account_id',
        'apply_store',
        'series_enabled',
        'web_platform_id',
        */
        ;
        $this->description = substr($this->description,0,600);
        $this->second_name = substr($this->second_name,0,600);
        $this->name = substr($this->name,0,600);
        return $this;
    }

    /**
     * Si la propiedad es nula, establece el valor value
     * @param $property
     * @param $value
     *
     * @return $this
     */
    protected function setInArray($property,$value){

        if($this->{$property} == null){
            $this->{$property} = $value;
        }

        return $this;
    }

    /**
     * @param $cod
     *
     * @return \App\Models\Tenant\Item|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|mixed|object|null
     */
    public static function FindByCodDigemid($cod){
        return self::where('cod_digemid',$cod)->first();

    }
}
