<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionsHubChoice extends Model
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
    protected $table = 'choices';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['question_id', 'content', 'is_correct'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_correct' => 'boolean',
    ];

    /**
     * Get the question that owns the choice.
     */
    public function question()
    {
        return $this->belongsTo(QuestionsHubQuestion::class, 'question_id');
    }
}
