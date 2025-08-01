<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HistoricoPreco extends Model
{
    use HasFactory;

    protected $table = 'historico_precos';

    protected $fillable = [
        'produto_id',
        'preco_compra',
        'preco_venda',
        'margem_lucro',
        'data_vigencia',
        'data_fim',
        'ativo',
        'motivo_alteracao'
    ];

    protected $casts = [
        'preco_compra' => 'decimal:2',
        'preco_venda' => 'decimal:2',
        'margem_lucro' => 'decimal:2',
        'data_vigencia' => 'datetime',
        'data_fim' => 'datetime',
        'ativo' => 'boolean',
    ];

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    // Accessors
    public function getPrecoCompraFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->preco_compra, 2, ',', '.');
    }

    public function getPrecoVendaFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->preco_venda, 2, ',', '.');
    }

    public function getPeriodoVigenciaAttribute()
    {
        $inicio = $this->data_vigencia->format('d/m/Y');
        $fim = $this->data_fim ? $this->data_fim->format('d/m/Y') : 'Atual';
        return "{$inicio} - {$fim}";
    }

    // Scopes
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    public function scopeVigentesEm($query, $data)
    {
        return $query->where('data_vigencia', '<=', $data)
            ->where(function ($q) use ($data) {
                $q->whereNull('data_fim')
                    ->orWhere('data_fim', '>', $data);
            });
    }
}
