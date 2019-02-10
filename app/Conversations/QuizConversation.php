<?php

namespace App\Conversations;

use App\Answer;
use App\Question;
use App\Highscore;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer as BotManAnswer;
use BotMan\BotMan\Messages\Outgoing\Question as BotManQuestion;

class QuizConversation extends Conversation
{
    /** @var Question */
    protected $quizQuestions;
    protected $telephone;

    /** @var integer */
    protected $userPoints = 0;

    /** @var integer */
    protected $userCorrectAnswers = 0;

    /** @var integer */
    protected $questionCount;

    /** @var integer */
    protected $currentQuestion = 1;

    /**
     * Start the conversation.
     *
     * @return mixed
     */
    public function run()
    {
        $this->quizQuestions = Question::inRandomOrder()->take(1)->get();
        $this->questionCount = $this->quizQuestions->count();
        $this->quizQuestions = $this->quizQuestions->keyBy('id');
        $this->showInfo();
    }

    private function showInfo()
    {
        $this->say("Tienes *{$this->questionCount} preguntas*. Cada respuesta correcta tendrias un cierto punto. Si estas en la lista de los mejores *recibiras 10 soles*. Suerte 🍀", ['parse_mode' => 'Markdown']);
        $this->say('Despues de elegir una respuesta, espere la siguiente pregunta antes de volver a hacer clic.');
        $this->checkForNextQuestion();
    }

    private function checkForNextQuestion()
    {
        if ($this->quizQuestions->count()) {
            return $this->askQuestion($this->quizQuestions->first());
        }

        $this->showResult();
    }

    private function askQuestion(Question $question)
    {
        $this->ask($this->createQuestionTemplate($question), function (BotManAnswer $answer) use ($question) {
            $quizAnswer = Answer::find($answer->getValue());

            if (! $quizAnswer) {
                $this->say('Lo siento, no entendi eso. Por favor use los botones.');
                return $this->checkForNextQuestion();
            }

            $this->quizQuestions->forget($question->id);

            if ($quizAnswer->correct_one) {
                $this->userPoints += $question->points;
                $this->userCorrectAnswers++;
                $answerResult = '✅';
            } else {
                $correctAnswer = $question->answers()->where('correct_one', true)->first()->text;
                $answerResult = "❌ (Correcta: {$correctAnswer})";
            }
            $this->currentQuestion++;

            $this->say("Tu respuesta: {$quizAnswer->text} {$answerResult}");
            $this->checkForNextQuestion();
        });
    }

    private function showResult()
    {
        $this->say('Finished 🏁');
        $this->say("Lo hiciste a traves de todas las preguntas. Alcanzaste *{$this->userPoints} puntos*! Correctas respuestas: {$this->userCorrectAnswers} / {$this->questionCount}", ['parse_mode' => 'Markdown']);

        $this->askHighscore();
    }

     private function askHighscore()
    {
        $question = BotManQuestion::create('Es muy facil?')
            ->addButtons([
                Button::create('Si')->value('yes'),
                Button::create('No')->value('yes'),
            ]);

        $this->ask($question, function (BotManAnswer $answer) {
            switch ($answer->getValue()) {
                case 'yes':

                    return $this->askAboutHighscore();                 
               
                case 'no':
                    return $this->say('De nada. No fuiste agregado a la puntuacion mas alta. Aun puedes contarle a tus amigos al respecto 😉');
                default:
                    return $this->repeat('Lo siento, no entendi eso. Por favor use los botones.');
            }
        });
    }

    private function askAboutHighscore()
    {
       

        $this->ask('Ingresa tu numero cuenta de Anypay de Bitel', function (BotManAnswer $answer) {
                    $this->telephone = $answer->getText();
                    $user = Highscore::saveUser($this->bot->getUser(), $this->userPoints, $this->userCorrectAnswers, $this->telephone);
                    $this->say("Hecho.");
                    return $this->bot->startConversation(new HighscoreConversation());              
           
        });
    }

    private function createQuestionTemplate(Question $question)
    {
        $questionTemplate = BotManQuestion::create("➡️ Pregunta: {$this->currentQuestion} / {$this->questionCount} : {$question->text}");

        foreach ($question->answers->shuffle() as $answer) {
            $questionTemplate->addButton(Button::create($answer->text)->value($answer->id));
        }

        return $questionTemplate;
    }
}
