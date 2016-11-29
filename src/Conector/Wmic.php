<?php

/**
 * Essa classe fornece uma interface de conexão WMI com máquinas Windows,
 * possibilitando dessa forma a execução de comandos remotos de forma
 * rápida, eficiente e segura. Atualmente só é possível efetuar CONSULTAS
 * 
 * @package     crphp
 * @subpackage  wmic
 * @author      Fábio J L Ferreira <contato@fabiojanio.com>
 * @license     MIT (consulte o arquivo "license" disponibilizado com este pacote)
 * @copyright   (c) 2016, Fábio J L Ferreira
 */


namespace Crphp\Wmic\Conector;

use \Exception;
use \RuntimeException;
use Crphp\Core\Sistema\Conector;

class Wmic extends Conector
{        
    /**
     * Estabelece conexão com máquinas Windows via chamada COM
     * 
     * @param   string  $host
     * @param   string  $usuario
     * @param   string  $senha
     * @param   int     $porta
     * @param   int     $timeout
     * @return  null
     */
    public function conectar($host, $usuario = null, $senha = null, $porta = 135, $timeout = 10)
    {
        try {
            /**
             * Testa conectividade com host alvo
             * 
             * @param string $host
             * @param string $porta
             * @param int    $errno   valor de sistema
             * @param string $errstr  mensagem de sistema
             * @param int    $timeout tempo máximo a esperar
             */
            if (!$socket = @fsockopen($host, $porta, $errno, $errstr, $timeout)) {
                // @see https://msdn.microsoft.com/en-us/library/windows/desktop/ms740668(v=vs.85).aspx
                $dic = [
                    110   => "Time Out ao tentar se conectar ao destino: <b>{$host}</b>",
                    113   => "Não existem rotas para o destino: <b>{$host}</b>",
                    10056 => "Já existe uma conexão socket aberta para o host <b>{$host}</b>",
                    10057 => "Não foi possível conectar ao socket na chamada do host <b>{$host}</b>",
                    10060 => "Time Out ao tentar se conectar ao destino: <b>{$host}</b>",
                    10061 => "Conexão recusada pelo destino: <b>{$host}</b>"
                ];

                $mensagem = (array_key_exists($errno, $dic)) ? strtr($errno, $dic) : $errstr;

                throw new RuntimeException("Erro ({$errno}): {$mensagem}");
            }

            fclose($socket); // Fecha o socket aberto anteriormente
            
            $this->conexao = "wmic --namespace='root\cimv2' -U {$usuario}%{$senha} //{$host} ";
        } catch (Exception $e) {
            $this->mensagemErro = $e->getMessage();
        }        
    }

    /**
     * Executa a instrução no host alvo
     * 
     * @param   string $instrucao
     * @return  array
     * @throws  \Exception
     */
    public function executar($instrucao)
    {
        try {                    
            if (empty($resultado = shell_exec($this->conexao . "\"$instrucao\""))) {
                throw new Exception('Ocorreu um erro na execução do WMIC.');
            }
            
            if(strpos($resultado, 'librpc/rpc/dcerpc_connect.c:329:dcerpc_pipe_connect_ncacn_ip_tcp_recv') == true) {
                throw new Exception('Não foi possível se conectar ao host de destino.');
            }
            
            return $this->convertToObject($this->parse($resultado));
        } catch (Exception $e) {
            return 'Erro fatal: ' . $e->getMessage() . "\n";
        }
    }
        
    /**
     * O WMIC retorna tudo em formato de string, esse método faz o parse destes
     * valores para um formato de array
     * 
     * @param   string $resultado
     * @return  array
     */
    protected function parse($resultado)
    {
        ## Remove a primeira e a ultima linha
        $var = trim(preg_replace('/^.+\n/', '', $resultado));

        # Transforma a lista retornada em array
        $wmic = explode("\n", $var);

        # Pega a primeira linha do array e cria um novo array de titulos
        $titulos = explode("|", $wmic[0]);
        unset($wmic[0]);
        $wmic = array_values($wmic);

            for ($i = 0; $i < sizeof($wmic); $i++) {
                $campos = explode("|", $wmic[$i]);
                for ($y = 0; $y < sizeof($campos); $y++) {
                    $novo[$i][$titulos[$y]] = $campos[$y];
                }
            }
        return $novo;
    }
    
    /**
     * Este método cria um array de objetos
     * 
     * @param   array $array
     * @return  object
     */
    protected function convertToObject($array) {
        $object = new \stdClass();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $value = $this->convertToObject($value);
            }
            $object->$key = $value;
        }
        return $object;
    }
}