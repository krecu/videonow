<?php

namespace VideoNowBundle\RuleInvoke;

use Symfony\Component\HttpFoundation\Request;

/**
 * Интерфейс для дополнительной валидации правила
 *
 * Interface RuleInvokeInterface
 * @package VideoNowBundle\RuleInvoke
 */
interface RuleInvokeInterface {

    /**
     * Проверка правила
     *
     * @param array $rule
     * @param Request $request
     * @return mixed
     */
    public static function check($rule, $request);

}