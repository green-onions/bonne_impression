<?php


namespace App\Service;


class LetterManager
{
    public function getLetters(string $word, array $letters)
    {
        $wordInArray = str_split($word);
        $goodLetters = [];
        $result = [];

        foreach ($letters as $goodLetter) {
            $goodLetters[] = $goodLetter->getLetter();
        }

        foreach ($wordInArray as $letter) {
            if (in_array($letter, $goodLetters)) {
                $result[] = $letter;
            } else {
                $result[] = '_';
            }
        }

        return $result;
    }

    public function checkWin ($word, $responses)
    {
        $wordInArray = str_split($word);
        $result = false;

        if ($wordInArray === $responses) {
            $result = true;
        }

        return $result;
    }
}
