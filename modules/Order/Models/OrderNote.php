<?php

namespace Modules\Order\Models;

use App\Models\Tenant\Catalogs\CurrencyType;
use App\Models\Tenant\User;
use App\Models\Tenant\SoapType;
use App\Models\Tenant\StateType;
use App\Models\Tenant\Person;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\Document;
use App\Models\Tenant\SaleNote;
use App\Models\Tenant\PaymentMethodType;
use App\Models\Tenant\ModelTenant;
use Modules\Inventory\Models\InventoryKardex;
use Modules\Item\Models\ItemLot;

/**
 * Class OrderNote
 *
 * @package Modules\Order\Models
 * @mixin ModelTenant
 */
class OrderNote extends ModelTenant
{
    protected $with = ['user', 'soap_type', 'state_type', 'currency_type', 'items'];

    protected $fillable = [
        'id',
        'user_id',
        'external_id',
        'establishment_id',
        'establishment',
        'soap_type_id',
        'state_type_id',
        'payment_method_type_id',
        'prefix',
        'date_of_issue',
        'time_of_issue',
        'date_of_due',
        'delivery_date',
        'customer_id',
        'customer',
        'currency_type_id',
        'exchange_rate_sale',
        'total_prepayment',
        'total_discount',
        'total_charge',
        'total_exportation',
        'total_free',
        'total_taxed',
        'total_unaffected',
        'total_exonerated',
        'total_igv',
        'total_base_isc',
        'total_isc',
        'total_base_other_taxes',
        'total_other_taxes',
        'total_taxes',
        'total_value',
        'total',
        'charges',
        'discounts',
        'prepayments',
        'guides',
        'related',
        'perception',
        'detraction',
        'legends',
        'filename',
        'shipping_address',
        'observation'

    ];

    protected $casts = [
        'date_of_issue' => 'date',
        'date_of_due' => 'date',
        'delivery_date' => 'date',
    ];

    public function getEstablishmentAttribute($value)
    {
        return (is_null($value))?null:(object) json_decode($value);
    }

    public function setEstablishmentAttribute($value)
    {
        $this->attributes['establishment'] = (is_null($value))?null:json_encode($value);
    }

    public function getCustomerAttribute($value)
    {
        return (is_null($value))?null:(object) json_decode($value);
    }

    public function setCustomerAttribute($value)
    {
        $this->attributes['customer'] = (is_null($value))?null:json_encode($value);
    }

    public function getChargesAttribute($value)
    {
        return (is_null($value))?null:(object) json_decode($value);
    }

    public function setChargesAttribute($value)
    {
        $this->attributes['charges'] = (is_null($value))?null:json_encode($value);
    }

    public function getDiscountsAttribute($value)
    {
        return (is_null($value))?null:(object) json_decode($value);
    }

    public function setDiscountsAttribute($value)
    {
        $this->attributes['discounts'] = (is_null($value))?null:json_encode($value);
    }

    public function getPrepaymentsAttribute($value)
    {
        return (is_null($value))?null:(object) json_decode($value);
    }

    public function setPrepaymentsAttribute($value)
    {
        $this->attributes['prepayments'] = (is_null($value))?null:json_encode($value);
    }

    public function getGuidesAttribute($value)
    {
        return (is_null($value))?null:(object) json_decode($value);
    }

    public function setGuidesAttribute($value)
    {
        $this->attributes['guides'] = (is_null($value))?null:json_encode($value);
    }

    public function getRelatedAttribute($value)
    {
        return (is_null($value))?null:(object) json_decode($value);
    }

    public function setRelatedAttribute($value)
    {
        $this->attributes['related'] = (is_null($value))?null:json_encode($value);
    }

    public function getPerceptionAttribute($value)
    {
        return (is_null($value))?null:(object) json_decode($value);
    }

    public function setPerceptionAttribute($value)
    {
        $this->attributes['perception'] = (is_null($value))?null:json_encode($value);
    }

    public function getDetractionAttribute($value)
    {
        return (is_null($value))?null:(object) json_decode($value);
    }

