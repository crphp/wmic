<?php

/** 
 * Classe utilizada para recuperar informações referentes ao adaptador de rede
 * 
 * @package     crphp
 * @subpackage  wmic
 * @author      Fábio J L Ferreira <contato@fabiojanio.com>
 * @license     MIT (consulte o arquivo license disponibilizado com este pacote)
 * @copyright   (c) 2016, Fábio J L Ferreira
 */

namespace Crphp\Wmic\Sistema;

use Crphp\Core\Sistema\Conector;
use Crphp\Core\Interfaces\Sistema\IpMacInterface;

class IpMac implements IpMacInterface
{  
    /**
     * Armazena as informações relacionadas a interface de rede
     *
     * @var object
     */
    private $ipMac;
    
    /**
     * Consulta os dados referentes a interface de rede
     * 
     * @param   \Crphp\Wmi\Conectores\Conector $conexao
     * @return  null
     */
    public function __construct(Conector $conexao)
    {
        $this->ipMac = $conexao->executar(
                                            "select
                                                Description,
                                                DNSHostName,
                                                DHCPEnabled,
                                                IPAddress,
                                                IPSubnet,
                                                DNSDomain,
                                                InterfaceIndex,
                                                IPSubnet
                                            from Win32_NetworkAdapterConfiguration
                                            where IPEnabled=1"
                                         );
    }
    
    /**
     * Retorna uma visão geral referente a interface de rede
     * 
     * @return array
     */
    public function detalhes()
    {
        foreach ($this->ipMac as $ipMac) {
            
            $ip = explode(',', substr($ipMac->IPAddress, 1, -1));
            
            $interface[$ipMac->InterfaceIndex] = [
                'interfaceDeRede' => $ipMac->Description,
                'hostName' => $ipMac->DNSHostName,
                'ipv4' => (string) $ip[0],
                'ipv6' => (string) $ip[1],
                'ipSubnet' => (string) $ipMac->IPSubnet,
                'dominio' => $ipMac->DNSDomain,
                'dhcp' => ($ipMac->DHCPEnabled) ? 'Ativo' : 'Inativo',
            ];
        }
        return $interface;
    }
}