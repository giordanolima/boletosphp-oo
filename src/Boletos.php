<?php

namespace GiordanoLima\BoletosPHP;

class Boletos
{
    const BOLETOSPHP_ITAU = 'Itau';
    const BOLETOSPHP_SICREDI = 'Sicredi';

    private $banco;
    private $bancoCls;
    private $defaults;
    private $dadosBoleto;
    private $imageBasePath = '';
    private $requires = [
        'valor_boleto',
        'data_vencimento',
        'nosso_numero',
        'agencia',
        'conta',
        'conta_dv',
        'carteira',
        'identificacao',
        'cpf_cnpj',
    ];
    public $nossoNumero;

    /**
     * Novo boleto.
     *
     * @param constant $banco
     */
    public function __construct($banco)
    {
        $this->banco = strtoupper($banco);
        $this->bancoCls = __NAMESPACE__.'\\Bancos\\'.$banco;
        $this->defaults = [
            'numero_documento'   => '',
            'data_documento'     => date('d/m/Y'),
            'data_processamento' => date('d/m/Y'),
            'sacado'             => '',
            'endereco'           => '',
            'endereco1'          => '',
            'endereco2'          => '',
            'demonstrativo1'     => '',
            'demonstrativo2'     => '',
            'demonstrativo3'     => '',
            'instrucoes1'        => '',
            'instrucoes2'        => '',
            'instrucoes3'        => '',
            'instrucoes4'        => '',
            'quantidade'         => '',
            'valor_unitario'     => '',
            'aceite'             => '',
            'especie'            => 'R$',
            'especie_doc'        => '',
            'cidade_uf'          => '',
            'cedente'            => '',
        ];
    }

    public function setImageBasePath($value)
    {
        $this->imageBasePath = $value;
    }

    public function getImageBasePath()
    {
        return $this->imageBasePath;
    }

    public function getDadosBoleto()
    {
        return $this->dadosBoleto;
    }

    public function setData(array $dadosBoleto)
    {
        $requires = array_merge($this->requires, get_class_vars($this->bancoCls)['requires'] ?: []);
        foreach ($requires as $required) {
            if (!array_key_exists($required, $dadosBoleto)) {
                throw BoletosException::requiredField('É obrigatório o preenchimento do campo '.$required);
            }
        }
        $this->dadosBoleto = array_replace($this->defaults, $dadosBoleto);
    }

    public function render()
    {
        if (is_array($this->dadosBoleto)) {
            return call_user_func([$this->bancoCls, 'render'], $this);
        } else {
            throw BoletosException::requiredSetData('É preencher os dados do boleto atraves do metodo setData.');
        }
    }
}
