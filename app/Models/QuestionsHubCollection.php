<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionsHubCollection extends Model
{
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'questions_hub';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'subject_categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];

    /**
     * Get the questions for this collection.
     */
    public function questions()
    {
        return $this->hasMany(QuestionsHubQuestion::class, 'collection_id');
    }
}
