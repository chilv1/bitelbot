<?php

use App\Conversations\HighscoreConversation;
use App\Conversations\PrivacyConversation;
use App\Http\Middleware\TypingMiddleware;
use BotMan\BotMan\BotMan;
use App\Conversations\QuizConversation;
use App\Conversations\WelcomeConversation;

$botman = resolve('botman');

$typingMiddleware = new TypingMiddleware();
$botman->middleware->sending($typingMiddleware);

$botman->hears('Hi', function (BotMan $bot) {
    $bot->reply('Hello!');
});

$botman->hears('/start', function (BotMan $bot) {
    $bot->startConversation(new WelcomeConversation());
})->stopsConversation();

$botman->hears('start|/startQuiz', function (BotMan $bot) {
    $bot->startConversation(new QuizConversation());
})->stopsConversation();

$botman->hears('/highscore|highscore', function (BotMan $bot) {
    $bot->startConversation(new HighscoreConversation());
})->stopsConversation();

$botman->hears('/about|about', function (BotMan $bot) {
    $bot->reply('Bitelbot es una Chat Bot para conocer las promociones de Bitel - Chilv1: 930541111');
})->stopsConversation();

$botman->hears('/deletedata|deletedata', function (BotMan $bot) {
    $bot->startConversation(new PrivacyConversation());
})->stopsConversation();
