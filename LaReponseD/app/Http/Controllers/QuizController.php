<?php

namespace App\Http\Controllers;

use App\Question;
use App\Quiz;
use View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use http\Exception\InvalidArgumentException;
use function Symfony\Component\HttpKernel\Tests\Controller\controller_function;
use function Symfony\Component\HttpKernel\Tests\controller_func;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class QuizController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('quizBlade.index', ['quizs' => Quiz::all()->sortByDesc('created_at')]);
    }

    public function allQuizs()
    {
        $quizs = Quiz::paginate(15);
        return view('quizBlade.indexall', compact('quizs'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        return view('quizBlade.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        $validatedQuiz = $request->validate([
            'titre' => 'required',
            'theme' => 'required',
        ]);

        $newQuiz = new Quiz;

        $newQuiz->titre = $request->titre;
        $newQuiz->theme = $request->theme;
        $newQuiz->user_id = Auth::user()->id;

        $newQuiz->save();

        return view('quizBlade.question.create', ['quiz' => $newQuiz]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Quiz  $quiz
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $quiz = Quiz::with('questions.choix')->find($id);
        return view('quizBlade.show', ['quiz' => $quiz]);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Quiz  $quiz
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //$questions = Question::where('quiz_id', $quiz_id)->get();
        $quiz = Quiz::with('questions.choix')->find($id);
        return view('quizBlade.edit', ['quiz' => $quiz]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Quiz  $quiz
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        $quiz = Quiz::with('questions.choix')->find($id);
        $request->validate([
            'theme'=>'required|string',
            'questions'=> ['required', 'array', function($attribute, $values, $fail) use($id){
                foreach($values as $question_id => $value) {
                    if(!Question::where('quiz_id', $id)->where('id', $question_id)->exists()){
                        $fail($attribute.' contains question_id '.$question_id.' which does not exist.');
                    }
                }
            }],
            'questions.*.question' => 'required|string',
            'questions.*.choix0'=>'required|string',
            'questions.*.choix1'=>'required|string',
            'questions.*.choix2'=>'required|string',
            'questions.*.choix3'=>'required|string',
            ]);

        $quiz->theme = $request->get('theme');
        foreach ($request->input('questions') as $question_id => $values) {
            $question = Question::find($question_id);
            $question->question = $values['question'];
            $question->save();
            $question->choix->choix0 = $values['choix0'];
            $question->choix->choix1 = $values['choix1'];
            $question->choix->choix2 = $values['choix2'];
            $question->choix->choix3 = $values['choix3'];
            $question->choix->save();
        }
        $quiz->save();

        return redirect('/quiz/show/'.$id)->with('success', 'Quiz mis à jour!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Quiz  $quiz
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $quiz = Quiz::find($id);
        $quiz->delete();

        return redirect('/quiz')->with('success', 'Quiz delete!');
    }

    public function verify(Request $request) {
        $points_max = sizeof($request->reponses);
        $points = 0;

        for ($i=0; $i < sizeof($request->reponses); $i++) {
            if ($request[$i] == $request->reponses[$i]) {
                $points += 1;
            }
        }

        $quiz = Quiz::find($request->quiz_id);
        $quiz->joues += 1;
        $quiz->save();

        return view('quizBlade.results', ['points' => $points, 'points_max' => $points_max]);
    }

    public function categorie($theme)
    {
        return view('quizBlade.index', ['quizs' => Quiz::all()->where('theme', $theme)]);
    }
}