    public function setDetractionAttribute($value)
    {
        $this->attributes['detraction'] = (is_null($value))?null:json_encode($value);
    }

    public function getLegendsAttribute($value)
    {
        return (is_null($value))?null:(object) json_decode($value);
    }

    public function setLegendsAttribute($value)
    {
        $this->attributes['legends'] = (is_null($value))?null:json_encode($value);
    }

    public function getIdentifierAttribute()
    {
        return $this->prefix.'-'.$this->id;
    }

    public function getNumberFullAttribute()
    {
        return $this->prefix.'-'.$this->id;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function soap_type()
    {
        return $this->belongsTo(SoapType::class);
    }

    public function state_type()
    {
        return $this->belongsTo(StateType::class);
    }

    public function person() {
        return $this->belongsTo(Person::class, 'customer_id');
    }


    public function currency_type()
    {
        return $this->belongsTo(CurrencyType::class, 'currency_type_id');
    }

    public function items()
    {
        return $this->hasMany(OrderNoteItem::class);
    }


    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function sale_notes()
    {
        return $this->hasMany(SaleNote::class);
    }

    public function payment_method_type()
    {
        return $this->belongsTo(PaymentMethodType::class);
    }

    public function getNumberToLetterAttribute()
    {
        $legends = $this->legends;
        $legend = collect($legends)->where('code', '1000')->first();
        return $legend->value;
    }

    public function scopeWhereTypeUser($query)
    {
        $user = auth()->user();
        return ($user->type == 'seller') ? $query->where('user_id', $user->id) : null;
    }


    public function inventory_kardex()
    {
        return $this->morphMany(InventoryKardex::class, 'inventory_kardexable');
    }


    public function scopeWherePendingState($query, $params)
    {

        if($params['person_id']){

            return $query->doesntHave('documents')
                            ->whereBetween($params['date_range_type_id'], [$params['date_start'], $params['date_end']])
                            ->where('customer_id', $params['person_id']);
        }


        return $query->doesntHave('documents')
                        ->whereBetween($params['date_range_type_id'], [$params['date_start'], $params['date_end']])
                        ->where('user_id', $params['seller_id']);

    }


    public function scopeWhereProcessedState($query, $params)
    {

        if($params['person_id']){

            return $query->whereHas('documents')
                            ->whereBetween($params['date_range_type_id'], [$params['date_start'], $params['date_end']])
                            ->where('customer_id', $params['person_id']);

        }


        return $query->whereHas('documents')
                        ->whereBetween($params['date_range_type_id'], [$params['date_start'], $params['date_end']])
                        ->where('user_id', $params['seller_id']);

    }


    public function scopeWhereDefaultState($query, $params)
    {

        if($params['person_id']){

            return $query->whereBetween($params['date_range_type_id'], [$params['date_start'], $params['date_end']])
                            ->where('customer_id', $params['person_id']);

        }

        return $query->whereBetween($params['date_range_type_id'], [$params['date_start'], $params['date_end']])
                        ->where('user_id', $params['seller_id']);

    }

    /**
     * Establece el status anulado (11) para el pedido
     *
     * Recorre los items, si estos tienen lotes ser??n habilitados nuevamente
     *
     * @return $this
     */
    public function VoidOrderNote(): OrderNote
    {
        $order_items = $this->items;
        /** @var OrderNoteItem $item */
        foreach ($order_items as $items) {
            $item = $items->item;
            if (property_exists($item, 'lots')) {
                $lots = $item->lots;
                $total_lot = count($lots);
                for ($i = 0; $i < $total_lot; $i++) {
                    $lot = $lots[$i];
                    if (property_exists($lot, 'has_sale') && $lot->has_sale == true) {
                        $item_lot = ItemLot::find($lot->id);
                        if (!empty($item_lot) && $item_lot->has_sale == true) {
                            $item_lot->setHasSale(false)->push();
                        }
                    }
                }
            }
        }
        $this->state_type_id = '11';
        return $this;
    }

}
