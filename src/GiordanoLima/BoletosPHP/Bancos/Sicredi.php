<?php

namespace GiordanoLima\BoletosPHP\Bancos;

class Sicredi
{
    public static $requires = [
        'posto',
        'byte_idt',
        'carteira',
    ];

    public static function render($boleto)
    {
        $dadosboleto = $boleto->getDadosBoleto();
        $codigobanco = '748';
        $codigo_banco_com_dv = self::geraCodigoBanco($codigobanco);
        $nummoeda = '9';
        $fator_vencimento = self::fator_vencimento($dadosboleto['data_vencimento']);

        $valor = self::formata_numero($dadosboleto['valor_boleto'], 10, 0, 'valor');
        $agencia = self::formata_numero($dadosboleto['agencia'], 4, 0);
        $posto = self::formata_numero($dadosboleto['posto'], 2, 0);
        $conta = self::formata_numero($dadosboleto['conta'], 5, 0);
        $conta_dv = self::formata_numero($dadosboleto['conta_dv'], 1, 0);
        $carteira = $dadosboleto['carteira'];

        $filler1 = 1;
        $filler2 = 0;

        $byteidt = $dadosboleto['byte_idt'];
        $tipo_cobranca = 3;
        $tipo_carteira = 1;
        $nnum = $dadosboleto['inicio_nosso_numero'].$byteidt.self::formata_numero($dadosboleto['nosso_numero'], 5, 0);
        $dv_nosso_numero = self::digitoVerificador_nossonumero("$agencia$posto$conta$nnum");
        $nossonumero_dv = "$nnum$dv_nosso_numero";
        $campolivre = "$tipo_cobranca$tipo_carteira$nossonumero_dv$agencia$posto$conta$filler1$filler2";
        $campolivre_dv = $campolivre.self::digitoVerificador_campolivre($campolivre);
        $dv = self::digitoVerificador_barra("$codigobanco$nummoeda$fator_vencimento$valor$campolivre_dv", 9, 0);
        $linha = "$codigobanco$nummoeda$dv$fator_vencimento$valor$campolivre_dv";
        $nossonumero = substr($nossonumero_dv, 0, 2).'/'.substr($nossonumero_dv, 2, 6).'-'.substr($nossonumero_dv, 8, 1);
        $agencia_codigo = $agencia.'.'.$posto.'.'.$conta;

        $dadosboleto['codigo_barras'] = $linha;
        $dadosboleto['linha_digitavel'] = self::monta_linha_digitavel($linha);
        $dadosboleto['agencia_codigo'] = $agencia_codigo;
        $dadosboleto['codigo_banco_com_dv'] = $codigo_banco_com_dv;
        $dadosboleto['nosso_numero'] = $nossonumero;
        $boleto->nossoNumero = $nossonumero;

        ob_start();
        require __DIR__.'/../includes/layout_sicredi.php';
        $r = ob_get_contents();
        ob_end_clean();

        return $r;
    }

    /* ======------- FUNÇOES -------======= */

    public static function geraCodigoBanco($numero)
    {
        $parte1 = substr($numero, 0, 3);

        return $parte1.'-X';
    }

    public static function fator_vencimento($data)
    {
        $data = explode('/', $data);
        $ano = $data[2];
        $mes = $data[1];
        $dia = $data[0];

        return abs((self::dateToDays('1997', '10', '07')) - (self::dateToDays($ano, $mes, $dia)));
    }

    public static function digitoVerificador_nossonumero($numero)
    {
        $resto2 = self::modulo_11($numero, 9, 1);
        $digito = 11 - $resto2;
        if ($digito > 9) {
            $dv = 0;
        } else {
            $dv = $digito;
        }

        return $dv;
    }

    public static function digitoVerificador_campolivre($numero)
    {
        $resto2 = self::modulo_11($numero, 9, 1);
        if ($resto2 <= 1) {
            $dv = 0;
        } else {
            $dv = 11 - $resto2;
        }

        return $dv;
    }

    public static function digitoVerificador_barra($numero)
    {
        $resto2 = self::modulo_11($numero, 9, 1);
        $digito = 11 - $resto2;
        if ($digito <= 1 || $digito >= 10) {
            $dv = 1;
        } else {
            $dv = $digito;
        }

        return $dv;
    }

    public static function formata_numero($numero, $loop, $insert, $tipo = 'geral')
    {
        if ($tipo == 'geral') {
            $numero = str_replace(',', '', $numero);
            while (strlen($numero) < $loop) {
                $numero = $insert.$numero;
            }
        }
        if ($tipo == 'valor') {
            $numero = str_replace(',', '', $numero);
            while (strlen($numero) < $loop) {
                $numero = $insert.$numero;
            }
        }
        if ($tipo == 'convenio') {
            while (strlen($numero) < $loop) {
                $numero = $numero.$insert;
            }
        }

        return $numero;
    }

    public static function dateToDays($year, $month, $day)
    {
        $century = substr($year, 0, 2);
        $year = substr($year, 2, 2);
        if ($month > 2) {
            $month -= 3;
        } else {
            $month += 9;
            if ($year) {
                $year--;
            } else {
                $year = 99;
                $century--;
            }
        }

        return  floor((146097 * $century) / 4) +
                floor((1461 * $year) / 4) +
                floor((153 * $month + 2) / 5) +
                $day + 1721119;
    }

