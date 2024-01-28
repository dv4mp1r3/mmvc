<?php

declare(strict_types=1);

namespace mmvc\core\routers;

use mmvc\core\Config;

/**
 * Обработка урл вида index/ctrlName/view/paramName/paramValue...
 * строка разбивается на пары (параметр-значение)
 */
class UrlFriendlyRouter extends BaseAbstractRouter
{

    private ?string $ctrlName = null;

    private ?string $action = null;

    private array $args = [];

    /**
     * @throws \Exception
     */
    public function __construct(Config $config)
    {
        parent::__construct($config);

        $dir = str_replace(DIRECTORY_SEPARATOR, '/', MMVC_ROOT_DIR);
        $url = $this->getUrl();

        $dirArr = explode('/', $dir);
        $urlArr = explode('/', $url);

        $result = [];

        foreach ($urlArr as $param) {
            if (in_array($param, $dirArr) || strlen($param) === 0) {
                continue;
            }
            $result[] = $param;
        }

        if (count($result) == 0) {
            return;
        }

        $this->setProperties($url, $result);
    }

    /**
     * @throws \Exception
     */
    protected function setProperties(string $url, array $explodedUrl) : void
    {
        $this->setControllerName($url, $explodedUrl);
        $this->setAction($url, $explodedUrl);
        $this->setArgs($url, $explodedUrl);
    }

    /**
     * @throws \Exception
     */
    protected function setControllerName(string $url, array $explodedUrl) : void
    {
        switch ($explodedUrl[0]) {
            case 'error':
            case 'gen':
            case 'cli':
                $this->ctrlName = 'mmvc\\controllers\\' . ucfirst($explodedUrl[0]) . 'Controller';
                break;
            default:
                if (!defined('MMVC_PROJECT_NAMESPACE')) {
                    throw new \Exception("constant MMVC_PROJECT_NAMESPACE undefined. Can not to route {$explodedUrl[0]}->{$explodedUrl[1]}");
                }
                $this->ctrlName = MMVC_PROJECT_NAMESPACE . '\\controllers\\' . ucfirst($explodedUrl[0]) . 'Controller';
                break;
        }
    }

    protected function setAction(string $url, array $explodedUrl) : void
    {
        $this->action = $explodedUrl[1] ?? null;
    }

    protected function setArgs(string $url, array $explodedUrl) : void
    {
        $count = count($explodedUrl);
        if ($count <= 2) {
            return;
        }
        for ($i = 2; $i < $count; $i++) {
            if (isset($explodedUrl[$i]) && isset($explodedUrl[$i + 1])) {
                $this->args[$explodedUrl[$i]] = $explodedUrl[$i + 1];
                $i++;
            }
        }
    }

    protected function getUrl(): string
    {
        return str_replace($_SERVER['SCRIPT_NAME'], "", $_SERVER['REQUEST_URI']);
    }


    public function getActionName(): ?string
    {
        return is_null($this->action) ? $this->action : 'action' . ucfirst($this->action);
    }

    public function getControllerName(): ?string
    {
        return $this->ctrlName;
    }

    public function getArgs(): array
    {
        return $this->args;
    }
}
