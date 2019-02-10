<?php

namespace App;

use BotMan\BotMan\Interfaces\UserInterface;
use Illuminate\Database\Eloquent\Model;

class Highscore extends Model
{

    protected $fillable = ['chat_id', 'name', 'points', 'correct_answers', 'tries', 'telephone'];

    protected $table = 'highscore';

    public static function saveUser(UserInterface $botUser, int $userPoints, int $userCorrectAnswers, $telephone)
    {
        $user = static::updateOrCreate(['chat_id' => $botUser->getId()], [
            'chat_id' => $botUser->getId(),
            'name' => $botUser->getFirstName().' '.$botUser->getLastName(),
            'points' => $userPoints,
            'correct_answers' => $userCorrectAnswers,
            'telephone' => $telephone,
        ]);

        $user->increment('tries');

        $user->save();

        return $user;
    }

 /*   public function getRankAttribute()
    {
        return static::query()->where('points', '>', $this->points)->pluck('points')->unique()->count() + 1;
    }
*/
    public static function topUsers($size = 20)
    {
        return static::query()->orderByDesc('points')->orderBy('correct_answers')->oldest('updated_at')->take($size)->get();
    }

    public static function deleteUser(string $chatId)
    {
        Highscore::where('chat_id', $chatId)->delete();
    }
}
