<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\QuestionsHubCollection;
use App\Models\QuestionsHubQuestion;

class QuestionsHubController extends Controller
{
    /**
     * Get all collections from the questions_hub database.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCollections()
    {
        try {
            $collections = QuestionsHubCollection::all();
            return response()->json([
                'success' => true,
                'data' => $collections,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve collections',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific collection with its questions.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCollectionWithQuestions($id)
    {
        try {
            $collection = QuestionsHubCollection::with('questions')->findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $collection,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Collection not found or error retrieving data',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Get all questions from the questions_hub database.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getQuestions()
    {
        try {
            $questions = QuestionsHubQuestion::all();
            return response()->json([
                'success' => true,
                'data' => $questions,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve questions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific question with its choices.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getQuestionWithChoices($id)
    {
        try {
            $question = QuestionsHubQuestion::with('choices')->findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $question,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Question not found or error retrieving data',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Get questions by collection id.
     *
     * @param int $collectionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getQuestionsByCollection($collectionId)
    {
        try {
            $questions = QuestionsHubQuestion::where('collection_id', $collectionId)
                ->with('choices')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $questions,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve questions for this collection',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
