<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class NavigationItem extends Model {
protected $fillable = ["label","href","parent_id","sort_order","is_active","icon","description"];
protected $casts = ["is_active" => "boolean"];
public function parent() { return $this->belongsTo(NavigationItem::class, "parent_id"); }
public function children() { return $this->hasMany(NavigationItem::class, "parent_id")->orderBy("sort_order"); }
}