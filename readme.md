# crphp/wmic
Está biblioteca faz uso do **WQL (WMI Query Language)** para disparar exclusivamente **consultas** 
remotas a máquinas Windows.

>**crphp/wmi** e **crphp/wmic** possuem os mesmos recursos de consulta, porém, somente 
[crphp/wmi](https://github.com/crphp/wmi) tem a capacidade de gerenciar recursos remotos, 
como parar serviços, matar processos etc.

Está biblioteca segue os padrões descritos na [PSR-2](http://www.php-fig.org/psr/psr-2/), logo, 
isso implica que a mesma está em conformidade com a [PSR-1](http://www.php-fig.org/psr/psr-1/).

As palavras-chave "DEVE", "NÃO DEVE", "REQUER", "DEVERIA", "NÃO DEVERIA", "PODERIA", "NÃO PODERIA", 
"RECOMENDÁVEL", "PODE", e "OPCIONAL" neste documento devem ser interpretadas como descritas no 
[RFC 2119](http://tools.ietf.org/html/rfc2119). Tradução livre [RFC 2119 pt-br](http://rfc.pt.webiwg.org/rfc2119).

1. [Referências](#referencia)
1. [Funcionalidades](#funcionalidades)
1. [Requisitos (recomendados)](#requisitos)
1. [Compilando e testando o WMIC](#compilando)
1. [Preparando as máquinas cliente](#preparando-a-maquina-cliente)
1. [Baixando o pacote crphp/wmic para o servidor](#wmic)
1. [Exemplos de uso](#exemplos)
1. [Licença (MIT)](#licenca)

## 1 - <a id="referencias"></a>Referências
 - [PSR-1](http://www.php-fig.org/psr/psr-1/)
 - [PSR-2](http://www.php-fig.org/psr/psr-2/)
 - [RFC 2119](http://tools.ietf.org/html/rfc2119). Tradução livre [RFC 2119 pt-br](http://rfc.pt.webiwg.org/rfc2119)

## 2 - <a id="funcionalidades"></a>Funcionalidades
- [x] Consultar CPU
- [x] Consultar RAM
- [x] Consultar Disco Rígido
- [x] Consultar Serviço
- [x] Listar Serviços
- [x] Consultar processo
- [x] Listar processos
- [x] Transformação de timestamp Windows para data/hora
- [ ] Listar sessões

## 3 - <a id="preparando-o-servidor"></a>Preparando o servidor
> :exclamation: Os requisitos sugeridos logo abaixo representam as versões utilizadas em nosso ambiente 
de desenvolvimento e produção, logo não garantimos que a solução aqui apresentada irá rodar integralmente 
caso as versões dos elementos abaixo sejam outras.

### 3.1 - <a id="requisitos"></a>Requisitos (recomendados)
Servidor
- REQUER Debian >= 8.5.0 (32 ou 64 Bits)
- REQUER wmi-1.3.14 (código fonte)
- REQUER Apache >= 2.4.10
- REQUER PHP >= 5.5.12

Cliente
- REQUER Windows (desktop >= Windows 7 ou Windows Server >= 2003)
- NÃO REQUER a instalação de nenhum componente

## 4 - <a id="compilando"></a>Compilando e testando o WMIC
Estou presupondo que você já tem uma distribuição GNU/Linux, preferencialmente 
Debian, com Apache e PHP devidamente configurados.

### 4.1 - autoconf
Instalando autoconf, make e gcc:
```
# apt-get install autoconf
# apt-get install make
# apt-get install gcc
```

### 4.2 - wmi-1.3.14
**Etapa 1** - download e extração dos fonts do WMIC:
```bash
$ wget http://www.openvas.org/download/wmi/wmi-1.3.14.tar.bz2
$ tar -xvf wmi-1.3.14.tar.bz2
```

**Etapa 2** - Configurar GNUmakefile
```bash
$ cd wmi-1.3.14/
$ sed -i "1s/^/ZENHOME=..\/..\n/" GNUmakefile
```

**Etapa 3** - Compilar WMIC
```bash
$ make "CPP=gcc -E -ffreestanding"
```

Ao fim da execução do comando make você saberá que terá corrido tudo bem caso as ultimas 3 linhas de output sejam algo parecido com:
```
cp: o alvo “../../lib/python” não é um diretório
GNUmakefile:43: recipe for target 'pywmi-installed' failed
make: *** [pywmi-installed] Error 1
```

**Etapa 4** - Renomear binário e testando:
```bash
$ mv bin wmic
$ ./wmic -U usuario%'senha' //ip "SELECT Caption FROM Win32_OperatingSystem"
```

Output do comando executado acima:
```
CLASS: Win32_OperatingSystem
Caption
Microsoft« Windows Server« 2008 Enterprise 
```

**Obs**: Caso ocorra erro de conexão consulte o tópico 5 para liberar regra de firewall no cliente

**Etapa 5** - Adicionando binário a um diretório de "sistema":
```bash
$ mv wmic /usr/bin/
```

## 5 - <a id="preparando-a-maquina-cliente"></a>Preparando a máquina cliente
Essas configurações DEVE ser executadas em todas as máquinas (cliente) alvos de gerenciamento remoto.

Caminho para as regras de firewall:
```
Painel de Controle > Ferramentas Administrativas > Firewall do Windows com Segurança Avançada
```

Para permitir as conexões externa teremos que habilitar as **Regras de Entrada**:
```
Instrumentação de Gerenciamento do Windows (DCOM-In)
Instrumentação de Gerenciamento do Windows (WMI-In)
```

E as **Regras de Saída**:
```
Instrumentação de Gerenciamento do Windows (WMI-Saída)
```

Para não ter problema, é RECOMENDÁVEL que o usuário de conexão remota tenha privilégio de administrador 
na máquina de destino. Obviamente você PODE configurar o contexto de acesso caso tenha alguma familiridade 
com este assunto.

## 6 - <a id="wmic"></a>Baixando o pacote crphp/wmic para o servidor
Para a etapa abaixo estou pressupondo que você tenha o composer instalado e saiba utilizá-lo:
```
composer require crphp/wmic
```

Ou se preferir criar um projeto:
```
composer create-project --prefer-dist crphp/wmic nome_projeto
```

Caso ainda não tenha o composer instalado, obtenha este em: https://getcomposer.org/download/

## 7 - <a id="exemplos"></a>Exemplos de uso
**Consultar CPU**:
```php
use Crphp\Wmic\Sistema\Cpu;
use Crphp\Wmic\Conector\Wmic;

$wmi = new Wmic;
$wmi->conectar('ip_ou_hostname', 'usuario', 'senha');

if($wmi->status()) {
    $cpu = new Cpu($wmi);
    echo "<pre>";
    print_r($cpu->detalhes());
    echo "</pre>";   
} else {
    echo $wmi->mensagemErro();
}
```

Todas as demais classes funcionam praticamente da mesma forma.

**Consultar Disco Rígido**
```php
use Crphp\Wmic\Conector\Wmic;
use Crphp\Wmic\Sistema\DiscoRigido;

$wmi = new Wmic;
$wmi->conectar('ip_ou_hostname', 'usuario', 'senha');

if($wmi->status())
{
    $obj = new DiscoRigido($wmi, "C"); // a unidade pode ser omitida
    echo "<pre>";
    print_r($obj->detalhes());
    echo "</pre>";   
} else {
    echo $wmi->mensagemErro();
}
```
> Você DEVE sempre instânciar o conector Wmi e a classe referente ao elemento que deseja manipular

**Também é possível executar suas próprias consultas customizadas**
```php
use Crphp\Wmic\Conector\Wmic;

$wmi = new Wmic;
$wmi->conectar('ip_ou_hostname', 'usuario', 'senha');

if($wmi->status()) {
    $memoria = $wmi->executar("select AvailableBytes from Win32_PerfRawData_PerfOS_Memory");
    // Será retornado um objeto em caso de sucesso ou uma string em caso de erro
} else {
    echo $wmi->mensagemErro();
}
```

## 8 - <a id="licenca">Licença (MIT)
Para maiores informações, leia o arquivo de licença disponibilizado junto desta biblioteca.