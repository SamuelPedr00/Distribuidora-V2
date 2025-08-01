<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Estoque extends Model
{
    use HasFactory;

    protected $table = 'estoque';
    protected $primaryKey = 'produto_id';
    public $incrementing = false;

    protected $fillable = [
        'produto_id',
        'quantidade'
    ];

    protected $casts = [
        'quantidade' => 'integer',
    ];

    // Relacionamento com produto (1:1)
    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    // Accessor para valor total do estoque
    public function getValorTotalAttribute()
    {
        return $this->quantidade * $this->produto->preco;
    }

    // Accessor para valor total formatado
    public function getValorTotalFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->valor_total, 2, ',', '.');
    }
}
