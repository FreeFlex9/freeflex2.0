<?php

namespace Tests\Unit;

use App\Services\DocumentTypeClassifier;
use PHPUnit\Framework\TestCase;

class DocumentTypeClassifierTest extends TestCase
{
    private DocumentTypeClassifier $classifier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->classifier = new DocumentTypeClassifier();
    }

    public function test_reconhece_rg_correto(): void
    {
        $texto = 'REPUBLICA FEDERATIVA DO BRASIL REGISTRO GERAL CARTEIRA DE IDENTIDADE '
            . 'SECRETARIA DE SEGURANCA PUBLICA NOME FILIACAO JOAO DA SILVA';

        $resultado = $this->classifier->validate($texto, DocumentTypeClassifier::RG);

        $this->assertSame('valid', $resultado['status']);
        $this->assertSame(DocumentTypeClassifier::RG, $resultado['detected']);
    }

    public function test_reconhece_cnh_correta(): void
    {
        $texto = 'CARTEIRA NACIONAL DE HABILITACAO PERMISSAO PARA DIRIGIR DETRAN RENACH CATEGORIA HAB AB';

        $resultado = $this->classifier->validate($texto, DocumentTypeClassifier::CNH);

        $this->assertSame('valid', $resultado['status']);
        $this->assertSame(DocumentTypeClassifier::CNH, $resultado['detected']);
    }

    public function test_reconhece_cartao_cnpj_correto(): void
    {
        $texto = 'COMPROVANTE DE INSCRICAO E DE SITUACAO CADASTRAL RECEITA FEDERAL NATUREZA JURIDICA NOME EMPRESARIAL';

        $resultado = $this->classifier->validate($texto, DocumentTypeClassifier::CNPJ_CARD);

        $this->assertSame('valid', $resultado['status']);
        $this->assertSame(DocumentTypeClassifier::CNPJ_CARD, $resultado['detected']);
    }

    public function test_rejeita_cnh_enviada_no_campo_de_rg(): void
    {
        $texto = 'CARTEIRA NACIONAL DE HABILITACAO PERMISSAO PARA DIRIGIR DETRAN RENACH';

        $resultado = $this->classifier->validate($texto, DocumentTypeClassifier::RG);

        $this->assertSame('invalid', $resultado['status']);
        $this->assertSame(DocumentTypeClassifier::CNH, $resultado['detected']);
    }

    public function test_rejeita_cartao_cnpj_enviado_no_campo_de_cnh(): void
    {
        $texto = 'COMPROVANTE DE INSCRICAO E DE SITUACAO CADASTRAL RECEITA FEDERAL NATUREZA JURIDICA';

        $resultado = $this->classifier->validate($texto, DocumentTypeClassifier::CNH);

        $this->assertSame('invalid', $resultado['status']);
        $this->assertSame(DocumentTypeClassifier::CNPJ_CARD, $resultado['detected']);
    }

    public function test_da_beneficio_da_duvida_quando_texto_nao_bate_com_nada(): void
    {
        $texto = 'texto ilegivel sem nenhuma palavra-chave reconhecida';

        $resultado = $this->classifier->validate($texto, DocumentTypeClassifier::RG);

        $this->assertSame('valid', $resultado['status']);
        $this->assertNull($resultado['detected']);
    }

    public function test_da_beneficio_da_duvida_quando_apenas_um_termo_ambiguo_aparece(): void
    {
        // Só uma palavra-chave de outro tipo (abaixo do threshold de mismatch) não deve bloquear.
        $texto = 'documento com FILIACAO mencionada mas sem mais nada reconhecivel';

        $resultado = $this->classifier->validate($texto, DocumentTypeClassifier::CNPJ_CARD);

        $this->assertSame('valid', $resultado['status']);
    }
}
