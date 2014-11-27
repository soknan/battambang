<?php
namespace Battambang\Cpanel\Validators;


class CommuneValidator extends \ValidatorAssistant
{
    protected function before()
    {
        \Rule::add('id')->required();
        \Rule::add('kh_name')->required();
        \Rule::add('en_name')->required();

        $this->rules = \Rule::get();
    }
}