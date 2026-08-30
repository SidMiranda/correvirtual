<?php

namespace App\Support;

/**
 * Monta a imagem do cartão de pré-visualização do link (Open Graph).
 *
 * Por que existe uma imagem só para isso, em vez de mandar a própria arte:
 *
 * 1. **Formato.** A arte do evento é retrato (576x1024, formato de story) e o
 *    cartão do WhatsApp e do Facebook é deitado. Mandando a arte crua, o
 *    robô recorta o meio — e o meio de um cartaz de corrida é a medalha, não
 *    o nome da prova.
 * 2. **Peso.** As artes que vieram do site pesam de 500 KB a 1,9 MB. O
 *    WhatsApp desiste da prévia bem antes disso; na prática o link chegava
 *    sem imagem nenhuma. O que sai daqui fica abaixo de 300 KB.
 *
 * O resultado é 1200x630 (a proporção que os dois esperam): a arte inteira,
 * nítida, no centro, sobre uma versão desfocada e escurecida dela mesma —
 * assim o cartão fica na cor do evento sem precisar de arte extra.
 */
class ImagemOg
{
    public const LARGURA = 1200;
    public const ALTURA = 630;

    /** Qualidade do JPEG. 82 mantém o cartaz legível e o arquivo pequeno. */
    private const QUALIDADE = 82;

    /**
     * Recebe e devolve o binário da imagem.
     *
     * @param  string  $original  conteúdo do arquivo da arte (JPG, PNG ou WEBP)
     * @return string             JPEG de 1200x630
     *
     * @throws \RuntimeException quando o conteúdo não é uma imagem que o GD leia
     */
    public static function gerar(string $original): string
    {
        $arte = @imagecreatefromstring($original);

        if ($arte === false) {
            throw new \RuntimeException('Não consegui ler a arte para montar a imagem de compartilhamento.');
        }

        $tela = imagecreatetruecolor(self::LARGURA, self::ALTURA);

        self::pintarFundo($tela, $arte);
        self::colarArte($tela, $arte);

        ob_start();
        imagejpeg($tela, null, self::QUALIDADE);
        $jpeg = ob_get_clean();

        imagedestroy($arte);
        imagedestroy($tela);

        return $jpeg;
    }

    /**
     * Fundo: a própria arte cobrindo a tela toda, desfocada e escurecida.
     *
     * O desfoque é feito numa miniatura e depois ampliado — o filtro do GD é
     * fraco e caro; numa imagem de 60 pixels ele custa quase nada e, ampliado
     * para 1200, o borrão fica bem mais suave do que aplicando na imagem
     * inteira.
     */
    private static function pintarFundo(\GdImage $tela, \GdImage $arte): void
    {
        $largura = imagesx($arte);
        $altura = imagesy($arte);

        $mini = imagecreatetruecolor(60, 32);

        // Recorte "cover": pega a faixa central da arte na proporção do cartão.
        $escala = max(60 / $largura, 32 / $altura);
        $recorteLargura = (int) round(60 / $escala);
        $recorteAltura = (int) round(32 / $escala);

        imagecopyresampled(
            $mini, $arte,
            0, 0,
            (int) round(($largura - $recorteLargura) / 2),
            (int) round(($altura - $recorteAltura) / 2),
            60, 32,
            $recorteLargura, $recorteAltura
        );

        for ($i = 0; $i < 4; $i++) {
            imagefilter($mini, IMG_FILTER_GAUSSIAN_BLUR);
        }

        imagecopyresampled($tela, $mini, 0, 0, 0, 0, self::LARGURA, self::ALTURA, 60, 32);
        imagedestroy($mini);

        // Escurece: sem isso o cartaz nítido some dentro do próprio borrão.
        imagealphablending($tela, true);
        $preto = imagecolorallocatealpha($tela, 5, 8, 13, 45);
        imagefilledrectangle($tela, 0, 0, self::LARGURA, self::ALTURA, $preto);
    }

    /** A arte inteira, nítida, centralizada — sem recorte e sem deformar. */
    private static function colarArte(\GdImage $tela, \GdImage $arte): void
    {
        $largura = imagesx($arte);
        $altura = imagesy($arte);

        // 0.92 deixa uma respiração em cima e embaixo; colada na borda o
        // cartaz parece cortado mesmo estando inteiro.
        $escala = min(self::LARGURA / $largura, (self::ALTURA * 0.92) / $altura);

        $novaLargura = (int) round($largura * $escala);
        $novaAltura = (int) round($altura * $escala);

        imagecopyresampled(
            $tela, $arte,
            (int) round((self::LARGURA - $novaLargura) / 2),
            (int) round((self::ALTURA - $novaAltura) / 2),
            0, 0,
            $novaLargura, $novaAltura,
            $largura, $altura
        );
    }
}