    public static function modulo_10($num)
    {
        $numtotal10 = 0;
        $fator = 2;

        for ($i = strlen($num); $i > 0; $i--) {
            $numeros[$i] = substr($num, $i - 1, 1);
            $temp = $numeros[$i] * $fator;
            $temp0 = 0;
            foreach (preg_split('//', $temp, -1, PREG_SPLIT_NO_EMPTY) as $k => $v) {
                $temp0 += $v;
            }
            $parcial10[$i] = $temp0;
            $numtotal10 += $parcial10[$i];
            if ($fator == 2) {
                $fator = 1;
            } else {
                $fator = 2;
            }
        }

        $resto = $numtotal10 % 10;
        $digito = 10 - $resto;
        if ($resto == 0) {
            $digito = 0;
        }

        return $digito;
    }

    public static function modulo_11($num, $base = 9, $r = 0)
    {
        $soma = 0;
        $fator = 2;
        for ($i = strlen($num); $i > 0; $i--) {
            $numeros[$i] = substr($num, $i - 1, 1);
            $parcial[$i] = $numeros[$i] * $fator;
            $soma += $parcial[$i];
            if ($fator == $base) {
                $fator = 1;
            }
            $fator++;
        }
        if ($r == 0) {
            $soma *= 10;
            $digito = $soma % 11;

            return $digito;
        } elseif ($r == 1) {
            $r_div = (int) ($soma / 11);
            $digito = ($soma - ($r_div * 11));

            return $digito;
        }
    }

    public static function monta_linha_digitavel($codigo)
    {
        $p1 = substr($codigo, 0, 4);
        $p2 = substr($codigo, 19, 5);
        $p3 = self::modulo_10("$p1$p2");
        $p4 = "$p1$p2$p3";
        $p5 = substr($p4, 0, 5);
        $p6 = substr($p4, 5);
        $campo1 = "$p5.$p6";

        $p1 = substr($codigo, 24, 10);
        $p2 = self::modulo_10($p1);
        $p3 = "$p1$p2";
        $p4 = substr($p3, 0, 5);
        $p5 = substr($p3, 5);
        $campo2 = "$p4.$p5";

        $p1 = substr($codigo, 34, 10);
        $p2 = self::modulo_10($p1);
        $p3 = "$p1$p2";
        $p4 = substr($p3, 0, 5);
        $p5 = substr($p3, 5);
        $campo3 = "$p4.$p5";

        $campo4 = substr($codigo, 4, 1);

        $p1 = substr($codigo, 5, 4);
        $p2 = substr($codigo, 9, 10);
        $campo5 = "$p1$p2";

        return "$campo1 $campo2 $campo3 $campo4 $campo5";
    }

    public static function fbarcode($valor, $imageBasePath)
    {
        $retorno = '';
        $fino = 1;
        $largo = 3;
        $altura = 50;

        $barcodes[0] = '00110';
        $barcodes[1] = '10001';
        $barcodes[2] = '01001';
        $barcodes[3] = '11000';
        $barcodes[4] = '00101';
        $barcodes[5] = '10100';
        $barcodes[6] = '01100';
        $barcodes[7] = '00011';
        $barcodes[8] = '10010';
        $barcodes[9] = '01010';
        for ($f1 = 9; $f1 >= 0; $f1--) {
            for ($f2 = 9; $f2 >= 0; $f2--) {
                $f = ($f1 * 10) + $f2;
                $texto = '';
                for ($i = 1; $i < 6; $i++) {
                    $texto .= substr($barcodes[$f1], ($i - 1), 1).substr($barcodes[$f2], ($i - 1), 1);
                }
                $barcodes[$f] = $texto;
            }
        }

        $texto = $valor;
        if ((strlen($texto) % 2) != 0) {
            $texto = '0'.$texto;
        }

        $retorno .= "<img src='".$imageBasePath."p.png' width=$fino height=$altura border=0>";
        $retorno .= "<img src='".$imageBasePath."b.png' width=$fino height=$altura border=0>";
        $retorno .= "<img src='".$imageBasePath."p.png' width=$fino height=$altura border=0>";
        $retorno .= "<img src='".$imageBasePath."b.png' width=$fino height=$altura border=0>";

        while (strlen($texto) > 0) {
            $i = round(self::esquerda($texto, 2));
            $texto = self::direita($texto, strlen($texto) - 2);
            $f = $barcodes[$i];
            for ($i = 1; $i < 11; $i += 2) {
                if (substr($f, ($i - 1), 1) == '0') {
                    $f1 = $fino;
                } else {
                    $f1 = $largo;
                }
                $retorno .= "<img src='".$imageBasePath."p.png' width=$f1 height=$altura border=0>";

                if (substr($f, $i, 1) == '0') {
                    $f2 = $fino;
                } else {
                    $f2 = $largo;
                }
                $retorno .= "<img src='".$imageBasePath."b.png' width=$f2 height=$altura border=0>";
            }
        }

        $retorno .= "<img src='".$imageBasePath."p.png' width=$largo height=$altura border=0>";
        $retorno .= "<img src='".$imageBasePath."b.png' width=$fino height=$altura border=0>";
        $retorno .= "<img src='".$imageBasePath."p.png' width=1 height=$altura border=0>";

        return $retorno;
    }

    public static function esquerda($entra, $comp)
    {
        return substr($entra, 0, $comp);
    }

    public static function direita($entra, $comp)
    {
        return substr($entra, strlen($entra) - $comp, $comp);
    }
}
