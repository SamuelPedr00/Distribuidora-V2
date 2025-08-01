<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Venda extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero_venda',
        'total_venda',
        'total_custo',
        'lucro_total',
        'status',
        'cliente_id',
        'observacoes',
        'data_venda'
    ];

    protected $casts = [
        'total_venda' => 'decimal:2',
        'total_custo' => 'decimal:2',
        'lucro_total' => 'decimal:2',
        'data_venda' => 'datetime',
    ];
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
    // Relacionamentos
    public function itensVenda()
    {
        return $this->hasMany(ItemVenda::class, 'venda_id');
    }

    public function caixa()
    {
        return $this->hasOne(Caixa::class, 'venda_id');
    }

    // Accessors
    public function getTotalVendaFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->total_venda, 2, ',', '.');
    }

    public function getTotalCustoFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->total_custo, 2, ',', '.');
    }

    public function getLucroTotalFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->lucro_total, 2, ',', '.');
    }

    public function getDataVendaFormatadaAttribute()
    {
        return $this->data_venda->format('d/m/Y H:i');
    }

    public function getMargemLucroPercentualAttribute()
    {
        return $this->total_custo > 0 ? ($this->lucro_total / $this->total_custo) * 100 : 0;
    }

    // Métodos
    public function calcularTotais()
    {
        $this->total_venda = $this->itens->sum('valor_total');
        $this->total_custo = $this->itens->sum('custo_total');
        $this->lucro_total = $this->total_venda - $this->total_custo;
        $this->save();
    }

    public function adicionarItem($produtoId, $quantidade, $precoVenda = null)
    {
        $produto = Produto::find($produtoId);

        if (!$produto) {
            throw new \Exception('Produto não encontrado');
        }

        $precoVenda = $precoVenda ?? $produto->preco_venda_atual;
        $precoCompra = $produto->preco_compra_atual;

        $item = $this->itens()->create([
            'produto_id' => $produtoId,
            'quantidade' => $quantidade,
            'preco_compra_unitario' => $precoCompra,
            'preco_venda_unitario' => $precoVenda,
            'custo_total' => $quantidade * $precoCompra,
            'valor_total' => $quantidade * $precoVenda,
            'lucro_item' => ($quantidade * $precoVenda) - ($quantidade * $precoCompra)
        ]);

        $this->calcularTotais();
        return $item;
    }

    public function finalizarVenda()
    {
        if ($this->status !== 'pendente') {
            throw new \Exception('Venda já foi finalizada ou cancelada');
        }

        // Atualizar estoque e criar movimentações
        foreach ($this->itens as $item) {
            // Reduzir estoque
            $estoque = $item->produto->estoque;
            if ($estoque && $estoque->quantidade >= $item->quantidade) {
                $estoque->decrement('quantidade', $item->quantidade);
            } else {
                throw new \Exception("Estoque insuficiente para o produto: {$item->produto->nome}");
            }

            // Criar movimentação de saída
            Movimentacao::create([
                'produto_id' => $item->produto_id,
                'venda_id' => $this->id,
                'tipo' => 'saida',
                'tipo_preco' => 'venda',
                'quantidade' => $item->quantidade,
                'preco_unitario' => $item->preco_venda_unitario,
                'total' => $item->valor_total,
                'observacao' => "Venda #{$this->numero_venda}",
                'data' => $this->data_venda
            ]);
        }

        // Registrar entrada no caixa
        Caixa::create([
            'tipo' => 'entrada',
            'categoria' => 'venda',
            'descricao' => "Venda #{$this->numero_venda}" . ($this->cliente_nome ? " - {$this->cliente_nome}" : ""),
            'valor' => $this->total_venda,
            'data' => $this->data_venda->toDateString()
        ]);

        $this->update(['status' => 'concluida']);
    }

    // Scopes
    public function scopeConcluidas($query)
    {
        return $query->where('status', 'concluida');
    }

    public function scopePeriodo($query, $dataInicio, $dataFim)
    {
        return $query->whereBetween('data_venda', [$dataInicio, $dataFim]);
    }

    // Geração automática do número da venda
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($venda) {
            if (!$venda->numero_venda) {
                $ultimaVenda = static::orderBy('id', 'desc')->first();
                $proximoNumero = $ultimaVenda ? (intval(substr($ultimaVenda->numero_venda, -6)) + 1) : 1;
                $venda->numero_venda = 'VD' . str_pad($proximoNumero, 6, '0', STR_PAD_LEFT);
            }
        });
    }
}
