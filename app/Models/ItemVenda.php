<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemVenda extends Model
{
    use HasFactory;

    protected $table = 'itens_venda';

    protected $fillable = [
        'venda_id',
        'produto_id',
        'quantidade',
        'preco_compra_unitario',
        'preco_venda_unitario',
        'custo_total',
        'valor_total',
        'lucro_item'
    ];

    protected $casts = [
        'quantidade' => 'integer',
        'preco_compra_unitario' => 'decimal:2',
        'preco_venda_unitario' => 'decimal:2',
        'custo_total' => 'decimal:2',
        'valor_total' => 'decimal:2',
        'lucro_item' => 'decimal:2',
    ];

    // Relacionamentos
    public function venda()
    {
        return $this->belongsTo(Venda::class);
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    // Accessors
    public function getPrecoCompraFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->preco_compra_unitario, 2, ',', '.');
    }

    public function getPrecoVendaFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->preco_venda_unitario, 2, ',', '.');
    }

    public function getCustoTotalFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->custo_total, 2, ',', '.');
    }

    public function getValorTotalFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->valor_total, 2, ',', '.');
    }

    public function getLucroItemFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->lucro_item, 2, ',', '.');
    }

    public function getMargemLucroPercentualAttribute()
    {
        return $this->custo_total > 0 ? ($this->lucro_item / $this->custo_total) * 100 : 0;
    }
}
