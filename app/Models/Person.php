<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    protected $table = 'persons';
   protected $fillable = [
    'user_id',
    'parent_id',
    'guardian_docs',
    'complex_id',
    'firstname',
    'lastname',
    'birth_date',
    'birth_city',
    'gender',
    'blood_type',
    'profession',
    'handicap',
    'phone',
    'address',
    'wilaya',
    'study_level',
    'education',
    'favorite_activity',
    'photo',
    'birth_certificate',
    'document_birth',
    'document_photo',
    'parent_firstname',
    'parent_lastname',
    'parent_phone',
    'parent_relation',
    'age_category_id',
    'club_id','license_number' ,'tuteur_fullname',
    'attachments'


     
    //'entreprise_id'
];

    protected $casts = [
        'guardian_docs' => 'array',
        'attachments'   => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 👨‍👦 Le tuteur/parent de cet enfant (pour les enfants inscrits par un parent)
     */
    public function parent()
    {
        return $this->belongsTo(Person::class, 'parent_id');
    }

    /**
     * 👶 Les enfants inscrits sous ce compte parent
     */
    public function children()
    {
        return $this->hasMany(Person::class, 'parent_id');
    }

    public function isChild(): bool
    {
        return !empty($this->parent_id);
    }

    /**
     * 👤 Compte utilisateur qui possède cette personne
     * (la personne elle-même, ou son parent si c'est un enfant)
     */
    public function ownerUser()
    {
        return $this->user ?: optional($this->parent)->user;
    }

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function complex()
    {
        return $this->belongsTo(Complex::class, 'complex_id');
    }

    public function ageCategory()
    {
        return $this->belongsTo(AgeCategory::class);
    }
public function age()
{
    return $this->hasOne(Person::class, 'user_id', 'id');
}


    public function dossier()
{
    return $this->hasOne(\App\Models\Dossier::class, 'person_id');
}
}
