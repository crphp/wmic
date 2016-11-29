<?php

/** 
 * Classe utilizada para recuperar informações referentes aos processos ativos 
 * no Sistema Operacional
 * 
 * @package     crphp
 * @subpackage  wmic
 * @author      Fábio J L Ferreira <contato@fabiojanio.com>
 * @license     MIT (consulte o arquivo license disponibilizado com este pacote)
 * @copyright   (c) 2016, Fábio J L Ferreira
 */

namespace Crphp\Wmic\Sistema;

use Crphp\Core\Sistema\Conector;
use Crphp\Wmic\Auxiliares\Transformar;
use Crphp\Core\Interfaces\Sistema\ProcessosInterface;

class Processos implements ProcessosInterface
{
    /**
     * Lista de processos ativos no SO
     *
     * @var object
     */
    private $instancia;
    
    /**
     * Critério de busca
     *
     * @var string|null
     */
    private $criterio;
    
    /**
     * Consulta os dados referentes a processos ativos no SO
     * 
     * @param \Crphp\Wmi\Conectores\Conector $conexao
     * @param string|int|null                $filtro
     */
    function __construct(Conector $conexao, $filtro = null)
    {
        if(is_string($filtro)) {
            $this->criterio = "and Name='{$filtro}'";
        } elseif (is_int($filtro)) {
            $this->criterio = "and ProcessID={$filtro}";
        } else {
            $this->criterio = null;
        }
        
        /*
         * Analisar troca de win32_process que passa uma visão de processo
         * para Win32_PerfFormattedData_PerfProc_Process ou ﻿Win32_PerfRawData_PerfProc_Process
         * que mostra uma visão de SO proxima ao task manager
         */
        $this->instancia = $conexao->executar(
                                                "select
                                                    Name,
                                                    ProcessID,
                                                    Priority,
                                                    WorkingSetSize,
                                                    CreationDate,
                                                    ExecutablePath
                                                  from Win32_Process
                                                  where processid <> 0 {$this->criterio}"
                                             );
    }
    
    public function killProcesso()
    {
        // em desenvolvimento
    }
    
    public function alterarPrioridade($prioridade = null)
    {
        // em desenvolvimento
    }
    
    /**
     * Retorna uma visão detalhada dos processos ativos no SO
     * 
     * @return array|null
     */
    public function detalhes()
    {
        foreach ($this->instancia as $p) {
            $processo[$p->ProcessId] = [
                "nome" => $p->Name,
                "Priority" => $p->Priority,
                "memoriaTotal" => Transformar::converterBytes($p->WorkingSetSize),
                "inicioDoProcesso" => Transformar::converterTimestamp($p->CreationDate),
                "path" => $p->ExecutablePath
            ];
        }
        return (isset($processo)) ? $processo : null;
    }
}