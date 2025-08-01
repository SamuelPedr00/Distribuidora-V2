<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Movimentacao extends Model
{
    use HasFactory;

    protected $table = 'movimentacoes';

    protected $fillable = [
        'produto_id',
        'tipo',
        'quantidade',
        'preco_unitario',
        'preco_custo',
        'total',
        'observacao',
        'data'
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'total' => 'decimal:2',
        'quantidade' => 'integer',
        'data' => 'datetime',
    ];

    public function caixa()
    {
        return $this->hasOne(Caixa::class);
    }


    // Relacionamento com produto (N:1)
    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    // Accessor para valor formatado
    public function getValorFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->valor, 2, ',', '.');
    }

    // Accessor para total formatado
    public function getTotalFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->total, 2, ',', '.');
    }

    // Accessor para data formatada
    public function getDataFormatadaAttribute()
    {
        return $this->data->format('d/m/Y H:i');
    }

    // Scope para filtrar por tipo
    public function scopeTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    // Scope para filtrar por período
    public function scopePeriodo($query, $dataInicio, $dataFim)
    {
        return $query->whereBetween('data', [$dataInicio, $dataFim]);
    }

    // Mutator para calcular o total automaticamente
    public function setQuantidadeAttribute($value)
    {
        $this->attributes['quantidade'] = $value;
        if (isset($this->attributes['valor'])) {
            $this->attributes['total'] = $value * $this->attributes['valor'];
        }
    }

    public function setValorAttribute($value)
    {
        $this->attributes['valor'] = $value;
        if (isset($this->attributes['quantidade'])) {
            $this->attributes['total'] = $this->attributes['quantidade'] * $value;
        }
    }
}
