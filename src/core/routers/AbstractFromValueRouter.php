<?php

declare(strict_types=1);

namespace mmvc\core\routers;

use mmvc\core\Config;

abstract class AbstractFromValueRouter extends BaseAbstractRouter
{
    abstract function getValue() : string;

    private string $url;

    private ?string $ctrlName;

    private ?string $action;

    public function __construct(Config $config, string $delimiter = '-')
    {
        parent::__construct($config);
        $this->url = $this->getValue();
        $delimiterPos = strpos($this->url, $delimiter);
        $ctrl = htmlspecialchars(substr( $this->url, 0, $delimiterPos));
        $this->action = htmlspecialchars(substr($this->url, $delimiterPos + 1));

        $expectedFilename = MMVC_ROOT_DIR.
            DIRECTORY_SEPARATOR.'controllers'.
            DIRECTORY_SEPARATOR.ucfirst($ctrl).'Controller.php';

        if (defined('MMVC_PROJECT_NAMESPACE') && file_exists($expectedFilename)) {
            $this->ctrlName = MMVC_PROJECT_NAMESPACE . '\\controllers\\' . ucfirst($ctrl) . 'Controller';
        } else {
            $this->ctrlName = 'mmvc\\controllers\\' . ucfirst($ctrl) . 'Controller';
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
        return [];
    }
}
