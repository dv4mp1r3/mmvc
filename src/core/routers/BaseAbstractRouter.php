<?php

declare(strict_types=1);

namespace mmvc\core\routers;

use mmvc\core\Config;
use mmvc\core\AccessChecker;
use mmvc\controllers\BaseController;
use mmvc\controllers\events\ActionEventAfter;
use mmvc\controllers\events\ActionEventBefore;

abstract class BaseAbstractRouter implements RouterInterface, RequestParserInterface
{
    /**
     * @var Config
     */
    private Config $config;

    /**
     * Конструктор роутера (обработка ссылок, выдача нужной страницы в зависимости от url)
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Обработка и вызов нужного действия для контроллера
     * @return string результат выполнения действия (шаблон страницы, json для ajax
     * и т.д.)
     * @throws \Exception выбрасывается если не найдено действие или контроллер
     */
    protected function callAction(BaseController $ctrl, string $action): ?string
    {
        if (!method_exists($ctrl, $action)) {
            throw new \Exception("Method $action in {$ctrl->getName()} is undefined");
        }

        if ($ctrl instanceof ActionEventBefore) {
            $ctrl->beforeAction($action);
        }
        $actionResult = call_user_func(array($ctrl, $action));
        if ($ctrl instanceof ActionEventAfter) {
            $ctrl->afterAction($action, $actionResult);
        }
        return $actionResult;
    }

    /**
     * Передача управления контроллеру
     * после обработки урла в конструкторе
     * @throws \Exception
     */
    public function route(): void
    {
        $action = $this->getActionName();
        $ctrlName = $this->getControllerName();
        if (is_null($ctrlName) || is_null($action)) {
            $defaultAction = $this->getDefaultAction();
            $action = $defaultAction['action'];
            $ctrlName = $defaultAction['controller'];
        }
        $ctrl = new $ctrlName($this->config);

        if (AccessChecker::checkAccess($ctrl, $action)) {
            echo $this->callAction($ctrl, $action);
        }
    }

    /**
     * @throws \Exception
     */
    protected function getDefaultAction(): array
    {
        $defaultAction = $this->config->getValueByKey('defaultAction');
        if (empty($defaultAction)) {
            throw new \Exception('defaultAction is undefined');
        }
        return $defaultAction;
    }
}
