<?php

namespace GiordanoLima\BoletosPHP\Bancos;

class Itau
{
    public static $requires = [];

    public static function render($boleto)
    {
        $dadosboleto = $boleto->getDadosBoleto();
        $codigobanco = '341';
        $nummoeda = '9';
        $codigo_banco_com_dv = self::geraCodigoBanco($codigobanco);
        $fator_vencimento = self::fator_vencimento($dadosboleto['data_vencimento']);

        $valor = self::formata_numero($dadosboleto['valor_boleto'], 10, 0, 'valor');
        $agencia = self::formata_numero($dadosboleto['agencia'], 4, 0);
        $conta = self::formata_numero($dadosboleto['conta'], 5, 0);
        $conta_dv = self::formata_numero($dadosboleto['conta_dv'], 1, 0);
        $carteira = $dadosboleto['carteira'];
        $nnum = self::formata_numero($dadosboleto['nosso_numero'], 8, 0);

        $codigo_barras = $codigobanco.$nummoeda.$fator_vencimento.$valor.$carteira.$nnum.self::modulo_10($agencia.$conta.$carteira.$nnum).$agencia.$conta.self::modulo_10($agencia.$conta).'000';
        $dv = self::digitoVerificador_barra($codigo_barras);
        $linha = substr($codigo_barras, 0, 4).$dv.substr($codigo_barras, 4, 43);

        $nossonumero = $carteira.'/'.$nnum.'-'.self::modulo_10($agencia.$conta.$carteira.$nnum);
        $agencia_codigo = $agencia.' / '.$conta.'-'.self::modulo_10($agencia.$conta);

        $dadosboleto['codigo_barras'] = $linha;
        $dadosboleto['linha_digitavel'] = self::monta_linha_digitavel($linha); // verificar
        $dadosboleto['agencia_codigo'] = $agencia_codigo;
        $dadosboleto['codigo_banco_com_dv'] = $codigo_banco_com_dv;
        $dadosboleto['nosso_numero'] = $nossonumero;
        $boleto->nossoNumero = $nossonumero;

        ob_start();
        require __DIR__.'/../includes/layout_itau.php';
        $r = ob_get_contents();
        ob_end_clean();

        return $r;
    }

    /* ======------- FUNÇOES -------======= */

    public static function geraCodigoBanco($numero)
    {
        $parte1 = substr($numero, 0, 3);
        $parte2 = self::modulo_11($parte1);

        return $parte1.'-'.$parte2;
    }

    public static function fator_vencimento($data)
    {
        $data = explode('/', $data);
        $ano = $data[2];
        $mes = $data[1];
        $dia = $data[0];

        return abs((self::dateToDays('1997', '10', '07')) - (self::dateToDays($ano, $mes, $dia)));
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
            if ($digito == 10) {
                $digito = 0;
            }

            return $digito;
        } elseif ($r == 1) {
            $resto = $soma % 11;

            return $resto;
        }
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

    public static function digitoVerificador_barra($numero)
    {
        $resto2 = self::modulo_11($numero, 9, 1);
        $digito = 11 - $resto2;
        if ($digito == 0 || $digito == 1 || $digito == 10 || $digito == 11) {
            $dv = 1;
        } else {
            $dv = $digito;
        }

        return $dv;
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

    public static function monta_linha_digitavel($codigo)
    {
        $banco = substr($codigo, 0, 3);
        $moeda = substr($codigo, 3, 1);
        $ccc = substr($codigo, 19, 3);
        $ddnnum = substr($codigo, 22, 2);
        $dv1 = self::modulo_10($banco.$moeda.$ccc.$ddnnum);
        $resnnum = substr($codigo, 24, 6);
        $dac1 = substr($codigo, 30, 1);
        $dddag = substr($codigo, 31, 3);
        $dv2 = self::modulo_10($resnnum.$dac1.$dddag);
        $resag = substr($codigo, 34, 1);
        $contadac = substr($codigo, 35, 6);
        $zeros = substr($codigo, 41, 3);
        $dv3 = self::modulo_10($resag.$contadac.$zeros);
        $dv4 = substr($codigo, 4, 1);
        $fator = substr($codigo, 5, 4);
        $valor = substr($codigo, 9, 10);
        $campo1 = substr($banco.$moeda.$ccc.$ddnnum.$dv1, 0, 5).'.'.substr($banco.$moeda.$ccc.$ddnnum.$dv1, 5, 5);
        $campo2 = substr($resnnum.$dac1.$dddag.$dv2, 0, 5).'.'.substr($resnnum.$dac1.$dddag.$dv2, 5, 6);
        $campo3 = substr($resag.$contadac.$zeros.$dv3, 0, 5).'.'.substr($resag.$contadac.$zeros.$dv3, 5, 6);
        $campo4 = $dv4;
        $campo5 = $fator.$valor;

        return "$campo1 $campo2 $campo3 $campo4 $campo5";
    }

    public static function fbarcode($valor, $imageBasePath)
    {
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

        $retorno = '';

        $retorno .= "<img src='".$imageBasePath."p.png' width='$fino' height='$altura' border=0>";
        $retorno .= "<img src='".$imageBasePath."b.png' width='$fino' height='$altura' border=0>";
        $retorno .= "<img src='".$imageBasePath."p.png' width='$fino' height='$altura' border=0>";
        $retorno .= "<img src='".$imageBasePath."b.png' width='$fino' height='$altura' border=0>";

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
                $retorno .= "<img src='".$imageBasePath."p.png' width='$f1' height='$altura' border='0'>";
                if (substr($f, $i, 1) == '0') {
                    $f2 = $fino;
                } else {
                    $f2 = $largo;
                }
                $retorno .= "<img src='".$imageBasePath."b.png' width='$f2' height='$altura' border='0'>";
            }
        }
        $retorno .= "<img src='".$imageBasePath."p.png' width='$largo' height='$altura' border='0'>";
        $retorno .= "<img src='".$imageBasePath."b.png' width='$fino' height='$altura' border='0'>";
        $retorno .= "<img src='".$imageBasePath."p.png' width='1' height='$altura' border='0'>";

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
