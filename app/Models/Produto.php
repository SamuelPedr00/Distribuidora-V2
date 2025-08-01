<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Produto extends Model
{
    use HasFactory;

    protected $table = 'produtos';

    protected $fillable = [
        'codigo',
        'nome',
        'preco_venda_atual',
        'preco_compra_atual',
        'preco_venda_fardo',
        'categoria',
        'descricao',
        'status'
    ];

    protected $casts = [
        'preco_venda_atual' => 'decimal:2',
        'preco_compra_atual' => 'decimal:2',
    ];

    // Relacionamentos
    public function estoque()
    {
        return $this->hasOne(Estoque::class);
    }

    public function movimentacoes()
    {
        return $this->hasMany(Movimentacao::class);
    }

    public function historicoPrecos()
    {
        return $this->hasMany(HistoricoPreco::class);
    }

    public function itensVenda()
    {
        return $this->hasMany(ItemVenda::class);
    }

    // Accessors
    public function getPrecoVendaFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->preco_venda_atual, 2, ',', '.');
    }

    public function getPrecoCompraFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->preco_compra_atual, 2, ',', '.');
    }

    public function getMargemLucroAtualAttribute()
    {
        if ($this->preco_compra_atual == 0) return 0;
        return (($this->preco_venda_atual - $this->preco_compra_atual) / $this->preco_compra_atual) * 100;
    }

    public function getLucroUnitarioAttribute()
    {
        return $this->preco_venda_atual - $this->preco_compra_atual;
    }

    // Métodos para histórico de preços
    public function atualizarPrecos($precoCompra, $precoVenda, $motivo = null)
    {
        // Finalizar preço atual no histórico
        $this->historicoPrecos()->where('ativo', true)->update([
            'ativo' => false,
            'data_fim' => now()
        ]);

        // Criar novo registro no histórico
        $this->historicoPrecos()->create([
            'preco_compra' => $precoCompra,
            'preco_venda' => $precoVenda,
            'margem_lucro' => $precoCompra > 0 ? (($precoVenda - $precoCompra) / $precoCompra) * 100 : 0,
            'data_vigencia' => now(),
            'ativo' => true,
            'motivo_alteracao' => $motivo
        ]);

        // Atualizar preços atuais do produto
        $this->update([
            'preco_compra_atual' => $precoCompra,
            'preco_venda_atual' => $precoVenda
        ]);
    }

    public function getPrecoNaData($data, $tipo = 'venda')
    {
        $campo = $tipo === 'venda' ? 'preco_venda' : 'preco_compra';

        $historico = $this->historicoPrecos()
            ->where('data_vigencia', '<=', $data)
            ->where(function ($q) use ($data) {
                $q->whereNull('data_fim')
                    ->orWhere('data_fim', '>', $data);
            })
            ->orderBy('data_vigencia', 'desc')
            ->first();

        return $historico ? $historico->$campo : $this->{"preco_{$tipo}_atual"};
    }

    // Relatórios
    public function totalVendido($dataInicio = null, $dataFim = null)
    {
        $query = $this->itensVenda()->whereHas('venda', function ($q) {
            $q->where('status', 'concluida');
        });

        if ($dataInicio && $dataFim) {
            $query->whereHas('venda', function ($q) use ($dataInicio, $dataFim) {
                $q->whereBetween('data_venda', [$dataInicio, $dataFim]);
            });
        }

        return $query->sum('quantidade');
    }

    public function lucroTotal($dataInicio = null, $dataFim = null)
    {
        $query = $this->itensVenda()->whereHas('venda', function ($q) {
            $q->where('status', 'concluida');
        });

        if ($dataInicio && $dataFim) {
            $query->whereHas('venda', function ($q) use ($dataInicio, $dataFim) {
                $q->whereBetween('data_venda', [$dataInicio, $dataFim]);
            });
        }

        return $query->sum('lucro_item');
    }
}
