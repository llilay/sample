<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    //在微博模型中定义 fillable 属性，来指定在微博模型中可以进行正常更新的字段
    protected $fillable = ['content'];

    //在微博模型中，指明一条微博属于一个用户。
    //在用户模型中，指明一个用户拥有多条微博。
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
