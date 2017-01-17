<?php

namespace VideoNowBundle\RuleInvoke;

/**
 * Class ExampleRuleInvoke
 * @package VideoNowBundle\RuleInvoke
 */
class ExampleRuleInvoke implements RuleInvokeInterface {

    /**
     * Простая проверка что метод являеться GET
     *
     * @param array $rule
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return bool
     */
    public static function check($rule, $request)
    {
        return $request->getMethod() == 'GET';
    }
}