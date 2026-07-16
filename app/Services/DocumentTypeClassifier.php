<?php

namespace App\Services;

class DocumentTypeClassifier
{
    public const RG = 'rg';
    public const CNH = 'cnh';
    public const CNPJ_CARD = 'cnpj_card';

    /**
     * Palavras-chave que costumam aparecer no texto de cada tipo de documento.
     * Termos que se repetem em mais de um tipo (ex: "REPUBLICA FEDERATIVA DO
     * BRASIL", que aparece tanto no RG quanto na CNH) ficam de fora para não
     * confundir a pontuação.
     */
    private const KEYWORDS = [
        self::RG => [
            'REGISTRO GERAL',
            'CARTEIRA DE IDENTIDADE',
            'SECRETARIA DE SEGURANCA',
            'INSTITUTO DE IDENTIFICACAO',
            'FILIACAO',
        ],
        self::CNH => [
            'CARTEIRA NACIONAL DE HABILITACAO',
            'PERMISSAO PARA DIRIGIR',
            'DETRAN',
            'RENACH',
            'CATEGORIA HAB',
        ],
        self::CNPJ_CARD => [
            'COMPROVANTE DE INSCRICAO',
            'SITUACAO CADASTRAL',
            'RECEITA FEDERAL',
            'NATUREZA JURIDICA',
            'NOME EMPRESARIAL',
        ],
    ];

    /**
     * Pontuação mínima para considerar que um tipo "diferente do esperado"
     * foi detectado com confiança suficiente para bloquear o envio.
     */
    private const MISMATCH_THRESHOLD = 2;

    /**
     * @return array<string,int> pontuação por tipo de documento
     */
    public function classify(string $text): array
    {
        $normalized = $this->normalize($text);

        $scores = [];
        foreach (self::KEYWORDS as $type => $keywords) {
            $scores[$type] = 0;
            foreach ($keywords as $keyword) {
                if (str_contains($normalized, $keyword)) {
                    $scores[$type]++;
                }
            }
        }

        return $scores;
    }

    /**
     * @return array{status: 'valid'|'invalid', detected: ?string}
     */
    public function validate(string $text, string $expectedType): array
    {
        $scores = $this->classify($text);
        $expectedScore = $scores[$expectedType] ?? 0;

        $otherScores = $scores;
        unset($otherScores[$expectedType]);
        arsort($otherScores);
        $bestOtherType  = array_key_first($otherScores);
        $bestOtherScore = $otherScores[$bestOtherType] ?? 0;

        if ($expectedScore === 0 && $bestOtherScore >= self::MISMATCH_THRESHOLD) {
            return ['status' => 'invalid', 'detected' => $bestOtherType];
        }

        $detected = $expectedScore > 0 ? $expectedType : ($bestOtherScore > 0 ? $bestOtherType : null);

        return ['status' => 'valid', 'detected' => $detected];
    }

    private function normalize(string $text): string
    {
        $text = mb_strtoupper($text, 'UTF-8');
        $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);

        return $transliterated !== false ? $transliterated : $text;
    }
}
