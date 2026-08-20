<?php

const DISC_QUESTIONS = [
    1 => ['category' => '決断の仕方', 'text' => '意思決定をするときは、慎重な検討や十分な分析よりも、スピード感と即断即決を最優先したい', 'elements' => ['D']],
    2 => ['category' => '決断の仕方', 'text' => '重要な判断を下す際は、直感や勢いよりも、データ・実績・論理的な裏付けを重視する', 'elements' => ['C']],
    3 => ['category' => '人とのかかわり方', 'text' => '会議や普段のコミュニケーションでは、ビジネスライクなやり取りよりも、雰囲気の明るさや親しみやすさを大切にしたい', 'elements' => ['I']],
    4 => ['category' => '人とのかかわり方', 'text' => '意見の食い違いが発生したときは、波風を立てずにお互いの納得感を探る調和的なアプローチを取りたい', 'elements' => ['S']],
    5 => ['category' => '仕事の進め方', 'text' => 'タスクを進める際は、事前に明確なルールや細かなプロセスが定義されている方が安心できる', 'elements' => ['C']],
    6 => ['category' => '仕事の進め方', 'text' => '予期せぬトラブルや仕様変更があっても、柔軟かつ臨機応変に方針を切り替えて対処するのが得意だ', 'elements' => ['I', 'D']],
    7 => ['category' => 'チームでの役割', 'text' => 'チーム内では、自ら先頭に立って方向性を示したり、主導権を握って引っ張っていきたい', 'elements' => ['D']],
    8 => ['category' => 'チームでの役割', 'text' => '主導権を握るよりも、チーム全体のサポート役や調整役として周囲を支える側に回りたい', 'elements' => ['S']],
    9 => ['category' => '変化への反応', 'text' => '確立されたやり方を維持するよりも、新しい手法や前例のないアイデアを積極的に試したい', 'elements' => ['D', 'I']],
    10 => ['category' => '変化への反応', 'text' => '急激な環境変化や構造改革よりも、見通しが立ちやすく予測可能な計画に基づいて安定して進めたい', 'elements' => ['S', 'C']],
];

/**
 * @param array<int,int> $answers question_no => 1-5の回答値
 * @return array{D:float,I:float,S:float,C:float}
 */
function calculateDiscScores(array $answers): array
{
    $sums = ['D' => 0, 'I' => 0, 'S' => 0, 'C' => 0];
    $counts = ['D' => 0, 'I' => 0, 'S' => 0, 'C' => 0];

    foreach (DISC_QUESTIONS as $questionNo => $meta) {
        if (!isset($answers[$questionNo])) {
            continue;
        }
        $value = $answers[$questionNo];
        foreach ($meta['elements'] as $element) {
            $sums[$element] += $value;
            $counts[$element]++;
        }
    }

    $scores = [];
    foreach (['D', 'I', 'S', 'C'] as $element) {
        $scores[$element] = $counts[$element] > 0
            ? round($sums[$element] / $counts[$element], 2)
            : 0.0;
    }

    return $scores;
}

/**
 * @param array<int,array{D:float,I:float,S:float,C:float}> $memberScoresList
 * @return array{D:float,I:float,S:float,C:float}
 */
function calculateTeamAverageScores(array $memberScoresList): array
{
    $sums = ['D' => 0, 'I' => 0, 'S' => 0, 'C' => 0];
    $count = count($memberScoresList);

    if ($count === 0) {
        return $sums;
    }

    foreach ($memberScoresList as $scores) {
        foreach (['D', 'I', 'S', 'C'] as $element) {
            $sums[$element] += $scores[$element];
        }
    }

    $averages = [];
    foreach (['D', 'I', 'S', 'C'] as $element) {
        $averages[$element] = round($sums[$element] / $count, 2);
    }

    return $averages;
}
