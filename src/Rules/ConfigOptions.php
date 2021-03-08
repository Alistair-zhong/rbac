<?php

namespace Rbac\Rules;

use Rbac\Models\Config;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Validator;

/**
 * 单选或者多选类型时，选项配置验证规则
 * 必须至少要有一个类似 1=>值 的值
 *
 * Class ConfigSelectTypeOptions
 * @package Rbac\Rules
 */
class ConfigSelectTypeOptions implements Rule
{
    protected $errorMessage;

    public function passes($attribute, $value)
    {
        // 为空不验证
        if (!$value) {
            return true;
        }

        if (!is_string($value)) {
            return false;
        }

        $pairs = explode("\n", $value);
        foreach ($pairs as $pair) {
            $p = explode('=>', $pair);
            if (count($p) < 2) {
                return false;
            }
            $label = $p[1];
            // label 有就表示有效，有一个有效，则可以通过
            if ($label) {
                return true;
            }
        }

        return false;
    }

    public function message()
    {
        return '选项设置 无效。';
    }
}

class ConfigOptions implements Rule
{
    /**
     * @var string 配置类型
     */
    protected $type;
    protected $errorMessage;

    /**
     * Create a new rule instance.
     *
     * @param string $type
     *
     * @return void
     */
    public function __construct(string $type = null)
    {
        $this->type = $type;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (isset($validator) && $validator->fails()) {
            $this->errorMessage = $validator->getMessageBag()->first();
        }

        return !$this->errorMessage;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->errorMessage;
    }
}
