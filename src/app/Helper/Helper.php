<?php
namespace App\Helper;

class Helper
{
    public static function trimSearchWord(string $word): array
    {
        $word = trim($word); // 前後の空白除去
        if ($word === '') return [];

        $word = mb_convert_kana($word, 'as'); // 全角英数→半角
        $word = mb_strtolower($word); // 小文字化
        $word = preg_replace('/\s+/u', ' ', $word); // 空白統一
        $wordList = preg_split('/\s+/', $word);

        return $wordList;
    }
}