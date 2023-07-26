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

    private array $params = [];

    public function __construct(Config $config)
    {
        parent::__construct($config);

        $dir = str_replace(DIRECTORY_SEPARATOR, '/', MMVC_ROOT_DIR);
        $url = str_replace($_SERVER['SCRIPT_NAME'], "", $_SERVER['REQUEST_URI']);

        $dirArr = explode('/', $dir);
        $urlArr = explode('/', $url);

        $result = [];

        foreach ($urlArr as $param) {
            if (in_array($param, $dirArr) || strlen($param) === 0) {
                continue;
            }
            $result[] = $param;
        }

        $count = count($result);

        if ($count == 0) {
            return;
        }

        switch ($result[0]) {
            case 'error':
            case 'gen':
            case 'cli':
                $this->ctrlName = 'mmvc\\controllers\\' . ucfirst($result[0]) . 'Controller';
                break;
            default:
                if (!defined('MMVC_PROJECT_NAMESPACE')) {
                    throw new \Exception("constant MMVC_PROJECT_NAMESPACE undefined. Can not route {$result[0]}->{$result[1]}");
                }
                $this->ctrlName = MMVC_PROJECT_NAMESPACE . '\\controllers\\' . ucfirst($result[0]) . 'Controller';
                break;
        }
        $this->action = $result[1];

        if ($count > 2) {
            for ($i = 2; $i < $count; $i++) {
                if (isset($result[$i]) && isset($result[$i + 1])) {
                    $this->params[$result[$i]] = $result[$i + 1];
                    $i++;
                }
            }
        }
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
        return $this->params;
    }
}
