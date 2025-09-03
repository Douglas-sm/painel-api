<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionsHubQuestion extends Model
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
    protected $table = 'questions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['collection_id', 'content'];

    /**
     * Get the collection that owns the question.
     */
    public function collection()
    {
        return $this->belongsTo(QuestionsHubCollection::class, 'collection_id');
    }

    /**
     * Get the choices for this question.
     */
    public function choices()
    {
        return $this->hasMany(QuestionsHubChoice::class, 'question_id');
    }
}
