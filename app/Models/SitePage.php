<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SitePage extends Model {
protected $fillable = ["slug","title","hero_title","hero_description","content","meta_title","meta_description","meta_keywords","status"];
}