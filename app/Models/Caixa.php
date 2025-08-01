<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Caixa extends Model
{
    use HasFactory;

    protected $table = 'caixa';

    protected $fillable = [
        'tipo',
        'categoria',
        'descricao',
        'valor',
        'data',
        'movimentacao_id',
        'venda_id',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'data' => 'date',
    ];

    public function venda()
    {
        return $this->belongsTo(Venda::class, 'venda_id');
    }
    public function movimentacao()
    {
        return $this->belongsTo(Movimentacao::class);
    }


    // Accessor para valor formatado
    public function getValorFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->valor, 2, ',', '.');
    }

    // Accessor para data formatada
    public function getDataFormatadaAttribute()
    {
        return $this->data->format('d/m/Y');
    }

    // Scope para filtrar por tipo
    public function scopeTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    // Scope para filtrar por categoria
    public function scopeCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    // Scope para filtrar por período
    public function scopePeriodo($query, $dataInicio, $dataFim)
    {
        return $query->whereBetween('data', [$dataInicio, $dataFim]);
    }

    // Scope para entradas
    public function scopeEntradas($query)
    {
        return $query->where('tipo', 'entrada');
    }

    // Scope para saídas
    public function scopeSaidas($query)
    {
        return $query->where('tipo', 'saida');
    }

    // Método estático para calcular saldo
    public static function calcularSaldo($dataInicio = null, $dataFim = null)
    {
        $query = self::query();

        if ($dataInicio && $dataFim) {
            $query->periodo($dataInicio, $dataFim);
        }

        $entradas = $query->entradas()->sum('valor');
        $saidas = $query->saidas()->sum('valor');

        return $entradas - $saidas;
    }

    // Método estático para resumo por categoria
    public static function resumoPorCategoria($dataInicio = null, $dataFim = null)
    {
        $query = self::query();

        if ($dataInicio && $dataFim) {
            $query->periodo($dataInicio, $dataFim);
        }

        return $query->selectRaw('categoria, tipo, SUM(valor) as total')
            ->groupBy('categoria', 'tipo')
            ->get()
            ->groupBy('categoria');
    }
}
