<?php

namespace App\Conversations;

use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Conversations\Conversation;

class WelcomeConversation extends Conversation
{
    /**
     * Start the conversation.
     *
     * @return mixed
     */
    public function run()
    {
        $this->welcomeUser();
    }

    private function welcomeUser()
    {
        $this->say('Hey '.$this->bot->getUser()->getFirstName().' 👋');
        $this->askIfReady();
    }

    private function askIfReady()
    {
        $question = Question::create('Bienvenido a Bitel Bot. Estas listo para la prueba?')
            ->addButtons([
                Button::create('Si')->value('yes'),
                Button::create('No')->value('no'),
            ]);

        $this->ask($question, function (Answer $answer) {
            if ($answer->getValue() === 'yes') {
                $this->say('Perfecto!');
                return $this->bot->startConversation(new QuizConversation());
            }

            $this->say('😒');
            $this->say('Si cambias de opinion, puede iniciar el cuestionario en cualquier momento con el comando de inicio o escribiendo /start.');
        });
    }
}
