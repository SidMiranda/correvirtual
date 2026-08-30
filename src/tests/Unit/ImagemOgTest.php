<?php

namespace Tests\Unit;

use App\Support\ImagemOg;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;
use PHPUnit\Framework\TestCase;

/**
 * A imagem do cartão de pré-visualização do link.
 *
 * Dois números mandam aqui, e os dois vêm de fora: 1200x630 é o que o WhatsApp
 * e o Facebook esperam, e a prévia não aparece quando o arquivo é pesado. Foi
 * exatamente isso que aconteceu com as artes importadas do site (até 1,9 MB):
 * o link chegava sem imagem nenhuma.
 */
class ImagemOgTest extends TestCase
{
    private function arte(int $largura, int $altura): string
    {
        $imagem = imagecreatetruecolor($largura, $altura);
        imagefilledrectangle($imagem, 0, 0, $largura, $altura, imagecolorallocate($imagem, 200, 40, 40));
        // Um retângulo claro fora do centro: se algum dia o código voltar a
        // recortar em vez de conter, ele some do resultado.
        imagefilledrectangle($imagem, 10, 10, $largura - 10, 60, imagecolorallocate($imagem, 250, 250, 250));

        ob_start();
        imagejpeg($imagem, null, 90);

        return ob_get_clean();
    }

    public function test_sai_sempre_em_1200x630(): void
    {
        // Retrato (formato das artes) e paisagem (banner do organizador) caem
        // no mesmo quadro — é isso que autoriza declarar as dimensões no HTML.
        foreach ([[576, 1024], [1500, 500], [800, 800]] as [$largura, $altura]) {
            $tamanho = getimagesizefromstring(ImagemOg::gerar($this->arte($largura, $altura)));

            $this->assertSame(ImagemOg::LARGURA, $tamanho[0]);
            $this->assertSame(ImagemOg::ALTURA, $tamanho[1]);
            $this->assertSame('image/jpeg', $tamanho['mime']);
        }
    }

    public function test_fica_leve_o_bastante_para_o_whatsapp_montar_a_previa(): void
    {
        $jpeg = ImagemOg::gerar($this->arte(576, 1024));

        $this->assertLessThan(300 * 1024, strlen($jpeg));
    }

    // O GD avisa antes de devolver false, e o @ do código não segura o aviso
    // dentro do PHPUnit — só este atributo segura.
    #[WithoutErrorHandler]
    public function test_reclama_quando_o_conteudo_nao_e_imagem(): void
    {
        $this->expectException(\RuntimeException::class);

        ImagemOg::gerar('isto aqui não é uma imagem');
    }
}
